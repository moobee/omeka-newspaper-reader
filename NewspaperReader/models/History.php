<?php

/**
 *  BMN Newspaper Reader - PHP - Read newspaper articles and interact with them
 *  File History.php : model used to represent the histories table
 *  Author : Valentin Kiffer
 *  Modifications : 1.0 - 10/03/2014 - Custom model class - VKIFFER
 *
 *  Version : 1.0
 *
 * @copyright Copyright 2014 moobee global solutions
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 * @package NewspaperReader
 */

/**
 * History model.
 *
 * @package NewspaperReader
 */
class History
{
    private $id;
    private $dateBeginning;
    private $dateEnd;
    private $reportsCount;
    private $documentsCount;
    private $reports;

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
    
    public function getDateBeginning()
    {
        return $this->dateBeginning;
    }

    public function setDateBeginning($dateBeginning)
    {
        $this->dateBeginning = $dateBeginning;
    }

    public function getDateEnd()
    {
        return $this->dateEnd;
    }

    public function setDateEnd($dateEnd)
    {
        $this->dateEnd = $dateEnd;
    }

    public function getReportsCount()
    {
        return $this->reportsCount;
    }

    public function setReportsCount($reportsCount)
    {
        $this->reportsCount = $reportsCount;
    }

    public function getDocumentsCount()
    {
        return $this->documentsCount;
    }

    public function setDocumentsCount($documentsCount)
    {
        $this->documentsCount = $documentsCount;
    }

    public function getReports()
    {
        return $this->reports;
    }

    public function setReports($reports)
    {
        $this->reports = $reports;
    }
}