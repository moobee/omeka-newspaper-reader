<?php

$pageTitle = __('Browse Items');
echo head(array('title'=>$pageTitle,'bodyid'=>'items','bodyclass' => 'browse'));
$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
$idCollection = Zend_Controller_Front::getInstance()->getRequest()->getParam('collection');

$db = get_db();
$idElement = $db->query("Select id from {$db->prefix}elements where `name`='Title'");
$idElement = $idElement->fetch();
$idElement = $idElement['id'];

if( isset($idCollection) ){
    $collections = $db->query("Select text from {$db->prefix}element_texts where `element_id`=$idElement and `record_id`=$idCollection");
    $collections = $collections->fetch();
}else{
    $collections['text'] = "Tout parcourir";
}

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
    <a href="<?php echo $baseUrl;?>/collections/browse">
        <?php echo $tab_title; ?>
    </a>
    <span class="breadcrumb-delimiter" >&raquo;</span>
    <span><?php echo $collections['text']; ?></span>

</div>

<!-- <h1 class='title-collection'><?php echo $pageTitle;?> <?php echo __('(%s total)', $total_results); ?></h1> -->
<h1 class='title-collection'><?php echo $collections['text']; ?></h1>
<p id="document-count" ><?php echo __('(%s contenus)', $total_results); ?></p>

<!-- <nav class="items-nav navigation" id="secondary-nav">
    <?php echo public_nav_items(); ?>
</nav> -->

<?php //echo pagination_links('total_results'=>,'per_page'=>); ?>

<?php if ($total_results > 0): ?>

<?php
$sortLinks[__('Date')] = 'Dublin Core,Date';
$sortLinks[__('Title')] = 'Dublin Core,Title';
$sortLinks[__('Creator')] = 'Dublin Core,Creator';
$sortLinks[__('Date Added')] = 'added';
$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
?>
<div id="sort-links">
    <span class="sort-label"><?php echo __('Sort by: '); ?></span><?php echo browse_sort_links($sortLinks); ?>
</div>

<?php endif; ?>

<?php echo pagination_links();?>

<?php foreach (loop('items') as $item): ?>
<?php $itemTitle = strip_formatting(metadata('item', array('Dublin Core', 'Title'))); ?>
<div class="item-liste">

    <a href = "<?php echo $baseUrl;?>/items/show/<?php echo $item->id;?>"></a>
    <?php if (metadata('item', 'has thumbnail')): ?>
        <div class="item-img">
            <?php echo item_image('square_thumbnail', array('alt' => $itemTitle)); ?>
        </div>
    <?php endif; ?>

    <div class="item-info">

        <div class="title">
            <h2 class="line-ellipsis" ><?php echo ellipseLine($itemTitle); ?></h2>
        </div>

        <div class="item-description">
            <?php if ($description = metadata('item', array('Dublin Core', 'Description'), array('snippet'=>250))): ?>
                    <p class='desc'><?php echo $description; ?></p>
            <?php endif; ?>

            <?php if ($contri = metadata('item', array('Dublin Core', 'Contributor'), array('snippet'=>250))): ?>
                <p class='contributor'><?php echo $contri; ?></p>
            <?php endif; ?>
        </div>
        <!--<?php if (metadata('item', 'has tags')): ?>
            <div class="tags"><p><strong><?php echo __('Tags'); ?>:</strong>
                <?php echo tag_string('items'); ?></p>
            </div>
        <?php endif; ?>-->

    <?php fire_plugin_hook('public_items_browse_each', array('view' => $this, 'item' =>$item)); ?>

    </div><!-- end class="item-meta" -->
</div><!-- end class="item hentry" -->
<?php endforeach; ?>

<?php echo pagination_links(); ?>

<?php fire_plugin_hook('public_items_browse', array('items'=>$items, 'view' => $this)); ?>

<?php echo foot(); ?>
