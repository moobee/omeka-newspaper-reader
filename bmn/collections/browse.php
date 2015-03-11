<?php
$pageTitle = __('Parcourir la liste des titres numérisés');
echo head(array('title'=>$pageTitle,'bodyid'=>'collections','bodyclass' => 'browse'));
$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();

//Récupére le titre de l'onglet pour l'afficher dans le fil d'ariane
$header_links = explode(",", get_theme_option('Header Links'));

//defaut
$tab_title = "Collections";
foreach($header_links as $header_link) {
    $fragments = explode("-&gt;", $header_link);
    if(!empty($fragments[1]) && $fragments[1] == 'collections/browse') {
        $tab_title = !empty($fragments[0]) ? $fragments[0] : '';
        break;
    }
}
?>

<div class="item-breadcrumb">
    <a href="<?php echo $baseUrl?>/">
        <img src="<?php echo $baseUrl?>/themes/bmn/images/button-home.png">
    </a>
    <span class="breadcrumb-delimiter" >&raquo;</span>
    <a id="current-breadcrumb-element" href="<?php echo $baseUrl;?>/collections/browse">
        <?php echo $tab_title; ?>
    </a>
</div>
<h1 class="title-collection"><?php echo $pageTitle; ?></h1>
<?php echo pagination_links(); ?>

<?php foreach (loop('collections') as $collection): ?>
<div class="item-liste">

    <?php $items = get_records('Item', array('tags' => 'picture-collection-'.$collection->id));?>
   <div class="item-img">
        <?php if(!empty($items)): ?>
            <?php   set_current_record('item', $items[0]); ?>
            <?php echo item_image('square_thumbnail', array('alt' => $collection->title)); ?>
        <?php else: ?>
            <img src="http://placehold.it/150x150" />
        <?php endif; ?>
   </div>

    <a href = "<?php echo $baseUrl?>/items/browse?collection=<?php echo $collection->id;?><?php echo '&sort_field='.urlencode('Dublin Core,Date').'&sort_dir=d'?>"></a>
    <div class="item-info">

        <div class="title">
            <h2><?php echo cutString(metadata('collection', array('Dublin Core', 'Title'), array('snippet'=>150)), 100); ?></h2>
        </div>

        <div class="item-description">
           <?php if (metadata('collection', array('Dublin Core', 'Description'))): ?>
                    <p class='desc'><?php echo metadata('collection', array('Dublin Core', 'Description')); ?></p>
            <?php endif; ?>

            <?php if ($collection->hasContributor()): ?>
                <p class='contributor'><?php echo metadata('collection', array('Dublin Core', 'Contributor'), array('all'=>true, 'delimiter'=>', ')); ?></p>
            <?php endif; ?>
        </div>

        <!-- <p class="view-items-link"><?php //echo link_to_items_browse('Voir les contenus', array('collection' => metadata('collection', 'id'))); ?></p> -->
    </div>
    <?php fire_plugin_hook('public_collections_browse_each', array('view' => $this, 'collection' => $collection)); ?>
</div><!-- end class="collection" -->

<?php endforeach; ?>

<?php echo pagination_links(); ?>

<?php fire_plugin_hook('public_collections_browse', array('collections'=>$collections, 'view' => $this)); ?>

<?php echo foot(); ?>
