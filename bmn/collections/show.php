<?php
$collectionTitle = strip_formatting(metadata('collection', array('Dublin Core', 'Title')));
if ($collectionTitle == '') {
    $collectionTitle = __('[Untitled]');
}
$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
?>

<?php echo head(array('title'=> $collectionTitle, 'bodyid'=>'collections', 'bodyclass' => 'show')); ?>

<h1><?php echo $collectionTitle; ?></h1>

<?php echo all_element_texts('collection'); ?>


    <h2><?php echo link_to_items_browse(__('Items in the %s Collection', $collectionTitle), array('collection' => metadata('collection', 'id'))); ?></h2>
    
    <?php if (metadata('collection', 'total_items') > 0): ?>
        
        <?php foreach (loop('items') as $item): ?>
        <div class="item-liste">
        
            <?php $itemTitle = strip_formatting(metadata('item', array('Dublin Core', 'Title'))); ?>
            
            <div class="item-img">
                <?php if (metadata('item', 'has thumbnail')): ?>
                        <?php echo item_image('square_thumbnail', array('alt' => $itemTitle)); ?>
                <?php else: ?>
                    <img src="http://placehold.it/150x150" />
                <?php endif; ?>
            </div>

           <a href="<?php echo $baseUrl?>/items/show/<?php echo $item['id'];?>"></a>

            <div class="item-info">
                <div class="title">
                    <h2><?php echo $itemTitle; ?></h2>
                </div>
                <?php if ($text = metadata('item', array('Item Type Metadata', 'Text'), array('snippet'=>250))): ?>
                        <div class="item-description">
                            <p class='desc'><?php echo $text; ?></p>
                            <?php if ($contri = metadata('item', array('Dublin Core', 'Contributor'), array('snippet'=>250))): ?>
                                <p class='contributor'><?php echo $contri; ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php elseif ($description = metadata('item', array('Dublin Core', 'Description'), array('snippet'=>250))): ?>
                        <div class="item-description">
                            <p class='desc'><?php echo $description; ?></p>
                            <?php if ($contri = metadata('item', array('Dublin Core', 'Contributor'), array('snippet'=>250))): ?>
                                <p class='contributor'><?php echo $contri; ?></p>
                            <?php endif; ?>
                        </div>
            </div>
            <?php endif; ?>
    
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p><?php echo __("There are currently no items within this collection."); ?></p>
    <?php endif; ?>
<!-- end collection-items -->

<?php fire_plugin_hook('public_collections_show', array('view' => $this, 'collection' => $collection)); ?>

<?php echo foot(); ?>
