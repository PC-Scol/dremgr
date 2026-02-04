-- -*- coding: utf-8 mode: sql -*- vim:sw=4:sts=4:et:ai:si:sta:fenc=utf-8
-- exemple de code pour créer l'arborescence des objets maquettes dans une table
-- nommée arbaume

-- à utiliser de cette façon:
--   select * from arbaume
--   where temoin_valide and code_periode = 'PERIODE'
--   order by chemin 

-- arbaume est un jeu de mot idiot entre "arbre objet maquette" et le fait
-- qu'avoir ces données est un soulagement certain.

drop table if exists public.arbaume cascade;

create table public.arbaume as
with recursive cte as (
  select
    0 niveau
  , e.code code_periode
  , o.code code_formation
  , case
    when c.temoin_inscription_administrative then o.code
    else null
    end code_pia
  , null::varchar code_parent
  , c.temoin_valide
  , o.code::varchar chemin
  -- toutes les colonnes de la table objet_maquette
  , o.type_objet_maquette
  , o.id
  , o.code_structure
  , o.code
  , o.code_diplome_sise
  , o.niveau_diplome_sise
  , o.code_parcours_type_sise
  , o.description
  , o.credit_ects
  , o.libelle_court
  , o.libelle_long
  , o.nature
  , o.temoin_stage
  , o.code_structure_principale
  , o.temoin_tele_enseignement
  , o.autres_informations
  , o.bibliographie
  , o.contacts
  , o.langue_enseignement
  , o.modalite_enseignement
  , o.objectifs
  , o.temoin_ouverture_mobilite_entrante
  , o.prerequis_pedagogique
  , o.temoin_mutualise
  , o.code_type_objet_formation
  , o.version
  , o.id_espace
  , o.plage_max
  , o.plage_min
  , o.code_type_formation
  , o.volume_horaire_par_type_de_cours
  , o.coefficient
  , o.modalites_evaluation
  , o.capacite_accueil
  , o.id_formation_porteuse
  , o.structures_porteuse
  , o.code_type_diplome
  , o.code_niveau_diplome
  , o.code_domaine_formation
  , o.code_mention
  , o.code_champ_formation
  , o.temoin_habilite_pour_bourses_aglae
  , o.niveau_aglae
  , o.codes_perimetres
  , o.declinaison_diplome
  , o.numero_fresq_niveau_1
  , o.numero_fresq_niveau_2
  , o.code_diplome_intermediaire_sise
  -- informations supplémentaires
  , c.temoin_inscription_administrative temoin_pia
  , c.temoin_inscription_administrative_active temoin_pia_actif
  , case
    when c.temoin_inscription_administrative then o.code::varchar
    else null
    end chemin_pia
  , null::varchar chemin_parent
  , c.chemin ctx_chemin

  from schema_odf.objet_maquette o
  inner join schema_odf.contexte c on c.chemin = array[o.id]
  inner join schema_odf.espace e on e.id = o.id_espace

  where o.type_objet_maquette = 'F'

union

  select
    (cte.niveau + 1) niveau
  , e.code code_periode
  , cte.code_formation
  , case
    when cte.code_pia is not null then cte.code_pia
    when c.temoin_inscription_administrative then o.code
    else null
    end code_pia
  , cte.code code_parent
  , c.temoin_valide
  , (cte.chemin || '>' || o.code) chemin
  -- toutes les colonnes de la table objet_maquette
  , o.type_objet_maquette
  , o.id
  , o.code_structure
  , o.code
  , o.code_diplome_sise
  , o.niveau_diplome_sise
  , o.code_parcours_type_sise
  , o.description
  , o.credit_ects
  , o.libelle_court
  , o.libelle_long
  , o.nature
  , o.temoin_stage
  , o.code_structure_principale
  , o.temoin_tele_enseignement
  , o.autres_informations
  , o.bibliographie
  , o.contacts
  , o.langue_enseignement
  , o.modalite_enseignement
  , o.objectifs
  , o.temoin_ouverture_mobilite_entrante
  , o.prerequis_pedagogique
  , o.temoin_mutualise
  , o.code_type_objet_formation
  , o.version
  , o.id_espace
  , o.plage_max
  , o.plage_min
  , o.code_type_formation
  , o.volume_horaire_par_type_de_cours
  , o.coefficient
  , o.modalites_evaluation
  , o.capacite_accueil
  , o.id_formation_porteuse
  , o.structures_porteuse
  , o.code_type_diplome
  , o.code_niveau_diplome
  , o.code_domaine_formation
  , o.code_mention
  , o.code_champ_formation
  , o.temoin_habilite_pour_bourses_aglae
  , o.niveau_aglae
  , o.codes_perimetres
  , o.declinaison_diplome
  , o.numero_fresq_niveau_1
  , o.numero_fresq_niveau_2
  , o.code_diplome_intermediaire_sise
  -- informations supplémentaires
  , c.temoin_inscription_administrative temoin_pia
  , c.temoin_inscription_administrative_active temoin_pia_actif
  , case
    when cte.chemin_pia is not null then cte.chemin_pia
    when c.temoin_inscription_administrative then cte.chemin || '>' || o.code
    else null
    end chemin_pia
  , cte.chemin chemin_parent
  , c.chemin ctx_chemin

  from schema_odf.objet_maquette o
  inner join schema_odf.enfant enf on enf.id_objet_maquette = o.id
  inner join cte on cte.id = enf.id_objet_maquette_parent
  inner join schema_odf.contexte c on c.chemin_pere = cte.ctx_chemin and c.chemin[array_upper(c.chemin, 1)] = o.id
  inner join schema_odf.espace e on e.id = o.id_espace
)
select * from cte;
