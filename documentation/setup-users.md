# Créer des utilisateurs supplémentaires

Vous pouvez spécifier des utilisateurs supplémentaires à créer. Cela permet de
faciliter le suivi et le controle d'accès à la base de données DRE.

Renseigner les paramètres `FE_USERS` et `FE_ACCESS`
~~~sh
# utilisateurs supplémentaires à créer, un par ligne. la syntaxe à utiliser est
#     user:password
FE_USERS="
alice:s3cret
bob:l3tme1n
"
# droits d'accès à accorder aux utilisateurs, un par ligne. la syntaxe à
# utiliser est
#     user:access
# les utilisateurs ont toujours un accès en lecture sur la base de données DRE.
# le type d'accès à la base de données pdata dépend de la valeur de access: en
# lecture si access vaut ro, en écriture si access vaut rw. la valeur par défaut
# est ro (si access n'est pas spécifié ou si l'utilisateur n'est pas mentionné)
FE_ACCESS="
alice:rw
bob:ro
"
~~~

Si l'un des paramètres `FE_USERS` et/ou `FE_ACCESS` est modifié, redémarrer les
instances
~~~sh
./dremgr -r
~~~

*Après* avoir redémarré les instances, lancer la commande pour créer les
nouveaux comptes
~~~sh
./dbinst -Ax create-pgusers.sh
~~~
NB: seuls les nouveaux comptes sont créés. les comptes existant ne sont pas
modifiés

IMPORTANT: notez que les nouveaux utilisateurs n'ont pas d'accès aux données
avant la prochaine importation, soit le lendemain. S'il faut leur donner l'accès
de suite, il faut forcer la réimportation des fichiers
~~~sh
./dbinst -Ai -- -@latest
~~~

-*- coding: utf-8 mode: markdown -*- vim:sw=4:sts=4:et:ai:si:sta:fenc=utf-8:noeol:binary