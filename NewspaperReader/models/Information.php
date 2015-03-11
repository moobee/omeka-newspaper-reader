<?php

/**
 *  BMN Newspaper Reader - PHP - Read newspaper articles and interact with them
 *  File Information.php : model used to represent the informations table
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
 * Information model.
 *
 * @package NewspaperReader
 */
class Information
{
    private $id;
    private $wording;
    private $status;

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

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }
}