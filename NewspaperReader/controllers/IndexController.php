<?php

/**
 *   BMN Newspaper Reader - PHP - Read newspaper articles and interact with them
 *   File IndexController.php : managing the reader, the data passed to it and the admin pages
 *   Author : Valentin Kiffer
 *   Modifications : 1.0 - 29/01/2014 - Redirection to the reader page - VKIFFER
 *                   1.1 - 30/01/2014 - Calling managers to retrieve data - VKIFFER
 *
 *   Version : 1.1
 *
 * @copyright Copyright 2014 moobee global solutions
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

define('NEWSPAPER_READER_PUBLIC_DIR', NEWSPAPER_READER_DIRECTORY."/views/public");
define('NEWSPAPER_READER_FILES_DIR', NEWSPAPER_READER_DIRECTORY."/files");
define('NEWSPAPER_READER_IMPORT_DIR', NEWSPAPER_READER_DIRECTORY."/import");

require_once NEWSPAPER_READER_DIRECTORY.'/managers/EntityManager.php';
require_once NEWSPAPER_READER_DIRECTORY.'/managers/ParsingManager.php';
require_once NEWSPAPER_READER_DIRECTORY.'/managers/ThumbnailManager.php';

/**
 * Plugin "NewspaperReader", controller "Reader"
 *
 * @package NewspaperReader
 */
class NewspaperReader_IndexController extends Omeka_Controller_AbstractActionController
{

	/**
	 * Retourne le chemin du jpg d'une vue à partir des fichiers de l'item et de l'id de la vue
	 * L'id de la vue du fichier est récupérée par l'original_filename (les derniers caractères)
	 * Retourne null si l'image n'existe pas
	 */
	private function getViewImagePath($itemFiles, $viewIndex) {

		foreach ($itemFiles as $file) {
			if (substr($file->original_filename, strlen($file->original_filename) - 8, 8) === str_pad($viewIndex, 4, '0', STR_PAD_LEFT) . '.jpg') {
				return $file->getProperty('uri');
			}
		}
		return null;
	}
	/********************************** Reader part **********************************/

	public function readAction() {

		if(!empty($_GET['item']))
		{
			$id = intval($_GET['item']);

			$db = $this->_helper->db;

			$baseUrl = Zend_Controller_Front::getInstance()->getRequest()->getBaseUrl();

			if($item = $db->getTable('Item')->find($id))
			{
				$this->view->item = $item;
			}

			else
			{
				$this->view->item = new Item();
			}

			$this->view->baseUrl = $baseUrl;
			$em = new EntityManager();

			$thumbnails = array();
			$files = array();

			if($document = $em->selectDocumentByItemId($id, array('company', 'views')))
			{
				$viewsCount = $document->getViewsCount();

				if($document->getDisabled() == 0 && $viewsCount > 0)
				{
					$this->view->errorText = "";
					$this->view->disponibiltyDoc = true;

					$companyName = $document->getCompany()->getName();
					$shelfNumber = $document->getShelfNumber();
					$views = $document->getViews();
					$format = $views[0]->getFormat();
					$institutionCode = get_option('institution_code');


					// On récupère l'identifiant de l'item à afficher
					$itemIdentifier = metadata('Item', array('Dublin Core', 'Identifier'));

					$filesUrl = $baseUrl."/plugins/NewspaperReader/files/".$itemIdentifier;
					$filesDir = NEWSPAPER_READER_FILES_DIR . '/' .$itemIdentifier;
					$filesName = $itemIdentifier . "_";
					$baseFileUrl = $filesUrl . '/' . $filesName;
					$files['pdf']['src'] = substr($filesUrl.'/'.$filesName, 0, -1).'.pdf';
					$files['pdf']['name'] = substr($filesName, 0, -1).'.pdf';

					$files['img']['src'] = $filesUrl.'/'.$filesName.$format;
					$files['img']['name'] = $filesName.$format;

					//Vues existantes
					$existingViews = array();
					$viewImages = array();

					for($i = 0; $i < $viewsCount; $i++) {

						$thumbnailPath = $filesDir . '/' . $filesName . str_pad($i + 1, 4, '0', STR_PAD_LEFT) . '_thumbnail' . $format;
						$thumbnailUrl = $filesUrl . '/' . $filesName . str_pad($i + 1, 4, '0', STR_PAD_LEFT) . '_thumbnail' . $format;

						if(file_exists($thumbnailPath)) {
							$thumbnails[$i]['src'] = $thumbnailUrl;
							$existingViews[] = $i + 1;
							$viewImages[$i + 1] = $this->getViewImagePath($this->view->item->Files, $i + 1);
						}
						else {
							$viewImages[$i + 1] = null;
							$thumbnails[$i]['src'] = $baseUrl . '/themes/bmn/images/default-thumbnail.jpg';
						}

					}

					$this->view->thumbnails = $thumbnails;
					$this->view->viewCount = count($thumbnails);
					$this->view->existingViews = $existingViews;
					$this->view->viewImages = $viewImages;
					$this->view->files = $files;
					$this->view->document = $document;
					$this->view->baseFileUrl = $baseFileUrl;
				}

				else
				{
					$this->view->errorText = "Ce document n'est pas disponible pour le moment";
					$this->view->disponibiltyDoc = false;
				}
			}

			else
			{
				$this->view->errorText = "Ce document n'existe pas";
				$this->view->disponibiltyDoc = false;
			}
		}
	}

	public function viewTextAction()
	{
		if(isset($_GET['doc']) && isset($_GET['view']))
		{
			$docId = $_GET['doc'];
			$viewId = $_GET['view'];
			$em = new EntityManager();

			if($doc = $em->selectDocument($docId, array('views')))
			{
				if($views = $doc->getViews())
				{
					foreach ($views as $view)
					{
						if($view->getNumber() == $viewId)
						{
							$this->view->viewText = $view->getText();
						}
					}
				}
			}
		}
	}

	public function searchAction(){

		//Si les paramètres sont présents
		if(isset($_GET['searchText']) && isset($_GET['scope'])){

			//termes recherché et limite de la recherche
			$searchText = $_GET['searchText'];
			$scope = $_GET['scope'];

			$options = array();
			$searchResult = array();

			$em = new EntityManager();

			switch ($scope){

				//Recherche dans l'item courant
				case 'currentDoc':

					//si l'id de l'item est bien récupéré
					if(isset($_GET['doc'])){

						//Récupération de l'id de l'item
						$docId = $_GET['doc'];


						//if($doc = $em->selectDocument($docId, array('views')))
						if($doc = $em->selectDocumentByItemId($docId, array('views'))){

							// var_dump($doc);

							if($views = $doc->getViews()){
								// var_dump($views);
								// return;
								foreach ($views as $view){

									$options['view'] = $view->getId();
									$pageobjects = $em->searchPOsForViewByText($searchText, $scope, $options);


									if(!empty($pageobjects)){
										// #050514
										foreach ($pageobjects as $pageobject){
											// var_dump($pageobject->getView());
											$searchResult[] = $pageobject->getAssociativeArray();
										}
									}

								}
								echo json_encode($searchResult);
							}
						}
					}

				break;

				case 'allDocs':

				break;

				default:
				break;
			}
		}
	}

	public function advancedSearchResultsAction(){
	}

	/********************************** Admin part **********************************/

	public function indexAction() {

		$baseUrl = Zend_Controller_Front::getInstance()->getRequest()->getBaseUrl();
		$this->view->baseUrl = $baseUrl;

		$importFolderPath = NEWSPAPER_READER_IMPORT_DIR;

		$this->view->importFolderPath = $importFolderPath;

		$em = new EntityManager($this->getPdoDb());
		$companies = $em->selectAllCompanies();

		$this->view->companies = $companies;

		$db = $this->_helper->db;
		$collectionsIds = $db->getTable('Collection')->findAll();
		$collections = array();

		$idElementTitle = $em->selectIdElementByText('Title');

		if(!empty($collectionsIds))
		{
			foreach ($collectionsIds as $collectionId) {

				$id = intval($collectionId['id']);
				$collection = $db->getTable('ElementText')->findBy(array('record_type' => 'Collection',
																		 'record_id' => intval($id),
																		 'element_id' => $idElementTitle));
				if($collection)
				{
					$collections[] = $collection;
				}
			}
		}

		$this->view->collections = $collections;
	}

	public function getPdoDb(){
		$dbConfig = $this->getInvokeArg('bootstrap')->getResource('db')->getAdapter()->getConfig();
		try {
			$options = array(
				PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
			);

			$dbh = new PDO("mysql:host=" . $dbConfig['host'] . ";dbname=" . $dbConfig['dbname'], $dbConfig['username'], $dbConfig['password'], $options);
		}catch (Exception $e) {
			echo "<p>Unable to connect: " . $e->getMessage() ."</p>";
		}
		return $dbh;
	}

	public function importFolderContentAction()
	{
		if(file_exists(NEWSPAPER_READER_IMPORT_DIR))
		{
			$filesTypes = array(
							'.jpg' 	 => 0,
							'.jpeg'   => 0,
							'.png' 	 => 0,
							'.gif'	 => 0,
							'.xml'	 => 0,
							'.pdf'	 => 0,
							);

			$files = array(
				'number' => 0,
				'alto' => 0,
				'to_be_imported' => 0,
				'others' => 0,
				);

			// Opens the directory
			if ($handle = opendir(NEWSPAPER_READER_IMPORT_DIR))
			{
				// Looping over the directory
				while(($file = readdir($handle)) !== false)
				{
					if($file !== '.' && $file !== '..' && $file !== '.gitkeep')
					{
						$files['number']++;

						foreach ($filesTypes as $key => $fileType)
						{
							if($pos = strpos($file, $key))
							{
								if($pos === (strlen($file) - strlen($key)))
								{
									if($key === '.xml')
									{
										if(strpos($file,'alto.') !== false)
										{
											if(strpos($file,'alto.') === 0)
											{
												$files['alto']++;
												$files['to_be_imported']++;
											}
										}
									}
									else
									{
										$filesTypes[$key]++;
										$files['to_be_imported']++;
									}
								}
							}
						}
					   }
				}
			 }
		 $files['others'] = $files['number'] - $files['to_be_imported'];
		 $this->view->filesTypes = $filesTypes;
		 $this->view->files = $files;
		}

		else
		{
			$error = 'ERREUR : Le dossier n\'existe pas ou n\'est pas au bon endroit';
			$this->view->error = $error;
		}
	}

	private function addErrorStatus($infoName, $statusName, $report) {

		$em = new EntityManager();

		// Création d'une info pour la vue courante
		$this->createInformation($em, $infoName);

		// On récupère l'info avec le nom du fichier csv
		$information = $em->selectInformationByWording($infoName);

		// Création du statut
		$this->createStatus($em, $information);

		// Récup du statut du fichier courant
		$status = $em->selectStatusByIdInformation($information->getId(), array('information'));

		// Création du gathering
		$em->insertGathering($report, $status);

		$this->changeStatus($em, $status, $statusName);

	}

	public function endImportAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);

		$em = new EntityManager();
		$em->endReport($_GET['error'], $_GET['nbImportItem']);
	}

	/*
	* Entré : données du post et file
	* Sorti : retourne le csv en JSON ou les erreurs
	*/
	public function getCsvAction(){

		define('CSV_TRANSFERT', 'Le fichier csv n\'a pas pu être transféré sur le serveur');
		define('CSV_GENERAL_FORMAT', 'Le fichier csv est mal formaté');
		define('CSV_MISSING_HEADER_IDENTIFIER', 'Le fichier csv ne contient pas l\'entête : dc:identifier');
		define('CSV_MISSING_HEADER_NBVIEWS','Le fichier csv ne contient pas l\'entête :nombre_de_vues');
		define('CSV_MISSING_HEADER_DATE','Le fichier csv ne contient pas l\'entête : dc:date');
		define('CSV_MISSING_HEADER_TITLE','Le fichier csv ne contient pas l\'entête : dc:title');

		define('FAIL_IMPORT_STATUS','Import échoué');


		//tableau de retour, ['error'] pour les erreurs
		$tabReturn = array();

		// Transfert du csv sur le serveur
		$tmpNameCsv = $_FILES['user-file']['tmp_name'];
		$nameCsv = $_FILES['user-file']['name'];

		$tabReturn['nameCsv'] = $nameCsv;

		/*	#ErrorStatus
		*	Si le csv n'est pas transféré sur le serveur
		*/
		if(!move_uploaded_file($tmpNameCsv, NEWSPAPER_READER_FILES_DIR.'/'.$nameCsv)) {

			$tabReturn['error'][] = CSV_TRANSFERT;
			// $this->view->viewError = 'Le fichier csv n\'a pas pu être transféré sur le serveur';
			return;

		}

		// Parse du csv
		$fieldDelimiter = $_POST['field-delimiter'];
		$pm = new ParsingManager();
		$csvContent = $pm->parseCsvFile(NEWSPAPER_READER_FILES_DIR.'/'.$nameCsv, $fieldDelimiter);

		/*	#ErrorStatus
		*	Si il y a une erreur de formatage dans le fichier csv
		*/
		if(!is_array($csvContent)) {

			$tabReturn['error'][] = CSV_GENERAL_FORMAT;

		} else {

			/*	#ErrorStatus
			*	Si l'entête dc:identifier est présente
			*/
			if(!array_key_exists('dc:identifier', $csvContent[0])) {

				$tabReturn['error'][] = CSV_MISSING_HEADER_IDENTIFIER;

				/*	#ErrorStatus
				*	Si l'entête nombre_de_vues est présente
				*/
			} elseif (!array_key_exists('nombre_de_vues', $csvContent[0])) {

				$tabReturn['error'][] = CSV_MISSING_HEADER_NBVIEWS;

				/*	#ErrorStatus
				*	Si l'entête dc:date est présente
				*/
			} elseif (!array_key_exists('dc:date', $csvContent[0])) {

				$tabReturn['error'][] = CSV_MISSING_HEADER_DATE;

				/*	#ErrorStatus
				*	Si l'entête dc:title est présente
				*/
			}elseif (!array_key_exists('dc:title', $csvContent[0])) {

				$tabReturn['error'][] = CSV_MISSING_HEADER_TITLE;

			}else{
				$tabReturn['csvContent'] = $csvContent;
			}
		}

		$em = new EntityManager();
		if(empty($tabReturn['csvContent'])){
			$em->createReport(FAIL_IMPORT_STATUS, 0);
			if (!empty($tabReturn['error'])){
				$em->changeReportStatus(FAIL_IMPORT_STATUS, $tabReturn['error']);
			}else{
				$em->changeReportStatus(FAIL_IMPORT_STATUS, '');
			}
		}else{

			//Nombre d'items dans l'import
			$totalItems = count($csvContent);

			$em->createReport(FAIL_IMPORT_STATUS, $totalItems);
		}

		echo json_encode($tabReturn);
	}

	public function importFilesAction(){

		//On autorise PHP à créer des fichiers en 777
		umask(0000);

		//tableau de retour, ['error'] pour les erreurs, [views] pour chaque vues
		$tabStatus = array();

		$pm = new ParsingManager();
		//On autorise le script à tourner pendant deux heures
		ini_set('max_execution_time', 7200);

		$this->addLog("\n");
		$this->addLog("---------------------------------------------------");
		$this->addLog('--------------Debut de l import');

		// Différents statuts d'import
		define('CSV_ITEM_FORMAT_IDENTIFER' , 'CSV -> Champ Identifier -> Information dc:identifier mal formatée');
		define('MAX_VIEW_NUMBER', 100);
		define('CSV_ITEM_FORMAT_NBVIEWS' , ' CSV -> Champ Nombre de vues -> Vérifier le nombre de vues\n(Valeur comprise entre 1 et ' . MAX_VIEW_NUMBER);
		define('CSV_ITEM_FORMAT_DATE' , ' CSV -> Champ Date -> Vérifier le formatage de la date\n(AAAA-MM-JJ) : ');
		define('CSV_ITEM_MISSING_TITLE' , 'CSV -> Champ(s) obligatoire manquant : dc:title');
		define('CSV_ITEM_MISSING_IDENTIFIER' , 'CSV -> Champ(s) obligatoire manquant : dc:identifier');
		define('CSV_ITEM_MISSING_DATE' , 'CSV -> Champ(s) obligatoire manquant : dc:date');
		define('CSV_ITEM_MISSING_NBVIEWS' , 'CSV -> Champ(s) obligatoire manquant : nombre_de_vues');
		define('PDF_MISSING' , 'Fichier PDF Absent');
		define('JPG_MISSING' , 'Fichier JPG Absent');
		define('JPG_ERROR' , 'Fichier JPG corrompu');
		define('XML_MISSING' , 'Fichier Alto Absent');
		define('XML_ERROR' , 'Fichier Alto vide');
		define('EXIST_VIEWS' ,' : La vue existe deja');
		define('SUCCESS_STATUS', 'Ok');
		define('PARTIEL_STATUS', 'Partiel');
		define('NO_STATUS', 'Exclu');

		// $this->view->viewError = 'Erreur inconnu';
		$em = new EntityManager($this->getPdoDb());
		//Id du report item courant
		$idReportItem = $em->createReportItem();


		// Verification de l'accès à la page
		if(!empty($_POST)) {

			$selectedCompany = $_POST['selectCompany'];
			$newCompany = $_POST['newCompany'];
			$selectedCollection = $_POST['selectCollection'];
			$documentsArePublic = $_POST['documentsArePublic'];
			$documentsAreFeatured = $_POST['documentsAreFeatured'];
			$itemToImport = $_POST['itemToImport'];
			$nameCsv = $_POST['nameCsv'];

		}

		$this->_helper->viewRenderer->setNoRender(TRUE);

		if($selectedCompany != 0) {

			$newCompany = $em->selectCompany($selectedCompany)->getName();

		}

		if(!$company = $em->selectCompanyByName($newCompany)) {

			// On crée une new company
			$this->createCompany($em, $newCompany);
			$this->addLog('Creation de l objet Company');
			$company = $em->selectCompanyByName($newCompany);

		}

		$idCompany = $company->getId();
		$this->addLog('Id l objet Company '.$idCompany);

		/*	#ErrorStatus
		*	Si les entête dc:title, dc:identifier, dc:date, nombre_de_vues sont remplis
		*/
		if($itemToImport['dc:title'] != '' && $itemToImport['dc:identifier'] != '' && $itemToImport['dc:date'] != '' && $itemToImport['nombre_de_vues'] != ''){

			/*	#ErrorStatus
			*	Si dc:identifier est correctement formaté (séparé par un '_')
			*/
			if(count(explode('_', $itemToImport['dc:identifier'])) !== 3){

				$tabStatus['error'] = CSV_ITEM_FORMAT_IDENTIFER . " ({$itemToImport['dc:identifier']})";
				$tabStatus['status'] = NO_STATUS;
				$em->changeReportStatus(PARTIEL_STATUS, CSV_ITEM_FORMAT_IDENTIFER);
				echo json_encode($tabStatus);
				return;

			}

			/*	#ErrorStatus
			*	Si nombre_de_vues est un nombre cohérent compris entre 0 et 96
			*/
			if(intval($itemToImport['nombre_de_vues']) > MAX_VIEW_NUMBER || intval($itemToImport['nombre_de_vues']) < 1){

				$tabStatus['error'] = CSV_ITEM_FORMAT_NBVIEWS . ' (' . $itemToImport["nombre_de_vues"] . ')';
				$tabStatus['status'] = NO_STATUS;
				$em->changeReportStatus(PARTIEL_STATUS, CSV_ITEM_FORMAT_NBVIEWS.$itemToImport["nombre_de_vues"]);
				echo json_encode($tabStatus);
				return;

			}

			$tabDateVerif = explode('-', $itemToImport['dc:date']);

			/*	#ErrorStatus
			*	Si dc:date est correctement formaté (séparé par un '-')
			*/
			if(count($tabDateVerif) === 3){

				/*	#ErrorStatus
				*	Si dc:date contient des valeurs cohérente (année = 4 chiffre, mois = 2 et inférieur à 12 et jours = 2, inférieur à 31)
				*/
				if(strlen($tabDateVerif[0]) !== 4 && strlen($tabDateVerif[1]) !== 2 && strlen($tabDateVerif[2]) !== 2 && $tabDateVerif[1] > 12 && $tabDateVerif[2] > 31 ){

					$tabStatus['error']  = CSV_ITEM_FORMAT_DATE.$itemToImport['dc:date'];
					$tabStatus['status'] = NO_STATUS;
					$em->changeReportStatus(PARTIEL_STATUS, CSV_ITEM_FORMAT_DATE.$itemToImport['dc:date']);
					echo json_encode($tabStatus);
					return;

				}

			}else{

				$tabStatus['error'] = CSV_ITEM_FORMAT_DATE.$itemToImport['dc:date'];
				$tabStatus['status'] = NO_STATUS;
				$em->changeReportStatus(PARTIEL_STATUS, CSV_ITEM_FORMAT_DATE.$itemToImport['dc:date']);
				echo json_encode($tabStatus);
				return;

			}

		}
		else {

			switch($itemToImport) {

				case ($itemToImport['dc:title'] = ''):
					$tabStatus['error'] = CSV_ITEM_MISSING_TITLE;
					$tabStatus['status'] = NO_STATUS;
					$em->changeReportStatus(PARTIEL_STATUS, CSV_ITEM_MISSING_TITLE);
					echo json_encode($tabStatus);
					return;

				case ($itemToImport['dc:identifier'] = ''):
					$tabStatus['error'] = CSV_ITEM_MISSING_IDENTIFIER;
					$tabStatus['status'] = NO_STATUS;
					$em->changeReportStatus(PARTIEL_STATUS, CSV_ITEM_MISSING_IDENTIFIER);
					echo json_encode($tabStatus);
					return;

				case ($itemToImport['dc:date'] = ''):
					$tabStatus['error'] = CSV_ITEM_MISSING_DATE;
					$tabStatus['status'] = NO_STATUS;
					$em->changeReportStatus(PARTIEL_STATUS, CSV_ITEM_MISSING_DATE);
					echo json_encode($tabStatus);
					return;

				case ($itemToImport['nombre_de_vues'] = ''):
					$tabStatus['error'] = CSV_ITEM_MISSING_NBVIEWS;
					$tabStatus['status'] = NO_STATUS;
					$em->changeReportStatus(PARTIEL_STATUS, CSV_ITEM_MISSING_NBVIEWS);
					echo json_encode($tabStatus);
					return;
			}

		}

		$sucessItem = true;
		$succesStatus = SUCCESS_STATUS;

		//Nombre de vues à importer
		$itemNbViews = intval($itemToImport['nombre_de_vues']);
		$itemName = $itemToImport['dc:identifier'];

		$em->insertInfoRapportItem($itemName, $succesStatus, $idReportItem);

		/*	#ErrorStatus
		*	Si le pdf est présent dans le dossier
		*/
		if(!file_exists(NEWSPAPER_READER_IMPORT_DIR . '/' . $itemName . '.pdf')) {

			//PDF absent
			$tabStatus['error'] = PDF_MISSING . ' : '. $itemName . '.pdf';
			$tabStatus['status'] = NO_STATUS;
			$em->addStatusLibelle($tabStatus['error'], $idReportItem);
			$em->changeStatus($tabStatus['status'], $idReportItem);
			$em->changeReportStatus(PARTIEL_STATUS, 'item');
			echo json_encode($tabStatus);
			return;

		}

		// Récup code institution, cote, date de publi, numéro de vue
		$fileName = $itemToImport['dc:identifier'];
		$fileStrings = explode('_', $fileName);

		// On supprime le code d'institution
		array_shift($fileStrings);

		$shelfNumber = $fileStrings[0];
		$publicationDate = $fileStrings[1];

		$this->addLog('itemNbViews '.$itemNbViews);


		//Pour chaque numéro de vue
		for ($i=1; $i <= $itemNbViews ; $i++) {

			$this->addLog('Numéro vues '.$i);
			$tabView = array();
			$tabView['label'] = $i;

			/*	#ErrorStatus
			*	Si le fichier jpg est présent dans le dossier d'import
			*/
			if(!file_exists(NEWSPAPER_READER_IMPORT_DIR . '/' . $itemName . '_' . str_pad($i, 4, '0', STR_PAD_LEFT) . '.jpg')) {

				$tabView['error'] = JPG_MISSING . ' : '. $itemName . '_' . str_pad($i, 4, '0', STR_PAD_LEFT) . '.jpg';
				$succesStatus = PARTIEL_STATUS;
				$tabStatus['views'][] = $tabView;
				$em->addStatusLibelle($tabView['error'].',', $idReportItem);
				$em->changeStatus($succesStatus, $idReportItem);
				$em->changeReportStatus(PARTIEL_STATUS, 'item');
				continue;

			}else{
				//Vérifie si l'image n'est pas corrompu
				$imageSize = @getimagesize(NEWSPAPER_READER_IMPORT_DIR . '/' . $itemName . '_' . str_pad($i, 4, '0', STR_PAD_LEFT) . '.jpg');
				if($imageSize === false){
					$tabView['error'] = JPG_ERROR . ' : '. $itemName . '_' . str_pad($i, 4, '0', STR_PAD_LEFT) . '.jpg';
					$succesStatus = PARTIEL_STATUS;
					$tabStatus['views'][] = $tabView;
					$em->addStatusLibelle($tabView['error'].',', $idReportItem);
					$em->changeStatus($succesStatus, $idReportItem);
					$em->changeReportStatus(PARTIEL_STATUS, 'item');
					continue;
				}
			}

			/*	#ErrorStatus
			*	Si l'alto est présent dans le dossier d'import
			*/
			if(!file_exists(NEWSPAPER_READER_IMPORT_DIR . '/' . 'alto.' . $itemName . '_' . str_pad($i, 4, '0', STR_PAD_LEFT) . '.xml')){

				$tabView['error'] = XML_MISSING . ' : '. 'alto.' . $itemName . '_' . str_pad($i, 4, '0', STR_PAD_LEFT) . '.xml';
				$succesStatus = PARTIEL_STATUS;
				$tabStatus['views'][] = $tabView;
				$em->addStatusLibelle($tabView['error'].',', $idReportItem);
				$em->changeStatus($succesStatus, $idReportItem);
				$em->changeReportStatus(PARTIEL_STATUS, 'item');
				continue;

			}

			/*	#ErrorStatus
			*	Si l'alto est inférieur à 5ko
			*	Note: Dans ce cas, on importe quand même les fichiers
			*/
			$altoIsEmpty = false;
			if(filesize(NEWSPAPER_READER_IMPORT_DIR . '/' . 'alto.' . $itemName . '_' . str_pad($i, 4, '0', STR_PAD_LEFT) . '.xml') < 5120){
				$tabView['error'] = XML_ERROR . ' : '. 'alto.' . $itemName . '_' . str_pad($i, 4, '0', STR_PAD_LEFT) . '.xml';
				$succesStatus = PARTIEL_STATUS;
				$tabStatus['views'][] = $tabView;
				$em->addStatusLibelle($tabView['error'].',', $idReportItem);
				$em->changeStatus($succesStatus, $idReportItem);
				$em->changeReportStatus(PARTIEL_STATUS, 'item');
				$altoIsEmpty = true;
			}



			$viewNumber = str_pad($i, 4, '0', STR_PAD_LEFT);
			$format = '.jpg';
			$viewStrings = array($viewNumber,$format);
			$infoName =  $itemName . '_' . $viewNumber . $format;

			// On créé les enregistrements Omeka
			$this->addLog('Debut de la creation des enregistrements Omeka');
			$insert_item_id = $this->createOmekaRecords($em, /*$status,*/ $company, $selectedCollection, $itemToImport,
				$viewStrings, $documentsArePublic, $documentsAreFeatured);

			// $status;

			//Si un l'idée de l'item n'est pas récupéré (pas d'insertion dans Omeka)
			if ($insert_item_id === false) {

				$this->addLog('Echec de l\'insertion de l\'item');
				$sucessItem = false;
				continue;

			} else {

				// Si le document à créer n'existe pas déjà
				if (!$document = $em->selectDocumentByCID_PD($idCompany, $publicationDate, array('company'))) {

					// On créé un nouveau document
					$this->createDocument($em, $shelfNumber, $publicationDate, $itemToImport, /*$history,*/ $idCompany, $insert_item_id);
					$this->addLog('Creation de l objet Document');

					// On récup le doc créé
					$document = $em->selectLastInsertedDocument(array('company'));
					$document->setViewsCount($itemNbViews);
					$em->updateDocument($document);

				}

				// Si aucune vue n'existe déjà avec ce numéro de vue pour ce document
				if(!$view = $em->selectViewByNumberAndDocument(intval($viewNumber), $document)) {

					$this->addLog('View recuperee');

					// On créé une nouvelle vue avec ce numéro de vue et avec le contenu du parsing de l'alto
					$this->createView($altoIsEmpty, $pm, $em, $fileName, $viewNumber, $format, $document);

					//On récupère le contenu du fichier csv
					$csvContent = file_get_contents(NEWSPAPER_READER_FILES_DIR.'/'.$nameCsv);
					$csvContent = explode("\n", $csvContent);

					//Si on est à la dernière itération du fascule et qu'il n'y pas eu de problème
					if ($i == $itemNbViews && $sucessItem) {

						$this->addLog('modification fichier csv');

						// Si il y a plus d'une ligne (header)
						if (count($csvContent) != 1) {

							// On supprime la ligne actuelle et la dernière
							unset($csvContent[$i]);
							unset($csvContent[count($csvContent)]);

							// On reforme le contenu avec des retours à la ligne
							$csvContent = implode("\n", $csvContent);
							file_put_contents(NEWSPAPER_READER_FILES_DIR.'/'.$nameCsv, $csvContent);
							$this->addLog('Suppression de la ligne dans le csv');

						} else {

							// On supprime le fichier csv
							unlink(NEWSPAPER_READER_FILES_DIR.'/'.$nameCsv);
							$this->addLog('Suppresion du fichier csv');

						}
					}


				} else {
					$tabView['success'] = EXIST_VIEWS;
					$tabStatus['views'][] = $tabView;
					$em->addStatusLibelle($tabView['success'], $idReportItem);
					$em->changeStatus($succesStatus, $idReportItem);
					$this->addLog('La view existe deja');

				}
			}
			$tabStatus['status']=$succesStatus;

		}
		echo json_encode($tabStatus);

	}

	public function createHistory($em)
	{
		// Création de l'histo || un new tous les 30 jours
		$history = new History();
		$date = date('Y-m-d');
		$history->setDateBeginning($date);
		$dateEnd = date('Y-m-d', strtotime("+30 day"));
		$history->setDateEnd($dateEnd);
		$history->setReportsCount(0);
		$history->setDocumentsCount(0);
		$em->insertHistory($history);
	}

	public function createReport($em, $history)
	{
		// Création du rapport
		$report = new Report();
		$report->setHistory($history);
		$date = date('Y-m-d');
		$report->setDate($date);
		$report = $em->insertReport($report);

		// Update nb de rapports de l'histo courant
		$history->setReportsCount($history->getReportsCount()+1);
		$em->updateHistory($history);
	}

	public function createInformation($em, $fileName)
	{
		// Création de l'info
		$information = new Information();
		$information->setWording($fileName);
		$em->insertInformation($information);
	}

	public function createStatus($em, $information)
	{
		// Création du statut
		$status = new Status();
		$status->setWording(INITIAL_STATUS);
		$status->setInformation($information);
		$em->insertStatus($status);
	}

	public function createCompany($em, $newCompany)
	{
		// Création du titre
		$company = new Company();
		$company->setName($newCompany);
		$date = date('Y-m-d');
		$company->setCreationDate($date);
		$company->setDisabled(0);
		$em->insertCompany($company);
	}

	public function createDocument($em, $shelfNumber, $publicationDate, $fileToImport, /*$history,*/ $idCompany, $idItem)
	{
		// Création du doc
		$document = new Document();
		$document->setViewsCount(0);

		$document->setId_item($idItem);

		$document->setShelfNumber($shelfNumber);
		$date = date('Y-m-d');
		$document->setCreationDate($date);
		$document->setDisabled(0);
		$document->setPublicationDate($publicationDate);
		$reductionRate = $fileToImport['reduction_rate'];
		$document->setReductionRate($reductionRate);
		$company = $em->selectCompany($idCompany);
		$document->setCompany($company);
		$em->insertDocument($document);

	}

	public function changeStatus($em, $status, $newStatus)
	{
		// Change le libellé du statut
		$status->setWording($newStatus);
		$em->updateStatus($status);
	}

	public function createView($altoIsEmpty, $pm, $em, /*$status,*/ $fileName, $viewNumber, $format, $document)
	{
		// Formats images authorisés
		$authorizedFormat = array('.png', '.jpg', '.jpeg', '.gif');

		// Si le fichier est un format image authorisé
		if(in_array($format, $authorizedFormat))
		{
			// On récupère le nom de fichier et on le formate pour le faire correspondre au fichier alto
			$altoFileName = 'alto.' . $fileName . '_' . $viewNumber . '.xml' ;


			if (!$altoIsEmpty) {
				$this->addLog('Debut du parse de l alto '.$altoFileName);

				$altoContent = $pm->parseAltoFile(NEWSPAPER_READER_IMPORT_DIR.'/'.$altoFileName);

				$this->addLog('Fin du parse de l alto');
			}
			else {
				$altoContent = null;
			}

			// Si le fichier alto existe
			$this->addLog('Chemin l alto : '.NEWSPAPER_READER_IMPORT_DIR.'/'.$altoFileName);

			if($altoIsEmpty || file_exists(NEWSPAPER_READER_IMPORT_DIR.'/'.$altoFileName))
			{
				$this->addLog('Le fichier alto existe');
				// On créé une nouvelle vue avec ce numéro de vue et avec le contenu du parsing de l'alto
				$view = new View();

				$view->setNumber($viewNumber);

				$reductionRate = $document->getReductionRate();

				$this->addLog('		Génération du layout...');
				$viewText = $pm->generateLayout($altoContent, $reductionRate);

				$this->addLog('		Set View');
				$view->setText($viewText);

				$this->addLog('		Set Format');
				$view->setFormat($format);

				$this->addLog('		Set Log');
				$view->setDisabled(0);

				$this->addLog('		Set Document');
				$view->setDocument($document);

				$this->addLog('		Génération du page object...');
				$pageobject = $pm->generatePageobject($altoContent, $view);

				$this->addLog('		Set pageobject');
				$view->setPageobject($pageobject->getData());

				$this->addLog('		Insertion de la vue...');
				$em->insertView($view, $this);


				// On supprime l'alto
				if (!$altoIsEmpty) {
					unlink(NEWSPAPER_READER_IMPORT_DIR.'/'.$altoFileName);
					$this->addLog('Le fichier alto a ete supprime');
				}
			}
			// Sinon
			else
			{
				// L'import n'est pas effectué et on change le statut
				$this->addLog(MISSING_ALTO_FILE_STATUS);
			}
		}
		// Sinon
		else
		{
			// L'import n'est pas effectué et on change le statut
			$this->addLog(WRONG_IMG_FILE_FORMAT_STATUS);
		}
	}

	/**
	 * Crée un item et une collection Omeka liée à cet item si elle n'existe pas
	 *
	 * @return type Retourne l'id du dernier item inséré ou mis à jour (ou false si l'insertion a échoué)
	 */
	public function createOmekaRecords($em/*, $status*/, $company, $selectedCollection, $fileToImport,
		$viewStrings, $documentsArePublic, $documentsAreFeatured)
	{

		//On récupère l'identifiant du fascicule pour créer un répertoire
		$item_identifier = $fileToImport['dc:identifier'];

		// Récup l'objet Db
		$db = $this->_helper->db;

		// Récup de l'id de l'élément title
		$idElementTitle = $em->selectIdElementByText('Title');

		// Niveau d'accessibilité
		$metadata = array(
						'public' => $documentsArePublic,
						'featured' => $documentsAreFeatured,
						);
		$this->addLog('before title');
		// Si le titre est précisé
		if(array_key_exists('dc:title', $fileToImport))
		{
			// Récupération des métadonnées et déf de la variable collection
			$elementTexts = $this->extractMetadata($fileToImport);

			$this->addLog(' title existe');

			// Titre du fichier
			$title = $elementTexts['Dublin Core']['Title'][0]['text'];


			// On récupère le nom de la collection avec sin id
			$this->addLog('$selectedCollection  : '.$selectedCollection);
			$this->addLog('$collection  : '.$idElementTitle);

			$titleCollectionSelected = $db->getTable('ElementText')->findBy(array('record_type' => 'Collection',
																	 'record_id' => $selectedCollection,
																	 'element_id' => $idElementTitle));

			// On récupère la collection avec ce titre
			$collection = $db->getTable('ElementText')->findBy(array('record_type' => 'Collection',
																	 'text' => $titleCollectionSelected[0]['text'],
																	 'element_id' => $idElementTitle));


			// Si aucune collection ne possède déjà ce titre
			if(!$collection)
			{
				// On récupère les métadonnées, on modifie l'identifier et on supprime celles qui sont inutiles
				$collectionElementTexts = $elementTexts;
				$collectionElementTexts['Dublin Core']['Identifier'][0]['text'] = $item_identifier;
				$collectionElementTexts['Dublin Core']['Title'][0]['text'] = $titleCollectionSelected[0]['text'];
				unset($collectionElementTexts['Dublin Core']['Date']);
				unset($collectionElementTexts['Dublin Core']['Nombre_de_vues']);

				// Création collection puis récup l'objet correspondant
				$collection = insert_collection($metadata, $collectionElementTexts);
				$this->addLog('Creation de la collection');
			}else{
				$this->addLog('$collection  : deja cree');
			}

			// Dossier de la collection
			$collectionFolderPath = NEWSPAPER_READER_FILES_DIR.'/'.$item_identifier;

			// Si le dossier de la collection n'existe pas
			if(!file_exists($collectionFolderPath))
			{
				// On crée le dossier de la collection avec le titre sans accent et espace
				mkdir($collectionFolderPath);

				$this->addLog('Creation du repertoire de la collection');
			}

			// Format et numéro de vue du fichier
			$viewNumber = $viewStrings[0];
			$format = $viewStrings[1];

			// Nom du fichier image
			$imageFile = $elementTexts['Dublin Core']['Identifier'][0]['text'] . '_' . $viewNumber . $format;


			// Fichier pdf
			$pdfFile = $elementTexts['Dublin Core']['Identifier'][0]['text'] . '.pdf';

			// On crée un titre du type N° du JOUR MOIS ANNEE - COMPANY
			$identifier = $elementTexts['Dublin Core']['Identifier'][0]['text'];
			$identifierStrings = explode('_', $identifier);
			$date = $identifierStrings[2];
			$companyName = $company->getName();
			$title = $companyName;

			// On récupère les métadonnées, on modifie l'identifier et on supprime celles qui sont inutiles
			$itemElementTexts = $elementTexts;
			$itemElementTexts['Dublin Core']['Identifier'][0]['text'] = $identifier;
			$metadata['collection_id'] =  ($selectedCollection != 0) ? $selectedCollection : $collection[0]['record_id'];

			//Onsupprime le nombre de vues pour créer l'élément
			unset($itemElementTexts['Dublin Core']['Nombre_de_vues']);

			$idElementTitle = $em->selectIdElementByText('Identifier');
			$this->addLog('$idElementTitle : '.$idElementTitle);
			$title = $identifier;
			$this->addLog('$title : '.$title);
			// On récupère l'item avec ce titre
			$item = $db->getTable('ElementText')->findBy(array('record_type' => 'Item',
																'text' => $title,
																'element_id' => $idElementTitle));
			// Si aucun item avec un tel titre n'existe
			if(empty($item))
			{
				// Création de l'item
				$oItem = insert_item($metadata, $itemElementTexts);
				$id_item = $oItem->id;
				$this->addLog('Creation de l item');
				$this->addLog('id de l item '.$id_item);
			}
			// Sinon
			else
			{
				$this->addLog('Maj de l item');
				// MAJ de l'item (public / featured)
				$em->updateItemAcessibility($item[0]['record_id'], $metadata);

				$id_item = $item[0]['record_id'];
				$this->addLog('id de l item '.$id_item);
			}
			$this->addLog('Chemin img '.NEWSPAPER_READER_IMPORT_DIR.'/'.$imageFile);
			// Si le fichier image existe dans le dossier d'import
			if(!file_exists(NEWSPAPER_READER_IMPORT_DIR.'/'.$imageFile)) {

				// L'import n'est pas effectué et on change le statut
				$this->addLog(MISSING_IMG_FILE_STATUS);
				return false;
			}

			$this->addLog('pdf '.NEWSPAPER_READER_IMPORT_DIR.'/'. $pdfFile);
			// Si le fichier pdf existe dans le dossier d'import
			if(file_exists(NEWSPAPER_READER_IMPORT_DIR.'/'. $pdfFile))
			{
				// On le déplace dans le dossier files
				rename(NEWSPAPER_READER_IMPORT_DIR.'/' . $pdfFile, $collectionFolderPath . '/' . $pdfFile);
				$this->addLog('Deplacement du pdf');
			}
			// Sinon si il n'existe pas dans le dossier de la collection
			else if(!file_exists($collectionFolderPath.'/'. $pdfFile))
			{
				// L'import n'est pas effectué et on change le statut
				$this->addLog(' pdf deja Deplacé');
				$this->addLog(MISSING_PDF_FILE_STATUS);
				return false;
			}

			// On récupère les métadonnées
			$fileElementTexts = $elementTexts;
			unset($fileElementTexts['Dublin Core']['Nombre_de_vues']);

			// On modifie l'identifier du file et on crée un titre du type N° du JOUR MOIS ANNEE - COMPANY - PAGE X
			$fileElementTexts['Dublin Core']['Identifier'][0]['text'] .= '_'.$viewNumber;
			$viewNumber = ltrim($viewNumber, '0');
			$title .= ' - Page '.$viewNumber;

			// Modification de la métadonnée titre

			// On récupère le file avec ce titre
			$file = $db->getTable('ElementText')->findBy(array('record_type' => 'File',
																'text' => $title,
																'element_id' => $idElementTitle));
			//Si aucun file avec un tel titre n'existe
			if(empty($file))
			{
				// On vide le csv uploadé et on insère le file en base
				unset($_FILES);
				$idItem = $id_item;

				// Si le fichier image existe dans le dossier de la collection
				if(file_exists(NEWSPAPER_READER_IMPORT_DIR.'/'.$imageFile))
				{
					insert_files_for_item($idItem, 'Filesystem', NEWSPAPER_READER_IMPORT_DIR.'/'.$imageFile, array('file_ingest_options'=> array('ignore_invalid_files' => false)));
					$em->insertElementTextsForLastFile($fileElementTexts);
					$this->addLog('Creation du file');
				}
				// Sinon
				else
				{
					// L'import n'est pas effectué et on change le statut
					return false;
				}
			}

			// On crée un titre de fichier du type INSTITUTIONCODE_SHELFNUMBER_YEAR-MONTH-DAY_THUMBNAIL.FORMAT
			$thumbnailFile = str_replace($format, '_thumbnail'.$format, $imageFile);

			// On crée un titre de fichier du type INSTITUTIONCODE_SHELFNUMBER_YEAR-MONTH-DAY_THUMBNAIL_CARD.FORMAT
			$thumbnailCardFile = str_replace($format, '_thumbnail_card'.$format, $imageFile);


			// Si une miniature avec un tel titre n'existe pas
			if(!file_exists($collectionFolderPath.'/'.$thumbnailFile))
			{
				// On la crée
				$tm = new ThumbnailManager();

				// Si le fichier image existe dans le dossier de la collection
				if(file_exists(NEWSPAPER_READER_IMPORT_DIR.'/'.$imageFile))
				{
					$tm->create_thumbnail(NEWSPAPER_READER_IMPORT_DIR.'/'.$imageFile, $collectionFolderPath.'/'.$thumbnailFile, array(
																													'max_width' => 55,
																													'max_height' => 75,
																													'fill_type' => 'crop'
																													));

					$tm->create_thumbnail(NEWSPAPER_READER_IMPORT_DIR.'/'.$imageFile, $collectionFolderPath.'/'.$thumbnailCardFile, array(
																													'max_width' => 270,
																													'max_height' => 210,
																													'fill_type' => 'crop'
																													));
					$this->addLog('Creation des miniatures');

					// On supprime le fichier original
					unlink(NEWSPAPER_READER_IMPORT_DIR.'/'.$imageFile);
					$this->addLog('Suppression du fichier original');
				}
				else
				{
					// L'import n'est pas effectué et on change le statut
					$this->addLog(MISSING_IMG_FILE_STATUS);
					return false;
				}
			}
			$this->addLog($id_item);
			return $id_item;

		}
		// Sinon
		else
		{
			// L'import n'est pas effectué et on change le statut
			$this->addLog(MISSING_TITLE_STATUS);
			return false;
		}
		return false;
	}

	public function slugify($str)
	{
	  if($str !== mb_convert_encoding( mb_convert_encoding($str, 'UTF-32', 'UTF-8'), 'UTF-8', 'UTF-32') )
	  $str = mb_convert_encoding($str, 'UTF-8', mb_detect_encoding($str));
	  $str = htmlentities($str, ENT_NOQUOTES, 'UTF-8');
	  $str = preg_replace('`&([a-z]{1,2})(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig);`i', '\\1', $str);
	  $str = html_entity_decode($str, ENT_NOQUOTES, 'UTF-8');
	  $str = preg_replace(array('`[^a-z0-9]`i','`[-]+`'), '-', $str);

	  return $str;
	}

	private function pr($o) {
		echo '<pre>' . print_r($o, true) . '</pre>';
	}

	public function extractMetadata($fileToImport)
	{
		$excludedMetadata = array("reduction_rate");
		$metadataCount = array();
		$elementTexts = array();

		foreach ($fileToImport as $key => $metadata)
		{
			if(!in_array($key, $excludedMetadata))
			{
				$index = str_replace('dc:', '', $key);
				$index = ucfirst($index);
				$index = preg_replace('/[0-9]+/', '', $index);

				if(!array_key_exists($index, $metadataCount))
				{
					$metadataCount[$index] = 0;
				}

				else
				{
					$metadataCount[$index]++;
				}

				$elementTexts['Dublin Core'][$index][$metadataCount[$index]]['text'] = $metadata;
				$elementTexts['Dublin Core'][$index][$metadataCount[$index]]['html'] = false;
			}
		}

		return $elementTexts;
	}

	public function deleteImportedFileAction()
	{
		if(isset($_GET['identifier']))
		{
			// Récup l'objet Db
			$db = $this->_helper->db;

			$em = new EntityManager();
			$identifier = $_GET['identifier'];

			if($information = $em->selectInformationByWording($identifier))
			{
				$documentStrings = explode("_", $identifier);
				$shelfNumber = $documentStrings[1];
				$publicationDate = $documentStrings[2];
				$viewNumberAndFormat = $documentStrings[3];

				$viewStrings = explode(".", $viewNumberAndFormat);
				$viewNumber = ltrim($viewStrings[0], '0');
				$format = $viewStrings[1];

				$itemIdentifier = str_replace('_'.$viewNumberAndFormat, '', $identifier);
				$fileIdentifier = $itemIdentifier.'_'.$viewStrings[0];

				// On crée un titre de fichier du type INSTITUTIONCODE_SHELFNUMBER_YEAR-MONTH-DAY_THUMBNAIL.FORMAT
				$thumbnail = str_replace('.'.$format, '_thumbnail.'.$format, $identifier);

				// Fichier pdf
				$pdfFile = str_replace('_'.$viewStrings[0], '', $identifier);
				$pdfFile = str_replace($format, 'pdf', $pdfFile);

				// Récup de l'id de l'élément identifier
				$idElementIdentifier = $em->selectIdElementByText('Identifier');

				// // Récup de l'id de l'élément title
				$idElementTitle = $em->selectIdElementByText('Title');

				// On récupère la collection avec cet identifier
				$collection = $db->getTable('ElementText')->findBy(array('record_type' => 'Collection',
																		 'text' => $identifier,
																		 'element_id' => $idElementIdentifier));
				$this->view->result = $collection;

			}
		}
	}

	public function historyAction()
	{

	}

	public function addLog($txt)
	 {
	 	if(NEWSPAPER_READER_DEBUG){
	 		$filename = dirname(__FILE__).'/import-log.txt';

	 		if(!file_exists($filename))
	 		{
	 			touch($filename);
	 		}

	 		$memory_usage = memory_get_usage();

	 		$ligne = date("[j/m/y H:i:s]") . " $memory_usage - $txt \r\n";
	 		$handle = fopen($filename, "a+");
	 		fwrite($handle, $ligne);
	 		fclose($handle);
	 	}

	 }

}
