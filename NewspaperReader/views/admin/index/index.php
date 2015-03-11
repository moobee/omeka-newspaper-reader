<?php
$head = array('title' => html_escape(__('Newspaper Reader')));
echo head($head);
?>
<?php echo common('newspaper-reader-nav'); ?>

<script>
	var baseUrl = "<?php echo $baseUrl; ?>";
</script>

<div id="primary">
	<div class="field">
		<h2><?php echo __('Étape 1 : Vérifiez que tous les fichiers à importer sont dans le bon dossier')?></h2>
	</div>

	<div class="field">
		<div class="two columns alpha">
			<label><?php echo __("Dossier d'import :")?></label>
		</div>
		<div class="inputs five columns omega">
			<p class="explanation"><?php echo __($importFolderPath) ?></p>
		</div>
	</div>

	<div class="field">
		<div class="two columns alpha">
			<label><?php echo __("Contenu du dossier d'import :")?></label>
		</div>
		<div class="inputs five columns omega">
			<p id="content-folder" class="explanation">
			</p>
		</div>
	</div>

	<div class="field">
		<div class="two columns alpha">
			<label><?php echo __('Récapitulatif :')?></label>
		</div>
		<div class="inputs five columns omega">
			<p id="recap-content-folder" class="explanation">
			</p>
		</div>
	</div>

	<form id="form-import-files" enctype="multipart/form-data" method="POST" action="../admin/newspaper-reader/index/get-csv">

		<div class="field">
			<h2><?php echo __('Étape 2 : Sélectionnez un fichier de récolement')?></h2>
		</div>

		<div class="field">
			<div class="two columns alpha">
				<label><?php echo __('Fichier au format CSV :')?></label>
			</div>
			<div class="inputs five columns omega">
				<p class="explanation"><?php echo __('La taille maximale du fichier est de 5 MB.')?></p>
		    	<input id="user-file" name="user-file" type="file"></input>
		    	<p class="explanation" id="file-info-message"></p>
			</div>
		</div>

		<div class="field">
			<div class="two columns alpha">
				<label><?php echo __('Séparateur de champ :')?></label>
			</div>
			<div class="inputs five columns omega">
				<p class="explanation"><?php echo __("Caractère séparant les champs d'une même ligne.")?></p>
		    	<input id="field-delimiter" name="field-delimiter" size="1" type="text" value=','></input>
		    	<p class="explanation" id="field-delimiter-info-message"></p>
			</div>
		</div>

		<div class="field">
			<h2><?php echo __('Étape 3 : Sélectionnez un titre et une collection')?></h2>
		</div>

		<div class="field">
			<div class="two columns alpha">
				<label><?php echo __('Titre des fascicules :'); ?></label>
			</div>
			<div class="inputs five columns omega">
				<p class="explanation"><?php echo __('Nom de la maison de presse ayant publié les fascicules.'); ?></p>
		    	<select id="select-company" name="select-company">
		    		<option value="0" label="Sélectionnez un titre"><?php echo __('Sélectionnez un titre'); ?></option>
		    		<?php foreach ($companies as $company): ?>
		    			<option value="<?php echo $company->getId(); ?>" label="<?php echo $company->getName(); ?>"><?php echo strlen($company->getName()) > 50 ? substr($company->getName(),0,50)." (...)" :  $company->getName(); ?></option>
		    		<?php endforeach; ?>
				</select>
				<p class="explanation"><?php echo __('Ou ajoutez un nouveau titre :'); ?></p>
				<input id="new-company" name="new-company" size="100" type="text" value=""></input>
		    	<p class="explanation" id="company-info-message"></p>
			</div>
		</div>

		<div class="field">
			<div class="two columns alpha">
				<label><?php echo __('Collection des fascicules :'); ?></label>
			</div>
			<div class="inputs five columns omega">
				<p class="explanation"><?php echo __('Nom de la collection à laquelle doivent être liés les fascicules.'); ?></p>
		    	<select id="select-collection" name="select-collection">
		    		<option value="" label="Sélectionnez une collection"><?php echo __('Sélectionnez une collection'); ?></option>
		    		<?php foreach ($collections as $collection): ?>
		    			<?php $i = 0; ?>
		    			<option value="<?php echo $collection[$i]['record_id']; ?>"	label="<?php echo $collection[$i]['text']; ?>">
		    				<?php echo strlen($collection[$i]['text']) > 50 ? substr($collection[$i]['text'],0,50)." (...)" :  $collection[$i]['text']; ?>
		    			</option>
		    			<?php $i++; ?>
		    		<?php endforeach; ?>
				</select>
		    	<p class="explanation" id="collection-info-message"></p>
			</div>
		</div>

		<div class="field">
			<h2><?php echo __('Étape 4 : Sélectionnez des options'); ?></h2>
		</div>

		<div class="field">
			<div class="two columns alpha">
				<label><?php echo __('Rendre les fascicules publics ?'); ?></label>
			</div>
			<div class="inputs five columns omega">
				<p class="explanation"><?php echo __('Les fascicules publics sont consultables par tout le monde.'); ?></p>
		    	<input id="documents-are-public" name="documents-are-public" type="checkbox">
			</div>
		</div>

		<div class="field">
			<div class="two columns alpha">
				<label><?php echo __('Mettre en avant les fascicules ?'); ?></label>
			</div>
			<div class="inputs five columns omega">
				<p class="explanation"><?php echo __("Les fascicules mis en avant apparaissent sur la page d'accueil."); ?></p>
		    	<input id="documents-are-featured" name="documents-are-featured" type="checkbox">
			</div>
		</div>

		<div class="field">
		<h2><?php echo __("Étape 5 : Procéder à l'import des fichiers"); ?></h2>
			<input id="submit-button" class="submit submit-medium" type="submit"></input>
		</div>
	</form>
</div>
<?php echo foot(); ?>
