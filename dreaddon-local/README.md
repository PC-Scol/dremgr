# dreaddon-local

Cet addon est spécial car il est livré avec dremgr et il est toujours
installé. Il permet d'utiliser les addons sans avoir à configurer un dépôt
tierce. Il n'est donc pas nécessaire comme pour les autres addons d'ajouter le
chemin de cet addon dans la configuration.

## Exécution

Pour rappel, le fichier `dreaddon.conf` permet de désigner les schémas à créer
ainsi que le mode de compatibilité.

Les fichiers sql ainsi que les scripts exécutables des répertoires `prepare`,
`vMM` et `updates` sont lancés pour mettre à jour la base de données

Les scripts du répertoire `notifications` sont lancés à la fin de l'import,
qu'il aie réussi ou non. Ils peuvent servir à envoyer des mails de rapport, à
notifier un service que les données ont été mises à jour, etc.

Cf [la documentation complète](../documentation/dreaddons-developpement.md) pour
les détails.

## Développement

Pour tester l'addon, les options -I ou -J peuvent être utilisées, e.g
~~~sh
# les deux commandes suivantes sont équivalentes:
./dbinst -I dreaddon-local
# ou
./dbinst -J dreaddon-local
~~~

-*- coding: utf-8 mode: markdown -*- vim:sw=4:sts=4:et:ai:si:sta:fenc=utf-8:noeol:binary