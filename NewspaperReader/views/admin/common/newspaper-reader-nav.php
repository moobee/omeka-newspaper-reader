<nav id="section-nav" class="navigation vertical">
<?php
    $navArray = array(
        array(
            'label' => __('Importer des fichiers'),
            'action' => 'index',
            'module' => 'newspaper-reader',
        ),
        array(
            'label' => __('Historique des imports'),
            'action' => 'history',
            'module' => 'newspaper-reader',
        ),
    );
    echo nav($navArray, 'admin_navigation_settings');
?>
</nav>