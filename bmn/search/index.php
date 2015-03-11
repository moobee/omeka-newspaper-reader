<?php
$dbObject = get_db();

/**
 * Recherche avancée par
 * - mots clés
 * - collections
 * - date
 * - date global
 * - période
 * - intervalle d'id
 * - exposition
 */
$baseUrl = Zend_Controller_Front::getInstance()->getRequest()->getBaseUrl();
$searchTerms = "";
$searchText = "";
$searchPeriodeGlobal = "";
$searchDates = "";
$listeDates = "";
$searchCollections = array();
$listeCollections = '';
$searchPeriodes = array();
$searchRange = "";
$listeRanges = "";
$searchFocus = "";
$searchTermsFocus = "";
$res=array();
$resFocus =array();

//Nombre de resultats total trouvé
$nbRes = 0;

//Recherche par mot clé
// if( !empty($_GET['search']) ) {
//     $searchTerms = $_GET['search'];
//     $searchText = "%$searchTerms%";
//     $_SESSION['search']=$searchTerms;
// }else{
//     unset($_SESSION['search']);
// }
if(!empty($_GET['query'])){
  $searchTerms = $_GET['query'];
  $searchText = "%$searchTerms%";
  $_SESSION['query']=$searchTerms;
}else{
  unset($_SESSION['query']);
}

//Recherche par période (Global)
if(!empty($_GET['periode-start-global'])){
    $searchPeriodeGlobal = $_GET['periode-start-global'];
    $_SESSION['periode-start-global'] = $searchPeriodeGlobal;
}else{
    unset($_SESSION['periode-start-global']);
}

//Recherche par date
if( !empty($_GET['date-search']) ) {
    $searchDates = $_GET['date-search'];
    $_SESSION['date-search'] = $searchDates;
}else{
    unset($_SESSION['date-search']);
}

//Recherche par collection
if( !empty($_GET['collection']) && is_array($_GET['collection']) && !empty($_GET['collection'][0]) ) {
    $searchCollections = $_GET['collection'];
    $_SESSION['collection'] = $searchCollections;
    $elementIdTitle = "SELECT id
    FROM omeka_elements AS element
    WHERE name = 'Title'" ;
    $resElementIdTitle = $dbObject->query($elementIdTitle)->fetch();
    // pr($resElementIdTitle);
}else{
    unset($_SESSION['collection']);
}

//Recherche par période
if( !empty($_GET['periode']) && is_array($_GET['periode']) && !empty($_GET['periode'][0]['begin'])) {
    $searchPeriodes = $_GET['periode'];
    $_SESSION['periode'] = $searchPeriodes;
}else{
    unset($_SESSION['periode']);
}

//Recherche par intervalle d'id
if( !empty($_GET['range'])){
    $searchRange = $_GET['range'];
    $_SESSION['range'] = $searchRange;
}else{
    unset($_SESSION['range']);
}

if( !empty($_GET['exposition-search'])){
    $searchFocus = $_GET['exposition-search'];
    $searchTermsFocus = $searchFocus;
    $_SESSION['exposition-search'] = $searchFocus;
}else{
    unset($_SESSION['exposition-search']);
}
if(!empty($searchTerms) && !empty($searchPeriodeGlobal) && empty($searchDates) && empty($searchCollections) && empty($searchPeriodes) && empty($searchRange) && empty($searchFocus) && empty($searchTermsFocus)){
    if(is_numeric(intval($searchPeriodeGlobal))){
        $searchPeriodeGlobal = intval($searchPeriodeGlobal);
        $searchPeriodes[0]['begin'] = '01/01/'.$searchPeriodeGlobal;
        $searchPeriodes[0]['end'] = '01/01/'.($searchPeriodeGlobal+4);
    }else{
        $searchPeriodes[0]['begin'] = '01/01/1914';
        $searchPeriodes[0]['end'] = '01/01/1918';
    }

}

//Tableau de conditions
$where = array();

//Recherche par date
if(!empty($searchDates)) {

    $searchDates = explode(',', $searchDates);
    $elementIdDate = "SELECT `id`
    FROM omeka_elements
    WHERE name = 'Date'" ;

    $resElementIdDate = $dbObject->query($elementIdDate)->fetch();

    $where[] = 'AND element_texts.element_id = '.$resElementIdDate['id'];

    $tmp_dates = array();

    foreach ($searchDates as $date) {
        //Ajouter les tests et les éventuelles conversions de dates
        $listeDates.= $date.', ';
        $tmp_dates[] = 'element_texts.text = ' . $dbObject->quote(date_fr_to_us(trim($date), '-'));
    }
    $listeDates = substr($listeDates, 0, strlen($listeDates)-2);
    $where[] = 'AND ( ' . implode(" OR ", $tmp_dates) . ')';
}

//Recherche par collection
if(!empty($searchCollections)) {

    $tmp_collections =  array();

    foreach ($searchCollections as $collection) {
        $collection = intval($collection);
        $tmp_collections[] = "$collection";
    }
    // pr($tmp_collections);

    $where[] = 'AND ( collections.id = ' . implode(" OR collections.id = ", $tmp_collections) . ')';
    if(!empty($resElementIdTitle['id'])){
        $titleCollections = "SELECT `text`
        FROM `omeka_element_texts`
        WHERE `record_type` = 'Collection' AND `element_id`= ".$resElementIdTitle['id'] . " AND (record_id = " . implode(" OR record_id = ", $tmp_collections) ." ) ";
        $listeCollections = $dbObject->query($titleCollections)->fetchAll();
        foreach ($listeCollections as $indListCol => $titleCollection) {
            $listeCollections[$indListCol] = $titleCollection['text'];
        }
    }
}

//Recherche par période
if(!empty($searchPeriodes)) {

    $elementIdDate = "SELECT `id`
    FROM omeka_elements
    WHERE name = 'Date'" ;

    $resElementIdDate = $dbObject->query($elementIdDate)->fetch();

    $where[] = 'AND element_texts.element_id = '.$resElementIdDate['id'];

    $tmp_periodes = array();

    $tabdateBegin = explode('/', $searchPeriodes[0]['begin']);
    if(sizeof($tabdateBegin) !== 1){
        $searchPeriodes[0]['begin'] = $tabdateBegin[0] . '-' . $tabdateBegin[1] . '-' . $tabdateBegin[2];
    }

    $tabdateEnd = explode('/', $searchPeriodes[0]['end']);
    if(sizeof($tabdateEnd) !== 1){
        $searchPeriodes[0]['end'] = $tabdateEnd[0] . '-' . $tabdateEnd[1] . '-' . $tabdateEnd[2];
    }

    //Si la date de début est plus grande que la date de fin on invers les deux
    if(strtotime($searchPeriodes[0]['begin']) > strtotime($searchPeriodes[0]['end'])){
        $tmp = $searchPeriodes[0]['begin'];
        $searchPeriodes[0]['begin'] = $searchPeriodes[0]['end'];
        $searchPeriodes[0]['end'] =$tmp;
    }

    foreach ($searchPeriodes as $periode) {
        $dateBegin = new DateTime(date_fr_to_us($periode['begin']));
        $dateEnd = new DateTime(date_fr_to_us($periode['end']));
        $tmp_periodes[] = "strcmp(". $dbObject->quote($dateBegin->modify('-1 day')->format('Y-m-d')) .", element_texts.text) = -1 AND strcmp(". $dbObject->quote($dateEnd->modify('+1 day')->format('Y-m-d')) .", element_texts.text) = 1";
    }

    $where[] = 'AND ( ' . implode(" AND ", $tmp_periodes) . ')';
}

/*
Recherche d'un fascicule par un interval d'identifiant ou un identifiant unique
*/
if(!empty($searchRange)){
    $tmp_range = explode(',', $searchRange);
    $syntaxError = false;
    foreach ($tmp_range as $keyInt => &$stringInterval) {
        $stringInterval = trim($stringInterval);
        $listeRanges .= $stringInterval.', ';
        if( strpos($stringInterval, '-') !== false){
            $tmp_rangeIntvalue = explode('-',  $stringInterval);

            foreach ($tmp_rangeIntvalue as $keyId => $id) {
                $tmp_rangeIntvalue[$keyId] = intval(trim($id));
            }

            if(sizeof($tmp_rangeIntvalue) === 2 ){
                $stringInterval = "element_texts.record_id BETWEEN ".implode(' AND ', $tmp_rangeIntvalue);
            }else{
                $syntaxError = true;
            }

        }else{
            $stringInterval = "element_texts.record_id = ".intval($stringInterval);
        }
    }
    unset($stringInterval);
    $listeRanges = substr($listeRanges, 0, strlen($listeRanges)-2);
    if(!$syntaxError){
        $where[] = 'AND ( ' . implode(' OR ', $tmp_range) . ')';
    }

}

//Recherche par mots clés
if(!empty($searchText)) {
    $where[] = "AND views.text LIKE " . $dbObject->quote($searchText);
}

if(!empty($searchFocus)){
    $searchFocus = "%".$searchFocus."%";
    $focusRequest = "SELECT DISTINCT exhibits.id, exhibits.slug, exhibits.title
    FROM omeka_exhibits AS exhibits

    LEFT JOIN omeka_exhibit_pages AS pages
    ON exhibits.id = pages.exhibit_id

    LEFT JOIN omeka_exhibit_page_entries AS entries
    ON pages.id = entries.page_id

    WHERE 1
    AND exhibits.title LIKE " . $dbObject->quote($searchFocus) .
    " OR exhibits.description LIKE " . $dbObject->quote($searchFocus) .
    " OR pages.title LIKE " . $dbObject->quote($searchFocus) .
    " OR entries.text LIKE " . $dbObject->quote($searchFocus) .
    " OR entries.caption LIKE " . $dbObject->quote($searchFocus);


    $resFocus = $dbObject->query($focusRequest)->fetchAll();
    $nbRes = sizeof($resFocus);
    // pr($focusRequest);
}
if((!empty($where) && empty($resFocus)) || (!empty($where) && !empty($resFocus))){
    $advancedQuery = 'SELECT DISTINCT id_document, id_item
        FROM omeka_newspaper_reader_views AS views

        INNER JOIN omeka_newspaper_reader_documents AS documents
        ON documents.id = views.id_document
        INNER JOIN omeka_items AS items
        ON items.id = documents.id_item
        INNER JOIN omeka_collections AS collections
        ON collections.id = items.collection_id
        INNER JOIN omeka_element_texts AS element_texts
        ON element_texts.record_id = items.id
        AND element_texts.record_type = "Item"

        WHERE 1

        ' . implode(" ", $where) . '

        LIMIT 0, 200';

    $res = $dbObject->query($advancedQuery)->fetchAll();
    if(!empty($res)){
        $nbRes = sizeof($res);
    }else{
        $nbRes = 0;
    }
    // pr($res);
    // pr($advancedQuery);
}


$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
//$pageTitle = __('Recherche de "' . htmlentities(utf8_decode($_GET['search'])) . '"');
echo head(array('title' => 'Recherche Avancée', 'bodyid' => 'search'));
// echo head(array('title' => $pageTitle, 'bodyid' => 'search'));
$searchRecordTypes = get_search_record_types();
// pr($res);
if(isset($res[0]['record_id'])){
    // pr($res[0]['record_id']);
}else{
    // pr($res[0]['id_item']);
}

/** S'il y a "/page/" dansl'url, on est sur la page Globale, on ne changera donc pas de page */
if(strpos($_SERVER['REQUEST_URI'], "/page/") !== false) {
    $page_fascicule = false;
}
else {
    $page_fascicule = true;
}

?>

<div id="search-Views">
        <?php if(isset($res) && count($res) > 0 || isset($resFocus) && count($resFocus) > 0):
            $nbRes = count($res) + count($resFocus); ?>

            <div id='search-result-div'>
                <p id='info-result-advanced-search'>
                <?php if(count($res) === 200){ ?>
                    <?php echo __("Votre recherche dépasse 200 résultats, veuillez affiner votre recherche pour :");?>
                <?php }else{ ?>
                    <strong><?php echo $nbRes; ?></strong>
                    résultat<?php echo $nbRes > 1 ? 's' : ''; ?> trouvé<?php echo $nbRes > 1 ? 's' : ''; ?> pour :  </p>
                <?php } ?>
                <ul id='list-result-advanced-search'>
                    <li><?php echo !empty($searchTerms) ? ' - Mot clé : <strong>' . $searchTerms .'</strong>' : ''; ?></li>
                    <li><?php echo !empty($listeCollections) ? ' - Collection : <strong>' . implode(', ',$listeCollections) .'</strong>' : ''; ?></li>
                    <li><?php echo !empty($searchDates) ? ' - Date : <strong>' . $listeDates .'</strong>' : ''; ?></li>
                    <li><?php echo !empty($searchPeriodes) ?  ' - Période : du <strong>' . $searchPeriodes[0]['begin'] .'</strong> au <strong>' .  $searchPeriodes[0]['end'] .'</strong>' : ''; ?></li>
                    <li><?php echo !empty($searchRange) ? ' - Intervalle d\'identifiant : <strong>'.$listeRanges .'</strong>' : ''; ?></li>
                    <li><?php echo !empty($searchFocus) ? ' - Focus : <strong>'.$searchTermsFocus .'</strong>' : ''; ?></li>
                </ul>
            </div>

            <div id="back-to-search">
                <div id='button-back-to-search'>
                    <a href ="<?php echo $baseUrl;?>/items/search"> Effectuer une autre recherche </a>
                </div>
            </div>

                <!--résultat<?php echo $nbRes > 1 ? 's' : ''; ?> trouvé<?php echo $nbRes > 1 ? 's' : ''; ?> pour la recherche "<strong><?php echo $searchTerms; ?></strong>".
            -->

            <table>
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Titre</th>
                    </tr>
                </thead>
                <tbody>
        <?php
        if(empty($searchTerms) && !empty($res)) { ?>

            <?php foreach ($res as $key => $value){
                @set_current_record('item',get_record_by_id('Item', $value['id_item'])); ?>
                            <tr>
                                <td>Item</td>
                                <td><a target="_blank" href="<?php echo $baseUrl; ?>/items/show/<?php echo $value['id_item']; ?>"><?php echo metadata('item', array('Dublin Core', 'Title')); ?></a></td>
                            </tr>

            <?php } ?>
        <?php
        }else{
            if(!empty($res)) {
                foreach ($res as $key => $value){
                    @set_current_record('item',get_record_by_id('Item', $value['id_item'])); ?>
                    <tr>
                        <td><?php echo __('Fascicule'); ?></td>
                        <td>
                            <a target="_blank" href="<?php echo $baseUrl; ?>/newspaper-reader/index/read?item=<?php echo $value['id_item']; ?>&query=<?php echo urlencode($searchTerms); ?>">
                                <?php echo metadata('item', array('Dublin Core', 'Title')); ?>
                            </a>
                        </td>
                    </tr>
                <?php }
            }
        }
        if(!empty($resFocus)){
            foreach ($resFocus as $key => $value){  ?>
                <tr>
                    <td>Focus</td>
                    <td>
                        <a target="_blank" href="<?php echo $baseUrl; ?>/exhibits/show/<?php echo $value['slug'] ;?>">
                            <?php echo $value['title'] ; ?>
                        </a>
                    </td>
                </tr>
        <?php }
        } ?>
        </tbody>
        </table>
        <?php else: ?>
        <div id='search-result-div'>
        <p>
            <strong><?php echo __('Aucun'); ?></strong> <?php echo __('résultat ne correspond à la recherche :'); ?></p>
            <ul id='list-result-advanced-search'>
                <li><?php echo !empty($searchTerms) ? ' - Mot clé : <strong>' . $searchTerms .'</strong>' : ''; ?></li>
                <li><?php echo !empty($listeCollections) ? ' - Collection : <strong>' . implode(', ',$listeCollections) .'</strong>' : ''; ?></li>
                <li><?php echo !empty($searchDates) ? ' - Date : <strong>' . $listeDates .'</strong>' : ''; ?></li>
                <li><?php echo !empty($searchPeriodes) ?  ' - Période : du <strong>' . $searchPeriodes[0]['begin'] .'</strong> au <strong>' .  $searchPeriodes[0]['end'] .'</strong>' : ''; ?></li>
                <li><?php echo !empty($searchRange) ? ' - Intervalle d\'identifiant : <strong>'.$listeRanges .'</strong>' : ''; ?></li>
                <li><?php echo !empty($searchFocus) ? ' - Focus : <strong>'.$searchTermsFocus .'</strong>' : ''; ?></li>
            </ul>
        </div>
            <div id="back-to-search">
                        <div id='button-back-to-search'>
                            <a href ="<?php echo $baseUrl;?>/items/search"> <?php echo __('Effectuer une autre recherche'); ?> </a>
                        </div>
                    </div>

        <?php endif; ?>
    </div>

<?php echo foot(); ?>
