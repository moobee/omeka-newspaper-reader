<?php

$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();

$dbObject = get_db();

$documents_count = $dbObject->getTable('Items')->count();
?>

<form id="advanced-search-form-fascicule" action="" method="GET">
    <div class='advanced-search-form'>

        <div id="search-keywords" class="field">
            <div class="labelFormInput">
                <?php echo $this->formLabel('keyword-search', __('Recherche par expression')); ?>
            </div>
            <div class="inputs">
            <?php
                echo $this->formText(
                    'q',
                    @$_SESSION['q'],
                    array('id' => 'keyword-search', "value" => "")
                );
            ?>
            </div>
        </div>

        <?php
        //On change l'affichage de la valeur par défaut "Choisir ci-dessous"->"Toutes les collections"
        $tab = get_table_options('Collection');
        $tab[''] = 'Tous les titres';
        foreach ($tab as $key => &$value) {
            $value = html_entity_decode($value, ENT_QUOTES);
        }
        unset($value);

        ?>

        <script>
            var nbCollection = 1;
            var tabCollection = <?php echo json_encode($tab);?>
        </script>
       <div id="collection" class="field">
            <div class="labelForm">
                <?php echo $this->formLabel('collection-search', __('Recherche par titre')); ?>
            </div>
           <div class="inputs">
               <?php if(!isset($_SESSION['collection'])): ?>
                    <div>
                        <?php echo $this->formSelect(
                           'collection[0]',
                           @$_REQUEST['collection'],
                           array('class' => 'collection-search'),
                           $tab
                       ); ?>
                       <a id='addCollection' class='add-line'>+</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($_SESSION['collection'] as $keyCollection => $idcollection): ?>
                        <div>
                            <?php echo $this->formSelect(
                                'collection[' . $keyCollection . ']',
                                $idcollection,
                                array('class' => 'collection-search'),
                                $tab
                            ); ?>

                            <?php //On rajoute le bouton ajouter sur le premier élement ?>
                            <?php if($keyCollection === 0): ?>
                                <a id='addCollection' class='add-line'>+</a>
                            <?php else: ?>
                                <a class='remove-line'>-</a>
                            <?php endif; ?>
                        </div>
                   <?php endforeach; ?>
                <?php endif; ?>

           </div>
        </div>

        <div id="date" class="field">
            <div class="labelFormInput">
                <?php echo $this->formLabel('date-search', __('Date')); ?>
                <span><?php echo __('(Exemple : 18/05/1918)'); ?></span>
            </div>
            <div class="inputs">
                <?php
                    echo $this->formText(
                        'date-search',
                        @$_SESSION['date-search'],
                        array('id' => 'date-search', 'placeholder' => __('JJ/MM/AAAA ou JJ-MM-AAAA'))
                    );
                ?>
            </div>
        </div>

        <div id="periode" class="field">
            <div class="labelForm">
                <?php echo $this->formLabel('periode-search', __('Période')); ?>
                <span>(Exemple : 15/05/1913)</span>
            </div>
            <div class="inputs">
                <div>
                    <label>Du : </label>
                    <?php
                        echo $this->formText(
                            'periode[0][begin]',
                            @$_SESSION['periode'][0]['begin'],
                            array('id' => 'periode-start', 'placeholder' => __('JJ/MM/AAAA ou JJ-MM-AAAA'))
                        );
                    ?>
                </div>
                <div>
                    <label>Au : </label>
                    <?php
                        echo $this->formText(
                            'periode[0][end]',
                            @$_SESSION['periode'][0]['end'],
                            array('id' => 'periode-end', 'placeholder' => __('JJ/MM/AAAA ou JJ-MM-AAAA'))
                        );
                    ?>
                </div>
            </div>
        </div>
        <div class="clearfix" >
            <div id="advanced-search-infos-fascicule" ><?php echo __("Recherche en cours...");?></div>
            <input type="submit" class="no-error submit" name="submit_search" id="submit_search_advanced_fascicule" value="<?php echo __('Search'); ?>" />
            <button type="submit" class="clear-advanced-form form bmn-btn bmn-btn-minor" data-js-action="clearForm" >
                Vider le formulaire
            </button>
        </div>
    </div>
</form>

<form id="advanced-search-form-focus" action="<?php echo $baseUrl; ?>/search" method="GET">
    <div class='advanced-search-form'>
        <div id="search-by-focus" class="field">
            <div class="labelForm">
                <?php echo $this->formLabel('date-search', __('Recherche par expression dans les focus')); ?>
            </div>
            <div class="inputs">
                <?php
                    echo $this->formText(
                        'exposition-search',
                        @$_SESSION['exposition-search'],
                        array('id' => 'exposition-search')
                    );
                ?>
            </div>
            <div id='button-form-focus'>
                <input type="submit" class="submit" name="submit_search" id="submit_search_advanced_focus" value="<?php echo __('Search'); ?>" />
                <div id="advanced-search-infos-focus" ><?php echo __("Recherche en cours...");?></div>
            </div>
        </div>
    </div>
</form>

<?php echo js_tag('items-search'); ?>
<script type="text/javascript">
    jQuery(document).ready(function () {
        Omeka.Search.activateSearchButtons();
    });
</script>
