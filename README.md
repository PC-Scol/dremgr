# dremgr

dremgr est un environnement pour la gestion d'une ou plusieurs instances de base
DRE

DRE est un acronyme de Données Répliquées en Etablissement, et permet d'avoir
accès à un export journalier des bases de données d'une instance PEGASE

IMPORTANT: Si vous mettez à jour votre installation, soyez sûr de prendre le
temps de lire les [Release Notes](RELEASE-NOTES.md)

## Pré-requis

dremgr est développé et testé sur Debian 11. Il devrait fonctionner sur
n'importe quel système Linux, pourvu que les pré-requis soient respectés.
* Installation des [pré-requis pour Debian](documentation/prerequis-linux.md) et
  autres distributions Linux. Ce mode d'installation est celui à sélectionner
  pour la production, mais peut aussi être utilisé pour les tests ou le
  développement, notamment si le poste de l'utilisateur est sous Linux.
* Installation des [pré-requis pour WSL](documentation/prerequis-wsl.md), le
  sous-système Linux pour Windows. Ce mode d'installation est approprié pour les
  tests ou le développement.

## Démarrage rapide

Ouvrez un terminal et clonez le dépôt
~~~sh
git clone https://github.com/PC-Scol/dremgr.git
~~~
~~~sh
cd dremgr
~~~

* Il faut d'abord construire les images docker nécessaires.
  [Construire les images](documentation/construire-images.md)
* Ensuite, vous devez choisir le mode de fonctionnement:
  * Le mode simple n'installe qu'une seule instance de la base de données ainsi
    que du mécanisme pour la mettre à jour quotidiennement. Ce mode n'offre
    aucune interface utilisateur.
    [Installer dremgr dans le mode simple](documentation/installation-simple.md)
  * Le mode avancé permet d'installer autant d'instances que nécessaire sur une
    même machine. Elle offre aussi une interface utilisateur, mais elle demande
    (un peu) plus de travail.
    [Installer dremgr dans le mode avancé](documentation/installation-avancee.md)

## Exploitation

* Chaque script build, inst, front possède une aide intégrée affichée avec
  l'option `--help`
  [Afficher l'aide complète des scripts](documentation/scripts--help.md)
* [Liste des paramètres des fichiers d'environnement](documentation/parametres.md)
* Installer une mise à jour: consultez la section correspondant au mode
  d'installation qui a été choisi
  * [Mise à jour dans le mode simple](documentation/installation-simple.md)
  * [Mise à jour dans le mode avancé](documentation/installation-avancee.md)
* Pour tester des fonctionnalités qui ne sont pas encore stabilisées, il est
  possible de basculer une installation en mode "développement". ATTENTION!
  Cette opération ne devrait pas être effectuée en production.
  [Installer une version de développement](documentation/installation-avancee.md)
* Les addons permettent de rajouter des fonctionnalités à DRE.
  * [Installation d'addons](documentation/dreaddons.md)
  * [Développement d'addons](documentation/dreaddons-developpement.md)

-*- coding: utf-8 mode: markdown -*- vim:sw=4:sts=4:et:ai:si:sta:fenc=utf-8:noeol:binary