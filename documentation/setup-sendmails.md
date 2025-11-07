# Envoi de mails quotidiens

Le script `sendmails.php` dans dreaddon-local permet d'envoyer des mails à la
fin de chaque import quotidien

Pour l'utiliser, il faut éditer le fichier `notifications/sendmails.yml` dans le
répertoire `dreaddon-local` puis
* supprimer ou commenter la ligne `disabled: true`
* configurer la section mailer et renseigner le nom du serveur SMTP ainsi que le
  cas échéant le compte / mot de passe à utiliser.
* puis configurer les valeurs to, to_error, cc pour spécifier le ou les
  destinataires des mails
* le cas échéant, modifier le texte des modèles de mails. il y a 3 modèles de
  mail: un pour les imports réussis, un pour les imports avec erreurs, un pour
  les erreurs critiques.

Vous pouvez tester la notification avec
~~~sh
./dbinst -I dreaddon-local
~~~
mais il faudra au moins temporairement paramétrer `require_cron: false` pour que
la notification soit envoyée alors que ce n'est pas l'import de la planification
quotidienne.

-*- coding: utf-8 mode: markdown -*- vim:sw=4:sts=4:et:ai:si:sta:fenc=utf-8:noeol:binary