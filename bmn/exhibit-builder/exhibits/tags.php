<?php
$title = __('Browse Exhibits by Tag');
echo head(array('title' => $title, 'bodyid' => 'exhibit', 'bodyclass' => 'tags'));
$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
?>

<div class="item-breadcrumb">
    <a href="<?php echo $baseUrl?>/">
        <img src="<?php echo $baseUrl?>/themes/bmn/images/button-home.png">
    </a>
    <span class="breadcrumb-delimiter" >&raquo;</span>
    <a href="<?php echo $baseUrl;?>/exhibits/browse">
        <?php echo __('Expositions virtuelles'); ?>
    </a>
</div>

<h1><?php echo $title; ?></h1>

<nav class="navigation exhibit-tags" id="secondary-nav">
    <?php echo nav(array(
            array(
                'label' => __('Browse All'),
                'uri' => url('exhibits/browse')
            ),
            array(
                'label' => __('Browse by Tag'),
                'uri' => url('exhibits/tags')
            )
        )
    ); ?>
</nav>

<?php echo tag_cloud($tags, 'exhibits/browse'); ?>

<?php echo foot(); ?>
