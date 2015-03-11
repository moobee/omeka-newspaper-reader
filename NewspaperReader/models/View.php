<?php

/**
 *  BMN Newspaper Reader - PHP - Read newspaper articles and interact with them
 *  File View.php : model used to represent the views table
 *  Author : Valentin Kiffer
 *  Modifications : 1.0 - 30/01/2014 - Model class extending Omeka_Record_AbstractRecord - VKIFFER
 *                  1.1 - 31/01/2014 - Custom model class - VKIFFER  
 *                  1.2 - 03/02/2014 - Disabled attribute added - VKIFFER
 *                  1.3 - 13/03/2014 - Format attribute added - VKIFFER
 *
 *   Version : 1.3
 *
 * @copyright Copyright 2014 moobee global solutions
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 * @package NewspaperReader
 */

/**
 * View model.
 *
 * @package NewspaperReader
 */
class View
{
	private $id;
	private $number;
	private $text;
    private $format;
    private $disabled;
    private $document;
    private $pageobject;

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

    public function getNumber()
    {
        return $this->number;
    }

    public function setNumber($number)
    {
        $this->number = $number;
    }

    public function getText()
    {
        return $this->text;
    }

    public function setText($text)
    {
        $this->text = $text;
    }

    public function getFormat()
    {
        return $this->format;
    }

    public function setFormat($format)
    {
        $this->format = $format;
    }

    public function getDisabled()
    {
        return $this->disabled;
    }

    public function setDisabled($disabled)
    {
        $this->disabled = $disabled;
    }

    public function getDocument()
    {
        return $this->document;
    }

    public function setDocument($document)
    {
        $this->document = $document;
    }

    public function getPageobject()
    {
        return $this->pageobject;
    }

    public function setPageobject($pageobject)
    {
        $this->pageobject = $pageobject;
    }
}