# DREaddons

dremgr supporte l'installation d'addons accessible via des dépôts git.

Une fois qu'on a sélectionné un addon à utiliser, il faut rajouter l'url du
dépôt dans la configuration:
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
configurée, c'est à dire 5h30 par défaut.

Si une ligne commence par `#`, elle est ignorée, ce qui permet de rajouter des
commentaire dans la liste des addons, e.g:
~~~sh
ADDON_URLS="
...
# décommenter la ligne suivante pour activer l'addon
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