# Installer la base de données persistante pdata

Depuis la version 1.5.0, les nouvelles installations bénéficient automatiquement
de la création de la base de données persistante. Pour les installations
existantes, il faut faire l'opération manuellement.

D'abord, mettez à jour la configuration. Copiez/collez la commande suivante
~~~sh
sed -i '
/^DBNAME=/a\
PDBNAME=pdata
/^PGBOUNCER_DBS=/s/=.*/="$DBNAME $PDBNAME"/
' dremgr.env
~~~
ou faites les modifications manuelles suivantes dans le fichier `dremgr.env`:
~~~sh
# avant la ligne PGBOUNCER_DBS= rajoutez
PDBNAME=pdata

# modifiez la ligne PGBOUNCER_DBS=
PGBOUNCER_DBS="$DBNAME $PDBNAME"
~~~

Puis, relancez les serveurs
~~~sh
./dbinst -AR
~~~

Enfin, créez la base de données `pdata` et configurez la liaison avec la base de
données `dre`
~~~sh
./dbinst -Ax -- setup-pdb.sh --create
~~~

-*- coding: utf-8 mode: markdown -*- vim:sw=4:sts=4:et:ai:si:sta:fenc=utf-8:noeol:binary