<?php

/**
 *   BMN Newspaper Reader - PHP - Read newspaper articles and interact with them
 *   File SearchController.php : Allow to process a search request on Collections/Items/Article
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

/**
 * Plugin "NewspaperReader", controller "Search"
 *
 * @package NewspaperReader
 */
class NewspaperReader_SearchController extends Omeka_Controller_AbstractActionController
{	
    /********************************** Reader part **********************************/

    public function indexAction()
    {



    	$this->view->resultByDate = array();
    	$this->view->resultByTitle = array();
    }

    public function _searchCollection() {

    }

}