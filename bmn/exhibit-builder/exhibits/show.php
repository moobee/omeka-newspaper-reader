<?php
echo head(array(
    'title' => metadata('exhibit_page', 'title') . ' &middot; ' . metadata('exhibit', 'title'),
    'bodyid' => 'exhibit',
    'bodyclass' => 'show'));
$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
//Récupére le titre de l'onglet pour l'afficher dans le fil d'ariane
$header_links = explode(",", get_theme_option('Header Links'));

//defaut
$tab_title = "Expositions";
foreach($header_links as $header_link) {
    $fragments = explode("-&gt;", $header_link);
    if($fragments[1] == 'exhibits/browse') {
        $tab_title = $fragments[0];
        break;
    }
}
?>
<div class="item-breadcrumb">
    <a href="<?php echo $baseUrl?>/">
        <img src="<?php echo $baseUrl?>/themes/bmn/images/button-home.png">
    </a>
    <span class="breadcrumb-delimiter" >&raquo;</span>
    <a href="<?php echo $baseUrl;?>/exhibits/browse">
        <?php echo $tab_title; ?>
    </a>
    <span class="breadcrumb-delimiter" >&raquo;</span>
    <?php echo link_to_exhibit();?>
    </a>
    <span class="breadcrumb-delimiter" >&raquo;</span>
    <span><?php echo metadata('exhibit_page', 'title');?></span>
</div>

<nav id="exhibit-pages">
    <?php echo exhibit_builder_page_nav(); ?>
</nav>

<h1>
    <span class="exhibit-page">
        <?php echo metadata('exhibit_page', 'title'); ?>
    </span>
</h1>

<nav id="exhibit-child-pages">
    <?php echo exhibit_builder_child_page_nav(); ?>
</nav>

<?php exhibit_builder_render_exhibit_page(); ?>

<div id="exhibit-page-navigation">
    <?php if ($prevLink = exhibit_builder_link_to_previous_page()): ?>
    <div id="exhibit-nav-prev">
    <?php echo $prevLink; ?>
    </div>
    <?php endif; ?>
    <?php if ($nextLink = exhibit_builder_link_to_next_page()): ?>
    <div id="exhibit-nav-next">
    <?php echo $nextLink; ?>
    </div>
    <?php endif; ?>
    <div id="exhibit-nav-up">
    <?php echo exhibit_builder_page_trail(); ?>
    </div>
</div>

<?php echo foot(); ?>
