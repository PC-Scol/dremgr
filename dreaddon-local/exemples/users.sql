-- -*- coding: utf-8 mode: sql -*- vim:sw=4:sts=4:et:ai:si:sta:fenc=utf-8
-- exemple de code pour créer des utilisateurs avec des permissions fines (accès
-- à certaines tables de certains schémas uniquement par exemple)
-- placer ce fichier dans le répertoire updates/ ou dans un répertoire vXX/

-- NB: il faut déclarer les utilisateurs dans FE_USERS (afin qu'ils soient créés
-- dans pgbouncer) et leur donner le droit d'accès none dans FE_ACCESS, afin que
-- ce soit les droits d'accès définis ci-dessous qui soient utilisés.
-- Dans le mode simple, on peut aussi créer les utilisateurs directement dans ce
-- fichier. dans le mode avancé, ça ne sert à rien, puisque les utilisateurs
-- auront déjà été créés.
/*
do $$ begin
  -- ne créer les utilisateurs que s'ils n'existent pas
  if not exists (select from pg_user where usename = 'myuser') then
    raise NOTICE 'create user myuser;';
    create user myuser with password 'ZEPASS';
  end if;
end $$;
*/

-- il faut toujours accorder le droit d'usage à schema_tech

grant usage on schema schema_tech to myuser;

-- pas besoin d'accorder le droit d'usage au schéma public, c'est natif
-- pour tous les autres schémas, il faut systématiquement accorder le droit
-- d'usage avant de pouvoir accorder des droits supplémentaires sur les tables

grant select on public.version to myuser;

-- on peut accorder le droit d'accès à toutes les tables d'un schéma

grant usage on schema schema_ref to myuser;
grand select on all tables in schema schema_ref to myuser;

-- on peut aussi accorder le droit d'accès à certaines tables uniquement

grant usage on schema schema_ins to myuser;
grant select on schema_ins.apprenant to myuser;
grant select on schema_ins.periode to myuser;
grant select on schema_ins.chemin to myuser;
grant select on schema_ins.inscription to myuser;
