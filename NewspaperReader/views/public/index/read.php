<?php
// Sur la liseuse, on detecte les tablettes pour afficher un éventuel message
require_once(dirname(__FILE__) . '/../../../libraries/Mobile_Detect.php');
$mobileDetect = new Mobile_Detect();
$isMobile = $mobileDetect->isMobile();
?>
<!DOCTYPE html>
<html>
    <head>
    	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>BMN Corpus Presse</title>
        <link rel="stylesheet" type="text/css" href="../../themes/bmn/css/style.css?<?php echo APP_VERSION; ?>"/>
        <link rel="stylesheet" href="//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css">
        <script type="text/javascript" src="../../application/views/scripts/javascripts/vendor/jquery.js"></script>
        <script type="text/javascript" src="../../application/views/scripts/javascripts/vendor/jquery-ui.js"></script>
        <!--[if lte IE 8]>
        	<script type="text/javascript" src="<?php echo $baseUrl; ?>/plugins/NewspaperReader/views/public/js/excanvas.js"></script>
        <![endif]-->
	</head>
	<body>
		<script type="text/javascript" src="<?php echo $baseUrl?>/plugins/NewspaperReader/views/public/js/spin.js"></script>
		<script>

			var existingViews = [<?php echo implode(', ', $existingViews); ?>];
			var viewImages = <?php echo json_encode($viewImages); ?>;
			var baseUrl = "<?php echo $baseUrl; ?>";
			var baseFileUrl = "<?php echo $baseFileUrl; ?>";
			var idItem = "<?php echo $_GET['item']; ?>";
			var query = undefined;
			<?php if (isset($_GET['query'])){ ?>
				query = <?php echo json_encode($_GET['query']); ?>;
			<?php } ?>

			<?php if ($isMobile): ?>
				alert('La liseuse n\'est pas encore compatible avec les tablettes et smartphones.\nEn attendant la version tablette, vous pouvez consulter le fascicule en téléchargeant la version PDF');
				window.location.href = baseUrl + '/items/show/' + idItem;
			<?php endif; ?>
		</script>
		<?php if($disponibiltyDoc) : ?>

		<div id="div-header" class="clearfix">
			<a id="link-close" href="" data-js-action="closeReader" >
				<span id="close-reader">Fermer</span>
				<img id="close-img" src="<?php echo $baseUrl; ?>/themes/bmn/images/bt-fermer.png">
			</a>
			<h1 id="item-title"><?php echo metadata('item', array('Dublin Core', 'Title')); ?></h1>
		</div>

		<!-- The thumbnails of the other pages -->
		<div id="div-thumbnails">
			<div id="thumbnails-wrapper" >
				<div id="thumbnails-positionner">
					<div class='thumbnails-buttons prev'>
						<a href="" id="btn-first-image" data-js-action="firstImage">
							<img class="hi-button" src="<?php echo $baseUrl; ?>/themes/bmn/images/bt-first.png"/>
						</a>
						<a href="" id="btn-previous-image" data-js-action="previousImage">
							<img class="hi-button" src="<?php echo $baseUrl; ?>/themes/bmn/images/bt-prev.png"/>
						</a>
					</div>

					<div id="thumbnails"  class="clearfix">
						<?php foreach($thumbnails as $number => $thumbnail): ?>
							<div id="div-thumbnail-<?php echo ($number+1); ?>" data="<?php echo ($number+1); ?>" class="thumbnail" data-js-action="changeImage">
								<img id="thumbnail-<?php echo ($number+1); ?>" src="<?php echo $thumbnail['src']; ?>"/>
								<span id="thumbnail-number-<?php echo ($number+1); ?>" class="thumbnail-number"><?php echo ($number+1); ?></span>
							</div>
						<?php endforeach; ?>
					</div>

					<div class='thumbnails-buttons next'>
						<a href="" id="btn-next-image" data-js-action="nextImage">
							<img class="hi-button" src="<?php echo $baseUrl; ?>/themes/bmn/images/bt-next.png"/>
						</a>
						<a href="" id="btn-last-image" data-js-action="lastImage">
							<img class="hi-button" src="<?php echo $baseUrl; ?>/themes/bmn/images/bt-last.png"/>
						</a>
					</div>
				</div>
				<div id="div-viewport"></div>
			</div>

			<!-- Searchbar -->
			<div id="div-searchbar">
				<div id="div-searchbar-form">
					<form id="searchbar">
						<input placeholder="Rechercher..." size="15" type="text" id="input-searchbar">
						<input id='submit-search-button' type="submit" value="">
						<div id="spinner"></div>
					</form>
					<a target="_blank" href="<?php echo $baseUrl; ?>/items/search" id="search-option" >
						&raquo; <?php echo __('Rechercher dans tout le corpus'); ?>
					</a>
				</div>
				<div id="div-searchbar-navigation">
					<p id="results-number" ><?php echo __('Occurrence : '); ?></p>
					<a class="nav-btn nav-prev" href="" data-js-action="previousSearchResult">
						<img class="hi-button" src="<?php echo $baseUrl; ?>/themes/bmn/images/bt-rouge-prev.png">
					</a>
					<a class="nav-btn nav-next"  href="" data-js-action="nextSearchResult">
						<img class="hi-button" src="<?php echo $baseUrl; ?>/themes/bmn/images/bt-rouge-next.png">
					</a>
					<p id="search-text" ><?php echo __('De : '); ?></p>
				</div>
			</div>
		</div>

		<div id="div-view" class="clearfix">

			<!-- <div id="div-text-pagination" >
				<a href="" data-js-action="changeTextPrev" >Page précédente</a>
				<span class="current-page" >1/<?php echo $viewCount; ?></span>
				<a href="" data-js-action="changeTextPrev" >Page suivante</a>
			</div> -->

			<!-- Text of the document -->
			<div id="div-text">

			</div>

			<!-- The canvas containing the image of the current page -->
			<div id ="div-canvas">
				<span id="tool-loader" ></span>
				<canvas id="canvas"><?php echo __('Votre navigateur ne supporte pas canvas, veuillez installer un navigateur compatible'); ?></canvas>
				<div id="loader-overlay" ></div>
			</div>

			<div id="tools-wrapper">

				<!-- Summary of the document -->
				<div id="div-summary">

					<div id="div-page-links">
						<?php //if ($viewCount < 16): ?>
							<?php foreach($thumbnails as $number => $thumbnail): ?>
								<a href ="" id="page-link-<?php echo ($number+1); ?>" data="<?php echo ($number+1); ?>" data-js-action="changeText">Page <?php echo ($number+1); ?></a></br>
							<?php endforeach ?>
						<?php //endif ?>
					</div>
				</div>

				<!-- Toolbar -->
				<div id="div-toolbar">
					<a class="not-tool" href="" id="bt-move" data-js-action="cursor">
						<img src="<?php echo $baseUrl; ?>/themes/bmn/images/ico-move.png" alt="Déplacement : survolez la page et cliquez en déplaçant la souris" title="Déplacement : survolez la page et cliquez en déplaçant la souris"/>
						<img class="ico-active" src="<?php echo $baseUrl; ?>/themes/bmn/images/ico-move-active.png" alt="Zoom : survolez la page et cliquez en déplaçant la souris vers le haut pour zoomer et vers le bas pour dézoomer" title="Zoom : survolez la page et cliquez en déplaçant la souris vers le haut pour zoomer et vers le bas pour dézoomer"/>
					</a>

					<a class="not-tool" href="" id="bt-zoom" data-js-action="zoom">
						<img src="<?php echo $baseUrl; ?>/themes/bmn/images/ico-zoom.png" alt="Zoom : survolez la page et cliquez en déplaçant la souris vers le haut pour zoomer et vers le bas pour dézoomer" title="Zoom : survolez la page et cliquez en déplaçant la souris vers le haut pour zoomer et vers le bas pour dézoomer"/>
						<img class="ico-active" src="<?php echo $baseUrl; ?>/themes/bmn/images/ico-zoom-active.png" alt="Zoom : survolez la page et cliquez en déplaçant la souris vers le haut pour zoomer et vers le bas pour dézoomer" title="Zoom : survolez la page et cliquez en déplaçant la souris vers le haut pour zoomer et vers le bas pour dézoomer"/>
					</a>

					<a class="tool" href="" id="bt-brightness" data-js-action="brightness">
						<img src="<?php echo $baseUrl; ?>/themes/bmn/images/ico-brightness.png" alt="Luminosité : survolez la page et cliquez en déplaçant la souris vers le haut pour éclaircir et vers le bas pour assombrir" title="Luminosité : survolez la page et cliquez en déplaçant la souris vers le haut pour éclaircir et vers le bas pour assombrir"/>
						<img class="ico-active" src="<?php echo $baseUrl; ?>/themes/bmn/images/ico-brightness-active.png" alt="Luminosité : survolez la page et cliquez en déplaçant la souris vers le haut pour éclaircir et vers le bas pour assombrir" title="Luminosité : survolez la page et cliquez en déplaçant la souris vers le haut pour éclaircir et vers le bas pour assombrir"/>
					</a>

					<a class="tool" href="" id="bt-contrast" data-js-action="contrast">
						<img src="<?php echo $baseUrl; ?>/themes/bmn/images/ico-contrast.png" alt="Contraste : survolez la page et cliquez en déplaçant la souris vers le haut pour augmenter le contraste et vers le bas pour le diminuer" title="Contraste : survolez la page et cliquez en déplaçant la souris vers le haut pour augmenter le contraste et vers le bas pour le diminuer"/>
						<img class="ico-active" src="<?php echo $baseUrl; ?>/themes/bmn/images/ico-contrast-active.png" alt="Contraste : survolez la page et cliquez en déplaçant la souris vers le haut pour augmenter le contraste et vers le bas pour le diminuer" title="Contraste : survolez la page et cliquez en déplaçant la souris vers le haut pour augmenter le contraste et vers le bas pour le diminuer"/>
					</a>

					<a class="not-tool" href="" id="bt-rotate-right" data-js-action="rotateRight">
						<img src="<?php echo $baseUrl; ?>/themes/bmn/images/ico-rotation.png" alt="Rotation : cliquez une fois pour tourner l'image vers la droite, vous pouvez cliquer plusieurs fois" title="Rotation : cliquez une fois pour tourner l'image vers la droite, vous pouvez cliquer plusieurs fois"/>
					</a>

					<a class="not-tool" href="" id="bt-reset" data-js-action="reset">
						<img src="<?php echo $baseUrl; ?>/themes/bmn/images/ico-reset.png" alt="Reset : Si vous avez des problèmes d'affichage, remettez les paramètres à zéro en cliquant sur ce bouton" title="Reset : Si vous avez des problèmes d'affichage, remettez les paramètres à zéro en cliquant sur ce bouton"/>
					</a>

					<div id="div-tool-progressbar"></div>
				</div>

				<!-- Informations about the document and the current view -->
				<div id="div-info-view">
						<!-- Informations about the current mode of display -->
					<div id="div-info-mode">
						<a href="" id="link-change-mode" data-js-action="changeMode">
							<img class="hi-button" id="text-mode" src="<?php echo $baseUrl; ?>/themes/bmn/images/bt-rouge-text-mode.png"/>
							<img class="hi-button" id="img-mode" src="<?php echo $baseUrl; ?>/themes/bmn/images/bt-rouge-image-mode.png"/>
						</a>
					</div>

					<!-- Download / Print the PDF of the current page / document -->
					<div id="div-href-pdf">
						<a href="<?php echo $files['pdf']['src']; ?>" download="<?php echo $files['pdf']['name']; ?>">
							<img class="hi-button" src="<?php echo $baseUrl; ?>/themes/bmn/images/bt-rouge-download-pdf.png"/>
						</a>
					</div>

					 <!-- Consult / Download the image version of the document -->
					<div id="div-href-img">
						<a href="<?php echo $files['img']['src']; ?>" js-action="downloadPage" download="<?php echo $files['img']['name']; ?>">
							<img class="hi-button" src="<?php echo $baseUrl; ?>/themes/bmn/images/bt-rouge-download-page.png"/>
						</a>
					</div>

				</div>
			</div>

		</div>
		<script type="text/javascript">
			var idDoc = <?php echo $document->getId(); ?>,
				PERCENTAGE_REDUCTION = <?php echo $document->getReductionRate(); ?>;
		</script>

		<script type="text/javascript" src="<?php echo $baseUrl; ?>/plugins/NewspaperReader/views/public/js/reader-config.js?<?php echo APP_VERSION; ?>"></script>
		<script type="text/javascript" src="<?php echo $baseUrl; ?>/plugins/NewspaperReader/views/public/js/reader.js?<?php echo APP_VERSION; ?>"></script>

		<?php else: ?>
		<?php echo($errorText); ?>
		<?php endif ?>
	</body>
</html>
