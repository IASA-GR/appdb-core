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
Previous version: 8.14.6
New version: 8.14.7
Author: wvkarag@kadath.priv.iasa.gr
*/

START TRANSACTION;

UPDATE relationtypes SET actionid = 39 WHERE id = 1;
UPDATE relationtypes SET actionid = 38 WHERE id IN (2,3);

CREATE OR REPLACE FUNCTION public.ppl_actions()
 RETURNS integer[]
 LANGUAGE sql
 IMMUTABLE
AS $function$
SELECT ARRAY[1, 21, 38, 39];
$function$;

DROP MATERIALIZED VIEW public.permissions CASCADE;

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
                 SELECT '-20'::integer AS id,
                    researchers.guid AS actor,
                    privileges.actionid,
                    privileges.object
                   FROM privileges
                     JOIN actor_group_members agm ON agm.payload = (( SELECT __va_providers.id
                           FROM __va_providers
                          WHERE __va_providers.guid = privileges.actor))
                     JOIN researchers ON agm.actorid = researchers.guid
                  WHERE agm.groupid = '-10'::integer AND (privileges.actionid = ANY (ARRAY[36, 37])) AND NOT privileges.revoked
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



--------------------------------

CREATE OR REPLACE VIEW public.editable_apps AS
 SELECT DISTINCT applications.id AS appid,
    permissions.actor
   FROM applications
     LEFT JOIN permissions ON permissions.object = applications.guid OR permissions.object IS NULL
  WHERE permissions.actionid = ANY (app_metadata_actions());

ALTER TABLE public.editable_apps
  OWNER TO appdb;

CREATE OR REPLACE VIEW public.vldap_group_members AS
 SELECT researchers.id AS user_id,
    editable_apps.appid AS group_id
   FROM editable_apps
     JOIN researchers ON researchers.guid = editable_apps.actor;

ALTER TABLE public.vldap_group_members
  OWNER TO appdb;

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

  CREATE OR REPLACE VIEW public.__permissions AS
 SELECT permissions.ids,
    permissions.system,
    permissions.actor,
    permissions.actionid,
    permissions.object
   FROM permissions;

ALTER TABLE public.__permissions
  OWNER TO appdb;

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

COMMIT;

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 14, 7, E'Fix permissions problem, where non-admins cannot edit their own profile organizations and projects'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=14 AND revision=7);
