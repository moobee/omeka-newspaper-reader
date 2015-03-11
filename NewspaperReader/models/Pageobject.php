<?php

/**
 *  BMN Newspaper Reader - PHP - Read newspaper articles and interact with them
 *  File Pageobject.php : model used to represent the pageobjects table
 *  Author : Valentin Kiffer
 *  Modifications : 1.0 - 30/01/2014 - Model class extending Omeka_Record_AbstractRecord - VKIFFER
 *                  1.1 - 31/01/2014 - Custom model class - VKIFFER  
 *
 *   Version : 1.1
 *
 * @copyright Copyright 2014 moobee global solutions
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 * @package NewspaperReader
 */

/**
 * Pageobject model.
 *
 * @package NewspaperReader
 */
class Pageobject
{
	private $id;
	private $data;
	private $view;

	public function __construct()
    {

    }

    public function getAssociativeArray()
    {
        $associativeArray = array("id" => $this->getId(),
                                  "data" => $this->getData(),
                                  "view" => $this->getView());
        return $associativeArray;
    }

	public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function getView()
    {
        return $this->view;
    }

    public function setView($view)
    {
        $this->view = $view;
    }
}