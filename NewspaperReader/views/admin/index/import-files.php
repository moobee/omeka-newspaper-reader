<?php
$head = array('title' => html_escape(__('Newspaper Reader')));
echo head($head);
echo common('newspaper-reader-nav');?>



<?php if(isset($report)) : ?>
	<div id="primary">
		<div class="field">
			<h2><?php echo __('Rapport d\'import du')?> <?php echo date("d-m-Y", strtotime($report->getDate())); ?></h2>
		</div>
	</div>
<?php foreach ($report->getStatutes() as $status): ?>
	<div class="field" id="<?php echo $status->getInformation()->getWording(); ?>">
		<div class="five columns alpha">
			<label><?php echo __('Fichier')?> <?php echo $status->getInformation()->getWording(); ?></label>
		</div>
		<div class="inputs five columns omega">
			<?php if($status->getWording() == $SUCCESS_STATUS): ?>
				<p class="green explanation"><?php echo nl2br($status->getWording()); ?></p>
				<a class="delete-import-link" href="" data-js="<?php echo $status->getInformation()->getWording(); ?>">Supprimer</a>
			<?php else: ?>
				<p class="red explanation"><?php echo $status->getWording(); ?></p>
			<?php endif; ?>
		</div>
	</div>
<?php endforeach; ?>
<?php else : ?>
	<?php if(isset($viewError)) : ?>
		<div class="five columns alpha">
			<label><?php echo __('Erreur')?></label>
		</div>
		<div class="inputs five columns omega">
				<p class="red explanation"><? echo $viewError ?></p>
		</div>
	<?php endif; ?>
<?php endif; ?>