<?php

/**
 * 	BMN Newspaper Reader - PHP - Read newspaper articles and interact with them
 *  File EntityManager.php : managing the entities and the DB queries
 *  Author : Valentin Kiffer
 *  Modifications : 1.0 - 29/01/2014 - Reading of the companies' data - VKIFFER
 *                  1.1 - 30/01/2014 - Reading of the documents' data  - VKIFFER
 *					1.2 - 31/01/2014 - Reading of the views' and pagobjects' data - VKIFFER
 *					1.3 - 03/02/2014 - Disabled attributes added - VKIFFER
 * 					1.4 - 11/03/2014 - CRUD operations for the import tables - VKIFFER
 *
 *   Version : 1.4
 *
 * @copyright Copyright 2014 moobee global solutionsFiles
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 * @package NewspaperReader
 */

/**
 * EntityManager class.
 *
 * @package NewspaperReader
 */
class EntityManager
{
	private $pdo;

	public function __construct($getpdo = null){
		$this->pdo = $getpdo;
	}

	/*
	* Crée un nouveaux Rapport d'import avec une date de début, status et nombre total d'item
	* Les autres champs sont à 0
	*/
	public function createReport($status, $nb_tot_items){
		$dbObject = get_db();

		$queryInsertReport = "INSERT INTO `omeka_newspaper_reader_reports` (date_beginning, status, nb_tot_items)
		VALUES (NOW(), " . $dbObject->quote($status) . ", " . $dbObject->quote($nb_tot_items) . ")";

		$resultCompanies = $dbObject->query($queryInsertReport);
	}

	/*
	* Change le status du dernier rapport et du libelle
	* (appelé dés qu'un import échoue)
	*/
	public function changeReportStatus($status, $libelle){
		$dbObject = get_db();

		$queryIdLastReport = "SELECT id FROM `omeka_newspaper_reader_reports` ORDER BY id DESC LIMIT 0, 1";
		$idLastReport = $dbObject->query($queryIdLastReport)->fetch();

		$queryInsertReport = "UPDATE `omeka_newspaper_reader_reports` SET `status` = " . $dbObject->quote($status) . ", `status_libelle` = " . $dbObject->quote($libelle) . " WHERE id = " . $idLastReport['id'];

		$resultCompanies = $dbObject->query($queryInsertReport);
	}


	/*
	* Clot le rapport en indiquant ça date de fin, le nombre d'item  importé et le status final
	*
	*/
	public function endReport($error, $nbImportItem){
		$setError = '';
		$setnbImportItem = 0;
		//Si il n'y a pas eu d'erreur -> status OK sinon le statut est déjà à jour
		if($error === 'false'){
			$setError = ', status_libelle = "OK"';
		}

		//Si au moins un item à été importé, on met à jour le nombre d'item importé
		if($nbImportItem !== 0 ){
			$setnbImportItem = ', nb_import_items = '.$nbImportItem;
		}


		$dbObject = get_db();

		//récupère le dernier id inséret dans la table rapport
		$queryIdLastReport = "SELECT id FROM `omeka_newspaper_reader_reports` ORDER BY id DESC LIMIT 0, 1";
		$idLastReport = $dbObject->query($queryIdLastReport)->fetch();

		//Lance la requete pour clore le rapport
		$queryEndReport = "UPDATE `omeka_newspaper_reader_reports` SET `date_end` = NOW() " . $setError  . $setnbImportItem . "  WHERE id = ".$idLastReport['id'];

		$dbObject->query($queryEndReport);
	}

	/**
	* Crée un nouveau rappport
	* @retourne l'id du dernier rapport d'item inséré
	*/
	public function createReportItem(){
		$dbObject = get_db();

		//Récupère l'i du dernier rapport inséré
		$queryIdLastReport = "SELECT id FROM `omeka_newspaper_reader_reports` ORDER BY id DESC LIMIT 0, 1";
		$idLastReport = $dbObject->query($queryIdLastReport)->fetch();

		//Crée un nouveau rapport d'item
		$queryInsertReportImport = "INSERT INTO `omeka_newspaper_reader_report_Item` ( id_report)
		VALUES (" . $idLastReport['id'] . ")";
		$dbObject->query($queryInsertReportImport);

		//Récupère le dernier rapport d'item inséré
		$queryLastInsertReportImport = "SELECT id FROM `omeka_newspaper_reader_report_Item` ORDER BY id DESC LIMIT 0, 1";
		$LastInsertReportImport = $dbObject->query($queryLastInsertReportImport)->fetch();

		//Dernier rapport d'item inséré
		return $LastInsertReportImport['id'];
	}


	/*
	 * Met à jour le rapport d'item idReportItem avec:
	 * un nouveau status et le nom du fascicule en paramètre
	 */
	public function insertInfoRapportItem($itemName, $succesStatus, $idReportItem){
		$dbObject = get_db();
		$queryInsert = "UPDATE `omeka_newspaper_reader_report_Item`  SET
		Status = '" . $succesStatus . "', fascicule_libelle = '" . $itemName . "', Status_libelle = ''
		WHERE id = " . $idReportItem;
		$dbObject->query($queryInsert);
	}

	/*
	 * Concatène le libelle du status du rapport d'item IdReportItem
 	 */
	public function addStatusLibelle($status, $idReportItem){

		$dbObject = get_db();
		$queryAddStatus = "UPDATE `omeka_newspaper_reader_report_Item`  SET
		Status_Libelle = CONCAT('" . $status . "', Status_Libelle)
		WHERE id = " . $idReportItem;
		$dbObject->query($queryAddStatus);
	}

	/*
	 * Change le statut d'un rapport d'item idReportItem
	 */
	public function changeStatus($status, $idReportItem){

		$dbObject = get_db();
		$queryAddStatus = "UPDATE `omeka_newspaper_reader_report_Item`  SET
		Status = '" . $status . "'
		WHERE id = " . $idReportItem;
		$dbObject->query($queryAddStatus);
	}



	/**
     * Selects from DB all companies and returns hydrated objects
     * @param $options Defines the hydration level regarding the linked entities
 	 * @return $companies The hydrated objects : companies with their infos and documents
     */
	public function selectAllCompanies($options = array())
	{
		$dbObject = get_db();

		$queryAllCompanies = "SELECT * FROM `omeka_newspaper_reader_companies`";

		$resultCompanies = $dbObject->query($queryAllCompanies)->fetchAll();

		$companies = array();
		$i = 0;

		if(!empty($resultCompanies))
		{
			foreach ($resultCompanies as $company)
			{
				$company = new Company();
				$company->setId($resultCompanies[$i]['id']);
				$company->setName($resultCompanies[$i]['name']);
				$company->setCreationDate($resultCompanies[$i]['creation_date']);
				$company->setDisabled($resultCompanies[$i]['disabled']);

				foreach ($options as $option)
				{
					switch ($option)
					{
						case 'documents':

							$id = $company->getId();

							$queryPublication = "SELECT `id_document` FROM `omeka_newspaper_reader_publications`
							 	 				 WHERE `omeka_newspaper_reader_publications`.`id_company` = $id";

							$resultPublication = $dbObject->query($queryPublication)->fetchAll();

							if(!empty($resultPublication))
							{
								$documents = array();

								for($j = 0; $j < count($resultPublication); $j++)
								{
									$documents[] = $this->selectDocument($resultPublication[$j]['id_document']);
								}

								$company->setDocuments($documents);
							}
							break;

						default:
							break;
					}
				}

				$companies[$i] = $company;
				$i++;
			}

			return $companies;
		}

		return null;
	}

	/**
     * Selects from DB a company by its id and returns an hydrated object
     * @param $id The company id
     * @param $options Defines the hydration level regarding the linked entities
 	 * @return $company The hydrated object : a company with its infos and documents
     */
	public function selectCompany($id, $options = array())
	{
		$dbObject = get_db();

		$queryCompany = "SELECT * FROM `omeka_newspaper_reader_companies`
						 WHERE `omeka_newspaper_reader_companies`.`id` = $id";
		$resultCompany = $dbObject->query($queryCompany)->fetch();

		$queryPublication = "SELECT `id_document` FROM `omeka_newspaper_reader_publications`
						 	 WHERE `omeka_newspaper_reader_publications`.`id_company` = $id";

		$resultPublication = $dbObject->query($queryPublication)->fetchAll();

		$company = new Company();

		if(!empty($resultCompany))
		{
			$company->setId($resultCompany['id']);
			$company->setName($resultCompany['name']);
			$company->setCreationDate($resultCompany['creation_date']);
			$company->setDisabled($resultCompany['disabled']);

			foreach ($options as $option)
			{
				switch ($option)
				{
					case 'documents':
						if(!empty($resultPublication))
						{
							$documents = array();

							for($i = 0; $i < count($resultPublication); $i++)
							{
								$documents[] = $this->selectDocument($resultPublication[$i]['id_document']);
							}

							$company->setDocuments($documents);
						}
						break;

					default:
						break;
				}
			}

			return $company;
		}

		return null;
	}

	/**
     * Selects from DB a company by its name and returns an hydrated object
     * @param $name The company's name
     * @param $options Defines the hydration level regarding the linked entities
 	 * @return $company The hydrated object : a company with its infos and documents
     */
	public function selectCompanyByName($name, $options = array())
	{
		$dbObject = get_db();

		$queryCompany = "SELECT * FROM `omeka_newspaper_reader_companies`
						 WHERE `omeka_newspaper_reader_companies`.`name` LIKE \"%$name%\"";
		$resultCompany = $dbObject->query($queryCompany)->fetch();

		$company = new Company();

		if(!empty($resultCompany))
		{
			$company->setId($resultCompany['id']);
			$company->setName($resultCompany['name']);
			$company->setCreationDate($resultCompany['creation_date']);
			$company->setDisabled($resultCompany['disabled']);

			foreach ($options as $option)
			{
				switch ($option)
				{
					case 'documents':
						$id = $company->getId();
						$queryPublication = "SELECT `id_document` FROM `omeka_newspaper_reader_publications`
										 	 WHERE `omeka_newspaper_reader_publications`.`id_company` = $id";

						$resultPublication = $dbObject->query($queryPublication)->fetchAll();

						if(!empty($resultPublication))
						{
							$documents = array();

							for($i = 0; $i < count($resultPublication); $i++)
							{
								$documents[] = $this->selectDocument($resultPublication[$i]['id_document']);
							}

							$company->setDocuments($documents);
						}
						break;

					default:
						break;
				}
			}

			return $company;
		}

		return null;
	}

	/**
     * Inserts a company into the DB
     * @param $company The company to insert
     */
	public function insertCompany($company)
	{
		$name = $company->getName();
		$creationDate = $company->getCreationDate();
		$disabled = $company->getDisabled();

		$dbObject = get_db();

		$queryInsertCompany = "INSERT INTO `omeka_newspaper_reader_companies` (`omeka_newspaper_reader_companies`.`name`,
																			   `omeka_newspaper_reader_companies`.`creation_date`,
																			   `omeka_newspaper_reader_companies`.`disabled`)
					  	       VALUES (\"$name\", \"$creationDate\", $disabled)";

		$dbObject->query($queryInsertCompany);
	}

	/**
     * Selects from DB a document by its id and returns an hydrated object
     * @param $id The document id
     * @param $options Defines the hydration level regarding the linked entities
 	 * @return $document The hydrated object : a document with its infos, company and views
     */
	public function selectDocument($id, $options = array())
	{
		$dbObject = get_db();
		// var_dump('idselect:'.$id);
		$queryDocument = "SELECT * FROM `omeka_newspaper_reader_documents`
						  WHERE `omeka_newspaper_reader_documents`.`id` = $id";

		$resultDocument = $dbObject->query($queryDocument)->fetch();
		//var_dump($resultDocument);

		$queryPublication = "SELECT * FROM `omeka_newspaper_reader_publications`
						 	 WHERE `omeka_newspaper_reader_publications`.`id_document` = $id";


		$resultPublication = $dbObject->query($queryPublication)->fetch();
		//var_dump($resultPublication);

		$document = new Document();

		if(!empty($resultDocument))
		{
			$document->setId($resultDocument['id']);
			$document->setViewsCount($resultDocument['views_count']);
			$document->setShelfNumber($resultDocument['shelf_number']);
			$document->setCreationDate($resultDocument['creation_date']);
			$document->setReductionRate($resultDocument['reduction_rate']);

			if(!empty($resultPublication))
			{
				$document->setPublicationDate($resultPublication['date']);
			}

			$document->setDisabled($resultDocument['disabled']);
			// var_dump('option'.$options);
			foreach ($options as $option)
			{
				// var_dump($option);
				switch ($option)
				{
					case 'company':
						if(!empty($resultPublication))
						{
							$document->setCompany($this->selectCompany($resultPublication['id_company']));
						}
						break;

					case 'views':
						$queryViews = "SELECT `id` FROM `omeka_newspaper_reader_views`
							 	 	   WHERE `omeka_newspaper_reader_views`.`id_document` = " . $id;
						// var_dump('views');
						$resultViews = $dbObject->query($queryViews)->fetchAll();

						if(!empty($resultViews))
						{
							$views = array();

							for($i = 0; $i < count($resultViews); $i++)
							{
								$views[] = $this->selectView($resultViews[$i]['id']);
							}

							$document->setViews($views);
						}
						break;

					default:
					break;
				}
			}

			return $document;
		}

		return null;
	}

		/**
	     * Selects from DB a document by its id and returns an hydrated object
	     * @param $id The item id linked to the document
	     * @param $options Defines the hydration level regarding the linked entities
	 	 * @return $document The hydrated object : a document with its infos, company and views
	     */
		public function selectDocumentByItemId($id_item, $options = array())
		{
			$dbObject = get_db();
			$queryDocument = "SELECT * FROM `omeka_newspaper_reader_documents`
							  WHERE `omeka_newspaper_reader_documents`.`id_item` = $id_item";

			$resultDocument = $dbObject->query($queryDocument)->fetch();

			//return $queryDocument;
			if(!empty($resultDocument))
			{
				$queryPublication = "SELECT * FROM `omeka_newspaper_reader_publications`
								 	 WHERE `omeka_newspaper_reader_publications`.`id_document` = " . $resultDocument['id'];

				$resultPublication = $dbObject->query($queryPublication)->fetch();

				$document = new Document();

				$document->setId($resultDocument['id']);
				$document->setViewsCount($resultDocument['views_count']);
				$document->setShelfNumber($resultDocument['shelf_number']);
				$document->setCreationDate($resultDocument['creation_date']);
				$document->setReductionRate($resultDocument['reduction_rate']);

				if(!empty($resultPublication))
				{
					$document->setPublicationDate($resultPublication['date']);
				}

				$document->setDisabled($resultDocument['disabled']);
				// var_dump('option'.$options);
				foreach ($options as $option)
				{
					// var_dump($option);
					switch ($option)
					{
						case 'company':
							if(!empty($resultPublication))
							{
								$document->setCompany($this->selectCompany($resultPublication['id_company']));
							}
							break;

						case 'views':
							$queryViews = "SELECT `id` FROM `omeka_newspaper_reader_views`
								 	 	   WHERE `omeka_newspaper_reader_views`.`id_document` = " . $document->getId();
							// var_dump('views');
							$resultViews = $dbObject->query($queryViews)->fetchAll();

							if(!empty($resultViews))
							{
								$views = array();

								for($i = 0; $i < count($resultViews); $i++)
								{
									$views[] = $this->selectView($resultViews[$i]['id']);
								}

								$document->setViews($views);
							}
							break;

						default:
						break;
					}
				}

				return $document;
			}

			return null;
		}


	/**
     * Selects from DB the last inserted document and returns an hydrated object
 	 * @return $document The hydrated object : a document with its infos, company and views
     */
	public function selectLastInsertedDocument($options = array())
	{
		$dbObject = get_db();

		$document = new Document();

		$queryDocument = "SELECT * FROM `omeka_newspaper_reader_documents`
						  ORDER BY `omeka_newspaper_reader_documents`.`id` DESC";

		$resultDocument = $dbObject->query($queryDocument)->fetch();

		if(!empty($resultDocument))
		{
			$idDocument = $resultDocument['id'];
			$queryPublication = "SELECT * FROM `omeka_newspaper_reader_publications`
						  		 WHERE `omeka_newspaper_reader_publications`.`id_document` = $idDocument";
			$resultPublication = $dbObject->query($queryPublication)->fetch();

			if(!empty($resultPublication))
			{
				$document->setPublicationDate($resultPublication['date']);
			}

			$document->setId($idDocument);
			$document->setViewsCount($resultDocument['views_count']);
			$document->setShelfNumber($resultDocument['shelf_number']);
			$document->setCreationDate($resultDocument['creation_date']);
			$document->setReductionRate($resultDocument['reduction_rate']);
			$document->setDisabled($resultDocument['disabled']);

			foreach ($options as $option)
			{
				switch ($option)
				{
					case 'company':
						if(!empty($resultPublication))
						{
							$document->setCompany($this->selectCompany($resultPublication['id_company']));
						}
						break;

					case 'views':
						$queryViews = "SELECT `id` FROM `omeka_newspaper_reader_views`
							 	 	   WHERE `omeka_newspaper_reader_views`.`id_document` = $id";

						$resultViews = $dbObject->query($queryViews)->fetchAll();

						if(!empty($resultViews))
						{
							$views = array();

							for($i = 0; $i < count($resultViews); $i++)
							{
								$views[] = $this->selectView($resultViews[$i]['id']);
							}

							$document->setViews($views);
						}
						break;

					default:
					break;
				}
			}

			return $document;
		}

		return null;
	}

	/**
     * Selects from DB the first document by its idCompany, publication date and shelf number and returns an hydrated object
     * /!\ this function doesn't work when there is more than one document by company / publicationDate
     * @param $idCompany The document's company id
     * @param $publicationDate The document's publication date
     * @param $options Defines the hydration level regarding the linked entities
 	 * @return $document The hydrated object : a document with its infos, company and views
     */
	public function selectDocumentByCID_PD($idCompany, $publicationDate, $options = array())
	{
		$dbObject = get_db();

		$queryPublication = "SELECT * FROM `omeka_newspaper_reader_publications`
						     WHERE `omeka_newspaper_reader_publications`.`date` = \"$publicationDate\"
						     AND `omeka_newspaper_reader_publications`.`id_company` = $idCompany
						     LIMIT 1";

		$resultPublication = $dbObject->query($queryPublication)->fetch();

		$document = new Document();

		if(!empty($resultPublication))
		{
			$id = $resultPublication['id_document'];
			$queryDocument = "SELECT * FROM `omeka_newspaper_reader_documents`
							  WHERE `omeka_newspaper_reader_documents`.`id` = $id";

			$resultDocument = $dbObject->query($queryDocument)->fetch();

			if(!empty($resultDocument))
			{
				$document->setId($resultDocument['id']);
				$document->setViewsCount($resultDocument['views_count']);
				$document->setShelfNumber($resultDocument['shelf_number']);
				$document->setCreationDate($resultDocument['creation_date']);
				$document->setPublicationDate($resultPublication['date']);
				$document->setReductionRate($resultDocument['reduction_rate']);
				$document->setDisabled($resultDocument['disabled']);

				foreach ($options as $option)
				{
					switch ($option)
					{
						case 'company':
							if(!empty($resultPublication))
							{
								$document->setCompany($this->selectCompany($resultPublication['id_company']));
							}
							break;

						case 'views':
							$queryViews = "SELECT `id` FROM `omeka_newspaper_reader_views`
								 	 	   WHERE `omeka_newspaper_reader_views`.`id_document` = $id";

							$resultViews = $dbObject->query($queryViews)->fetchAll();

							if(!empty($resultViews))
							{
								$views = array();

								for($i = 0; $i < count($resultViews); $i++)
								{
									$views[] = $this->selectView($resultViews[$i]['id']);
								}

								$document->setViews($views);
							}
							break;

						default:
						break;
					}
				}

				return $document;
			}
		}

		return null;
	}

	/**
     * Inserts a document and its corresponding publication into the DB
     * @param $document The document to insert
     */
	public function insertDocument($document)
	{
		$viewsCount = $document->getViewsCount();
		$shelfNumber = $document->getShelfNumber();
		$creationDate = $document->getCreationDate();
		$disabled = $document->getDisabled();
		$publicationDate = $document->getPublicationDate();
		$reductionRate = $document->getReductionRate();
		$idCompany = $document->getCompany()->getId();
		$id_item =  $document->getId_item();


		$dbObject = get_db();

		$queryInsertDocument = "INSERT INTO `omeka_newspaper_reader_documents` (
																				`omeka_newspaper_reader_documents`.`id_item`,
																				`omeka_newspaper_reader_documents`.`views_count`,
																				`omeka_newspaper_reader_documents`.`shelf_number`,
																			    `omeka_newspaper_reader_documents`.`creation_date`,
																			    `omeka_newspaper_reader_documents`.`reduction_rate`,
																			    `omeka_newspaper_reader_documents`.`disabled`)
					  	       VALUES ($id_item, $viewsCount, \"$shelfNumber\", \"$creationDate\", $reductionRate, $disabled)";

		$dbObject->query($queryInsertDocument);

		$queryInsertPublication = "INSERT INTO `omeka_newspaper_reader_publications` (`omeka_newspaper_reader_publications`.`id_company`,
																			    	  `omeka_newspaper_reader_publications`.`date`)
					  	       	   VALUES ($idCompany, \"$publicationDate\")";

		$dbObject->query($queryInsertPublication);

		$document = $this->selectLastInsertedDocument();
		$idDocument = $document->getId();

		$queryUpdatePublication = "UPDATE `omeka_newspaper_reader_publications`
								   SET `omeka_newspaper_reader_publications`.`id_document` = $idDocument
								   WHERE `omeka_newspaper_reader_publications`.`id_company` = $idCompany
								   AND `omeka_newspaper_reader_publications`.`date` = \"$publicationDate\"";
		$dbObject->query($queryUpdatePublication);

	}

	/**
     * Updates a document in the DB
     * @param $document The document to update
     */
	public function updateDocument($document)
	{
		$id = $document->getId();
		$viewsCount = $document->getViewsCount();
		$shelfNumber = $document->getShelfNumber();
		$creationDate = $document->getCreationDate();
		$disabled = $document->getDisabled();
		$publicationDate = $document->getPublicationDate();
		$reductionRate = $document->getReductionRate();
		$idCompany = $document->getCompany()->getId();

		$dbObject = get_db();

		$queryUpdateDocument = "UPDATE `omeka_newspaper_reader_documents`
					  	           SET `omeka_newspaper_reader_documents`.`views_count` = $viewsCount,
								       `omeka_newspaper_reader_documents`.`shelf_number` = \"$shelfNumber\",
								       `omeka_newspaper_reader_documents`.`creation_date` = \"$creationDate\",
								       `omeka_newspaper_reader_documents`.`reduction_rate` = $reductionRate,
								       `omeka_newspaper_reader_documents`.`disabled` = $disabled
					  	         WHERE `omeka_newspaper_reader_documents`.`id` = $id";
		$dbObject->query($queryUpdateDocument);

		$queryUpdatePublication = "UPDATE `omeka_newspaper_reader_publications`
								      SET `omeka_newspaper_reader_publications`.`id_company` = $idCompany,
								          `omeka_newspaper_reader_publications`.`date` = \"$publicationDate\"
					  	       	    WHERE `omeka_newspaper_reader_publications`.`id_document` = $id";
		$dbObject->query($queryUpdatePublication);
	}

	/**
     * Selects from DB a view by its id and returns an hydrated object
     * @param $id The view id
     * @param $options Defines the hydration level regarding the linked entities
 	 * @return $view The hydrated object : a view with its infos, document and pageobject
     */
	public function selectView($id, $options = array())
	{
		$dbObject = get_db();

		$queryView = "SELECT * FROM `omeka_newspaper_reader_views`
					  WHERE `omeka_newspaper_reader_views`.`id` = $id";
		$resultView = $dbObject->query($queryView)->fetch();

		$view = new View();

		if(!empty($resultView))
		{
			$view->setId($resultView['id']);
			$view->setNumber($resultView['number']);
			$view->setText(htmlspecialchars_decode($resultView['text']));
			$view->setFormat($resultView['format']);
			$view->setDisabled($resultView['disabled']);


			foreach ($options as $option)
			{
				switch ($option)
				{
					case 'document':
						if(!empty($resultView))
						{
							$view->setDocument($this->selectDocument($resultView['id_document']));
						}
						break;

					case 'pageobject':
						$queryPageobject = "SELECT `id` FROM `omeka_newspaper_reader_views`
							 	 	   		WHERE `omeka_newspaper_reader_views`.`id` = $id";

						$resultPageobject = $dbObject->query($queryPageobject)->fetch();

						if(!empty($resultPageobject))
						{
							$view->setPageobject($this->selectPageobject($resultPageobject['id']));
						}
						break;



					default:
						break;
				}
			}

			return $view;
		}

		return null;
	}

	/**
     * Selects from DB a view by its number and document and returns an hydrated object
     * @param $number The view number
     * @param $document The view's document
     * @param $options Defines the hydration level regarding the linked entities
 	 * @return $view The hydrated object : a view with its infos, document and pageobject
     */
	public function selectViewByNumberAndDocument($number, $document, $options = array())
	{
		$idDocument = $document->getId();
		$dbObject = get_db();

		$queryView = "SELECT * FROM `omeka_newspaper_reader_views`
					  WHERE `omeka_newspaper_reader_views`.`number` = $number
					  AND  `omeka_newspaper_reader_views`.`id_document` = $idDocument";

		$resultView = $dbObject->query($queryView)->fetch();

		$view = new View();

		if(!empty($resultView))
		{
			$view->setId($resultView['id']);
			$view->setNumber($resultView['number']);
			$view->setText(htmlspecialchars_decode($resultView['text']));
			$view->setFormat($resultView['format']);
			$view->setDisabled($resultView['disabled']);

			foreach ($options as $option)
			{
				switch ($option)
				{
					case 'document':
						if(!empty($resultView))
						{
							$view->setDocument($this->selectDocument($resultView['id_document']));
						}
					break;

					case 'pageobject':
						$queryPageobject = "SELECT `id` FROM `omeka_newspaper_reader_pageobjects`
							 	 	   		WHERE `omeka_newspaper_reader_pageobjects`.`id_view` = $id";

						$resultPageobject = $dbObject->query($queryPageobject)->fetch();

						if(!empty($resultPageobject))
						{
							$view->setPageobject($this->selectPageobject($resultPageobject['id']));
						}
					break;



					default:
						break;
				}
			}

			return $view;
		}

		return null;
	}

	/**
     * Inserts a view into the DB
     * @param $view The view to insert
     */
	public function insertView($view)
	{
		// $this->pdo->addLog("Get Number");

		$number = $view->getNumber();
		// $index->addLog("getText");
		$text = htmlspecialchars($view->getText());
		// $index->addLog("getFormat");
		$format = $view->getFormat();
		// $index->addLog("getDisabled");
		$disabled = $view->getDisabled();
		// $index->addLog("getDocument");
		$idDocument = $view->getDocument()->getId();
		$pageobject = addslashes(serialize($view->getPageobject()));

		// $index->addLog("get Db");
		$dbObject = get_db();

		// $index->addLog("query prepare");

		$dbh = $this->pdo;

		//$dbh->beginTransaction();

		$queryInsertView = "INSERT INTO `omeka_newspaper_reader_views` (`omeka_newspaper_reader_views`.`number`,
																		`omeka_newspaper_reader_views`.`text`,
																		`omeka_newspaper_reader_views`.`format`,
																		`omeka_newspaper_reader_views`.`disabled`,
																		`omeka_newspaper_reader_views`.`id_document`,
																		`omeka_newspaper_reader_views`.`data`)
					  	    VALUES ($number, \"$text\", \"$format\", $disabled, $idDocument, \"$pageobject\");";

		$dbh->query($queryInsertView);
		// $index->addLog("Insert query");
		// $index->addLog($queryInsertView);
		/*$queryInsertView = "INSERT INTO `omeka_newspaper_reader_views` (`omeka_newspaper_reader_views`.`number`,
																		`omeka_newspaper_reader_views`.`text`,
																		`omeka_newspaper_reader_views`.`format`,
																		`omeka_newspaper_reader_views`.`disabled`,
																		`omeka_newspaper_reader_views`.`id_document`)
					  	    VALUES ($number, \"$text\", \"$format\", $disabled, $idDocument)";
		$index->addLog("Insert query");
		$index->addLog($queryInsertView);
		$dbObject->query($queryInsertView);*/
	}

	/**
     * Removes a view from DB by its id and eventually deletes the linked entities
     * @param $view The view
     * @param $options Defines the deletion level regarding the linked entities
     */
	public function deleteView($view, $options = array())
	{
		$dbObject = get_db();
		$idView = $view->getId();
		$idDocument = $view->getDocument()->getId();

		$queryDeleteView = "DELETE FROM `omeka_newspaper_reader_views`
					  		WHERE `omeka_newspaper_reader_views`.`id` = $idView";
		$dbObject->query($queryDeleteView);

		foreach ($options as $option)
		{
			switch ($option)
			{
				case 'document':
					$queryDeleteDocument = "DELETE FROM `omeka_newspaper_reader_documents`
										 	WHERE `omeka_newspaper_reader_documents`.`id` = $idDocument";

					$dbObject->query($queryDeleteDocument);

					$queryDeletePublication = "DELETE FROM `omeka_newspaper_reader_publications`
										 	   WHERE `omeka_newspaper_reader_publications`.`id_document` = $idDocument";

					$dbObject->query($queryDeletePublication);
				break;

				case 'pageobject':
					$queryDeletePageobject = "DELETE FROM `omeka_newspaper_reader_pageobjects`
					  						  WHERE `omeka_newspaper_reader_pageobjects`.`id_view` = $idView";

					$dbObject->query($queryDeletePageobject);
				break;

				default:
					break;
			}
		}
	}

	/**
     * Selects from DB a pageobject by its id and returns an hydrated object
     * @param $id The pageobject id
     * @param $options Defines the hydration level regarding the linked entities
 	 * @return $pageobject The hydrated object : a pageobject with its infos and view
     */
	public function selectPageobject($id, $options = array())
	{
		$dbObject = get_db();

		$queryPageobject = "SELECT * FROM `omeka_newspaper_reader_views`
					 		WHERE `omeka_newspaper_reader_views`.`id` = $id";
		$resultPageobject = $dbObject->query($queryPageobject)->fetch();

		$pageobject = new Pageobject();

		if(!empty($resultPageobject))
		{
			$pageobject->setId($resultPageobject['id']);
			$pageobject->setData(unserialize($resultPageobject['data']));
			//stripslashes
			foreach ($options as $option)
			{
				switch ($option)
				{
					case 'view':
						$pageobject->setView($this->selectView($resultPageobject['id_view']));
					break;

					default:
						break;
				}
			}

			return $pageobject;
		}

		return null;
	}

	/**
     * Searches for some text in a view stored in the DB and returns the corresponding page objects
     * @param $searchText The text to look for
     * @param $scope The scope of the search : the current document, all documents, a collection, a date, a period, a company
     * @param $options Defines the hydration level regarding the linked entities
 	 * @return $pageobject The hydrated object : a pageobject with its infos and view
     */
	public function searchPOsForViewByText($searchText, $scope, $options = array())
	{
		// $searchStr = '"%'.$searchText.'%"';

		$tabSearchText = explode(' ', $searchText);
		$searchText = '"%'.$tabSearchText[0];
		if(sizeof($tabSearchText) < 1){
			foreach ($tabSearchText as $key => $value) {
				$searchText.=$value.'%';
			}
			$searchText.='"';
		}else{
			$searchText.='%"';
		}
		$dbObject = get_db();
		$querySearchInView = "";
		$resultPageobjects = array();
		// echo $searchText;

		switch ($scope)
		{
			case 'currentDoc':

			$querySearchInView .= "SELECT * FROM `omeka_newspaper_reader_views` WHERE `text` LIKE $searchText ";

				foreach ($options as $key => $option)
				{
					switch ($key)
					{
						case 'view':
							$querySearchInView .= "AND `id` = $option";
						break;

						default:

						break;
					}
				}
			break;

			case 'allDocs':

			break;

			default:
				# code...
			break;
		}

		$resultSearchInView = $dbObject->query($querySearchInView)->fetchAll();
		// echo $querySearchInView;
		if(!empty($resultSearchInView))
		{
			// echo 'before boucle';
			foreach ($resultSearchInView as $result)
			{
				$page = $this->selectView($result['id'], array('pageobject'));
				$pageobject = $page->getPageobject();
				$pageobject->setView($page->getNumber());
				// echo 'inside boucle';
				$resultPageobjects[] = $pageobject;
			}
			// echo 'after boucle';
		}

		return $resultPageobjects;
		// #050514
		// return $resultSearchInView;
	}

	/**
     * Inserts a pageobject into the DB
     * @param $pageobject The pageobject to insert
     */
	public function insertPageobject($pageobject, $index)
	{
		$data = addslashes(serialize($pageobject->getData()));
		$view = $pageobject->getView()->getId();

		$index->addLog("get Db");
		$dbObject = get_db();

		$index->addLog("query prepare");

		$dbh = $index->getPdoDb();

		$dbh->beginTransaction();

		$queryInsertPageobject = "INSERT INTO `omeka_newspaper_reader_pageobjects` (`omeka_newspaper_reader_pageobjects`.`data`, `omeka_newspaper_reader_pageobjects`.`id_view`)
				 			  	    	  VALUES (\"$data\", $view)";

		$dbh->query($queryInsertPageobject);
		$index->addLog("Insert query");
		$index->addLog($queryInsertPageobject);

		/*$dbObject = get_db();

		$queryInsertPageobject = "INSERT INTO `omeka_newspaper_reader_pageobjects` (`omeka_newspaper_reader_pageobjects`.`data`, `omeka_newspaper_reader_pageobjects`.`id_view`)
		 			  	    	  VALUES (\"$data\", $view)";
		$dbObject->query($queryInsertPageobject);*/
	}

	// /**
 //     * Updates the accessibility of an item
 //     * @param $id The id of the item
 //     * @param $accessibility The accessibility of the item
 //     */
	public function updateItemAcessibility($id, $accessibility)
	{
		$featured = intval($accessibility['featured']);
		$public = intval($accessibility['public']);
		$idCollection = intval($accessibility['collection_id']);

		$dbObject = get_db();

		$queryUpdateItemAccessibility = "UPDATE `omeka_items`
										 SET    `omeka_items`.`featured` = $featured,
										 	    `omeka_items`.`public` = $public,
										 	    `omeka_items`.`collection_id` = $idCollection,
										 	    `omeka_items`.`modified` = NOW()
										 WHERE  `omeka_items`.`id` = $id;";

		$dbObject->query($queryUpdateItemAccessibility);
	}

	/**
     * Selects the id of an element from DB by its text
     * @param $text The text of the element
     * @return $idElement The id of the element
     */
	public function selectIdElementByText($text)
	{
		$dbObject = get_db();

		$DC = $dbObject->getTable('ElementSet')->findBy(array('name' => 'Dublin Core'));
		$idDC = $DC[0]['id'];

		$queryElement = "SELECT `id` FROM `omeka_elements`
					     WHERE `omeka_elements`.`element_set_id` = $idDC
					     AND `omeka_elements`.`name`
					     LIKE \"%$text%\"";

		$element = $dbObject->query($queryElement)->fetch();

		if(!empty($element))
		{
			$idElement = $element['id'];
			return $idElement;
		}
		else
		{
			return null;
		}

	}

	/**
     * Inserts element texts for the file inserted into DB
     * @param $elementTexts The element texts
     */
	public function insertElementTextsForLastFile($elementTexts)
	{
		$dbObject = get_db();

		$queryIdFile = "SELECT `id` FROM `omeka_files`
					    ORDER BY `omeka_files`.`id` DESC";
		$resultIdFile = $dbObject->query($queryIdFile)->fetch();

		if(!empty($resultIdFile))
		{
			$idFile = $resultIdFile['id'];

			foreach ($elementTexts['Dublin Core'] as $key => $elementText)
			{
				$recordType = "File";
				$idElement = $this->selectIdElementByText($key);
				$text = $elementText[0]['text'];

				$queryInsertElementText = "INSERT INTO `omeka_element_texts` (`omeka_element_texts`.`record_id`, `omeka_element_texts`.`record_type`, `omeka_element_texts`.`element_id`, `omeka_element_texts`.`html`, `omeka_element_texts`.`text`)
									 	   VALUES ($idFile, \"$recordType\", $idElement, 0, \"$text\")";
				$dbObject->query($queryInsertElementText);
			}
		}
	}

	/**
     * Removes a file and its element texts from DB
     * @param $idFile The file id
     */
	public function deleteOmekaFile($idFile)
	{
		$dbObject = get_db();

		$queryDeleteFile = "DELETE FROM `omeka_files`
							WHERE 'id' = $idFile";
		$dbObject->query($queryDeleteFile);

		$queryDeleteElementTexts = "DELETE FROM `omeka_element_texts`
									WHERE 'record_id' = $idFile
									AND 'record_type' = 'File'";
		$dbObject->query($queryDeleteElementTexts);
	}

	/**
     * Removes an item, its files and its element texts from DB
     * @param $idItem The item id
     */
	public function deleteOmekaItem($idItem)
	{
		$dbObject = get_db();

		$queryDeleteItem = "DELETE FROM `omeka_items`
							WHERE 'id' = $idItem";
		$dbObject->query($queryDeleteItem);

		$queryDeleteElementTexts = "DELETE FROM `omeka_element_texts`
									WHERE 'record_id' = $idItem
									AND 'record_type' = 'Item'";
		$dbObject->query($queryDeleteElementTexts);

		$querySelectFiles = "SELECT 'id' FROM `omeka_files`
							 WHERE 'item_id' = $idItem";
		$resultFiles = $dbObject->query($querySelectFiles)->fetchAll();

		if(!empty($resultFiles))
		{
			for($i = 0; $i < count($resultFiles); $i++)
			{
				$this->deleteOmekaFile($resultFiles[$i]['id']);
			}
		}
	}
}