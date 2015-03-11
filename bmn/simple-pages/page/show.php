<?php echo head(array(
    'title' => metadata('simple_pages_page', 'title'),
    'bodyclass' => 'page simple-page',
    'bodyid' => metadata('simple_pages_page', 'slug')
)); 
$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
?>
<div id="simple-page">

    <div class="item-breadcrumb">
        <a href="<?php echo $baseUrl?>/">
            <img src="<?php echo $baseUrl?>/themes/bmn/images/button-home.png">
        </a>
        <span class="breadcrumb-delimiter" >&raquo;</span>
        <a href="<?php echo $baseUrl;?>/<?php echo metadata('simple_pages_page', 'slug'); ?>">
            <?php echo metadata('simple_pages_page', 'title'); ?>
        </a>
    </div>

    <h1><?php echo metadata('simple_pages_page', 'title'); ?></h1>
    <?php
    $text = metadata('simple_pages_page', 'text', array('no_escape' => true));
    if (metadata('simple_pages_page', 'use_tiny_mce')) {
        echo $text;
    } else {
        echo '<p>'.eval('?>' . $text).'</p>';
    }
    ?>
</div>

<?php echo foot(); ?>
