<?php echo head(array(
	'title' => metadata(
		'item',
		array( 'Dublin Core', 'Title')
	),
	'bodyid' => 'item',
	'bodyclass' => 'show'
)); ?>

<?php
require_once NEWSPAPER_READER_DIRECTORY.'/managers/EntityManager.php';

$baseUrl = Zend_Controller_Front::getInstance()->getRequest()->getBaseUrl();
$em = new EntityManager();

$idItem = metadata('item', 'Id');
$idCollection = metadata('item', 'Collection Id');
$collectionName = metadata('item', 'Collection Name');

$title = metadata('item', array('Dublin Core', 'Title'));
$identifier = metadata('item', array('Dublin Core', 'Identifier'));


//Récupération des champs DC

//Liste des champs Dublin Core à récupérer
$dcFields = array(
	'Title' => 'Titre',
	'Description' => 'Description',
	'Date' => 'Date',
	'Publisher' => 'Éditeur',
	'Creator' => 'Auteur',
	'Contributor' => 'Contributeur',
	'Subject' => 'Sujet',
	'Source' => 'Source',
	'Rights' => 'Droits',
	'Relation' => 'Relation',
	'Language' => 'Langue',
	'Format' => 'Format',
	'Type' => 'Type',
	'Identifier' => 'Identifiant'
);

$fields = array();

foreach ($dcFields as $dcField => $dcLabel) {

	$data = metadata('item', array('Dublin Core', $dcField), 'all');

	if(!empty($data)) {

		//On transforme les éventuelles url en liens
		foreach ($data as &$string) {
			if(substr($string, 0, 4) === 'http') {
				$string = '<a target="_blank" href="' . $string . '" >' . $string . '</a>';
			}
		}
		unset($string);

		$fields[$dcLabel] = $data;
	}
	else {
		unset($dcFields[$dcField]);
	}
}

$dcFieldCol1 = array();
$dcFieldCol2 = array();

//On réparti les champs dans deux colonnes
foreach($fields as $dcLabel => $field) {
	if(count($dcFieldCol1) < count($fields) / 2) {
		$dcFieldCol1[$dcLabel] = $field;
	}
	else {
		$dcFieldCol2[$dcLabel] = $field;
	}
}

$doc = null;

if (plugin_is_active('NewspaperReader')) {
	$doc = $em->selectDocumentByItemId($idItem, array('views', 'company'));
}

if ($doc !== null) {
	$companyName = $doc->getCompany()->getName();

	$views = $doc->getViews();

	$format = $views[0]->getFormat();


	//On essaie d'afficher une miniature
	$first_page = current($views);
	$first_page_number = str_pad($first_page->getNumber(), 4, '0', STR_PAD_LEFT);

	if(file_exists(NEWSPAPER_READER_DIRECTORY . '/files/' . $identifier
			. '/' . $identifier . '_' . $first_page_number . '_thumbnail_card' . $format)) {

		$thumbnail = $baseUrl . '/plugins/NewspaperReader/files/' . $identifier . '/' .
		$identifier . '_' . $first_page_number . '_thumbnail_card' . $format;
	}
}

$elements = explode('<div id="dublin-core', all_element_texts('item'));
unset($elements[0]);

$db = get_db();
$nameItemPicture = $db->query("Select filename from {$db->prefix}files where `item_id` = $idItem");
$nameItemPicture = $nameItemPicture->fetchAll();

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

<script type="text/javascript" src="<?php echo $baseUrl?>/plugins/NewspaperReader/views/public/js/reader-config.js"></script>
<script type="text/javascript" src="<?php echo $baseUrl?>/plugins/NewspaperReader/views/public/js/jquery.fancybox.pack.js"></script>
<link rel="stylesheet" href="<?php echo $baseUrl?>/plugins/NewspaperReader/views/public/css/jquery.fancybox.css" type="text/css" media="screen" />
<div id="item-card">
	<div id="item-breadcrumb" class="item-breadcrumb">
		<a href="<?php echo $baseUrl?>">
			<img src="<?php echo $baseUrl?>/themes/bmn/images/button-home.png">
		</a>
		<span class="breadcrumb-delimiter" >&raquo;</span>
		<a href="<?php echo $baseUrl?>/collections/browse">
			<?php echo __($tab_title); ?>
		</a>
		<span class="breadcrumb-delimiter" >&raquo;</span>
		<a href="<?php echo $baseUrl?>/items/browse?collection=<?php echo $idCollection ?>">
		    <?php echo strlen($collectionName) > 50 ? substr($collectionName,0,50)." (...)" : html_entity_decode($collectionName, ENT_QUOTES);?>
		</a>
		<span class="breadcrumb-delimiter" >&raquo;</span>
		<span><?php echo __($title); ?></span>
	</div>
	<div id="item-card-summary" class="clearfix">
		<div id="item-cover">
			<?php if(!empty($thumbnail)) : ?>
				<img width="270" height="210" src="<?php echo $thumbnail; ?>">
			<?php else: ?>
			<?php if($doc === null) : ?>
				<a href="<?php echo $baseUrl; ?>/files/original/<?php echo $nameItemPicture[0]['filename'];?>" class="fancybox-gallery-trigger">
					<img width="270" src="<?php echo $baseUrl; ?>/files/original/<?php echo $nameItemPicture[0]['filename'];?>">
				</a>
				<a class="see-illustrations fancybox-gallery-trigger" >
					<p>
						<?php if(count($nameItemPicture) > 0): ?>
							<?php if(count($nameItemPicture) > 1): ?>
								<?php echo __('Voir les'); ?> <?php echo count($nameItemPicture); ?> <?php echo __('illustrations'); ?>
							<?php else: ?>
								<?php echo __("Voir l'illustration"); ?>
							<?php endif; ?>
						<?php endif; ?>
					</p>
				</a>
				<?php for($i = 0 ; $i < count($nameItemPicture) ; $i++){ ?>
					<a style="display:none" class="groupe" rel="groupe1" href="<?php echo $baseUrl; ?>/files/original/<?php echo $nameItemPicture[$i]['filename']?>">
						<img width="270" src="<?php echo $baseUrl; ?>/files/original/<?php echo $nameItemPicture[1]['filename'];?>">
					</a>
				<?php }?>
			<?php else: ?>
				<?php echo item_image('square_thumbnail', array()); ?>
			<?php endif; ?>
			<?php endif; ?>
		</div>
		<div id="item-infos">
			<h2><?php echo $doc !== null ? $companyName : ''; ?></h2>
			<h3><?php echo $title; ?></h3>
			<?php if ($doc !== null): ?>
				<div id="item-buttons">
					<a id="item-btn-consult" class="fancybox.ajax" href="<?php echo $baseUrl; ?>/newspaper-reader/index/read?item=<?php echo $idItem; ?>">
						<img class="hi-button" src="<?php echo $baseUrl; ?>/themes/bmn/images/button-consult.png">
					</a>
					<a href="<?php echo $baseUrl?>/plugins/NewspaperReader/files/<?php echo $identifier; ?>/<?php echo $identifier; ?>.pdf" download="<?php echo $identifier; ?>.pdf">
						<img class="hi-button" src="<?php echo $baseUrl?>/themes/bmn/images/button-download.png">
					</a>
				</div>
			<?php endif ?>
			<?php fire_plugin_hook('public_items_show'); ?>
		</div>
	</div>
	<div id="item-card-metadata" class="clearfix">
		<div id="metadata-first-column">
			<?php foreach ($dcFieldCol1 as $label => $values): ?>
				<div id="dublin-core-<?php echo strtolower($label); ?>" class="element" >
					<h3><?php echo $label; ?></h3>
					<?php foreach ($values as $value): ?>
						<div class="element-text" >
							<?php echo $value; ?>
						</div>
					<?php endforeach ?>
				</div>
			<?php endforeach ?>
		</div>

		<div id="metadata-second-column">
			<?php foreach ($dcFieldCol2 as $label => $values): ?>
				<div id="dublin-core-<?php echo strtolower($label); ?>" class="element" >
					<h3><?php echo $label; ?></h3>
					<?php foreach ($values as $value): ?>
						<div class="element-text" >
							<?php echo $value; ?>
						</div>
					<?php endforeach ?>
				</div>
			<?php endforeach ?>
		</div>
	</div>
	<!-- <ul class="item-pagination navigation">
	    <li id="previous-item" class="previous"><?php echo link_to_previous_item_show(); ?></li>
	    <li id="next-item" class="next"><?php echo link_to_next_item_show(); ?></li>
	</ul> -->
</div>

<script type="text/javascript">

	$(document).ready(function() {
		$("a[id='item-btn-consult']").fancybox({
			scrolling: 'no',
			closeBtn: false,
			autoSize: false,
			width: 999999,
			height: 999999,
			padding: 0,
			margin: 0,
		});

		$(".fancybox-gallery-trigger").on("click", function(event) {
			event.preventDefault();
			$(".groupe").get(0).click();
		});

		$("a.groupe").fancybox({
			'transitionIn'		:'elastic',
			'transitionOut'		:'elastic',
			'speedIn'		:600,
			'speedOut'		:200,
			'padding'	  	: 0,
		        'titlePosition'    	: 'over',
		        'titleFormat'      	: function(title, currentArray, currentIndex, currentOpts) {
		   	return '<span id="fancybox-title-over">Image ' + (currentIndex + 1) + ' / '
		   	+ currentArray.length + '   -:-' + (title.length ? '   ' + title : '') + '</span>';
		        }
		});
	});

	var fancybox = $.fancybox;
</script>

<?php echo foot(); ?>
