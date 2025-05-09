# Installer la base de données persistante pdata

Depuis la version 1.5.0, les nouvelles installations bénéficient automatiquement
de la création de la base de données persistante. Pour les installations
existantes, il faut faire l'opération manuellement.

Mettre à jour la configuration
~~~sh
# rajouter
PDBNAME=pdata

# modifier
PGBOUNCER_DBS="$DBNAME $PDBNAME"
~~~

Puis, pour chaque profil (`-P`, `-T`, `-gPROFILE`, etc.)
* relancer le serveur
  ~~~sh
  ./dbinst -P -R
  ~~~
* puis créer la base de données `pdata` et configurer la liaison avec la base de
  données `dre`
  ~~~sh
  ./dbinst -P --shell-db
  ~~~
  ~~~sh
  cd /docker-entrypoint-initdb.d
   
  ./01-pdata-create.sh
   
  ./03-dre-setup-pdata.sh
  ~~~

-*- coding: utf-8 mode: markdown -*- vim:sw=4:sts=4:et:ai:si:sta:fenc=utf-8:noeol:binary