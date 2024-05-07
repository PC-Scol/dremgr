# DREaddons

dremgr supporte l'installation d'addons accessible via des dépôts git.

Il existe un [template sur github](https://github.com/PC-Scol/dreaddon-template)
qu'on peut utiliser pour démarrer facilement.

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
configurée, c'est à dire 5h45 par défaut.

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

## Configuration

la configuration de l'addon se fait dans le fichier `dreaddon.conf`, qui est
obligatoire pour que le dépôt soit considéré comme un addon DRE.

Les variables suivantes peuvent être configurées:

`SCHEMAS`
: cette variable liste le ou les schémas à créer et provisionnés par cet addon.

  la création des schémas se fait par le script d'import de dremgr sur la base
  de cette variable, mais il est possible aussi de faire créer le schéma par
  l'addon, surtout si la création se fait avec des paramètres particuliers.

  dans tous les cas, il faut lister les schémas, parce que ça permet au script
  d'import de savoir quel schémas supprimer avant la recréation quotidienne.

`COMPAT`
: indiquer les versions de DRE avec lesquelles cet addon est compatible. seules
  deux valeurs sont autorisées: `all` et `vxx`

  La valeur de ce paramètre influe sur les répertoires depuis lesquels sont
  chargés les scripts SQL

## Scripts SQL

L'importation des schémas DRE se fait de cette manière:

* Suppression des schémas livrés par DRE
* Suppression des schémas mentionnés dans la variable `SCHEMAS` de
  `dreaddon.conf`
* Importation des dumps du jour livrés par DRE
* Traitements des scripts SQL des addons, dans l'ordre mentionné dans la
  configuration de dremgr

Pour chaque addon, les scripts SQL sont lancés depuis les répertoires suivants:

* les scripts du répertoire `prepare` sont lancés après que tous les schémas
  livrés par DRE ont été importés. Après ces scripts, si ce n'est pas déjà le
  cas, les schémas mentionnés dans la variable `SCHEMAS` de `dreaddon.conf` sont
  créés.
* En mode de compatibilité `vxx`, les scripts du répertoire nommé `vMM` où MM
  est la version majeure de la base DRE sont lancés.

  Les scripts à lancer sont cherchés dans le répertoire de numéro égal ou
  inférieur le plus proche. par exemple, si la version majeure de la base DRE
  est 24, les scripts sont chargés depuis le répertoire `v24`. Si celui-ci
  n'existe pas, ils sont chargés depuis le répertoire `v23` et ainsi de suite.
  Si aucun répertoire n'est trouvé, alors l'addon est considéré comme
  incompatible avec la version de DRE installée, et il est ignoré.

  Une autre façon de le dire est que si le répertoire `vMM` existe, il est
  utilisé par toutes les versions de DRE égales ou supérieures à `MM`, jusqu'à
  ce qu'une version spécifique soit livrée.
* l'accès à toutes les tables des schémas est donné à l'utilisateur `reader`
  configuré dans dremgr. puis les scripts du répertoire `updates` sont lancés.

Cette section parle de scripts SQL, mais en réalité, les fichiers `*.sql` et
`*.sh` (s'ils sont exécutables) sont considérés

Comme l'environnement est configuré comme il se doit par dremgr, les scripts
`*.sh` peuvent lancer directement psql pour attaquer la base de données. 

## Documentation

Si le répertoire `documentation` existe, son contenu direct est mis à
disposition du frontal de dremgr

En mode de compatibilité `vxx`, si le répertoire `documentation/vMM` existe (où
MM est la version majeure de DRE), son contenu direct est aussi mis à
disposition du frontal de dremgr

Cela permet de fournir aux utilisateurs la version la plus à jour correspondant
à la base DRE installée

Les fichiers `.url` sont traités de façon particulière: si c'est un fichier JSON
avec le schéma suivant, alors le lien avec la description spécifiée est ajouté
dans la documentation.
~~~json
{"url": "https://monsite.fr/madoc", "title": "Lien vers madoc"}
~~~

## Exploitation

Le mode de compatibilité `vxx` permet de préparer à l'avance les scripts et
documentation éventuelles pour une version donnée. En mettant à jour le dépôt
avec ces informations, les scripts appropriés sont disponibles et utilisés
aussitôt que la base DRE est dans la bonne version.

-*- coding: utf-8 mode: markdown -*- vim:sw=4:sts=4:et:ai:si:sta:fenc=utf-8:noeol:binary