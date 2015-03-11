<?php

/**
 *  BMN Newspaper Reader - PHP - Read newspaper articles and interact with them
 *  File Company.php : model used to represent the companies table
 *  Author : Valentin Kiffer
 *  Modifications : 1.0 - 30/01/2014 - Model class extending Omeka_Record_AbstractRecord - VKIFFER
 *                  1.1 - 31/01/2014 - Custom model class - VKIFFER  
 *                  1.2 - 03/02/2014 - Disabled attribute added - VKIFFER
 *
 *  Version : 1.2
 *
 * @copyright Copyright 2014 moobee global solutions
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 * @package NewspaperReader
 */

/**
 * Company model.
 *
 * @package NewspaperReader
 */
class Company
{
    private $id;
	private $name;
	private $creationDate;
    private $disabled;
    private $documents;

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
    
    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getCreationDate()
    {
        return $this->creationDate;
    }

    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    }

    public function getDisabled()
    {
        return $this->disabled;
    }

    public function setDisabled($disabled)
    {
        $this->disabled = $disabled;
    }

    public function getDocuments()
    {
        return $this->documents;
    }

    public function setDocuments($documents)
    {
        $this->documents = $documents;
    }
}