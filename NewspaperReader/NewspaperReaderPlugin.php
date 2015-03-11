<?php

/**
 *   BMN Newspaper Reader - PHP - Read newspaper articles and interact with them
 *   File NewspaperReaderPlugin.php : setting plugin hooks and filters
 *   Author : Valentin Kiffer
 *   Modifications : 1.0 - 28/01/2014 - Insertion of the link to reader before footer - VKIFFER
 *                   1.1 - 29/01/2014 - Creation of the DB tables at plugin install - VKIFFER
 *                   1.2 - 30/01/2014 - Suppression of the DB tables at plugin uninstall - VKIFFER
 *                   1.3 - 31/01/2014 - Adding a NewspaperReader tab in the admin navigation,
 *                                      the languages directory for translations at plugin initialization - VKIFFER
 *                   1.4 - 05/03/2014 - Adding the css and js for the admin page - VKIFFER
 *                   1.5 - 10/03/2014 - Creation of the import DB tables at plugin install,
 *                                      suppression of the import DB Tables at plugin uninstall - VKIFFER
 *   Version : 1.5
 *
 *   @copyright Copyright 2014 moobee global solutions
 *   @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

define('NEWSPAPER_READER_DEBUG', false);
define('NEWSPAPER_READER_DIRECTORY', dirname(__FILE__));

/**
 * The NewspaperReader plugin class.
 *
 * @package Omeka\Plugins\NewspaperReader
 */

class NewspaperReaderPlugin extends Omeka_Plugin_AbstractPlugin
{
    // Plugin's hooks
    protected $_hooks = array(
        'config_form',
        'config',
        'install',
        'initialize',
        'admin_head',
        'uninstall',
        'before_delete_item',
    );

    // Plugin's filters
    protected $_filters = array(
        'admin_navigation_main',
    );

    public function hookBeforeDeleteItem($record){

      /***********Constantes***********/
      define('NEWSPAPER_READER_PUBLIC_DIR', NEWSPAPER_READER_DIRECTORY."/views/public");
      define('NEWSPAPER_READER_FILES_DIR', NEWSPAPER_READER_DIRECTORY."/files");

      /***********Variables***********/
      //Nom du répertoire et préfixe des noms de fichier
      $fileName = '';
      $idFileName = '';

      //Id du document a supprimer
      $idDocument = '';

      $queryElementIdFileName = '';
      $queryFileName = '';
      $queryIdDocuments = '';
      $querySuppDocument = '';
      $querySuppView = '';
      $querySuppPublication = '';

      $db = get_db();

      if( !empty($record['record']['id']) ){

        $queryElementIdFileName = "Select id from omeka_elements where `name` = 'Identifier'";
        $idFileName = $db->query($queryElementIdFileName)->fetch();

        //si l'id identifier est bien récupéré
        if( !empty($idFileName['id']) ){

          $idFileName = $idFileName['id'];

          /***********Requêtes***********/
          //Récupère le nom du répertoire et préfixe des fichiers à supprimer
          $queryFileName = 'SELECT `text` FROM omeka_element_texts WHERE `element_id` = ' . $idFileName . ' AND `record_id` = '.$record['record']['id'];

          //Récupère l'id du document pour supprimer dans la table views
          $queryIdDocuments = 'SELECT `id` FROM `omeka_newspaper_reader_documents` WHERE `id_item` = '.$record['record']['id'];

          $idDocument = $db->query($queryIdDocuments)->fetch();

          //si le document existe bien
          if(!empty($idDocument['id'])){

            $idDocument = $idDocument['id'];

            //Supprime le document
            $querySuppDocument ='DELETE FROM `omeka_newspaper_reader_documents` WHERE `id_item` = '.$record['record']['id'];

            //Supprime la publication
            $querySuppPublication ='DELETE FROM `omeka_newspaper_reader_publications` WHERE `id_document` = '.$idDocument;

            //Supprime la vue
            $querySuppView ='DELETE FROM `omeka_newspaper_reader_views` WHERE `id_document` = '.$idDocument;

          }
        }
      }

      //Si les requêtes de supressions sont valide
      if( !empty($queryFileName) &&!empty($querySuppDocument) && !empty($querySuppView) && !empty($querySuppPublication)){

        /***********Exécution des Requêtes***********/

        $db->query($querySuppDocument);
        $db->query($querySuppPublication);
        $db->query($querySuppView);

        $fileName = $db->query($queryFileName)->fetch();

        if( !empty($fileName['text']) ){
          $fileName = $fileName['text'];

          /***********Suppressions du dossier***********/

          //Si le dossier du journal existe on le supprime
          if( file_exists(NEWSPAPER_READER_FILES_DIR . '/' . $fileName) ) {
            // echo 'Dossier existant';
            // echo '</br>';
            $dir = NEWSPAPER_READER_FILES_DIR . '/' . $fileName;

            //Suprimer un dossier de manière récurcive
            if (is_dir($dir)) {

              $objects = scandir($dir);

              foreach ($objects as $object) {

                if ($object != "." && $object != "..") {

                  if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object);

                }

              }

              reset($objects);
              rmdir($dir);

            }
          }
        }
      }
    }

    /**
    * Displays the config form.
    */
    public function hookConfigForm()
    {
      require 'config-form.php';
    }

    /**
     * Handles the config form.
     */
    public function hookConfig()
    {
      //'B543956101' pour la BMN de Nancy
      set_option('institution_code', trim($_POST['institution_code']));
    }

    /**
     * Creates the DB tables when installing the plugin
     */
    public function hookInstall()
    {
        // Retrieves the DB object
        $db = get_db();

        // Companies table : id, name, creation_date, code
        $sql ="
         CREATE TABLE IF NOT EXISTS `omeka_newspaper_reader_companies` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `name` text,
          `creation_date` date DEFAULT 0,
          `disabled` int(11) DEFAULT 0,
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM CHARSET=utf8 AUTO_INCREMENT=1 ;";

        $db->query($sql);

        // Documents table : id, views_count, pressmark, creation_date
        $sql ="
        CREATE TABLE IF NOT EXISTS `omeka_newspaper_reader_documents` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `id_item` int(11) DEFAULT 0,
          `views_count` int(11) DEFAULT 0,
          `shelf_number` varchar(30) DEFAULT NULL,
          `creation_date` date DEFAULT 0,
          `reduction_rate` float DEFAULT 0,
          `disabled` int(11) DEFAULT 0,
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM CHARSET=utf8 AUTO_INCREMENT=1 ;";

        $db->query($sql);

        // Publications table : id_company, id_document, date
        $sql ="
        CREATE TABLE IF NOT EXISTS `omeka_newspaper_reader_publications` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `id_company` int(11) NOT NULL,
          `id_document` int(11) NOT NULL,
          `date` date DEFAULT 0,
          PRIMARY KEY (`id`),
          KEY `FK_Publications_id_company` (`id_company`),
          KEY `FK_Publications_id_document` (`id_document`)
        ) ENGINE=MyISAM CHARSET=utf8 AUTO_INCREMENT=1 ;";

        $db->query($sql);

        // Publications table's Foreign Keys
        $sql ="
        ALTER TABLE `omeka_newspaper_reader_publications`
          ADD CONSTRAINT `FK_Publications_id_company` FOREIGN KEY (`id_company`) REFERENCES `omeka_newspaper_reader_companies` (`id`),
          ADD CONSTRAINT `FK_Publications_id_document` FOREIGN KEY (`id_document`) REFERENCES `omeka_newspaper_reader_documents` (`id`);";

        $db->query($sql);



        // Views table : id, number, text, id_document
        $sql ="
        CREATE TABLE IF NOT EXISTS `omeka_newspaper_reader_views` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `number` int(11) DEFAULT 0,
          `text` text,
          `format` text,
          `disabled` int(11) DEFAULT 0,
          `id_document` int(11) NOT NULL,
          `data` mediumblob,
          PRIMARY KEY (`id`),
          KEY `FK_View_id_document` (`id_document`),
          FULLTEXT (text)
        ) ENGINE=MyISAM CHARSET=utf8;";

        $db->query($sql);

        // Views table's Foreign Keys
        $sql ="
        ALTER TABLE `omeka_newspaper_reader_views`
          ADD CONSTRAINT `FK_View_id_document` FOREIGN KEY (`id_document`) REFERENCES `omeka_newspaper_reader_documents` (`id`);";

        $db->query($sql);


        // Reports table : id, date_beginning, date_end, status, status_libelle, nb_tot_items, nb_import_items
        // Stock tout les rapport en base
        $sql ="CREATE TABLE IF NOT EXISTS `omeka_newspaper_reader_reports` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `date_beginning` datetime DEFAULT 0,
          `date_end` datetime DEFAULT 0,
          `status` text,
          `status_libelle` text,
          `nb_tot_items` int(11) DEFAULT 0,
          `nb_import_items` int(11) DEFAULT 0,
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM CHARSET=utf8 AUTO_INCREMENT=1 ;";

        $db->query($sql);

        //Stock pour chaque journaux, sont status (OK, partiel, exclu), status_libelle : motif de non import
        //fascicule_libelle: Nom du fascicule importé
        $sql ="CREATE TABLE IF NOT EXISTS `omeka_newspaper_reader_report_Item` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `status` text,
          `status_libelle` text,
          `fascicule_libelle` text,
          `id_item` int(11),
          `id_report` int(11),
          PRIMARY KEY (`id`),
          FOREIGN KEY (`id_item`) REFERENCES omeka_newspaper_reader_views(id),
          FOREIGN KEY (`id_report`) REFERENCES omeka_newspaper_reader_reports(id)
        ) ENGINE=MyISAM CHARSET=utf8 AUTO_INCREMENT=1 ;";

        $db->query($sql);



        $this->_installOptions();
    }

    /**
     * Adds the translations
     */
    public function hookInitialize()
    {
        add_translation_source(dirname(__FILE__) . '/languages');
    }

    /**
     * Configure admin theme header.
     *
     * @param array $args
     */
    public function hookAdminHead($args)
    {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        if ($request->getModuleName() == 'newspaper-reader') {
            queue_css_file('newspaper-reader-import');
            queue_js_file('newspaper-reader-import-config');
            queue_js_file('newspaper-reader-import');
            queue_js_file('jquery.form.min');
            queue_js_file('importDocuments');
            queue_js_file('spin');
        }
    }

    /**
     * Drops the DB tables when uninstalling the plugin
     */
    public function hookUninstall()
    {
        $db = $this->_db;

        // Drops the Companies table
        $sql = "DROP TABLE IF EXISTS `omeka_newspaper_reader_companies`";
        $db->query($sql);

        // Drops the Documents table
        $sql = "DROP TABLE IF EXISTS `omeka_newspaper_reader_documents`";
        $db->query($sql);

        // Drops the Views table
        $sql = "DROP TABLE IF EXISTS `omeka_newspaper_reader_views`";
        $db->query($sql);

        // Drops the Pageobjects table
        $sql = "DROP TABLE IF EXISTS `omeka_newspaper_reader_pageobjects`";
        $db->query($sql);

        // Drops the Publications table
        $sql = "DROP TABLE IF EXISTS `omeka_newspaper_reader_publications`";
        $db->query($sql);

        // Drops the Histories table
        $sql = "DROP TABLE IF EXISTS `omeka_newspaper_reader_histories`";
        $db->query($sql);

        // Drops the Reports table
        $sql = "DROP TABLE IF EXISTS `omeka_newspaper_reader_reports`";
        $db->query($sql);

        // Drops the Gatherings table
        $sql = "DROP TABLE IF EXISTS `omeka_newspaper_reader_gatherings`";
        $db->query($sql);

        // Drops the Statutes table
        $sql = "DROP TABLE IF EXISTS `omeka_newspaper_reader_statutes`";
        $db->query($sql);

        // Drops the Informations table
        $sql = "DROP TABLE IF EXISTS `omeka_newspaper_reader_informations`";
        $db->query($sql);

        // Drops the Informations table
        $sql = "DROP TABLE IF EXISTS `omeka_newspaper_reader_reports`";
        $db->query($sql);

        // Drops the Informations table
        $sql = "DROP TABLE IF EXISTS `omeka_newspaper_reader_report_Item`";
        $db->query($sql);

        $this->_uninstallOptions();
    }

    /**
     * Add the Newspaper Reader link to the admin main navigation.
     *
     * @param array Navigation array.
     * @return array Filtered navigation array.
     */
    public function filterAdminNavigationMain($nav)
    {
        $nav[] = array(
            'label' => __('Newspaper Reader'),
            'uri' => url('newspaper-reader'),
            'privilege' => 'index'
        );
        return $nav;
    }
}
