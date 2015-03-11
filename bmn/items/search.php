<?php
$pageTitle = __('Search Items');
echo head(array('title' => $pageTitle,
           'bodyclass' => 'items advanced-search',
           'bodyid' => 'items'));
$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
?>

<h1><?php echo $pageTitle; ?></h1>

<nav class="items-nav navigation" id="secondary-nav">
    <ul class="navigation recherche">
        <li id="tout-parcourir"><a href="<?php echo $baseUrl;?>/items/browse"><?php echo __('Tout parcourir'); ?></a> </li>
        <li id="recherche-contenus" class="active"><a href="<?php echo $baseUrl;?>/items/search"/><?php echo __('Recherche de contenus'); ?></a></li>
    </ul>
</nav>

<?php echo $this->partial('items/search-form.php',
    array('formAttributes' =>
        array('id'=>'advanced-search-form'))); ?>

<?php echo foot(); ?>
