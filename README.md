> [!IMPORTANT]
> **En cas de livraison d'une nouvelle version de l'application**
> Prenez le temps de lire [la liste des changements](CHANGES.md) AVANT de
> commencer à faire quoi que ce soit. Ensuite, vous pourrez consulter la section
> "Installer une mise à jour" ci-dessous.

# dremgr

dremgr est un environnement pour la gestion d'une ou plusieurs instances de base
DRE

DRE est un acronyme de Données Répliquées en Etablissement, et permet d'avoir
accès à un export journalier des bases de données d'une instance PEGASE

> [!TIP]
> **Obtenir de l'aide**
> Envoyez un message sur le [forum PC-SCOL](https://forum.pc-scol.fr)
> en mentionnant `@jclain`

## Faire l'installation initiale

dremgr est développé et testé sur Debian 11. Il devrait fonctionner sur
n'importe quel système Linux, pourvu que les pré-requis soient respectés.

Les commandes listées ci-dessous sont pour un démarrage rapide si vous savez ce
que vous faites. Si c'est la première fois, il est conseillée de cliquer sur les
liens pour avoir des détails sur la procédure.

* Installez d'abord les pré-requis
  * Installation des [pré-requis pour linux](documentation/00prerequis-linux.md)
    (Debian ou autres distributions Linux). Ce mode d'installation est celui à
    sélectionner pour la production, mais peut aussi être utilisé pour les tests
    ou le développement, notamment si le poste de l'utilisateur est sous Linux.
    ~~~sh
    sudo apt update && sudo apt install git curl rsync tar unzip python3 gawk
    ~~~
    ~~~sh
    curl -fsSL https://get.docker.com | sudo sh
    ~~~
    ~~~sh
    [ -n "$(getent group docker)" ] || sudo groupadd docker
    sudo usermod -aG docker $USER
    ~~~

    > [!IMPORTANT]
    > **Configuration du proxy**
    > Si vous utilisez un proxy, veuillez consulter la page
    > [pré-requis pour linux](documentation/00prerequis-linux.md)
    > pour des instructions sur la façon de le configurer
  * Installation des [pré-requis pour WSL](documentation/00prerequis-wsl.md), le
    sous-système Linux pour Windows. Ce mode d'installation est approprié pour
    les tests ou le développement.
* Puis ouvrez un terminal et clonez le dépôt
  ~~~sh
  git clone https://github.com/PC-Scol/dremgr.git
  ~~~
  ~~~sh
  cd dremgr
  ~~~
* Ensuite, Il faut construire les images docker nécessaires.
  [Construire les images](documentation/02construire-images.md)
  ~~~sh
  ./build
  ~~~
  ~~~sh
  nano build.env
  ~~~
  ~~~sh
  ./build
  ~~~
* Enfin, vous devez choisir le mode de fonctionnement:
  * Le mode simple n'installe qu'une seule instance de la base de données ainsi
    que du mécanisme pour la mettre à jour quotidiennement. Ce mode n'offre
    aucune interface utilisateur.
    [Installer dremgr dans le mode simple](documentation/03installation-simple.md)
    ~~~sh
    ./dbinst
    ~~~
    ~~~sh
    nano prod_profile.env
    ~~~
    ~~~sh
    ./dbinst
    ~~~
  * Le mode avancé permet d'installer autant d'instances que nécessaire sur une
    même machine. Elle offre aussi une interface utilisateur, mais elle demande
    (un peu) plus de travail.
    [Installer dremgr dans le mode avancé](documentation/03installation-avancee.md)

## Installer une mise à jour

IMPORTANT: *AVANT* de commencer à faire quoi que ce soit, prenez le temps de
lire [la liste des changements](CHANGES.md)

Généralement, il faut reconstruire les images avant de relancer les services:
~~~sh
cd dremgr

# mettre à jour le dépôt
git pull

# reconstruire les images si nécessaire
./build -r

# redémarrer les services le cas échéant
./dremgr -r
~~~
Cependant, la liste des changements peut parfois contenir des instructions
différentes de celles mentionnées ci-dessus.

## Exploitation

* Chaque script `build`, `dremgr`, `dbinst`, `dbfront` et `webfront` possède une
  aide intégrée affichée avec l'option `--help`
  [Afficher l'aide complète des scripts](documentation/scripts--help.md)
* [Liste des paramètres des fichiers d'environnement](documentation/parametres.md)
* Pour tester des fonctionnalités qui ne sont pas encore stabilisées, il est
  possible de basculer une installation en mode "développement". ATTENTION!
  Cette opération ne devrait pas être effectuée en production.
  [Installer une version de développement](documentation/03installation-avancee.md)
* Les addons permettent de rajouter des fonctionnalités à DRE.
  * [Installation d'addons](documentation/dreaddons.md)
  * [Développement d'addons](documentation/dreaddons-developpement.md)

-*- coding: utf-8 mode: markdown -*- vim:sw=4:sts=4:et:ai:si:sta:fenc=utf-8:noeol:binary