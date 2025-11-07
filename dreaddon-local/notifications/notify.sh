#!/bin/bash
# -*- coding: utf-8 mode: sh -*- vim:sw=4:sts=4:et:ai:si:sta:fenc=utf-8

# Exemple de script pour notifier un webservice suite à une importation des
# dumps et l'exécution des addons *sans erreurs*
# Si une erreur se produit pendant l'importation, ne script ne fait rien.

# si une erreur critique s'est produite, arrêter le script
[ -n "$CRITICAL_ERROR" ] && exit

# si une erreur non critique s'est produite, arrêter le script
[ -n "$HAVE_ERRORS" ] && exit

# ne continuer que si cette notification survient suite à la planification
# quotidienne
[ -n "$TEM_CRON" ] || exit

# c'est bon toutes les conditions sont remplies, lancer la notification

#curl -d service=dremgr 'https://notifs.domaine.fr/service-hooks/'
