<?php
$title = html_escape(__('Item #%s', $item->id));
echo head(array('title' => $title, 'bodyid' => 'exhibit', 'bodyclass' => 'exhibit-item-show'));
$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
?>

<div class="item-breadcrumb">
    <a href="<?php echo $baseUrl?>/">
        <img src="<?php echo $baseUrl?>/themes/bmn/images/button-home.png">
    </a>
    <span class="breadcrumb-delimiter" >&raquo;</span>
    <a href="<?php echo $baseUrl;?>/exhibits/browse">
        Expositions virtuelles
    </a>
    <span class="breadcrumb-delimiter" >&raquo;</span>
    <?php echo link_to_exhibit();?>
    </a>
</div>

<h1 class="item-title"><?php echo metadata('item', array('Dublin Core', 'Title')); ?></h1>

<?php echo all_element_texts('item'); ?>

<div id="itemfiles">
    <?php echo files_for_item(); ?>
</div>

<?php if (metadata('item', 'Collection Name')): ?>
    <div id="collection" class="field">
        <h2><?php echo __('Collection'); ?></h2>
        <div class="field-value"><p><?php echo link_to_collection_for_item(); ?></p></div>
    </div>
<?php endif; ?>

<?php if (metadata('item', 'has tags')): ?>
  <div class="tags">
    <h2><?php echo __('Tags'); ?></h2>
   <?php echo tag_string('item'); ?>
</div>
<?php endif;?>

<div id="citation" class="field">
    <h2><?php echo __('Citation'); ?></h2>
    <p id="citation-value" class="field-value"><?php echo metadata('item', 'citation', array('no_escape' => true)); ?></p>
</div>
<?php echo foot(); ?>
