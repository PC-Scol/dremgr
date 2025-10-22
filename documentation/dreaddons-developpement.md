# Développement DREaddons

dremgr supporte l'installation d'addons accessible via des dépôts git. Cette
page indique comment développer ses propres addons.

Il existe un [template sur github](https://github.com/PC-Scol/dreaddon-template)
qu'on peut utiliser pour démarrer facilement.

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
  chargés les scripts

  Le mode de compatibilité `vxx` permet de préparer à l'avance les scripts et
  documentation éventuelles pour une version donnée. En mettant à jour le dépôt
  avec ces informations, les scripts appropriés sont disponibles et utilisés
  aussitôt que la base DRE est dans la bonne version.

`IGNORE_ERRORS`
: demander à ignorer les erreurs lors de l'exécution des scripts de l'addon

  normalement, si une erreur se produit, le script est arrêté et un message dans
  les logs indique que l'installation de l'addon n'est pas complète.

  dans certains cas, on peut souhaiter que les scripts soient lancés jusqu'au
  bout, même si certaines requêtes sont en erreur.

## Scripts

L'importation des schémas DRE se fait de cette manière:

* Suppression des schémas livrés par DRE
* Suppression des schémas mentionnés dans la variable `SCHEMAS` de
  `dreaddon.conf`
* Importation des dumps du jour livrés par DRE
* Traitements des scripts des addons, dans l'ordre mentionné dans la
  configuration de dremgr

Pour chaque addon, les scripts sont lancés depuis les répertoires suivants:

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
* l'accès en lecture à toutes les tables des schémas est donné aux utilisateurs
  configurés dans dremgr. puis les scripts du répertoire `updates` sont lancés.

Quand on parle de scripts, il s'agit de tous les fichiers `*.sql` ainsi que des
scripts exécutables (quelle que soit l'extension). Les autres fichiers sont
ignorés.

Comme l'environnement est configuré comme il se doit par dremgr, les scripts
exécutables shell peuvent lancer directement psql pour attaquer la base de
données.

---

Une précision concernant l'organisation des fichiers et l'ordre de lancement des
scripts: d'abord les scripts de "prepare", puis ceux de "vXX", puis ceux de
"updates". Au sein de chaque répertoire, les scripts sont exécutés par ordre
alphabétique

Dans le principe, on s'attend à ce qu'il y a soit un répertoire vXX (pour un
addon dépendant de la version de la release), soit un répertoire "updates" (pour
un addon indépendant de la version de la release), mais pas les deux (bien que
ce soit techniquement possible et justifié dans certains cas). Si les deux
existent, l'ordre sélectionné est celui indiqué ci-dessus

Le raisonnement est le suivant: pour un addon qui dépend de la version de la
release, tous les fichiers sont dans un répertoire vXX. Si jamais il faut
mettre à jour les scripts pour une release supérieure, *tout* le répertoire vXX
est copié dans vYY, puis les fichiers sont adaptés au besoin. Ça simplifie la
maintenance: pas besoin de fusionner mentalement la liste des fichiers de
plusieurs répertoires pour savoir ce qui est exécuté.

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
{"url": "https://monsite.fr/madoc"
, "title": "Lien vers madoc"
, "desc": "Description longue au format markdown"
, "target": "_blank"
}
~~~
`desc` et `target` sont optionnels

Le fichier `metadata.yml` est aussi traité de façon particulière: il permet de
spécifier des informations supplémentaires associé à un fichier téléchargeable.
Il est de la forme
~~~yaml
files:
  monfichier.doc:
    title: Titre du fichier
    desc: |
      description longue du fichier
      au format markdown
~~~
La documentation affichera le titre et la description spécifiés en plus du lien
vers `monfichier.doc`

## Tester un addon en cours de développement

Lorsqu'on développe un addon, il faut pouvoir tester les scripts avant de les
envoyer en production. Une façon de faire est d'avoir une installation de dremgr
sur son poste (via WSL ou autre méthode) et lancer l'importation à chaque fois.

* Installer dremgr sur son poste avec la même configuration qu'en prod
* Il n'est pas forcément possible de télécharger les fichiers depuis le poste du
  développeur à cause de la restriction sur l'adresse IP. il faut donc récupérer
  les fichiers depuis le serveur de prod
  ~~~sh
  # ici, on récupére les fichiers depuis le serveur monserveur.univ.tld dans le
  # profil prod, en partant du principe que dremgr est installé dans le
  # répertoire d'origine de root, et on les copie dans le répertoire courant
  # dans le profil prod
  src_dremgr=root@monserveur.univ.tld:dremgr
  src_profile=prod
  dest_profile=prod

  rsync -avP "${src_dremgr}/var/${src_profile}-dredata/downloads/" "var/${dest_profile}-dredata/downloads/"
  ~~~
* Ensuite, on peut lancer l'imporation des fichiers du jour
  ~~~sh
  ./dbinst -i
  ~~~

  NB: il est possible de spécifier la date des fichiers à importer avec l'option
  `-@ YYYYMMDD`. Par exemple, pour importer les fichiers du 15/06/2024, on peut
  faire ceci (le `--` entre `-i` et `-@` est requis):
  ~~~sh
  ./dbinst -i -- -@ 20240615
  ~~~

La méthode ci-dessus réimporte TOUS les dumps et TOUS les addons, ce qui permet
de vérifier que l'import quotidien fonctionnera correctement une fois en
production, mais ça peut prendre un certain temps en fonction du nombre
d'addons.

Si on veut uniquement réimporter l'addon sur lequel on travaille, il est
possible de le faire avec l'option `-I`, e.g:
~~~sh
# on ne peut importer que ce qui a été enregistré et poussé
cd ~/path/to/dreaddon-myaddon
git commit -am "mes modifications" && git push

# puis importer les modifications
cd ~/path/to/dremgr
./dbinst -I myaddon
~~~
Dans cet exemple, seul l'addon `dreaddon-myaddon` est réimporté, ce qui permet
de vérifier par exemple que les données sont correctement provisionnées, ou que
la documentation associée est bien celle attendue.

Pour faciliter le développement, l'option `-J` permet de synchroniser le contenu
du répertoire local d'addon puis de lancer son import, e.g:
~~~sh
./dbinst -J path/to/dreaddon-myaddon

# la commande ci-dessus est grossièrement équivalente à:
rsync -rlp --delete path/to/dreaddon-myaddon/ var/prod-dredata/addons/dreaddon-myaddon/
./dbinst -i -- --no-updateao --runao -o dreaddon-myaddon -@ latest
less var/prod-dredata/import.log
~~~

-*- coding: utf-8 mode: markdown -*- vim:sw=4:sts=4:et:ai:si:sta:fenc=utf-8:noeol:binary