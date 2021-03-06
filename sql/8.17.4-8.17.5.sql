/*
 Copyright (C) 2015 IASA - Institute of Accelerating Systems and Applications (http://www.iasa.gr)

 Licensed under the Apache License, Version 2.0 (the "License");
 you may not use this file except in compliance with the License.
 You may obtain a copy of the License at
 
 http://www.apache.org/licenses/LICENSE-2.0

 Unless required by applicable law or agreed to in writing, software
 distributed under the License is distributed on an "AS IS" BASIS,
 WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 See the License for the specific language governing permissions and 
 limitations under the License.
*/

/* 
EGI AppDB incremental SQL script
Previous version: 8.17.4
New version: 8.17.5
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

INSERT INTO actor_groups (id, name) VALUES (-22, 'Site Security Officers');
INSERT INTO actor_groups (id, name) VALUES (-23, 'Site Operations Deputy Managers');
UPDATE actor_groups SET name = 'Site Operations Managers' WHERE id = -14;

-- Materialized View: public._actor_group_members
DROP MATERIALIZED VIEW public._actor_group_members CASCADE;

CREATE MATERIALIZED VIEW public._actor_group_members AS
 SELECT __actor_group_members.id,
    __actor_group_members.groupid,
    __actor_group_members.actorid,
    __actor_group_members.payload
   FROM __actor_group_members
UNION
 SELECT NULL::integer AS id,
    '-5'::integer AS groupid,
    researchers.guid AS actorid,
    NULL::text AS payload
   FROM researchers
UNION
 SELECT NULL::integer AS id,
    '-4'::integer AS groupid,
    researchers.guid AS actorid,
    vo_contacts.void::text AS payload
   FROM vo_contacts
     JOIN researchers ON researchers.id = vo_contacts.researcherid
  WHERE upper(vo_contacts.role) = 'VO MANAGER'::text
UNION
 SELECT NULL::integer AS id,
    '-6'::integer AS groupid,
    researchers.guid AS actorid,
    researchers_apps.appid::text AS payload
   FROM researchers_apps
     JOIN researchers ON researchers.id = researchers_apps.researcherid
UNION
 SELECT NULL::integer AS id,
    '-7'::integer AS groupid,
    researchers.guid AS actorid,
    vo_members.void::text AS payload
   FROM vo_members
     JOIN researchers ON researchers.id = vo_members.researcherid
UNION
 SELECT DISTINCT ON (privileges.actor) NULL::integer AS id,
    '-8'::integer AS groupid,
    privileges.actor AS actorid,
    NULL::text AS payload
   FROM privileges
  WHERE privileges.object IS NULL AND NOT privileges.revoked
UNION
 SELECT NULL::integer AS id,
    '-10'::integer AS groupid,
    researchers.guid AS actorid,
    sites.id AS payload
   FROM site_contacts
     JOIN sites ON sites.id = site_contacts.siteid
     JOIN researchers ON researchers.id = site_contacts.researcherid
  WHERE site_contacts.role ~* 'Site.Administrator.*'::text AND (EXISTS ( SELECT config.var,
            config.data
           FROM config
          WHERE config.var = 'managed_site_admins'::text AND config.data = '1'::text))
UNION
 SELECT NULL::integer AS id,
    '-11'::integer AS groupid,
    researchers.guid AS actorid,
    vo_contacts.void::text AS payload
   FROM vo_contacts
     JOIN researchers ON researchers.id = vo_contacts.researcherid
  WHERE upper(vo_contacts.role) = 'VO DEPUTY'::text
UNION
 SELECT NULL::integer AS id,
    '-12'::integer AS groupid,
    researchers.guid AS actorid,
    vo_contacts.void::text AS payload
   FROM vo_contacts
     JOIN researchers ON researchers.id = vo_contacts.researcherid
  WHERE upper(vo_contacts.role) = 'VO EXPERT'::text
UNION
 SELECT NULL::integer AS id,
    '-13'::integer AS groupid,
    researchers.guid AS actorid,
    vo_contacts.void::text AS payload
   FROM vo_contacts
     JOIN researchers ON researchers.id = vo_contacts.researcherid
  WHERE upper(vo_contacts.role) = 'VO SHIFTER'::text
UNION
 SELECT NULL::integer AS id,
    '-14'::integer AS groupid,
    researchers.guid AS actorid,
    sites.id AS payload
   FROM site_contacts
     JOIN sites ON sites.id = site_contacts.siteid
     JOIN researchers ON researchers.id = site_contacts.researcherid
  WHERE site_contacts.role ~* 'Site.Operations.Manager.*'::text AND (EXISTS ( SELECT config.var,
            config.data
           FROM config
          WHERE config.var = 'managed_site_admins'::text AND config.data = '1'::text))
UNION
SELECT NULL::integer AS id,
    '-23'::integer AS groupid,
    researchers.guid AS actorid,
    sites.id AS payload
   FROM site_contacts
     JOIN sites ON sites.id = site_contacts.siteid
     JOIN researchers ON researchers.id = site_contacts.researcherid
  WHERE site_contacts.role ~* 'Site.Operations.Deputy.Manager.*'::text AND (EXISTS ( SELECT config.var,
            config.data
           FROM config
          WHERE config.var = 'managed_site_admins'::text AND config.data = '1'::text))
UNION
SELECT NULL::integer AS id,
    '-22'::integer AS groupid,
    researchers.guid AS actorid,
    sites.id AS payload
   FROM site_contacts
     JOIN sites ON sites.id = site_contacts.siteid
     JOIN researchers ON researchers.id = site_contacts.researcherid
  WHERE site_contacts.role ~* 'Site.Security.Officer.*'::text AND (EXISTS ( SELECT config.var,
            config.data
           FROM config
          WHERE config.var = 'managed_site_admins'::text AND config.data = '1'::text))
WITH DATA;

ALTER TABLE public._actor_group_members
  OWNER TO appdb;

-- Index: public.idx__actor_group_members_actorid

-- DROP INDEX public.idx__actor_group_members_actorid;

CREATE INDEX idx__actor_group_members_actorid
  ON public._actor_group_members
  USING btree
  (actorid);

-- Index: public.idx__actor_group_members_payload

-- DROP INDEX public.idx__actor_group_members_payload;

CREATE INDEX idx__actor_group_members_payload
  ON public._actor_group_members
  USING btree
  (payload COLLATE pg_catalog."default");

-- Index: public.idx__actor_group_members_unique

-- DROP INDEX public.idx__actor_group_members_unique;

CREATE UNIQUE INDEX idx__actor_group_members_unique
  ON public._actor_group_members
  USING btree
  (groupid, actorid, payload COLLATE pg_catalog."default");

--------------------------
-- Materialized View: public.permissions

-- DROP MATERIALIZED VIEW public.permissions;

CREATE MATERIALIZED VIEW public.permissions AS
 WITH actor_group_members AS (
         SELECT _actor_group_members.id,
            _actor_group_members.groupid,
            _actor_group_members.actorid,
            _actor_group_members.payload
           FROM _actor_group_members
        )
 SELECT DISTINCT ON (_permissions.actor, _permissions.actionid, _permissions.object) array_agg(_permissions.id) AS ids,
    (EXISTS ( SELECT u.u
           FROM unnest(array_agg(_permissions.id)) u(u)
          WHERE u.u < 0)) AS system,
    _permissions.actor,
    _permissions.actionid,
    _permissions.object
   FROM ( SELECT __permissions.id,
            __permissions.actor,
            __permissions.actionid,
            __permissions.object
           FROM ( SELECT privileges.id,
                    privileges.actor,
                    privileges.actionid,
                    privileges.object
                   FROM privileges
                  WHERE NOT privileges.revoked
                UNION
                 SELECT privileges.id,
                    actors.guid AS actor,
                    privileges.actionid,
                    privileges.object
                   FROM privileges
                     CROSS JOIN actors
                     JOIN actor_group_members ON actor_group_members.actorid = actors.guid
                     JOIN actor_groups ON actor_groups.id = actor_group_members.groupid
                  WHERE actor_groups.guid = privileges.actor AND NOT privileges.revoked
                UNION
                 SELECT '-1'::integer AS id,
                    actors.guid AS actor,
                    actions.id AS actionid,
                    applications.guid AS object
                   FROM actions
                     CROSS JOIN applications
                     CROSS JOIN actor_group_members
                     JOIN actors ON actors.guid = actor_group_members.actorid
                     JOIN app_countries ON app_countries.appid = applications.id AND app_countries.countryid::text = actor_group_members.payload
                  WHERE actor_group_members.groupid = '-3'::integer AND (actions.id = ANY (app_actions()))
                UNION
                 SELECT '-2'::integer AS id,
                    actors.guid AS actor,
                    3 AS actionid,
                    NULL::uuid AS object
                   FROM actors
                     JOIN actor_group_members ON actor_group_members.actorid = actors.guid
                  WHERE actor_group_members.groupid = '-5'::integer
                UNION
                 SELECT
                        CASE actor_group_members.groupid
                            WHEN '-1'::integer THEN '-3'::integer
                            WHEN '-2'::integer THEN '-9'::integer
                            ELSE NULL::integer
                        END AS id,
                    actors.guid AS actor,
                    actions.id AS actionid,
                    NULL::uuid AS object
                   FROM actors
                     CROSS JOIN actions
                     JOIN actor_group_members ON actor_group_members.actorid = actors.guid
                  WHERE (actor_group_members.groupid = ANY (ARRAY['-1'::integer, '-2'::integer])) AND
                        CASE actor_group_members.groupid
                            WHEN '-2'::integer THEN NOT (actions.id = ANY (admin_only_actions()))
                            ELSE true
                        END
                UNION
                 SELECT '-4'::integer AS id,
                    researchers.guid AS actor,
                    actions.id AS actionid,
                    applications.guid AS object
                   FROM applications
                     CROSS JOIN actions
                     JOIN researchers ON researchers.id = applications.addedby OR researchers.id = applications.owner
                  WHERE NOT applications.addedby IS NULL AND (actions.id = ANY (app_actions()))
                UNION
                 SELECT '-5'::integer AS id,
                    r1.guid AS actor,
                    act.act AS actionid,
                    r2.guid AS object
                   FROM actors r1
                     CROSS JOIN researchers r2
                     CROSS JOIN unnest(ppl_actions()) act(act)
                     JOIN actor_group_members agm1 ON agm1.actorid = r1.guid
                  WHERE act.act <> 1 AND agm1.groupid = '-3'::integer AND r2.countryid::text = agm1.payload AND NOT (r2.guid IN ( SELECT agm2.actorid
                           FROM actor_group_members agm2
                          WHERE agm2.groupid = ANY (ARRAY['-1'::integer, '-2'::integer])))
                UNION
                 SELECT '-7'::integer AS id,
                    researchers.guid AS actor,
                    act.act AS actionid,
                    researchers.guid AS object
                   FROM researchers
                     CROSS JOIN unnest(ppl_actions()) act(act)
                  WHERE act.act <> 1
                UNION
                 SELECT '-8'::integer AS id,
                    actors.guid AS actor,
                    actions.id AS actionid,
                    applications.guid AS object
                   FROM applications
                     CROSS JOIN actions
                     JOIN actor_group_members ON actor_group_members.payload = applications.id::text
                     JOIN actors ON actor_group_members.actorid = actors.guid
                  WHERE actor_group_members.groupid = '-6'::integer AND (actions.id = ANY (app_metadata_actions()))
                UNION
                 SELECT '-14'::integer AS id,
                    researchers.guid AS actor,
                    25 AS actionid,
                    userrequests.guid AS object
                   FROM userrequests
                     JOIN applications ON applications.guid = userrequests.targetguid
                     JOIN researchers ON researchers.id = applications.addedby OR researchers.id = applications.owner
                  WHERE (userrequests.typeid = ANY (ARRAY[1, 2])) AND NOT (applications.addedby IS NULL AND applications.owner IS NULL)
                UNION

                 SELECT '-23'::integer AS id, /*grant 36 to sites that have cloud end-points*/
                        sites.guid AS actor,
                        36 AS actionid,
                        NULL as object
                 FROM sites
                 WHERE EXISTS (SELECT * FROM va_providers WHERE sitename = sites.name)

                 UNION

                 SELECT '-24'::integer AS id, /*grant 36 to admins and managers of sites that have cloud end-points*/
                        agm.actorid AS actor,
                        36 AS actionid,
                        NULL as object
                 FROM sites INNER JOIN actor_group_members agm ON agm.payload = sites.id
                 WHERE EXISTS (SELECT * FROM va_providers WHERE sitename = sites.name)

                UNION

                 SELECT '-20'::integer AS id, /*propagate 36 to admins and managers of sites that have been explicitly mentioned in privileges*/
                    researchers.guid AS actor,
                    privileges.actionid,
                    privileges.object
                   FROM privileges
                     JOIN actor_group_members agm ON agm.payload =
                                ((
                                        SELECT __sites.id
                                        FROM __sites
                                        WHERE __sites.guid = privileges.actor
                                ))
                     JOIN researchers ON agm.actorid = researchers.guid
                  WHERE agm.groupid IN (-10, -14) AND (privileges.actionid = 36) AND NOT privileges.revoked
                UNION
                 SELECT '-21'::integer AS id,
                    actor_group_members.actorid AS actor,
                    37 AS actionid,
                    vos.guid AS object
                   FROM actor_group_members
                     JOIN vos ON vos.id::text = actor_group_members.payload AND NOT vos.deleted
                  WHERE actor_group_members.groupid = ANY (ARRAY['-4'::integer, '-11'::integer, '-12'::integer, '-13'::integer])
                UNION
                 SELECT '-22'::integer AS id,
                    actor_group_members.actorid AS actor,
                    45 AS actionid,
                    NULL::uuid AS object
                   FROM actor_group_members
                  WHERE actor_group_members.groupid = '-19'::integer
                UNION
                 SELECT '-15'::integer AS id,
                    privileges.actor,
                    34 AS actionid,
                    privileges.object
                   FROM privileges
                  WHERE privileges.actionid = 32) __permissions
          WHERE NOT ((__permissions.actor, __permissions.actionid, __permissions.object) IN ( SELECT privileges.actor,
                    privileges.actionid,
                    targets.guid
                   FROM privileges
                     JOIN targets ON targets.guid = COALESCE(privileges.object, targets.guid)
                  WHERE privileges.revoked = true
                UNION
                 SELECT privileges.actor,
                    privileges.actionid,
                    privileges.object
                   FROM privileges
                  WHERE privileges.revoked = true))) _permissions
  GROUP BY _permissions.actor, _permissions.actionid, _permissions.object
WITH DATA;

ALTER TABLE public.permissions
  OWNER TO appdb;

-- Index: public.idx_permissions_actionid

-- DROP INDEX public.idx_permissions_actionid;

CREATE INDEX idx_permissions_actionid
  ON public.permissions
  USING btree
  (actionid);

-- Index: public.idx_permissions_actionid_object_actor

-- DROP INDEX public.idx_permissions_actionid_object_actor;

CREATE INDEX idx_permissions_actionid_object_actor
  ON public.permissions
  USING btree
  (actionid, object, actor);

-- Index: public.idx_permissions_actor

-- DROP INDEX public.idx_permissions_actor;

CREATE INDEX idx_permissions_actor
  ON public.permissions
  USING btree
  (actor);

-- Index: public.idx_permissions_actor_actionid_objnotnull

-- DROP INDEX public.idx_permissions_actor_actionid_objnotnull;

CREATE INDEX idx_permissions_actor_actionid_objnotnull
  ON public.permissions
  USING btree
  (actor, actionid, object)
  WHERE NOT object IS NULL;

-- Index: public.idx_permissions_actor_actionid_objnull

-- DROP INDEX public.idx_permissions_actor_actionid_objnull;

CREATE INDEX idx_permissions_actor_actionid_objnull
  ON public.permissions
  USING btree
  (actor, actionid, object)
  WHERE object IS NULL;

-- Index: public.idx_permissions_object

-- DROP INDEX public.idx_permissions_object;

CREATE INDEX idx_permissions_object
  ON public.permissions
  USING btree
  (object);

-- Index: public.idx_permissions_unique

-- DROP INDEX public.idx_permissions_unique;

CREATE UNIQUE INDEX idx_permissions_unique
  ON public.permissions
  USING btree
  (actionid, actor, object);

------------------------

-- View: public.editable_apps

-- DROP VIEW public.editable_apps;

CREATE OR REPLACE VIEW public.editable_apps AS
 SELECT DISTINCT applications.id AS appid,
    permissions.actor
   FROM applications
     LEFT JOIN permissions ON permissions.object = applications.guid OR permissions.object IS NULL
  WHERE permissions.actionid = ANY (app_metadata_actions());

ALTER TABLE public.editable_apps
  OWNER TO appdb;

---------------------------

-- View: public.vldap_group_members

-- DROP VIEW public.vldap_group_members;

CREATE OR REPLACE VIEW public.vldap_group_members AS
 SELECT researchers.id AS user_id,
    editable_apps.appid AS group_id
   FROM editable_apps
     JOIN researchers ON researchers.guid = editable_apps.actor;

ALTER TABLE public.vldap_group_members
  OWNER TO appdb;

---------------------------

-- View: public.editable_apps2

-- DROP VIEW public.editable_apps2;

CREATE OR REPLACE VIEW public.editable_apps2 AS
 SELECT DISTINCT applications.id AS appid,
    permissions.actor
   FROM applications
     LEFT JOIN permissions ON permissions.object = applications.guid
     LEFT JOIN actions ON actions.id = permissions.actionid
  WHERE actions.id = ANY (app_metadata_actions())
UNION
 SELECT DISTINCT applications.id AS appid,
    permissions.actor
   FROM applications
     LEFT JOIN permissions ON permissions.object IS NULL
     LEFT JOIN actions ON actions.id = permissions.actionid
  WHERE actions.id = ANY (app_metadata_actions());

ALTER TABLE public.editable_apps2
  OWNER TO appdb;
------------------------

-- View: public.__permissions

-- DROP VIEW public.__permissions;

CREATE OR REPLACE VIEW public.__permissions AS
 SELECT permissions.ids,
    permissions.system,
    permissions.actor,
    permissions.actionid,
    permissions.object
   FROM permissions;

ALTER TABLE public.__permissions
  OWNER TO appdb;
-----------------------
-- Materialized View: public._actor_group_members2

-- DROP MATERIALIZED VIEW public._actor_group_members2;

CREATE MATERIALIZED VIEW public._actor_group_members2 AS
 SELECT _actor_group_members.id,
    _actor_group_members.groupid,
    _actor_group_members.actorid,
    _actor_group_members.payload
   FROM _actor_group_members
UNION
 SELECT DISTINCT ON (privileges.actor) NULL::integer AS id,
    '-9'::integer AS groupid,
    privileges.actor AS actorid,
    (( SELECT applications.id
           FROM applications
          WHERE applications.guid = privileges.object))::text AS payload
   FROM ( SELECT __permissions.actionid,
            __permissions.actor,
            __permissions.object,
            false AS revoked
           FROM __permissions
        UNION
         SELECT privileges_1.actionid,
            privileges_1.actor,
            privileges_1.object,
            privileges_1.revoked
           FROM privileges privileges_1) privileges
     JOIN targets ON targets.guid = privileges.object
  WHERE NOT privileges.revoked AND targets.type = 'app'::text
  GROUP BY privileges.actor, privileges.object
 HAVING array_agg(privileges.actionid) @> app_fc_actions()
WITH DATA;

ALTER TABLE public._actor_group_members2
  OWNER TO appdb;

-- Index: public.idx_actor_group_members_unique

-- DROP INDEX public.idx_actor_group_members_unique;

CREATE UNIQUE INDEX idx_actor_group_members_unique
  ON public._actor_group_members2
  USING btree
  (groupid, actorid, payload COLLATE pg_catalog."default");


-----------------------------

-- View: public.actor_group_members

-- DROP VIEW public.actor_group_members;

CREATE OR REPLACE VIEW public.actor_group_members AS
 SELECT _actor_group_members2.id,
    _actor_group_members2.groupid,
    _actor_group_members2.actorid,
    _actor_group_members2.payload
   FROM _actor_group_members2;

ALTER TABLE public.actor_group_members
  OWNER TO appdb;

-- Rule: r_delete_actor_group_members ON public.actor_group_members

-- DROP RULE r_delete_actor_group_members ON public.actor_group_members;

CREATE OR REPLACE RULE r_delete_actor_group_members AS
    ON DELETE TO actor_group_members DO INSTEAD  DELETE FROM __actor_group_members
  WHERE __actor_group_members.id = old.id AND NOT old.id IS NULL
  RETURNING __actor_group_members.id,
    __actor_group_members.groupid,
    __actor_group_members.actorid,
    __actor_group_members.payload;

-- Rule: r_insert_actor_group_members ON public.actor_group_members

-- DROP RULE r_insert_actor_group_members ON public.actor_group_members;

CREATE OR REPLACE RULE r_insert_actor_group_members AS
    ON INSERT TO actor_group_members DO INSTEAD  INSERT INTO __actor_group_members (groupid, actorid, payload)
  VALUES (new.groupid, new.actorid, new.payload)
  RETURNING __actor_group_members.id,
    __actor_group_members.groupid,
    __actor_group_members.actorid,
    __actor_group_members.payload;

-- Rule: r_update_actor_group_members ON public.actor_group_members

-- DROP RULE r_update_actor_group_members ON public.actor_group_members;

CREATE OR REPLACE RULE r_update_actor_group_members AS
    ON UPDATE TO actor_group_members DO INSTEAD  UPDATE __actor_group_members SET groupid = new.groupid, actorid = new.actorid, payload = new.payload
  WHERE __actor_group_members.id = old.id AND NOT old.id IS NULL
  RETURNING __actor_group_members.id,
    __actor_group_members.groupid,
    __actor_group_members.actorid,
    __actor_group_members.payload;
---------------

-- Function: public.delete_agm(integer)

-- DROP FUNCTION public.delete_agm(integer);

CREATE OR REPLACE FUNCTION public.delete_agm(_id integer)
  RETURNS SETOF actor_group_members AS
$BODY$
BEGIN
        DELETE FROM __actor_group_members
        WHERE __actor_group_members.id = _id;
        RETURN QUERY SELECT * FROM __actor_group_members WHERE FALSE;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE STRICT
  COST 100
  ROWS 1000;
ALTER FUNCTION public.delete_agm(integer)
  OWNER TO appdb;
-------------------

REFRESH MATERIALIZED VIEW _actor_group_members;
REFRESH MATERIALIZED VIEW _actor_group_members2;
REFRESH MATERIALIZED VIEW permissions;

-------------------

/* UPSERT config TO enable managed_site_admins */
UPDATE config SET data = '1' WHERE var = 'managed_site_admins';
INSERT INTO config (var, data) SELECT 'managed_site_admins', 1 WHERE NOT EXISTS (
        SELECT * FROM config WHERE var = 'managed_site_admins'
);

ALTER TABLE gocdb.site_contacts RENAME COLUMN dn TO accountid;
ALTER TABLE gocdb.site_contacts ALTER COLUMN accountid SET NOT NULL;
ALTER TABLE gocdb.site_contacts ADD COLUMN account_type e_account_type NOT NULL; -- DEFAULT 'egi-aai'::e_account_type;

CREATE OR REPLACE VIEW site_contacts AS
 SELECT sites.id AS siteid,
    user_accounts.researcherid,
    site_contacts.role,
        CASE
            WHEN user_accounts.researcherid IS NULL THEN site_contacts.name
            ELSE researchers.name
        END AS name,
    researchers.cname
   FROM gocdb.site_contacts
     JOIN sites ON sites.id = site_contacts.site_pkey
     LEFT JOIN user_accounts ON user_accounts.accountid = site_contacts.accountid AND user_accounts.account_type = site_contacts.account_type
     LEFT JOIN researchers ON researchers.id = user_accounts.researcherid;

CREATE OR REPLACE FUNCTION trfn_gocdb_site_contacts_refresh_permissions() RETURNS TRIGGER AS
$$
BEGIN
	IF TG_WHEN = 'AFTER' THEN
		REFRESH MATERIALIZED VIEW CONCURRENTLY permissions;		
		RETURN NULL;
	END IF;
END;
$$ LANGUAGE plpgsql;
ALTER FUNCTION trfn_gocdb_site_contacts_refresh_permissions() OWNER TO appdb;

CREATE TRIGGER tr_90_gocdb_site_contacts_refresh_permissions
AFTER INSERT OR UPDATE OR DELETE ON gocdb.site_contacts 
FOR EACH STATEMENT
EXECUTE PROCEDURE trfn_gocdb_site_contacts_refresh_permissions();

CREATE OR REPLACE FUNCTION public.add_egiaai_user_site_contact_info(puid text, site_pkey text, role text)
 RETURNS void
 LANGUAGE sql
AS $function$
INSERT INTO gocdb.site_contacts(site_pkey, accountid, account_type, role) 
SELECT $2, $1, 'egi-aai'::e_account_type, $3
WHERE NOT EXISTS (
        SELECT * FROM gocdb.site_contacts
        WHERE 
                site_pkey = $2 AND
                accountid = $1 AND
                account_type = 'egi-aai'::e_account_type AND
                role = $3
)
$function$;
ALTER FUNCTION add_egiaai_user_site_contact_info(text, text, text) OWNER TO appdb;

CREATE OR REPLACE FUNCTION public.clear_egiaai_user_info(puid text, extended boolean DEFAULT false)
 RETURNS void
 LANGUAGE plpgsql
AS $function$
DECLARE rid int;
BEGIN
        IF $2 THEN
                rid := (SELECT DISTINCT researcherid FROM user_accounts WHERE accountid = $1 AND account_type = 'egi-aai');
                DELETE FROM egiaai.vo_members WHERE egiaai.vo_members.puid IN (SELECT DISTINCT accountid FROM user_accounts WHERE account_type = 'egi-aai' AND researcherid = rid) OR egiaai.vo_members.puid = $1;
                DELETE FROM egiaai.vo_contacts WHERE egiaai.vo_contacts.puid IN (SELECT DISTINCT accountid FROM user_accounts WHERE account_type = 'egi-aai' AND researcherid = rid) OR egiaai.vo_contacts.puid = $1;
		DELETE FROM gocdb.site_contacts WHERE (gocdb.site_contacts.account_type = 'egi-aai'::e_account_type) AND (gocdb.site_contacts.accountid IN (SELECT DISTINCT accountid FROM user_accounts WHERE account_type = 'egi-aai' AND researcherid = rid) OR gocdb.site_contacts.account_type = $1);
        ELSE
                DELETE FROM egiaai.vo_members WHERE egiaai.vo_members.puid = $1;
                DELETE FROM egiaai.vo_contacts WHERE egiaai.vo_contacts.puid = $1;
		DELETE FROM gocdb.site_contacts WHERE (gocdb.site_contacts.account_type = 'egi-aai'::e_account_type) AND (gocdb.site_contacts.accountid = $1);
        END IF;
END
$function$;

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 17, 5, E'Update and enable managed_site_admins permissions machinery'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=17 AND revision=5);

COMMIT;
