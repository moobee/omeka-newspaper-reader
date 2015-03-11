<?php

/**
 *  BMN Newspaper Reader - PHP - Read newspaper articles and interact with them
 *  File Document.php : model used to represent the documents table
 *  Author : Valentin Kiffer
 *  Modifications : 1.0 - 30/01/2014 - Model class extending Omeka_Record_AbstractRecord - VKIFFER
 *                  1.1 - 31/01/2014 - Custom model class - VKIFFER 
 *                  1.2 - 03/02/2014 - Disabled attribute added - VKIFFER 
 *                  1.3 - 11/03/2014 - Reduction rate attribute added - VKIFFER 
 *
 *   Version : 1.3
 *
 * @copyright Copyright 2014 moobee global solutions
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 * @package NewspaperReader
 */

/**
 * Document model.
 *
 * @package NewspaperReader
 */
class Document
{
	private $id;
	private $viewsCount;
	private $shelfNumber;
	private $creationDate;
    private $publicationDate;
    private $reductionRate;
    private $disabled;
    private $company;
    private $views;
    private $id_item;

    public function __construct()
    {

    }

	public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId_item()
    {
        return $this->id_item;
    }

    public function setId_item($id_item)
    {
        $this->id_item = $id_item;
    }

    public function getViewsCount()
    {
        return $this->viewsCount;
    }

    public function setViewsCount($viewsCount)
    {
        $this->viewsCount = $viewsCount;
    }

    public function getShelfNumber()
    {
        return $this->shelfNumber;
    }

    public function setShelfNumber($shelfNumber)
    {
        $this->shelfNumber = $shelfNumber;
    }

    public function getCreationDate()
    {
        return $this->creationDate;
    }

    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    }

    public function getPublicationDate()
    {
        return $this->publicationDate;
    }

    public function setPublicationDate($publicationDate)
    {
        $this->publicationDate = $publicationDate;
    }

    public function getReductionRate()
    {
        return $this->reductionRate;
    }

    public function setReductionRate($reductionRate)
    {
        $this->reductionRate = $reductionRate;
    }

    public function getDisabled()
    {
        return $this->disabled;
    }

    public function setDisabled($disabled)
    {
        $this->disabled = $disabled;
    }

    public function getCompany()
    {
        return $this->company;
    }

    public function setCompany($company)
    {
        $this->company = $company;
    }

    public function getViews()
    {
        return $this->views;
    }

    public function setViews($views)
    {
        $this->views = $views;
    }
}