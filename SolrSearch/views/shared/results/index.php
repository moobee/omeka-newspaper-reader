<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

//récupère la recherche
$searchTerms = '';
if (isset($_GET['q'])) {
  $searchTerms = $_GET['q'];
}

// Récupère l'ordre de tri
$sortOrder = '';
if (!empty($_GET['sort'])) {
  $sortOrder = $_GET['sort'];
}

?>


<?php queue_css_file('results'); ?>
<?php echo head(array('title' => __('Recherche : %s', $searchTerms)));?>

<?php


//récupère l'url
$baseUrl = Zend_Controller_Front::getInstance()->getRequest()->getBaseUrl();
?>

<!-- Search form. -->
<div class="all-result">
<ul>
<form id="solr-search-form">
   <span class="float-wrap">
     <input value="<?php echo str_replace('"', '&quot;', $searchTerms); ?>" type="text" title="<?php echo __('Expression à rechercher') ?>" name="q" />
   </span>
   <input type="submit" value="<?php echo __('Nouvelle recherche'); ?>" id="recherche-solr-input-text"/>
  <hr>

  <!-- Filtres appliqués. -->
  <div id="solr-app-facets">

    <h3><?php echo __('Filtres appliqués'); ?></h3>

    <?php if (!empty($appliedFacets)): ?>

      <ul>
        <?php foreach ($appliedFacets as $appliedFacet): ?>
          <li class="ellipse-text" title="<?php echo strip_tags($appliedFacet->label); ?>">
            [<a title="Supprimer ce filtre" href="<?php echo $appliedFacet->removeUrl; ?>" class="face-value">x</a>]
            <span class="facet-label">
              <?php echo $appliedFacet->label; ?>
            </span>
          </li>
        <?php endforeach ?>
      </ul>
    <?php else: ?>
      <?php echo __('Aucun filtre appliqué'); ?>
    <?php endif ?>
  </div>

  <hr>

  <!-- Facets. -->
  <div id="solr-facets">

    <?php if (!empty($facets) && $facetteDisponible): ?>
      <h3><?php echo __('Limiter votre recherche'); ?></h3>
      <?php foreach ($facets as $facet): ?>
        <?php if (!in_array($facet->key, $appliedFacetKeys) && !empty($facet->facets)): ?>
          <strong><?php echo $facet->name; ?></strong>
          <ul>
            <?php foreach ($facet->facets as $facetOption): ?>
              <li class="ellipse-text" title="<?php echo strip_tags($facetOption->label); ?> (<?php echo $facetOption->resultCount; ?>)">
                <a href="<?php echo $facetOption->addUrl; ?>" class="face-value">
                  <?php echo $facetOption->label; ?>
                </a>
                <span class="facet-count">
                  (<?php echo $facetOption->resultCount; ?>)
                </span>
              </li>
            <?php endforeach ?>
          </ul>
        <?php endif ?>
      <?php endforeach ?>
    <?php endif ?>
  </div>
</form>


<div id="search-Views">

<!-- Results. -->
<div id="solr-results">

  <!-- Number found. -->
  <?php /* Affiche le nombre de résultat de cette recherche */ ?>
  <p id="num-found">
    <strong>
      <?php echo ($results->response->numFound === 0) ? 'Aucun' : $results->response->numFound; ?>
    </strong>
    <?php if ($results->response->numFound === 0 || $results->response->numFound === 1) : ?>
      <?php if (!empty($searchTerms)) : ?>
        fascicules trouvé pour l'expression : <strong><?php echo $searchTerms; ?></strong>
      <?php else : ?>
        fascicule trouvé.
      <?php endif; ?>
    <?php else : ?>
      <?php if (!empty($searchTerms)) : ?>
        fascicules trouvés pour l'expression : <strong><?php echo $searchTerms; ?></strong>
      <?php else : ?>
        fascicules trouvés.
      <?php endif; ?>
    <?php endif; ?>
  </p>

  <?php /* CREATION DE LA TABLE QUI CONTIENT TOUS LES RESULTATS */ ?>
  <?php if ($results->response->numFound !== 0) : ?>

    <div class="result-options-box clearfix" >
      <div class="sort-options">
        <label>
          Trier par :
        </label>
        <a href="<?php echo SolrSearch_Helpers_Facet::addSortUrl('date asc'); ?>" class="bmn-tab <?php echo $sortOrder === 'date asc' ? 'active' : ''; ?>" >Date de parution</a>
        <a href="<?php echo SolrSearch_Helpers_Facet::removeSortUrl(); ?>" class="bmn-tab <?php echo empty($sortOrder) ? 'active' : ''; ?>" >Pertinence</a>
      </div>
    </div>

    <ul id="tableau-resultat-fascicule" >
        <?php foreach ($results->response->docs as $doc) : ?>
          <?php $docFilename = !empty($doc->filename) ? '/files/square_thumbnails/' . $doc->filename : '/themes/bmn/images/default.png'; ?>
          <?php $url = SolrSearch_Helpers_View::getDocumentUrl($doc); ?>
          <li class="document-result">

            <a class="clearfix" target="_blank" href="<?php echo $baseUrl ?><?php echo $url; ?>&query=<?php echo urlencode($searchTerms); ?>" >
                <img width="44" height="44" src="<?php echo $baseUrl . $docFilename; ?>" />

                <!-- Title. -->
                <span title="<?php echo $doc->title; ?>" class="result-title ellipse-text">
                  <?php echo $doc->title; ?>
                <span>
                <?php /*
                <span title="<?php echo __('Score de pertinence calculé par l\'algorithme de recherche'); ?>" class="relevancy-score" >
                  Pertinence : <strong><?php echo intval(floatval($doc->score) * 10000); ?></strong>
                </span>
                */ ?>
            </a>
        </li>
        <?php endforeach; ?>
      </ul>
  <?php endif; ?>

</div>
<?php echo pagination_links(); ?>
</div>
</ul>
</div>
<?php echo foot();
