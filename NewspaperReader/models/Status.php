<?php

/**
 *  BMN Newspaper Reader - PHP - Read newspaper articles and interact with them
 *  File Status.php : model used to represent the statutes table
 *  Author : Valentin Kiffer
 *  Modifications : 1.0 - 10/03/2014 - Custom model class - VKIFFER
 *
 *   Version : 1.0
 *
 * @copyright Copyright 2014 moobee global solutions
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 * @package NewspaperReader
 */

/**
 * Status model.
 *
 * @package NewspaperReader
 */
class Status
{
    private $id;
    private $wording;
    private $information;
    private $report;

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
    
    public function getWording()
    {
        return $this->wording;
    }

    public function setWording($wording)
    {
        $this->wording = $wording;
    }

    public function getInformation()
    {
        return $this->information;
    }

    public function setInformation($information)
    {
        $this->information = $information;
    }

    public function getReport()
    {
        return $this->report;
    }

    public function setReport($report)
    {
        $this->report = $report;
    }
}