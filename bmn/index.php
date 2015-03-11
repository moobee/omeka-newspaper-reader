<?php echo head(array('bodyid'=>'home', 'bodyclass' =>'two-col')); ?>

<?php
	//Récupère 3 Items mis en avant aléatoirement
	$items = get_random_featured_items(3, true);

	$baseUrl = Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();

	$db = get_db();
	$autresCollections = $db->query("Select text, title
									 from {$db->prefix}simple_pages_pages
									 where `slug`='projet'");
	$autresCollections = $autresCollections->fetch();
	$imageAutreCollection = get_records('Item',array('tags' => 'autres-collections'), 1);
	// var_dump($imageAutreCollection);

	//Construction de la requête pour récupérer les URLs
	$fields = array();
	if(isset($items) && isset($items[0])){
		$idUrl = $db->query("SELECT id FROM {$db->prefix}elements WHERE name LIKE 'URL'");
		$idUrl = $idUrl->fetch();
		foreach ($items as $key => $value) {
			$fields[] = $value['id'];
		}
	}

	if(sizeof($autresCollections)===0){
		$autresCollections['title']='';
		$autresCollections['text']='';
	}

	$footer = $db->query("Select text, title
						  from {$db->prefix}simple_pages_pages
						  where `slug`='bloc-footer'");
	$footer = $footer->fetch();

	if(sizeof($footer)===0){
		$footer['title']='';
		$footer['text']='';
	}

	$itemsFocus = get_records('Item', array('tags' => 'home-page-exhibit'), 1);
	if(isset($itemsFocus[0]['id'])){
		$urlfocus = $db->query("Select text from {$db->prefix}element_texts where `element_id`=".$idUrl['id']." AND record_id = ".$itemsFocus[0]['id']);
		$urlfocus = $urlfocus->fetch();
	}


	if (!empty($itemsFocus)) {
		$itemFocus = current($itemsFocus);
	}

	$projetImage = get_theme_option('Projet Image');
	$projetUrl = get_theme_option('Projet url');

?>

<div id="home-page" class = "clearfix">
	<div id="show-item-0" class = "show-item">
		<?php if(isset($items[0]) ): ?>
		<a href = "<?php echo $baseUrl;?>items/show/<?php echo $items[0]->id; ?>"></a>
 		<?php echo item_image('fullsize', array(), 0, $items[0]); ?>
		<div class="text-item">
			<h3><?php echo metadata($items[0], array('Dublin Core', 'Subject'));?></h3>
			<h2><?php echo metadata($items[0], array('Dublin Core', 'Title'));?></h2>
			<p><?php echo cutString(metadata($items[0], array('Dublin Core', 'Description')), 140);?></p>
		</div>
		<?php endif; ?>
	</div>

	<div id = "feature-focus">
		<?php if(isset($itemFocus)) : ?>
		<a href = "<?php echo $baseUrl;?><?php echo $urlfocus['text']; ?>"></a>
			<h2><?php echo metadata($itemsFocus[0], array('Dublin Core', 'Title'));?></h2>
			<?php echo item_image('fullsize', array(), 0, $itemsFocus[0]); ?>
		<?php endif; ?>
	</div>

	<div id="show-item-1" class = "show-item">
		<?php if(isset($items[1])) : ?>
			<a href = "<?php echo $baseUrl;?>items/show/<?php echo $items[1]->id; ?>"></a>
			<?php echo item_image('fullsize', array(), 0, $items[1]);?>
			<div class="text-item">
				<h3><?php echo metadata($items[1], array('Dublin Core', 'Subject'));?></h3>
				<h2><?php echo metadata($items[1], array('Dublin Core', 'Title'));?></h2>
				<p><?php echo cutString(metadata($items[1], array('Dublin Core', 'Description')),140);?></p>
			</div>
		<?php endif; ?>
	</div>

	<div id="show-item-2" class = "show-item">
		<?php if(isset($items[2])) : ?>
		<a href = "<?php echo $baseUrl;?>items/show/<?php echo $items[2]->id; ?>"></a>
		<?php echo item_image('fullsize', array(), 0, $items[2]);?>
		<div class="text-item">
			<h3><?php echo metadata($items[2], array('Dublin Core', 'Subject'));?></h3>
			<h2><?php echo metadata($items[2], array('Dublin Core', 'Title'));?></h2>
			<p><?php echo cutString(metadata($items[2], array('Dublin Core', 'Description')),140);?></p>
		</div>

		<?php endif; ?>
	</div>

	<div id = "documents">
		<a href = "<?php echo $baseUrl;?><?php echo $projetUrl; ?>"></a>
		<div class="text-item">
			<h2 id="autre-collection-title" >
				<?php echo $autresCollections['title']?>
			</h2>
			<p><?php echo $autresCollections['text']?></p>
		</div>
		<?php if(isset($projetImage)): ?>
			<img src="<?php echo $baseUrl?>files/theme_uploads/<?php echo $projetImage;?>" />
		<?php endif;?>
	</div>

</div><!-- end home-page -->

<div id="secondary">
</div><!-- end secondary -->

<div id="footer-first-part">
    <h3 id="footer-title"><?php echo $footer['title']; ?></h3>
    <p id="footer-text"><?php echo $footer['text']; ?></p>
</div>

<?php echo foot(); ?>
