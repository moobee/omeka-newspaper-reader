<?php
$title = __('Parcourir les focus');
echo head(array('title' => $title, 'bodyid' => 'exhibit', 'bodyclass' => 'browse'));
$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();

//Récupére le titre de l'onglet pour l'afficher dans le fil d'ariane
$header_links = explode(",", get_theme_option('Header Links'));

//defaut
$tab_title = "Expositions";
foreach($header_links as $header_link) {
    $fragments = explode("-&gt;", $header_link);
    if($fragments[1] == 'exhibits/browse') {
        $tab_title = $fragments[0];
        break;
    }
}

?>
<div class="item-breadcrumb">
    <a href="<?php echo $baseUrl?>/">
        <img src="<?php echo $baseUrl?>/themes/bmn/images/button-home.png">
    </a>
    <span class="breadcrumb-delimiter" >&raquo;</span>
    <a href="<?php echo $baseUrl;?>/exhibits/browse">
        <?php echo $tab_title; ?>
    </a>
</div>

<h1><?php echo $title; ?> <?php echo __('(%s total)', $total_results); ?></h1>
<?php if (count($exhibits) > 0): ?>

<div class="pagination"><?php echo pagination_links(); ?></div>

<?php foreach (loop('exhibit') as $exhibit): ?>

    <div class="exhibit ">
        <?php $itemsImage = get_records('Item',array('tags' => 'picture-exhibit-'.metadata('exhibit', 'slug')), 1);?>
        
        <div class="item-img">
            <?php if(sizeof($itemsImage) != 0): ?>
                <?php echo item_image('fullsize', array(), 0, $itemsImage[0]);?>
            <?php else: ?>
                <img src="http://placehold.it/150x150" />
            <?php endif; ?> 
        </div>

        <?php echo link_to_exhibit(' '); ?>
        <div class='exhibit-description'>
            <h2><?php echo metadata('exhibit', 'title');?></h2>
            <?php if ($exhibitDescription = metadata('exhibit', 'description', array('no_escape' => true))): ?>
                <div class="description"><?php echo cutString($exhibitDescription, 365); ?></div>
            <?php endif; ?>

            <?php if ($exhibitTags = tag_string('exhibit', 'exhibits')): ?>
                <p class="tags"><?php echo $exhibitTags; ?></p>
            <?php endif; ?>
        </div>
    </div>
<?php endforeach; ?>

<div class="pagination"><?php echo pagination_links(); ?></div>

<?php else: ?>
<p><?php echo __('There are no exhibits available yet.'); ?></p>
<?php endif; ?>

<?php echo foot(); ?>
