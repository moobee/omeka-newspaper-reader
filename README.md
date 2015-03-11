# Omeka NewspaperReader : Import et lecture de fascicules

Omeka NewspaperReader permet d'importer des documents numérisés dans Omeka afin de pouvoir les visualiser directement sur le site public.
Le __Kiosque lorrain__, produit par la [Bibliothèque-médiathèque de Nancy (BmN)](http://www.reseau-colibris.fr/iguana/www.main.cls?surl=nancybmn), est un exemple d'utilisation de NewspaperReader : [Visiter le Kiosque lorrain](http://www.kiosque-lorrain.fr/)

Omeka NewspaperReader est composé d'un thème et de deux plugins.
Ces trois composants sont nécessaires au bon fonctionnement de l'ensemble, il faut donc tous les installer.

- __NewspaperReader theme__ : Le thème nécessaire au bon fonctionnement de NewspaperReader, il intégre aussi la liseuse permettant de visualiser les fascicules importés.
- __NewspaperReader plugin__ : Le plugin principal de NewspaperReader (utilisé pour l'import des fascicules dans Omeka)
- Une version modifiée de __[SolrSearch](https://github.com/scholarslab/SolrSearch)__ permettant d'effectuer des recherches efficaces dans les fascicules importés.

## Prérequis

- Une installation d'Omeka 2.0
- Un serveur Solr fonctionnel, configuré avec [un coeur SolrSearch](https://github.com/scholarslab/SolrSearch/tree/master/solr-core/omeka)
- Le thème et les deux plugins récupérables dans ce dépôt
- Les plugins `SimplePage` et `Exhibit` pour Omeka

## Installation

- Copier le répertoire `bmn` dans `themes`
- Copier les répertoires `NewspaperReader` et `SolrSearch` dans `plugins`
- Dans l'admin d'Omeka, choisir le thème `NewspaperReader theme`
- Installer les deux plugins `SolrSearch` et `Newspaper Reader`
- Modifier la configuration du coeur de Solr en suivant [ce guide](newspaper-reader-solr-config.md)

## Configuration

La documentation du thème NewspaperReader se trouve dans [le guide d'utilisation](newspaper-reader-user-guide.pdf)
La configuration technique du thème et du plugin se trouve dans `themes/bmn/config.php`.
La configuration technique de Solr se trouve dans [le guide de configuration de Solr](newspaper-reader-solr-config.md)

## Effectuer un import

La documentation d'import de fascicules dans Omeka avec NewspaperReader se trouve dans [le guide d'import](newspaper-reader-import-doc.pdf)



