<?php echo head(array('title' => metadata('exhibit', 'title'), 'bodyid'=>'exhibit', 'bodyclass'=>'summary')); ?>

<?php $baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();?>

<?php 
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

//On récupère l'image associé à la collection avec son tag
$itemsImage = get_records('Item',array('tags' => 'picture-exhibit-'.metadata('exhibit', 'slug')), 1);

?>
<div class="item-breadcrumb">
    <a href="<?php echo $baseUrl?>/">
        <img src="<?php echo $baseUrl?>/themes/bmn/images/button-home.png">
    </a>
    <span class="breadcrumb-delimiter" >&raquo;</span>
    <a href="<?php echo $baseUrl;?>/exhibits/browse">
        <?php echo $tab_title; ?>
    </a>
    <span class="breadcrumb-delimiter" >&raquo;</span>
    <span><?php echo metadata('exhibits', 'title');?></span>
</div>
<h1><?php echo metadata('exhibit', 'title'); ?></h1>
<?php echo exhibit_builder_page_nav(); ?>

<div id="primary">
	<div id="exhibit-picture">
		<?php if(!empty($itemsImage[0])): ?>
			<?php echo item_image('fullsize', array(), 0, $itemsImage[0]);?>
		<?php endif;?>
	</div>
	<?php if ($exhibitDescription = metadata('exhibit', 'description', array('no_escape' => true))): ?>
	<div class="exhibit-description">
	    <?php echo $exhibitDescription; ?>
	</div>
	<?php endif; ?>

	<nav id="exhibit-pages">
	    <ul>
	        <?php set_exhibit_pages_for_loop_by_exhibit(); ?>
	        <?php foreach (loop('exhibit_page') as $exhibitPage): ?>
	        <?php echo exhibit_builder_page_summary($exhibitPage); ?>
	        <?php endforeach; ?>
	    </ul>
	</nav>

	<?php if (($exhibitCredits = metadata('exhibit', 'credits'))): ?>
	<div class="exhibit-credits">
	    <h3><?php echo __('Credits'); ?></h3>
	    <p><?php echo $exhibitCredits; ?></p>
	</div>
	<?php endif; ?>

</div>



<?php echo foot(); ?>
