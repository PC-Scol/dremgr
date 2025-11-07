Si vous n'avez pas encore installé les pré-requis ni cloné le dépôt, retournez
aux sections précédentes: installer les pré-requis [pour linux](00prerequis-linux.md)
ou [pour windows/WSL](00prerequis-wsl.md) puis [cloner le dépôt](01cloner-depot.md)

# Contruire les images

Avant de pouvoir utiliser dremgr, il faut construire les images docker
utilisées par l'application

Commencer en faisant une copie de `build.env` depuis `.build.env.dist`
~~~sh
cp .build.env.dist build.env

# NB: cette copie est automatiquement effectuée si vous lancez build
~~~
Il FAUT consulter `build.env` et l'éditer AVANT de continuer. Notamment, les
variables suivantes doivent être configurées le cas échéant:

`APT_PROXY`
: proxy pour l'installation des paquets Debian, e.g `http://monproxy.tld:3142`

`APT_MIRROR`
`SEC_MIRROR`
: miroirs à utiliser. Il n'est généralement pas nécessaire de modifier ces
  valeurs

`TIMEZONE`
: Fuseau horaire, si vous n'êtes pas en France métropolitaine, e.g
  `Indian/Reunion`

`PRIVAREG`
: nom d'un registry docker interne vers lequel les images pourraient être
  poussées. Il n'est pas nécessaire de modifier ce paramètre.

Une fois le fichier configuré, les images peuvent être construites
~~~sh
./build
~~~

--

Une fois que vous avez construit les images, vous devez choisir le mode de
fonctionnement:
* Le mode simple n'installe qu'une seule instance de la base de données ainsi
  que du mécanisme pour la mettre à jour quotidiennement. Ce mode n'offre
  aucune interface utilisateur.
  [>> Installer dremgr dans le mode simple](03installation-simple.md)
* Le mode avancé permet d'installer autant d'instances que nécessaire sur une
  même machine. Elle offre aussi une interface utilisateur, mais elle demande
  (un peu) plus de travail.
  [>> Installer dremgr dans le mode avancé](03installation-avancee.md)

-*- coding: utf-8 mode: markdown -*- vim:sw=4:sts=4:et:ai:si:sta:fenc=utf-8:noeol:binary