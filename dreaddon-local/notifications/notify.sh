#!/bin/bash
# -*- coding: utf-8 mode: sh -*- vim:sw=4:sts=4:et:ai:si:sta:fenc=utf-8

# Exemple de script pour notifier un webservice suite à une importation des
# dumps et l'exécution des addons *sans erreurs*
# Si une erreur se produit pendant l'importation, ne script ne fait rien.

[ -n "$CRITICAL_ERROR" -o -n "$HAVE_ERRORS" ] && exit

#curl -d service=dremgr 'https://notifs.domaine.fr/service-hooks/'
