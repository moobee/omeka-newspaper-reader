<!DOCTYPE html>
<html lang="<?php echo get_html_lang(); ?>">
<head>
    <meta charset="utf-8">

    <?php if ( $description = option('description')): ?>
    <meta name="description" content="<?php echo $description; ?>" />
    <?php endif; ?>

    <?php if(INDEXING): ?>
        <meta name="robots" content="index, follow">
    <?php else: ?>
        <meta name="robots" content="noindex, nofollow">
    <?php endif; ?>

    <?php if(ANALYTICS): ?>
        <!-- Analytics code here -->
    <?php endif; ?>

    <?php


    if (isset($title)) {
        $titleParts[] = strip_formatting($title);
    }
    $titleParts[] = option('site_title');
    ?>
    <title><?php echo implode(' &middot; ', $titleParts); ?></title>

    <?php echo auto_discovery_link_tags(); ?>

    <!-- Plugin Stuff -->

    <?php fire_plugin_hook('public_head', array('view'=>$this)); ?>

    <?php
    $requestUri = Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();
    $baseUrl = Zend_Controller_Front::getInstance()->getRequest()->getBaseUrl();
    ?>

    <!-- Stylesheets -->
    <?php
    queue_css_url($baseUrl . '/themes/bmn/css/style.css?' . APP_VERSION);
    queue_css_url('http://fonts.googleapis.com/css?family=PT+Serif:400,700,400italic,700italic');
    echo head_css();


    $db = get_db();

    $documents_count = $db->getTable('Items')->count();

    $textLinks = (get_theme_option('Header Links'));
    $links = explode(',', $textLinks);


    echo theme_header_background();
    ?>
    <!-- JavaScripts -->
    <?php queue_js_url($baseUrl . '/themes/bmn/javascripts/vendor/modernizr.js?' . APP_VERSION); ?>
    <?php queue_js_url($baseUrl . '/themes/bmn/javascripts/vendor/selectivizr', array('conditional' => '(gte IE 6)&(lte IE 8)')); ?>
    <?php queue_js_url($baseUrl . '/themes/bmn/javascripts/vendor/respond.js?' . APP_VERSION); ?>
    <?php queue_js_url($baseUrl . '/themes/bmn/javascripts/globals.js?' . APP_VERSION); ?>
    <?php echo head_js(); ?>
    <script type="text/javascript" src="<?php echo $baseUrl;?>/plugins/NewspaperReader/views/public/js/spin.js"></script>
    <!--<script type="text/javascript">
    jQuery(function() {
            Omeka.showAdvancedForm();
    });-->
    <!-- </script> -->

</head>
<?php echo body_tag(array('id' => @$bodyid, 'class' => @$bodyclass)); ?>
    <?php fire_plugin_hook('public_body', array('view'=>$this)); ?>
        <div id="container">
            <header>
                <?php fire_plugin_hook('public_header'); ?>
                <div id="header-first-part" class="clearfix">
                    <div id="header-title">
                        <h1>
                            <a href="<?php echo $baseUrl; ?>/" ><?php echo get_theme_option('Header Text'); ?></a>
                        </h1>

                    </div>

                    <?php if(plugin_is_active('NewspaperReader')): ?>
                        <div class="clearfix" id="header-search">
                            <p><?php echo __('Expression')?></p>
                            <form id="search-form" name="search-form" action="<?php echo $baseUrl; ?>/solr-search/results/index" method="get">
                                <div class="input-wrp" >
                                    <input placeholder="Rechercher" name="q" id="query" value="<?php echo (!empty($_GET['q']))?  str_replace('"', '&quot;', $_GET['q']) :  "" ;?>" type="text">
                                    <div id="spinner-global"></div>
                                    <div id="search-infos" >
                                        <?php echo __("Recherche en cours..."); ?>
                                    </div>
                                </div>
                              <input title="Rechercher cette expression" name="" value="" type="submit">
                            </form>
                            <a title="<?php echo __('Faire une recherche avancée'); ?>" href="<?php echo $baseUrl; ?>/items/search" id="go-to-advanced-search"></a>
                        </div>
                    <?php endif; ?>
                </div>
                <div id="header-second-part" class="clearfix">
                    <ul>
                        <?php foreach ($links as $link): ?>
                            <?php $strings = explode("-&gt;", $link); ?>
                            <?php if(count($strings) > 1): ?>
                                <?php if($baseUrl.'/'.$strings[1] === $requestUri): ?>
                                    <li class="header-active-link"><a href="<?php echo $baseUrl; ?>/<?php echo $strings[1]; ?>"><?php echo $strings[0]; ?></a></li>
                                <?php elseif($baseUrl.'/' === $requestUri && $strings[1] === 'index'): ?>
                                    <li class="header-active-link"><a href="<?php echo $baseUrl; ?>/<?php echo $strings[1]; ?>"><?php echo $strings[0]; ?></a></li>
                                <?php else: ?>
                                    <li><a href="<?php echo $baseUrl; ?>/<?php echo $strings[1]; ?>"><?php echo $strings[0]; ?></a></li>
                                <?php endif; ?>
                            <?php else: ?>
                                <li></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </header>
