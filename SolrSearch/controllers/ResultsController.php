<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


class SolrSearch_ResultsController
    extends Omeka_Controller_AbstractActionController
{


    /**
     * Cache the facets table.
     */
    public function init()
    {
        $this->_fields = $this->_helper->db->getTable('SolrSearchField');
    }


    /**
     * Intercept queries from the simple search form.
     */
    public function interceptorAction()
    {
        $this->_redirect('solr-search?'.http_build_query(array(
            'q' => $this->_request->getParam('query')
        )));
    }


    /**
     * Display Solr results.
     */
    public function indexAction()
    {

        // Get pagination settings.
        $limit = 20;
        $page  = $this->_request->page ? $this->_request->page : 1;
        $start = ($page-1) * $limit;

        // Execute the query.
        $results = $this->_search($start, $limit);

        // Passage des facettes à la vue
        $response = json_decode($results->getRawResponse());

        $facets = array();

        // Facette de dates

        $rawDateFacet = $response->facet_counts->facet_ranges->date;
        $dateGap = $rawDateFacet->gap;
        unset($rawDateFacet->gap);
        unset($rawDateFacet->start);
        unset($rawDateFacet->end);

        $dateFacets = new stdClass();
        $dateFacets->key = 'date';
        $dateFacets->name = __('Par période');
        $dateFacets->facets = array();
        foreach ($rawDateFacet->counts as $date => $resultCount) {

            $dateFacet = new stdClass();
            $dateFacet->startDate = new DateTime($date);
            $dateFacet->endDate = clone $dateFacet->startDate;
            $dateFacet->endDate->modify($dateGap);
            $dateFacet->endDate->modify('-1second'); // On exclut la date de fin
            $dateFacet->resultCount = $resultCount;
            $dateFacet->label = $dateFacet->startDate->format('Y') . ' - ' . $dateFacet->endDate->format('Y');
            $dateFacet->value = '[' . $dateFacet->startDate->format('Y-m-d\TH:i:s\Z') . ' TO ' . $dateFacet->endDate->format('Y-m-d\TH:i:s\Z') . ']';
            $dateFacet->addUrl = SolrSearch_Helpers_Facet::addFacetUrl($dateFacets->key, $dateFacet->value);
            $dateFacets->facets[] = $dateFacet;
        }

        $facets[] = $dateFacets;

        // Facette de collection
        $rawCollectionFacet = $response->facet_counts->facet_fields->id_collection;
        $collectionFacets = new stdClass();
        $collectionFacets->key = 'id_collection';
        $collectionFacets->name = __('Par titre');
        $collectionFacets->facets = array();
        foreach ($rawCollectionFacet as $id_collection => $resultCount) {

            $collectionFacet = new stdClass();
            $collectionFacet->resultCount = $resultCount;
            $collectionFacet->value = $id_collection;
            $collectionFacet->label = SolrSearch_Helpers_Facet::getFacetLabel($collectionFacets->key, $collectionFacet->value);
            $collectionFacet->addUrl = SolrSearch_Helpers_Facet::addFacetUrl($collectionFacets->key, $collectionFacet->value);
            $collectionFacets->facets[] = $collectionFacet;
        }

        $facets[] = $collectionFacets;


        // Récupération des facette appliquées
        $appliedFacets = array();
        $appliedFacetKeys = array();

        $queryStringFragments = SolrSearch_Helpers_Facet::parseQueryString();

        foreach ($queryStringFragments['fq'] as $key => $value) {
            $appliedFacet = new stdClass();
            $appliedFacet->key = $key;
            $appliedFacet->value = $value;
            $appliedFacet->removeUrl = SolrSearch_Helpers_Facet::removeFacetUrl($key);
            $appliedFacet->label = SolrSearch_Helpers_Facet::getFacetLabel($key, $value);
            $appliedFacets[] = $appliedFacet;
            $appliedFacetKeys[] = $appliedFacet->key;
        }

        // S'il n'y a aucune facette disponibles, on l'indique
        $facetteDisponible = false;
        foreach ($facets as $facet) {
            if (!in_array($facet->key, $appliedFacetKeys)) {
                $facetteDisponible = true;
                break;
            }
        }

        // Set the pagination.
        Zend_Registry::set('pagination', array(
            'page'          => $page,
            'total_results' => $results->response->numFound,
            'per_page'      => $limit
        ));


        // Push results to the view.
        $this->view->results = $results;
        $this->view->facets = $facets;
        $this->view->appliedFacets = $appliedFacets;
        $this->view->appliedFacetKeys = $appliedFacetKeys;
        $this->view->facetteDisponible = $facetteDisponible;

    }


    /**
     * Pass setting to Solr search
     *
     * @param int $offset Results offset
     * @param int $limit  Limit per page
     * @return SolrResultDoc Solr results
     */
    protected function _search($offset, $limit)
    {

        // Connect to Solr.
        $solr = SolrSearch_Helpers_Index::connect();

        // Get the parameters.
        $params = $this->_getParameters();

        // Construct the query.
        $query = $this->_getQuery();

        // Execute the query.
        return $solr->search($query, $offset, $limit, $params);

    }


    /**
     * Form the complete Solr query.
     *
     * @return string The Solr query.
     */
    protected function _getQuery()
    {

        // Get the `q` GET parameter.
        $query = $this->_request->q;

        // If defined, replace `:`; otherwise, revert to `*:*`
        if (!empty($query)) $query = str_replace(':', ' ', $query);
        else $query = '*:*';

        // Récupère les FQ de l'URL
        $fq = SolrSearch_Helpers_Facet::getFilters();
        if (!empty($fq)) $query .= ' AND ' . implode(' AND ', $fq);

        return $query;

    }


    /**
     * Construct the Solr search parameters.
     *
     * @return array Array of fields to pass to Solr
     */
    protected function _getParameters()
    {

        $parameters = array(

            'facet'          => 'true',

            // Récupération du score
            // 'fl' => '*,score',

            // Ajout de la facette de dates
            'facet.range' => 'date',
            'facet.range.start' => '1800-01-01T00:00:00Z',
            'facet.range.end' => 'NOW',
            'facet.range.gap' => '+10YEAR',

            // Ajout de la facette de collections
            'facet.field' => 'id_collection',

            // Les facettes sont en dur, pas de gestion dans le BO
            'facet.mincount' => 1,
            'facet.limit'    => get_option('solr_search_facet_limit'),
            'facet.sort'     => get_option('solr_search_facet_sort'),
            'hl'             => get_option('solr_search_hl')?'true':'false',
            'hl.snippets'    => get_option('solr_search_hl_snippets'),
            'hl.fragsize'    => get_option('solr_search_hl_fragsize'),
            'hl.fl'          => '*_t'

        );

        // Récupère le champ sort
        $sort = $this->_request->sort;

        if (!empty($sort)) {
            $parameters['sort'] = $sort;
        }

        return $parameters;

    }


}
