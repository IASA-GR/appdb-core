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
Previous version: 8.13.1
New version: 8.13.2
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

ALTER TABLE gocdb.va_providers RENAME COLUMN occi_downtime TO service_downtime;
ALTER TABLE gocdb.va_providers RENAME COLUMN argo_status TO service_status;
ALTER TABLE gocdb.va_providers RENAME COLUMN argo_status_date TO service_status_date;

ALTER TABLE __va_providers RENAME COLUMN occi_downtime TO service_downtime;
ALTER TABLE __va_providers RENAME COLUMN argo_status TO service_status;
ALTER TABLE __va_providers RENAME COLUMN argo_status_date TO service_status_date;

CREATE OR REPLACE VIEW public.__va_providers AS 
 SELECT va_providers.pkey AS id,
    va_providers.sitename,
    va_providers.url,
    va_providers.gocdb_url,
    va_providers.hostname,
    va_providers.host_dn,
    va_providers.host_ip,
    oses.id AS host_os_id,
    archs.id AS host_arch_id,
    va_providers.beta,
    va_providers.in_production,
    va_providers.node_monitored,
    countries.id AS country_id,
    va_providers.roc_name AS ngi,
    uuid_generate_v5(uuid_namespace('ISO OID'::text), va_providers.pkey) AS guid,
    va_providers.serviceid,
    va_providers.service_downtime,
    va_providers.service_status,
    va_providers.service_status_date
   FROM gocdb.va_providers
     LEFT JOIN oses ON oses.name = va_providers.host_os
     LEFT JOIN archs ON archs.name = va_providers.host_arch OR (va_providers.host_arch = ANY (archs.aliases))
     LEFT JOIN countries ON countries.isocode = va_providers.country_code;

ALTER TABLE public.__va_providers
  OWNER TO appdb;
COMMENT ON VIEW public.__va_providers
  IS '
6ba7b812-9dad-11d1-80b4-00c04fd430c8 is the ISO OID namespace uuid seed for SHA1-based uuid generator (v5)
';

DROP MATERIALIZED VIEW public.va_providers CASCADE;
CREATE MATERIALIZED VIEW public.va_providers AS 
 SELECT __va_providers.id,
    __va_providers.sitename,
    __va_providers.url,
    __va_providers.gocdb_url,
    __va_providers.hostname,
    __va_providers.host_dn,
    __va_providers.host_ip,
    __va_providers.host_os_id,
    __va_providers.host_arch_id,
    __va_providers.beta,
    __va_providers.in_production,
    __va_providers.node_monitored,
    __va_providers.country_id,
    __va_providers.ngi,
    __va_providers.guid,
    __va_providers.serviceid,
    __va_providers.service_downtime,
    __va_providers.service_status,
    __va_providers.service_status_date
   FROM __va_providers
WITH DATA;

ALTER TABLE public.va_providers
  OWNER TO appdb;

-- Index: public.idx_va_providers_guid

-- DROP INDEX public.idx_va_providers_guid;

CREATE INDEX idx_va_providers_guid
  ON public.va_providers
  USING btree
  (guid);

-- Index: public.idx_va_providers_id

-- DROP INDEX public.idx_va_providers_id;

CREATE UNIQUE INDEX idx_va_providers_id
  ON public.va_providers
  USING btree
  (id COLLATE pg_catalog."default");

-- Index: public.idx_va_providers_sitename

-- DROP INDEX public.idx_va_providers_sitename;

CREATE INDEX idx_va_providers_sitename
  ON public.va_providers
  USING btree
  (sitename COLLATE pg_catalog."default");

-- Index: public.idx_va_providers_sitename_in_production

-- DROP INDEX public.idx_va_providers_sitename_in_production;

CREATE INDEX idx_va_providers_sitename_in_production
  ON public.va_providers
  USING btree
  (sitename COLLATE pg_catalog."default", in_production);

-- Index: public.idx_va_providers_sitename_isprod

-- DROP INDEX public.idx_va_providers_sitename_isprod;

CREATE INDEX idx_va_providers_sitename_isprod
  ON public.va_providers
  USING btree
  (sitename COLLATE pg_catalog."default")
  WHERE in_production IS TRUE;

-- Index: public.idx_va_providers_sitename_textops

-- DROP INDEX public.idx_va_providers_sitename_textops;

CREATE INDEX idx_va_providers_sitename_textops
  ON public.va_providers
  USING btree
  (sitename COLLATE pg_catalog."default" text_pattern_ops);

-- Index: public.idx_va_providers_sitename_trgmops

-- DROP INDEX public.idx_va_providers_sitename_trgmops;

CREATE INDEX idx_va_providers_sitename_trgmops
  ON public.va_providers
  USING gin
  (sitename COLLATE pg_catalog."default" gin_trgm_ops);

  --------------------------------------------------------------

-- Materialized View: public._actor_group_members

-- DROP MATERIALIZED VIEW public._actor_group_members;

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
    va_providers.id AS payload
   FROM site_contacts
     JOIN sites ON sites.id = site_contacts.siteid
     JOIN va_providers ON va_providers.sitename = sites.name
     JOIN researchers ON researchers.id = site_contacts.researcherid
  WHERE site_contacts.role = 'Site Administrator'::text AND (EXISTS ( SELECT config.var,
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
    va_providers.id AS payload
   FROM site_contacts
     JOIN sites ON sites.id = site_contacts.siteid
     JOIN va_providers ON va_providers.sitename = sites.name
     JOIN researchers ON researchers.id = site_contacts.researcherid
  WHERE site_contacts.role = 'Site Operations Manager'::text AND (EXISTS ( SELECT config.var,
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

------------------------------------

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
                     CROSS JOIN unnest(ARRAY[21, 40, 41]) act(act)
                     JOIN actor_group_members agm1 ON agm1.actorid = r1.guid
                  WHERE agm1.groupid = '-3'::integer AND r2.countryid::text = agm1.payload AND NOT (r2.guid IN ( SELECT agm2.actorid
                           FROM actor_group_members agm2
                          WHERE agm2.groupid = ANY (ARRAY['-1'::integer, '-2'::integer])))
                UNION
                 SELECT '-7'::integer AS id,
                    researchers.guid AS actor,
                    act.act AS actionid,
                    researchers.guid AS object
                   FROM researchers
                     CROSS JOIN unnest(ARRAY[21, 40, 41]) act(act)
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

-------------------
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
--------------------
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
--------------------------------

-- View: public.vldap_group_members

-- DROP VIEW public.vldap_group_members;

CREATE OR REPLACE VIEW public.vldap_group_members AS 
 SELECT researchers.id AS user_id,
    editable_apps.appid AS group_id
   FROM editable_apps
     JOIN researchers ON researchers.guid = editable_apps.actor;

ALTER TABLE public.vldap_group_members
  OWNER TO appdb;

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
-------------------------------------

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

----------------------------------

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

------------------
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

REFRESH MATERIALIZED VIEW CONCURRENTLY _actor_group_members;
REFRESH MATERIALIZED VIEW CONCURRENTLY _actor_group_members2;
REFRESH MATERIALIZED VIEW CONCURRENTLY permissions;

-- Function: public.va_provider_to_xml(text)

-- DROP FUNCTION public.va_provider_to_xml(text);

CREATE OR REPLACE FUNCTION public.va_provider_to_xml(mid text)
  RETURNS SETOF xml AS
$BODY$
BEGIN
RETURN QUERY
SELECT 
	xmlelement(
		name "virtualization:provider", 
		xmlattributes(
			va_providers.id,
			beta,
			in_production,
			node_monitored,
			service_downtime::int AS service_downtime,
			service_status AS service_status,
			service_status_date AS service_status_date
		),
		xmlelement(name "provider:name", sitename)
	)
FROM
	va_providers
WHERE id = mid;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION public.va_provider_to_xml(text)
  OWNER TO appdb;

  -- Function: public.va_provider_to_xml_ext(text)

-- DROP FUNCTION public.va_provider_to_xml_ext(text);

CREATE OR REPLACE FUNCTION public.va_provider_to_xml_ext(mid text)
  RETURNS SETOF xml AS
$BODY$
BEGIN
RETURN QUERY
SELECT 
	xmlelement(
		name "virtualization:provider", 
		xmlattributes(
			va_providers.id,
			beta,
			in_production,
			node_monitored,
			service_downtime::int AS service_downtime,
			service_status AS service_status,
			service_status_date AS service_status_date
		),
		xmlelement(name "provider:name", sitename),
		CASE WHEN NOT sites.id IS NULL THEN
		XMLELEMENT(
			name "appdb:site", 
			XMLATTRIBUTES(
				sites.id as id,
				sites.name as name,
				sites.productioninfrastructure as infrastructure, 
				sites.certificationstatus as status,
				sites.deleted as deleted,
				sites.datasource as source
			),
			XMLELEMENT(
				name "site:officialname", 
				sites.officialname
			), 
			XMLELEMENT(
				name "site:url", 
				XMLATTRIBUTES('portal' as type),
				sites.portalurl
			), 
			XMLELEMENT(
				name "site:url", 
				XMLATTRIBUTES('home' as type), 
				sites.homeurl
			)
		)
		END,
		xmlelement(name "provider:url", url),
		CASE WHEN EXISTS (SELECT * FROM va_provider_endpoints WHERE va_provider_endpoints.va_provider_id = va_providers.id) THEN 
			array_to_string(array_agg(DISTINCT
				xmlelement(name "provider:endpoint_url", endpoint_url)::text ||
				xmlelement(name "provider:deployment_type", deployment_type)::text
			),'')::xml 
		END,
		xmlelement(name "provider:gocdb_url", gocdb_url),
		CASE WHEN COALESCE(host_dn, '') <> '' THEN xmlelement(name "provider:dn", host_dn) END,
		CASE WHEN COALESCE(host_ip, '') <> '' THEN xmlelement(name "provider:ip", host_ip) END,
		CASE WHEN COALESCE(host_os_id, 0) <> 0 THEN xmlelement(
			name "provider:os", 
			xmlattributes(host_os_id AS id),
			oses.name
		) END,
		CASE WHEN COALESCE(host_arch_id, 0) <> 0 THEN xmlelement(
			name "provider:arch", 
			xmlattributes(host_arch_id AS id),
			archs.name
		) END,
		country_to_xml(country_id),
		CASE WHEN EXISTS(SELECT * FROM va_provider_templates WHERE va_provider_templates.va_provider_id = va_providers.id) THEN
		array_to_string(array_agg(DISTINCT
			xmlelement(name "provider:template",
				 xmlattributes(
					va_provider_templates.group_hash AS group_hash
				 ),
				 xmlelement(name "provider_template:resource_name", resource_name),
				 xmlelement(name "provider_template:main_memory_size", memsize),
				 xmlelement(name "provider_template:logical_cpus", logical_cpus),
				 xmlelement(name "provider_template:physical_cpus", physical_cpus),
				 xmlelement(name "provider_template:cpu_multiplicity", cpu_multiplicity),
				 xmlelement(name "provider_template:resource_manager", resource_manager),
				 xmlelement(name "provider_template:computing_manager", computing_manager),
				 xmlelement(name "provider_template:os_family", os_family),
				 xmlelement(name "provider_template:connectivity_in", connectivity_in),
				 xmlelement(name "provider_template:connectivity_out", connectivity_out),
				 xmlelement(name "provider_template:cpu_model", cpu_model),
				 xmlelement(name "provider_template:resource_id", resource_id)
			)::text
		), '')::xml
		END,
		CASE WHEN EXISTS(SELECT * FROM va_provider_images WHERE va_provider_images.va_provider_id = va_providers.id) THEN
		(
			SELECT (array_to_string(array_agg(DISTINCT
				xmlelement(name "provider:image",
					xmlattributes(
						content_type,
						mp_uri,
						vmiinstances.version AS "vmiversion",
						va_provider_image_id,
						va_provider_images.vmiinstanceid,
						va_provider_images.vowide_vmiinstanceid,	
						va_provider_images.good_vmiinstanceid,
						applications.id as "appid", 
						applications.name as "appname", 
						applications.cname as "appcname", 
						vos.id as "void", 
						vos.name as "voname",
						vapp_versions.archived
					)
				)::text), '')::XML
			) FROM va_provider_images 
			INNER JOIN vmiinstances ON vmiinstances.id = va_provider_images.vmiinstanceid
			INNER JOIN vmiflavours ON vmiflavours.id = vmiinstances.vmiflavourid
			INNER JOIN vmis ON vmis.id = vmiflavours.vmiid
			INNER JOIN vapplications ON vapplications.id = vmis.vappid
			INNER JOIN vapplists ON vapplists.vmiinstanceid = va_provider_images.vmiinstanceid
			INNER JOIN vapp_versions ON vapp_versions.id = vapplists.vappversionid
			INNER JOIN applications ON applications.id = vapplications.appid
			LEFT OUTER JOIN vowide_image_list_images ON vowide_image_list_images.id = va_provider_images.vowide_vmiinstanceid
			LEFT OUTER JOIN vowide_image_lists ON vowide_image_lists.id = vowide_image_list_images.vowide_image_list_id
			LEFT OUTER JOIN vos ON vos.id = vowide_image_lists.void			
			WHERE va_provider_id = va_providers.id AND ((
				vowide_image_lists.state IN ('published'::e_vowide_image_state, 'obsolete'::e_vowide_image_state)
			) OR (
				vowide_image_lists.state IS NULL
			)) 
			
		)
		END
	)
FROM
	va_providers 
	LEFT JOIN oses ON oses.id = host_os_id
	LEFT JOIN archs ON archs.id = host_arch_id
	LEFT JOIN va_provider_endpoints ON va_provider_endpoints.va_provider_id = va_providers.id
	LEFT JOIN va_provider_templates ON va_provider_templates.va_provider_id = va_providers.id
	LEFT OUTER JOIN sites ON sites.name = va_providers.sitename
WHERE va_providers.id = mid
	GROUP BY 
		va_providers.id,
		va_providers.beta,
		va_providers.in_production,
		va_providers.node_monitored,
		va_providers.service_downtime,
		va_providers.service_status,
		va_providers.service_status_date,
		va_providers.sitename,
		va_providers.url,
		va_providers.gocdb_url,
		va_providers.host_dn,
		va_providers.host_ip,
		va_providers.host_os_id,
		va_providers.host_arch_id,
		oses.name,
		archs.name,
		country_id,
		sites.id,
		sites.name,
		sites.productioninfrastructure,
		sites.certificationstatus,
		sites.deleted,
		sites.datasource,
		sites.officialname,
		sites.portalurl,
		sites.homeurl
;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION public.va_provider_to_xml_ext(text)
  OWNER TO appdb;

-- Materialized View: public.site_services_xml

DROP MATERIALIZED VIEW public.site_services_xml;

CREATE MATERIALIZED VIEW public.site_services_xml AS 
 SELECT __va_providers.sitename,
    XMLELEMENT(NAME "site:service", XMLATTRIBUTES('occi' AS type, __va_providers.id AS id, __va_providers.hostname AS host, count(DISTINCT good_vmiinstanceid(va_provider_images.*)) AS instances, __va_providers.beta AS beta, __va_providers.in_production AS in_production, __va_providers.service_downtime::int AS service_downtime, __va_providers.service_status AS service_status, __va_providers.service_status_date AS service_status_date), xmlagg(XMLELEMENT(NAME "siteservice:image", XMLATTRIBUTES(va_provider_images.vmiinstanceid AS id, good_vmiinstanceid(va_provider_images.*) AS goodid)))) AS x
   FROM __va_providers
     LEFT JOIN va_provider_images ON va_provider_images.va_provider_id = __va_providers.id AND (va_provider_images.vmiinstanceid IN ( SELECT __vaviews.vmiinstanceid
           FROM __vaviews))
  GROUP BY __va_providers.id, __va_providers.hostname, __va_providers.beta, __va_providers.in_production, __va_providers.service_downtime, __va_providers.sitename, __va_providers.service_status, __va_providers.service_status_date
WITH DATA;

ALTER TABLE public.site_services_xml
  OWNER TO appdb;

-- Index: public.idx_site_services_xml_sitename

-- DROP INDEX public.idx_site_services_xml_sitename;

CREATE INDEX idx_site_services_xml_sitename
  ON public.site_services_xml
  USING btree
  (sitename COLLATE pg_catalog."default");

-- Index: public.idx_site_services_xml_sitename_textops

-- DROP INDEX public.idx_site_services_xml_sitename_textops;

CREATE INDEX idx_site_services_xml_sitename_textops
  ON public.site_services_xml
  USING btree
  (sitename COLLATE pg_catalog."default" text_pattern_ops);

-- Index: public.idx_site_services_xml_sitename_trgmops

-- DROP INDEX public.idx_site_services_xml_sitename_trgmops;

CREATE INDEX idx_site_services_xml_sitename_trgmops
  ON public.site_services_xml
  USING gin
  (sitename COLLATE pg_catalog."default" gin_trgm_ops);

REFRESH MATERIALIZED VIEW site_services_xml;

-- Function: public.site_service_to_xml_ext(text)

-- DROP FUNCTION public.site_service_to_xml_ext(text);

CREATE OR REPLACE FUNCTION public.site_service_to_xml_ext(sitename text)
  RETURNS xml AS
$BODY$
SELECT xmlagg(services.x) FROM (SELECT XMLELEMENT(NAME "site:service", 
    XMLATTRIBUTES( 'occi' as type, va_providers.id as id , hostname as host, va_providers.beta as beta, va_providers.in_production as in_production, va_providers.service_downtime::int as service_downtime, va_providers.service_status, va_providers.service_status_date, va_providers.node_monitored as monitored, va_providers.ngi as ngi),
    XMLELEMENT( NAME "siteservice:host", XMLATTRIBUTES( hostname as name , host_dn as dn, host_ip as ip)),
    XMLELEMENT( NAME "siteservice:url", XMLATTRIBUTES( 'default' as type ) , va_providers.url),
    XMLELEMENT( NAME "siteservice:url", XMLATTRIBUTES( 'gocdb' as type ) , va_providers.gocdb_url),
   CASE WHEN EXISTS (SELECT * FROM va_provider_endpoints WHERE va_provider_endpoints.va_provider_id = va_providers.id) THEN array_to_string(array_agg( 
	DISTINCT xmlelement(name "siteservice:occi_endpoint_url", endpoint_url)::text
    ),'')::xml END,
    array_to_string(array_agg(DISTINCT
	xmlelement(name "provider:template",
		xmlattributes(
			va_provider_templates.group_hash AS group_hash
		),
	xmlelement(name "provider_template:resource_name", resource_name),
	xmlelement(name "provider_template:main_memory_size", memsize),
	xmlelement(name "provider_template:logical_cpus", logical_cpus),
	xmlelement(name "provider_template:physical_cpus", physical_cpus),
	xmlelement(name "provider_template:cpu_multiplicity", cpu_multiplicity),
	xmlelement(name "provider_template:resource_manager", resource_manager),
	xmlelement(name "provider_template:computing_manager", computing_manager),
	xmlelement(name "provider_template:os_family", os_family),
	xmlelement(name "provider_template:connectivity_in", connectivity_in),
	xmlelement(name "provider_template:connectivity_out", connectivity_out),
	xmlelement(name "provider_template:cpu_model", cpu_model),
	xmlelement(name "provider_template:resource_id", resource_id)
	)::text
    ), '')::xml,
    site_service_images_to_xml(va_providers.id::TEXT)
    ) as x 
   FROM va_providers 
   LEFT JOIN va_provider_endpoints ON va_provider_endpoints.va_provider_id = va_providers.id
   LEFT JOIN va_provider_templates ON va_provider_templates.va_provider_id = va_providers.id
   WHERE  va_providers.sitename = $1::TEXT 
   GROUP BY va_providers.id, hostname,va_providers.id, va_providers.hostname, 
   va_providers.beta, va_providers.in_production, va_providers.service_downtime, va_providers.service_status, va_providers.service_status_date, va_providers.node_monitored, 
   va_providers.ngi, va_providers.host_dn, va_providers.host_ip,va_providers.url,va_providers.gocdb_url) as services
$BODY$
  LANGUAGE sql STABLE
  COST 100;
ALTER FUNCTION public.site_service_to_xml_ext(text)
  OWNER TO appdb;

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 13, 2, E'renamed downtime and status related columns of VA providers'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=13 AND revision=2);

COMMIT;
