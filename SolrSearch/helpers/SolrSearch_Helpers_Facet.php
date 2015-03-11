<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


class SolrSearch_Helpers_Facet
{


    /**
     * Convert $_GET into an array with exploded facets.
     *
     * @return array The parsed parameters.
     */
    public static function parseFacets()
    {

        $facets = array();

        if (array_key_exists('facet', $_GET)) {

            // Extract the field/value facet pairs.
            preg_match_all('/(?P<field>[\w]+):"(?P<value>[\w\s]+)"/',
                $_GET['facet'], $matches
            );

            // Collapse into an array of pairs.
            foreach ($matches['field'] as $i => $field) {
                $facets[] = array($field, $matches['value'][$i]);
            }

        }

        return $facets;

    }

    /**
     * Sur BMN, la gstion des facettes se fait en fait avec des fq (filter query).
     * Cette fonction parse l'url et met les fq de côté pour pouvoir les gérer
     * facilement.
     * Elle retourne un tableau de cette forme :
     * array(
     *     'fq' => les différentes fq...
     *     'autre' => les autres paramètres d'url
     * )
     */
    public static function parseQueryString() {
        $queryString = urldecode($_SERVER['QUERY_STRING']);
        $queryFrag = explode('&', $queryString);

        $fragments = array(
            'fq' => array(),
            'autre' => array(),
        );


        foreach ($queryFrag as $fragment) {
            if (empty($fragment)) {
                continue;
            }
            if (preg_match('/^fq=([^:]+):([^&]+)$/', $fragment, $matches)) {
                $fragments['fq'][$matches[1]] = $matches[2];
            }
            else if (preg_match('/^([^=]+)=(.+)$/', $fragment, $matches)) {
                $fragments['autre'][$matches[1]] = $matches[2];
            }
            else {
                $fragments['autre'][] = $fragment;
            }
        }
        return $fragments;
    }

    /**
     * Retourne la partie FilterQuery de L'URL Solr sous forme d'un tableau
     */
    public static function getFilters() {

        $fragments = self::parseQueryString();

        $filters = array();

        foreach ($fragments['fq'] as $key => $value) {
            $filters[] = "$key:$value";
        }

        return $filters;
    }

    /**
     * Reconstruit la queryString à partir d'un tableau issu de parseQueryString
     */
    public static function buildQueryString($fragments) {

        $queryString = '?';

        foreach ($fragments['fq'] as $key => $value) {
            $queryString .= "fq=$key:$value&";
        }

        foreach ($fragments['autre'] as $key => $value) {

            if (!is_int($key)) {
                $queryString .= "$key=$value&";
            }
            else {
                $queryString .= "$value&";
            }
        }

        $queryString = rtrim($queryString, '&');

        return $queryString;
    }

    /**
     * Retourne une URL complète à partir d'une queryString
     */
    public static function getQueryStringUrl($queryString) {

        $results = url('solr-search/results/index');
        return htmlspecialchars($results . $queryString);
    }

    /**
     * Ajoute une facette (fq) à l'URL actuelle
     * (retourne la nouvelle URL)
     */
    public static function getFacetLabel($facetKey, $value) {

        $label = __('Filtre inconnu');

        switch ($facetKey) {
            case 'date':
                // Période : https://regex101.com/r/qV8cZ4/4
                if (preg_match('/^^\["?(\d{4})-(\d{2})-(\d{2})T\d{2}:\d{2}:\d{2}Z"? TO "?(\d{4})-(\d{2})-(\d{2})T\d{2}:\d{2}:\d{2}Z"?\]$$/', $value, $matches)) {

                    $startYear = $matches[1];
                    $startMonth = $matches[2];
                    $startDay = $matches[3];
                    $endYear = $matches[4];
                    $endMonth = $matches[5];
                    $endDay = $matches[6];

                    // On n'affiche pas le jour si on prend les années complètes
                    if ($startDay === '01' && $startMonth === '01' && $endDay === '31' && $endMonth === '12') {
                        $label = "$startYear - $endYear";
                    }
                    else {
                        $label = "$startDay/$startMonth/$startYear - $endDay/$endMonth/$endYear";
                    }
                }

                // Dates simples : https://regex101.com/r/oO7gL8/1
                else if (preg_match('/^"?(\d{4})-(\d{2})-(\d{2})T\d{2}:\d{2}:\d{2}Z"?$/', $value, $matches)) {
                    $label = "{$matches[3]}/{$matches[2]}/{$matches[1]}";
                }

                break;
            case 'id_collection':

                $collectionIds = array();
                $collectionLabels = array();

                // Collection simple
                if (is_numeric($value)) {
                    $collectionIds[] = $value;
                }
                // Collections multiples : https://regex101.com/r/qV5vF2/1
                else if (preg_match('/^\(([ OR\d]+)\)$/', $value, $matches)) {
                    $collectionIds = array_map('trim', explode('OR', $matches[1]));
                }

                $db = get_db();

                foreach ($collectionIds as $collectionId) {
                    $collectionElementText = $db->getTable('ElementText')->findBy(array('record_type' => 'Collection',
                                                                             'record_id' => intval($value),
                                                                             'element_id' => 50));
                    if (!empty($collectionElementText)) {
                        $collectionLabels[] = $collectionElementText[0]->text;
                    }
                    else {
                        $collectionLabels[] = 'Collection introuvable';
                    }
                }

                $label = implode('<br>&nbsp;&nbsp;<strong>ou</strong> ', $collectionLabels);
                break;
        }
        return $label;
    }

    /**
     * Ajoute une facette (fq) à l'URL actuelle
     * (retourne la nouvelle URL)
     */
    public static function addFacetUrl($facetKey, $value) {
        $frags = self::parseQueryString();

        $frags['fq'][$facetKey] = $value;

        return self::getQueryStringUrl(self::buildQueryString($frags));
    }

    /**
     * Supprime une facette (fq) de l'URL actuelle
     * (retourne la nouvelle URL)
     */
    public static function removeFacetUrl($facetKey) {
        $frags = self::parseQueryString();

        unset($frags['fq'][$facetKey]);

        return self::getQueryStringUrl(self::buildQueryString($frags));
    }

    /**
     * Modifie le paramètre de tri de l'url
     * exemple : ::addSortUrl('date desc');
     * (Retourne la nouvelle url)
     */
    public static function addSortUrl($sortParameter) {
        $frags = self::parseQueryString();

        $frags['autre']['sort'] = urlencode($sortParameter);

        return self::getQueryStringUrl(self::buildQueryString($frags));
    }

    /**
     * Supprime le paramètre de tri de l'url
     * exemple : ::addSortUrl('date desc');
     * (Retourne la nouvelle url)
     */
    public static function removeSortUrl() {
        $frags = self::parseQueryString();

        unset($frags['autre']['sort']);

        return self::getQueryStringUrl(self::buildQueryString($frags));
    }


    /**
     * Rebuild the URL with a new array of facets.
     *
     * @param array $facets The parsed facets.
     * @return string The new URL.
     */
    public static function makeUrl($facets)
    {

        // Collapse the facets to `:` delimited pairs.
        $fParam = array();
        foreach ($facets as $facet) {
            $fParam[] = "{$facet[0]}:\"{$facet[1]}\"";
        }

        // Implode on ` AND `.
        $fParam = implode(' AND ', $fParam);

        // Get the `q` parameter, reverting to ''.
        $qParam = array_key_exists('q', $_GET) ? $_GET['q'] : '';

        // Get the base results URL.
        $results = url('solr-search/results/index');

        // String together the final route.
        return htmlspecialchars("$results?q=$qParam&facet=$fParam");

    }


    /**
     * Add a facet to the current URL.
     *
     * @param string $field The facet field.
     * @param string $value The facet value.
     * @return string The new URL.
     */
    public static function addFacet($field, $value)
    {

        // Get the current facets.
        $facets = self::parseFacets();

        // Add the facet, if it's not already present.
        if (!in_array(array($field, $value), $facets)) {
            $facets[] = array($field, $value);
        }

        // Rebuild the route.
        return self::makeUrl($facets);

    }


    /**
     * Remove a facet to the current URL.
     *
     * @param string $field The facet field.
     * @param string $value The facet value.
     * @return string The new URL.
     */
    public static function removeFacet($field, $value)
    {

        // Get the current facets.
        $facets = self::parseFacets();

        // Reject the field/value pair.
        $reduced = array();
        foreach ($facets as $facet) {
            if ($facet !== array($field, $value)) $reduced[] = $facet;
        }

        // Rebuild the route.
        return self::makeUrl($reduced);

    }


    /**
     * Get the human-readable label for a facet key.
     *
     * @param string $key The facet key.
     * @return string The label.
     */
    public static function keyToLabel($key)
    {
        $fields = get_db()->getTable('SolrSearchField');
        return $fields->findBySlug(rtrim($key, '_s'))->label;
    }


}
