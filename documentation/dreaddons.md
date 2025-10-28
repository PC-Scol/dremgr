# DREaddons

dremgr supporte l'installation d'addons accessible via des dépôts git.

## Addon local

[Le répertoire dreaddon-local](../dreaddon-local/README.md) est particulier: il
est livré avec dremgr et il est toujours installé, indépendamment de la
configuration `ADDON_URLS`.

Cet addon est directement modifiable par l'utilisateur. Cela permet d'installer
des paramétrages ou des traitement locaux sans avoir besoin de créer un dépôt
tierce

## Addon installables

Une fois qu'on a sélectionné un addon à utiliser, il faut rajouter l'url du
dépôt git dans la configuration:
~~~sh
ADDON_URLS="
...
PC-Scol/addonpublic.git
...
"
~~~
NB: le préfixe `https://github.com/` est rajouté automatiquement. On peut aussi
utiliser un serveur privé ou un autre serveur public en mentionnant l'URL
complète.

Si c'est un addon privé, il faudra sans doute rajouter le compte et le mot de
passe pour l'accès dans l'url:
~~~sh
ADDON_URLS="
...
https://compte:motdepasse@gitprive.univ.fr/addonprive.git
...
"
~~~

Cet addon sera mis à jour depuis le dépôt et importé chaque jour à l'heure
configurée, c'est à dire 4h par défaut.

Si une ligne commence par `#`, elle est ignorée, ce qui permet de désactiver
temporairement un addon, e.g:
~~~sh
ADDON_URLS="
...
#PC-Scol/addonpublic.git
...
"
~~~

Par défaut, la branche `master` du dépôt est attaquée. Il est possible de
sélectionner une autre branche avec le suffixe `#branch` e.g
~~~sh
ADDON_URLS="
PC-Scol/dreaddon-pilotage.git#develop
"
~~~

Il est possible aussi de désigner un tag ou un commit précis avec le suffixe
`^commitId` e.g
~~~sh
ADDON_URLS="
PC-Scol/dreaddon-pilotage.git^cff8bfbefdd19a746a4b729c958f366e6274cff0
"
~~~

-*- coding: utf-8 mode: markdown -*- vim:sw=4:sts=4:et:ai:si:sta:fenc=utf-8:noeol:binary