# Installation de dremgr

## Contruire les images

Avant de pouvoir utiliser dremgr, il faut construire les images docker
utilisées par l'application

Commencer en faisant une copie de `build.env` depuis `.build.env.dist`
~~~sh
cp .build.env.dist build.env
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

Une fois les images construites, et le mode de fonctionnement choisi, dremgr
peut être installé
* [Installer dremgr dans le mode simple](installation-simple.md)
* [Installer dremgr dans le mode avancé](installation-avancee.md)

-*- coding: utf-8 mode: markdown -*- vim:sw=4:sts=4:et:ai:si:sta:fenc=utf-8:noeol:binary