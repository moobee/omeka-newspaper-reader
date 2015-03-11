<?php

/**
 *  BMN Newspaper Reader - PHP - Read newspaper articles and interact with them
 *  File Report.php : model used to represent the reports table
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
 * Report model.
 *
 * @package NewspaperReader
 */
class Report
{
    private $id;
    private $history;
    private $date;
    private $documents;
    private $statutes;

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
    
    public function getHistory()
    {
        return $this->history;
    }

    public function setHistory($history)
    {
        $this->history = $history;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function setDate($date)
    {
        $this->date = $date;
    }

    public function getDocuments()
    {
        return $this->documents;
    }

    public function setDocuments($documents)
    {
        $this->documents = $documents;
    }

    public function getStatutes()
    {
        return $this->statutes;
    }

    public function setStatutes($statutes)
    {
        $this->statutes = $statutes;
    }
}