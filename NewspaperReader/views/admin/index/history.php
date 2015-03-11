<?php
$head = array('title' => html_escape(__('Newspaper Reader')));
echo head($head);
?>
<?php echo common('newspaper-reader-nav'); ?>


<?php
$db = get_db();
$idReport = $db->query("SELECT * FROM `omeka_newspaper_reader_reports` ORDER BY id DESC LIMIT 10");

$reports = array();

while($resIdReport = $idReport->fetch()) {
	$reports[] = $resIdReport;
}

?>

<h2 class="history-page-title" ><?php echo __('Historique des 10 derniers imports'); ?></h2>

<?php if (count($reports) > 0): ?>

	<a class="button" data-unfold="off" data-js-action="expand-all-histories" ><?php echo __("Déplier tout l'historique"); ?></a>

	<?php foreach ($reports as $idReport): ?>


		<div class="history-bloc">
			<h3 class="history-title"><?php echo __('Import du %s au %s', format_date($idReport['date_beginning'], $format = Zend_Date::DATETIME), format_date($idReport['date_end'], $format = Zend_Date::DATETIME)); ?> </h3>

			<span class="history-title-span">
				<?php echo __('fascicules importés : %s / %s', $idReport['nb_import_items'], $idReport['nb_tot_items']); ?> <br/>
			</span>
			<a data-js-action="expand-history" class='history add-line button blue'>+</a>

			<div class="history-lines" >
				<?php if ($idReport['nb_tot_items'] > 0): ?>

					<?php
					$allImport = $db->query("SELECT * FROM `omeka_newspaper_reader_report_Item` WHERE id_report = ".$idReport['id']);
					$allImport = $allImport->fetchAll();
					?>

						<?php foreach ($allImport as $key => $value): ?>
							<?php if($value['status'] === 'Ok'): ?>
							<div class="history-line green-bc">
								<p><?php echo $value['fascicule_libelle']; ?> : <?php echo __($value['status']); ?></p>
								<p><?php echo __($value['status_libelle']); ?></p>
							</div>
							<?php endif; ?>
							<?php if($value['status'] === 'Partiel'): ?>
								<div class="history-line orange-bc">
									<p><?php echo $value['fascicule_libelle']; ?> : <?php echo __($value['status']); ?></p>
									<?php $libelle = explode(',', $value['status_libelle']); ?>
									<?php foreach ($libelle as $key1 => $value1) : ?>
										<p class="alinea orange"> <?php echo __($value1); ?></p>
									<?php endforeach;?>

								</div>
							<?php endif; ?>
							<?php if($value['status'] === 'Exclu'): ?>
								<div class="history-line red-bc">
									<p><?php echo $value['fascicule_libelle']; ?> : <?php echo __($value['status']); ?></p>
									<p class="alinea red"> <?php echo __($value['status_libelle']); ?></p>
								</div>
							<?php endif; ?>
						<?php endforeach; ?>

				<?php else: ?>
					<div class="history-line grey-bc">
						<p><?php echo __('Import échoué pour une raison inconnue')?></p>
					</div>
				<?php endif ?>
			</div>
		</div>
	<?php endforeach ?>
<?php else: ?>
	<p>
		<?php echo __("Il n'y a pas encore d'historiques à afficher. Cliquez sur l'onglet <<Importer des fichiers>> pour ajouter des fascicules.")?>
	</p>
<?php endif ?>
<?php echo foot(); ?>