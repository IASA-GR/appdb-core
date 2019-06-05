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
Previous version: 8.22.x
New version: 8.22.99
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

CREATE OR REPLACE FUNCTION cloud_service_types() RETURNS TEXT[] AS 
$$
	SELECT ARRAY['eu.egi.cloud.vm-management.occi', 'org.openstack.nova'];
$$
LANGUAGE SQL IMMUTABLE;
ALTER FUNCTION cloud_service_types() OWNER TO appdb;

CREATE OR REPLACE FUNCTION cloud_service_names() RETURNS TEXT[] AS 
$$
	SELECT ARRAY['occi', 'openstack'];
$$
LANGUAGE SQL IMMUTABLE;
ALTER FUNCTION cloud_service_names() OWNER TO appdb;

CREATE OR REPLACE FUNCTION cloud_service_name_from_type(servtype TEXT) RETURNS TEXT AS 
$$
SELECT CASE LOWER($1)
	WHEN 'eu.egi.cloud.vm-management.occi' THEN 'occi'
	WHEN 'org.openstack.nova' THEN 'openstack'
	ELSE 'unknown'
END;
$$
LANGUAGE SQL IMMUTABLE;
ALTER FUNCTION cloud_service_name_from_type(TEXT) OWNER TO appdb;

DROP VIEW vldap_group_members;
DROP VIEW editable_apps;
DROP VIEW editable_apps2;
DROP FUNCTION delete_agm(int);
DROP VIEW actor_group_members;
DROP MATERIALIZED VIEW _actor_group_members2;
DROP VIEW __permissions;
DROP MATERIALIZED VIEW permissions;
DROP MATERIALIZED VIEW _actor_group_members;

CREATE OR REPLACE VIEW public."__va_providers"
AS SELECT va_providers.pkey AS id,
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
    va_providers.service_status_date,
    va_providers.service_type
   FROM gocdb.va_providers
     LEFT JOIN oses ON lower(oses.name) = lower(va_providers.host_os)
     LEFT JOIN archs ON lower(archs.name) = lower(va_providers.host_arch) OR (lower(va_providers.host_arch) = ANY (lower(archs.aliases::text)::text[]))
     LEFT JOIN countries ON countries.isocode = va_providers.country_code
     JOIN gocdb.sites ON lower(sites.name) = lower(va_providers.sitename)
  WHERE sites.certstatus <> ALL (ARRAY['Closed'::text, 'Suspended'::text]);

DROP MATERIALIZED VIEW public.va_providers; -- CASCADE;
CREATE MATERIALIZED VIEW public.va_providers
TABLESPACE pg_default
AS SELECT __va_providers.id,
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
    __va_providers.service_type,
    __va_providers.serviceid,
    __va_providers.service_downtime,
    __va_providers.service_status,
    __va_providers.service_status_date
   FROM __va_providers
WITH DATA;

-- View indexes:
CREATE INDEX idx_va_providers_guid ON public.va_providers USING btree (guid);
CREATE UNIQUE INDEX idx_va_providers_id ON public.va_providers USING btree (id);
CREATE INDEX idx_va_providers_sitename ON public.va_providers USING btree (sitename);
CREATE INDEX idx_va_providers_sitename_in_production ON public.va_providers USING btree (sitename, in_production);
CREATE INDEX idx_va_providers_sitename_isprod ON public.va_providers USING btree (sitename) WHERE (in_production IS TRUE);
CREATE INDEX idx_va_providers_sitename_textops ON public.va_providers USING btree (sitename text_pattern_ops);
CREATE INDEX idx_va_providers_sitename_trgmops ON public.va_providers USING gin (sitename gin_trgm_ops);


-----------------------------

CREATE MATERIALIZED VIEW _actor_group_members AS
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
  WHERE ((site_contacts.role ~* 'Site.Administrator.*'::text) OR (site_contacts.role = 'administrator')) AND (
  	EXISTS (SELECT config.var,
    	config.data
		FROM config
		WHERE config.var = 'managed_site_admins'::text AND config.data = '1'::text)
    )/* AND NOT EXISTS (
    	SELECT 1 FROM __actor_group_members AS tmp10
    	WHERE tmp10.groupid = -10 AND tmp10.actorid = researchers.guid AND tmp10.payload = sites.id
    )*/
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
          WHERE config.var = 'managed_site_admins'::text AND config.data = '1'::text));
ALTER MATERIALIZED VIEW _actor_group_members OWNER TO appdb;

CREATE TABLE agm_tmp(groupid int, actorid uuid, payload text);
INSERT INTO agm_tmp
	SELECT groupid, actorid, payload FROM __actor_group_members;

DELETE FROM __actor_group_members AS agm WHERE groupid IN (-10, -14) AND EXISTS (
	SELECT * FROM _actor_group_members
	WHERE
		groupid = agm.groupid AND
		actorid = agm.actorid AND
		payload = agm.payload
);

REFRESH MATERIALIZED VIEW _actor_group_members;

CREATE UNIQUE INDEX idx__actor_group_members_unique ON public._actor_group_members USING btree (groupid, actorid, payload);
CREATE INDEX idx__actor_group_members_actorid ON public._actor_group_members USING btree (actorid);
CREATE INDEX idx__actor_group_members_payload ON public._actor_group_members USING btree (payload);

CREATE MATERIALIZED VIEW public.permissions
TABLESPACE pg_default
AS WITH actor_group_members AS (
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
                 SELECT '-23'::integer AS id,
                    sites.guid AS actor,
                    36 AS actionid,
                    NULL::uuid AS object
                   FROM sites
                  WHERE
	                  (EXISTS (SELECT 1 FROM va_providers WHERE va_providers.sitename = sites.name)) OR
	                  (EXISTS (SELECT 1 FROM nova_providers WHERE nova_providers.sitename = sites.name))
                UNION
                 SELECT '-24'::integer AS id,
                    agm.actorid AS actor,
                    36 AS actionid,
                    NULL::uuid AS object
                   FROM sites
                     JOIN actor_group_members agm ON agm.payload = sites.id
                     WHERE (agm.groupid = ANY(ARRAY[-10, -14, -22, -23])) AND (
                     	(EXISTS (SELECT 1 FROM va_providers WHERE va_providers.sitename = sites.name)) OR
	                  	(EXISTS (SELECT 1 FROM nova_providers WHERE nova_providers.sitename = sites.name))
                     )
                UNION
                 SELECT '-20'::integer AS id,
                    researchers.guid AS actor,
                    privileges.actionid,
                    privileges.object
                   FROM privileges
                     JOIN actor_group_members agm ON agm.payload = (( SELECT __sites.id
                           FROM __sites
                          WHERE __sites.guid = privileges.actor))
                     JOIN researchers ON agm.actorid = researchers.guid
                  WHERE (agm.groupid = ANY (ARRAY[-10, -14, -22, -23])) AND privileges.actionid = 36 AND NOT privileges.revoked
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
ALTER TABLE permissions OWNER TO appdb;

CREATE INDEX idx_permissions_actionid ON public.permissions USING btree (actionid);
CREATE INDEX idx_permissions_actionid_object_actor ON public.permissions USING btree (actionid, object, actor);
CREATE INDEX idx_permissions_actor ON public.permissions USING btree (actor);
CREATE INDEX idx_permissions_actor_actionid_objnotnull ON public.permissions USING btree (actor, actionid, object) WHERE (NOT (object IS NULL));
CREATE INDEX idx_permissions_actor_actionid_objnull ON public.permissions USING btree (actor, actionid, object) WHERE (object IS NULL);
CREATE INDEX idx_permissions_object ON public.permissions USING btree (object);
CREATE UNIQUE INDEX idx_permissions_unique ON public.permissions USING btree (actionid, actor, object);

CREATE OR REPLACE VIEW public.__permissions AS
 SELECT permissions.ids,
    permissions.system,
    permissions.actor,
    permissions.actionid,
    permissions.object
   FROM permissions;
ALTER TABLE __permissions OWNER TO appdb;

CREATE MATERIALIZED VIEW _actor_group_members2
TABLESPACE pg_default
AS SELECT _actor_group_members.id,
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
ALTER MATERIALIZED VIEW _actor_group_members2 OWNER TO appdb;
REFRESH MATERIALIZED VIEW _actor_group_members2;
CREATE UNIQUE INDEX idx_actor_group_members_unique ON public._actor_group_members2 USING btree (groupid, actorid, payload);

CREATE VIEW actor_group_members AS
 SELECT _actor_group_members2.id,
    _actor_group_members2.groupid,
    _actor_group_members2.actorid,
    _actor_group_members2.payload
   FROM _actor_group_members2;
ALTER VIEW actor_group_members OWNER TO appdb;

CREATE RULE r_delete_actor_group_members AS
    ON DELETE TO public.actor_group_members DO INSTEAD  DELETE FROM __actor_group_members
  WHERE ((__actor_group_members.id = old.id) AND (NOT (old.id IS NULL)))
  RETURNING __actor_group_members.id,
    __actor_group_members.groupid,
    __actor_group_members.actorid,
    __actor_group_members.payload;

CREATE RULE r_insert_actor_group_members AS
    ON INSERT TO public.actor_group_members DO INSTEAD  INSERT INTO __actor_group_members (groupid, actorid, payload)
  VALUES (new.groupid, new.actorid, new.payload)
  RETURNING __actor_group_members.id,
    __actor_group_members.groupid,
    __actor_group_members.actorid,
    __actor_group_members.payload;

CREATE RULE r_update_actor_group_members AS
    ON UPDATE TO public.actor_group_members DO INSTEAD  UPDATE __actor_group_members SET groupid = new.groupid, actorid = new.actorid, payload = new.payload
  WHERE ((__actor_group_members.id = old.id) AND (NOT (old.id IS NULL)))
  RETURNING __actor_group_members.id,
    __actor_group_members.groupid,
    __actor_group_members.actorid,
    __actor_group_members.payload;

CREATE VIEW editable_apps AS
 SELECT DISTINCT applications.id AS appid,
    permissions.actor
   FROM applications
     LEFT JOIN permissions ON permissions.object = applications.guid OR permissions.object IS NULL
  WHERE permissions.actionid = ANY (app_metadata_actions());
ALTER VIEW editable_apps OWNER TO appdb;

CREATE VIEW editable_apps2 AS
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
ALTER VIEW editable_apps2 OWNER TO appdb;

CREATE VIEW vldap_group_members AS
 SELECT researchers.id AS user_id,
    editable_apps.appid AS group_id
   FROM editable_apps
     JOIN researchers ON researchers.guid = editable_apps.actor;
ALTER VIEW vldap_group_members OWNER TO appdb;

CREATE OR REPLACE FUNCTION public.delete_agm(_id integer)
 RETURNS SETOF actor_group_members
 LANGUAGE plpgsql
 STRICT
AS $function$
BEGIN
        DELETE FROM __actor_group_members
        WHERE __actor_group_members.id = _id;
        RETURN QUERY SELECT * FROM __actor_group_members WHERE FALSE;
END;
$function$;
ALTER FUNCTION delete_agm(int) OWNER TO appdb;

REFRESH MATERIALIZED VIEW CONCURRENTLY _actor_group_members;
REFRESH MATERIALIZED VIEW CONCURRENTLY _actor_group_members2;
REFRESH MATERIALIZED VIEW CONCURRENTLY permissions;

INSERT INTO __actor_group_members (groupid, actorid, payload)
SELECT groupid, actorid, payload FROM (
	SELECT groupid, actorid, payload
	FROM agm_tmp
	EXCEPT
	SELECT groupid, actorid, payload
	FROM actor_group_members
) t
WHERE t.payload IN (
	SELECT id FROM sites
);

DROP TABLE IF EXISTS agm_tmp;

REFRESH MATERIALIZED VIEW CONCURRENTLY _actor_group_members;
REFRESH MATERIALIZED VIEW CONCURRENTLY _actor_group_members2;
REFRESH MATERIALIZED VIEW CONCURRENTLY permissions;

CREATE OR REPLACE FUNCTION public.process_site_argo_status(dat jsonb[])
 RETURNS void
 LANGUAGE plpgsql
 STRICT
AS $function$
DECLARE j jsonb;
DECLARE statust TIMESTAMP WITHOUT TIME ZONE;
DECLARE statusv TEXT;
DECLARE epkey TEXT;
DECLARE srvgrp TEXT;
BEGIN
	FOREACH j IN ARRAY dat LOOP
    	statust := ((j->>'info')::jsonb->>'StatusTimestamp')::TIMESTAMP;
        statusv := (j->>'info')::jsonb->>'StatusValue';
        epkey := (j->>'info')::jsonb->>'SiteEndpointPKey';
        srvgrp := (j->>'info')::jsonb->>'StatusEndpointGroup';
        IF LOWER(srvgrp) = ANY(cloud_service_types()) THEN
        -- IF (srvgrp = 'eu.egi.cloud.vm-management.occi') OR (srvgrp = 'org.openstack.nova') THEN
        	-- RAISE NOTICE 'processing status: %, ts: %, pkey: % for srvgrp %', statusv, statust, epkey, srvgrp;
            UPDATE gocdb.va_providers
                SET service_status = statusv, service_status_date = statust
                WHERE
                    (pkey = epkey)
                AND
                    ((service_status_date <= statust) OR (service_status_date IS NULL))
                AND
                    (LOWER(TRIM(COALESCE(statusv,''))) NOT IN ('', 'missing'));
         -- ELSE
         	-- RAISE NOTICE 'ignoring status: %, ts: %, pkey: % for srvgrp %', statusv, statust, epkey, srvgrp;
         END IF;
    END LOOP;
END;
$function$
;

CREATE OR REPLACE FUNCTION public.site_service_to_xml(sitename text)
 RETURNS xml
 LANGUAGE sql
 STABLE
AS $function$
SELECT
	xmlagg(services.x)
FROM (
	SELECT
		XMLELEMENT(
			name "site:service",
			XMLATTRIBUTES(
				cloud_service_name_from_type(va_providers.service_type) AS type,
				/*CASE va_providers.service_type
					WHEN 'eu.egi.cloud.vm-management.occi' THEN 'occi'
					WHEN 'org.openstack.nova' THEN 'openstack'
					ELSE 'UNKNOWN'
				END AS type,*/
				va_providers.id AS id ,
				hostname AS host,
				COUNT(DISTINCT va_provider_images.good_vmiinstanceid) AS instances,
				va_providers.beta AS beta,
				va_providers.in_production AS in_production
			),
			xmlagg(
				XMLELEMENT(NAME "siteservice:image", XMLATTRIBUTES(
					va_provider_images.vmiinstanceid AS id,
					va_provider_images.good_vmiinstanceid AS goodid
				))
			)
		)AS x
	FROM va_providers
	LEFT JOIN va_provider_images ON va_provider_images.va_provider_id = va_providers.id
	LEFT JOIN vaviews ON vaviews.vmiinstanceid = va_provider_images.vmiinstanceid
	WHERE va_providers.sitename = $1::TEXT
	AND	vaviews.appid NOT IN (SELECT appid FROM app_order_hack)
	GROUP BY
		va_providers.id,
		va_providers.service_type,
		hostname,
		beta,
		in_production
) AS services
$function$
;

CREATE OR REPLACE FUNCTION public.site_service_to_xml_ext(sitename text)
 RETURNS xml
 LANGUAGE sql
 STABLE
AS $function$
SELECT xmlagg(services.x) FROM (SELECT XMLELEMENT(NAME "site:service",
    XMLATTRIBUTES(
    		cloud_service_name_from_type(va_providers.service_type) AS type,
    		/*CASE va_providers.service_type
			WHEN 'eu.egi.cloud.vm-management.occi' THEN 'occi'
			WHEN 'org.openstack.nova' THEN 'openstack'
			ELSE 'UNKNOWN'
		END AS type,*/
    	va_providers.id as id,
    	hostname as host,
    	va_providers.beta as beta,
    	va_providers.in_production as in_production,
    	va_providers.service_downtime::int as service_downtime,
    	va_providers.service_status,
    	va_providers.service_status_date,
    	va_providers.node_monitored as monitored,
    	va_providers.ngi as ngi
    ),
    XMLELEMENT( NAME "siteservice:host", XMLATTRIBUTES( hostname as name , host_dn as dn, host_ip as ip)),
    XMLELEMENT( NAME "siteservice:url", XMLATTRIBUTES( 'default' as type ) , va_providers.url),
    XMLELEMENT( NAME "siteservice:url", XMLATTRIBUTES( 'gocdb' as type ) , va_providers.gocdb_url),
    CASE
    WHEN EXISTS (
    	SELECT * FROM va_provider_endpoints
    	WHERE va_provider_endpoints.va_provider_id = va_providers.id
    ) THEN
    	array_to_string(array_agg(
			DISTINCT xmlelement(name "siteservice:occi_endpoint_url",
				XMLATTRIBUTES(
					cloud_service_name_from_type(va_providers.service_type) AS type
				/*	CASE va_providers.service_type
						WHEN 'eu.egi.cloud.vm-management.occi' THEN 'occi'
						WHEN 'org.openstack.nova' THEN 'openstack'
						ELSE 'UNKNOWN'
					END AS type*/
				),
				endpoint_url
			)::text
    	),'')::xml
    END,
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
	xmlelement(name "provider_template:disc_size", disc_size),
	xmlelement(name "provider_template:resource_id", resource_id)
	)::text
    ), '')::xml,
    site_service_images_to_xml(va_providers.id::TEXT)
    ) as x
   FROM va_providers
   LEFT JOIN va_provider_endpoints ON va_provider_endpoints.va_provider_id = va_providers.id
   LEFT JOIN va_provider_templates ON va_provider_templates.va_provider_id = va_providers.id
   WHERE  va_providers.sitename = $1::TEXT
   GROUP BY
   		va_providers.id,
   		va_providers.hostname,
   		va_providers.beta,
   		va_providers.in_production,
   		va_providers.service_downtime,
   		va_providers.service_type,
   		va_providers.service_status,
   		va_providers.service_status_date,
   		va_providers.node_monitored,
   		va_providers.ngi,
   		va_providers.host_dn,
   		va_providers.host_ip,
   		va_providers.url,
   		va_providers.gocdb_url
   ) as services
$function$
;

CREATE OR REPLACE FUNCTION public.site_supports(servname text)
 RETURNS SETOF text
 LANGUAGE plpgsql
AS $function$
BEGIN
	IF LOWER($1) = ANY(cloud_service_names()) THEN
	-- IF ($1 = 'occi') OR ($1 = 'openstack') THEN
		RETURN QUERY SELECT DISTINCT(sites.id) FROM sites INNER JOIN va_providers ON va_providers.sitename = sites.name AND va_providers.in_production = true;
	ELSE
		RETURN QUERY SELECT DISTINCT(sites.id) FROM sites WHERE sites.name NOT IN (SELECT va_providers.sitename FROM va_providers WHERE va_providers.in_production = true);
	END IF;
END;
$function$
;


CREATE OR REPLACE FUNCTION public.site_instances(servname text)
 RETURNS SETOF text
 LANGUAGE plpgsql
AS $function$
BEGIN
	IF LOWER($1) = ANY(cloud_service_names()) THEN
	-- IF ($1 = 'occi') OR ($1 = 'openstack') THEN
		RETURN QUERY
			SELECT DISTINCT(sites.id)
			FROM sites
			INNER JOIN va_providers ON va_providers.sitename = sites.name AND va_providers.in_production = true
			INNER JOIN va_provider_images ON va_provider_images.va_provider_id = va_providers.id
			INNER JOIN vaviews ON vaviews.vmiinstanceid = va_provider_images.vmiinstanceid;
	ELSE
		RETURN QUERY
			SELECT DISTINCT(sites.id)
			FROM sites WHERE sites.name NOT IN (
				SELECT va_providers.sitename FROM va_providers
				INNER JOIN va_provider_images ON va_provider_images.va_provider_id = va_providers.id
				INNER JOIN vaviews ON vaviews.vmiinstanceid = va_provider_images.vmiinstanceid
				WHERE va_providers.in_production = true
			);
	END IF;
END;
$function$
;

DROP MATERIALIZED VIEW public.site_services_xml;
DROP MATERIALIZED VIEW site_service_images_xml;
DROP FUNCTION good_vmiinstanceid(va_provider_images);

DROP FUNCTION group_hash(va_provider_templates);
DROP MATERIALIZED VIEW public.va_provider_templates;
CREATE MATERIALIZED VIEW public.va_provider_templates
TABLESPACE pg_default
AS SELECT nextval('va_provider_templates_id_seq'::regclass) AS id,
    g.pkey AS va_provider_id,
    jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'templates'::text)::jsonb) ->> 'GLUE2EntityName'::text AS resource_name,
    jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'templates'::text)::jsonb) ->> 'GLUE2ExecutionEnvironmentMainMemorySize'::text AS memsize,
    jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'templates'::text)::jsonb) ->> 'GLUE2ExecutionEnvironmentLogicalCPUs'::text AS logical_cpus,
    jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'templates'::text)::jsonb) ->> 'GLUE2ExecutionEnvironmentPhysicalCPUs'::text AS physical_cpus,
    jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'templates'::text)::jsonb) ->> 'GLUE2ExecutionEnvironmentCPUMultiplicity'::text AS cpu_multiplicity,
    jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'templates'::text)::jsonb) ->> 'GLUE2ResourceManagerForeignKey'::text AS resource_manager,
    jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'templates'::text)::jsonb) ->> 'GLUE2ExecutionEnvironmentComputingManagerForeignKey'::text AS computing_manager,
    jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'templates'::text)::jsonb) ->> 'GLUE2ExecutionEnvironmentOSFamily'::text AS os_family,
    jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'templates'::text)::jsonb) ->> 'GLUE2ExecutionEnvironmentConnectivityIn'::text AS connectivity_in,
    jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'templates'::text)::jsonb) ->> 'GLUE2ExecutionEnvironmentConnectivityOut'::text AS connectivity_out,
    jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'templates'::text)::jsonb) ->> 'GLUE2ExecutionEnvironmentCPUModel'::text AS cpu_model,
    jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'templates'::text)::jsonb) ->> 'GLUE2ResourceID'::text AS resource_id,
    jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'templates'::text)::jsonb) ->> 'GLUE2ExecutionEnvironmentDiskSize'::text AS disc_size
   FROM egiis.vapj g
     LEFT JOIN egiis.tvapj t ON t.pkey = g.pkey AND (g.lastseen - t.lastseen) < '00:10:00'::interval
  WHERE LOWER(((t.j ->> 'info'::text)::jsonb) ->> 'GLUE2EndpointInterfaceName'::text) = ANY(cloud_service_names()) AND COALESCE(btrim(((t.j ->> 'info'::text)::jsonb) ->> 'GLUE2ComputingEndpointComputingServiceForeignKey'::text), ''::text) <> ''::text AND COALESCE(((g.j ->> 'info'::text)::jsonb) ->> 'SiteEndpointInProduction'::text, 'FALSE'::text)::boolean IS DISTINCT FROM false
WITH DATA;

-- View indexes:
CREATE UNIQUE INDEX idx_va_provider_templates_id ON public.va_provider_templates USING btree (id);
CREATE INDEX idx_va_provider_templates_va_provider_id_textops ON public.va_provider_templates USING btree (va_provider_id text_pattern_ops);
CREATE INDEX idx_va_provider_templates_va_provider_id_trgmops ON public.va_provider_templates USING gin (va_provider_id gin_trgm_ops);

CREATE OR REPLACE FUNCTION public.group_hash(v va_provider_templates)
 RETURNS text
 LANGUAGE sql
 STABLE
AS $function$
SELECT md5(
COALESCE(v.memsize, '') || '_' ||
COALESCE(v.logical_cpus, '') || '_' ||
COALESCE(v.physical_cpus,'') || '_' ||
COALESCE(v.cpu_multiplicity, '') || '_' ||
COALESCE(v.os_family, '') || '_' ||
COALESCE(v.connectivity_in, '') || '_' ||
COALESCE(v.connectivity_out, '') || '_' ||
COALESCE(v.cpu_model, '') || '_' ||
COALESCE(v.disc_size, '')
);
$function$
;

DROP MATERIALIZED VIEW public.va_provider_images;
CREATE MATERIALIZED VIEW public.va_provider_images
TABLESPACE pg_default
AS SELECT xx.id,
    xx.va_provider_id,
        CASE
            WHEN btrim(COALESCE(xx.vmiinstanceid, ''::text)) = ''::text THEN NULL::integer
            ELSE xx.vmiinstanceid::integer
        END AS vmiinstanceid,
    xx.content_type,
    xx.va_provider_image_id,
    xx.mp_uri,
        CASE
            WHEN lower(xx.vowide_vmiinstanceid::text) = 'null'::text THEN NULL::integer
            ELSE xx.vowide_vmiinstanceid
        END AS vowide_vmiinstanceid
   FROM ( SELECT x.id,
            x.va_provider_id,
                CASE lower(x.content_type)
                    WHEN 'vo'::text THEN (( SELECT vapplists.vmiinstanceid
                       FROM vowide_image_list_images
                         JOIN vapplists ON vapplists.id = vowide_image_list_images.vapplistid
                      WHERE vowide_image_list_images.id::text = (( SELECT regexp_split_to_array(replace((regexp_matches(x.mp_uri, ':[0-9]+[:/]*[0-9]*'::text, ''::text))[1], '/'::text, ''::text), ':'::text, ''::text) AS regexp_split_to_array))[2]))::text
                    ELSE (( SELECT regexp_split_to_array(replace((regexp_matches(x.mp_uri, ':[0-9]+[:/]*[0-9]*'::text, ''::text))[1], '/'::text, ''::text), ':'::text, ''::text) AS regexp_split_to_array))[2]
                END AS vmiinstanceid,
                CASE lower(x.content_type)
                    WHEN 'va'::text THEN 'vm'::text
                    ELSE lower(x.content_type)
                END AS content_type,
            x.va_provider_image_id,
            x.mp_uri,
                CASE
                    WHEN lower(x.vowide_vmiinstanceid) = 'null'::text THEN NULL::integer
                    ELSE x.vowide_vmiinstanceid::integer
                END AS vowide_vmiinstanceid
           FROM ( SELECT nextval('va_provider_images_id_seq'::regclass) AS id,
                    g.pkey AS va_provider_id,
                    (jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'images'::text)::jsonb) -> 'ImageVmiInstanceId'::text)::text AS vmiinstanceid,
                    jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'images'::text)::jsonb) ->> 'ImageContentType'::text AS content_type,
                    jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'images'::text)::jsonb) ->> 'GLUE2EntityName'::text AS va_provider_image_id,
                    jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'images'::text)::jsonb) ->> 'GLUE2ApplicationEnvironmentRepository'::text AS mp_uri,
                    (jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'images'::text)::jsonb) -> 'ImageVoVmiInstanceId'::text)::text AS vowide_vmiinstanceid
                   FROM egiis.vapj g
                     LEFT JOIN egiis.tvapj t ON g.pkey = t.pkey AND (g.lastseen - t.lastseen) < '00:10:00'::interval
                  WHERE LOWER(((t.j ->> 'info'::text)::jsonb) ->> 'GLUE2EndpointInterfaceName'::text) = ANY(cloud_service_names()) AND COALESCE(((g.j ->> 'info'::text)::jsonb) ->> 'SiteEndpointInProduction'::text, 'FALSE'::text)::boolean IS DISTINCT FROM false) x) xx
WITH DATA;

-- View indexes:
CREATE UNIQUE INDEX idx_va_provider_images_id ON public.va_provider_images USING btree (id);
CREATE INDEX idx_va_provider_images_va_provider_id ON public.va_provider_images USING btree (va_provider_id);
CREATE INDEX idx_va_provider_images_va_provider_id_textops ON public.va_provider_images USING btree (va_provider_id text_pattern_ops);
CREATE INDEX idx_va_provider_images_va_provider_id_trgmops ON public.va_provider_images USING gin (va_provider_id gin_trgm_ops);
CREATE INDEX idx_va_provider_images_vmiinstanceid ON public.va_provider_images USING btree (vmiinstanceid);
CREATE INDEX idx_va_provider_images_vowide_vmiinstanceid ON public.va_provider_images USING btree (vowide_vmiinstanceid);

CREATE OR REPLACE FUNCTION public.good_vmiinstanceid(va_provider_images)
 RETURNS integer
 LANGUAGE sql
 STABLE
AS $function$
--      SELECT public.get_good_vmiinstanceid($1.vmiinstanceid)
        SELECT CASE WHEN goodid IS NULL THEN $1.vmiinstanceid ELSE goodid END FROM (
                        SELECT max(t1.id) as goodid FROM public.vmiinstances AS t1
                        INNER JOIN public.vmiinstances AS t2 ON t1.checksum = t2.checksum AND t1.guid = t2.guid AND t2.id = $1.vmiinstanceid
                        INNER JOIN public.vapplists ON t1.id = vapplists.vmiinstanceid
                        INNER JOIN public.vapp_versions ON vapplists.vappversionid = vapp_versions.id
                        WHERE vapp_versions.published
        ) AS t
$function$
;

CREATE MATERIALIZED VIEW public.site_services_xml
TABLESPACE pg_default
AS SELECT __va_providers.id,
    __va_providers.sitename,
    XMLELEMENT(
    	NAME "site:service",
    	XMLATTRIBUTES(
    			cloud_service_name_from_type(__va_providers.service_type) AS type,
			/*CASE __va_providers.service_type
				WHEN 'eu.egi.cloud.vm-management.occi' THEN 'occi'
				WHEN 'org.openstack.nova' THEN 'openstack'
				ELSE 'UNKNOWN'
			END AS type,*/
    		__va_providers.id AS id,
    		__va_providers.hostname AS host,
    		COUNT(DISTINCT good_vmiinstanceid(va_provider_images.*)) AS instances,
    		__va_providers.beta AS beta,
    		__va_providers.in_production AS in_production,
    		__va_providers.service_downtime::integer AS service_downtime,
    		__va_providers.service_status AS service_status,
    		__va_providers.service_status_date AS service_status_date
    	),
    	XMLAGG(XMLELEMENT(NAME "siteservice:image", XMLATTRIBUTES(va_provider_images.vmiinstanceid AS id, good_vmiinstanceid(va_provider_images.*) AS goodid)))) AS x
   FROM __va_providers
     LEFT JOIN va_provider_images ON va_provider_images.va_provider_id = __va_providers.id AND (va_provider_images.vmiinstanceid IN ( SELECT __vaviews.vmiinstanceid
           FROM __vaviews))
  GROUP BY
  	__va_providers.id, __va_providers.hostname, __va_providers.beta, __va_providers.in_production, __va_providers.service_downtime, __va_providers.sitename, __va_providers.service_status, __va_providers.service_status_date, __va_providers.service_type
WITH DATA;

CREATE MATERIALIZED VIEW public.site_service_images_xml
TABLESPACE pg_default
AS SELECT siteimages.va_provider_id,
    xmlagg(siteimages.x) AS xmlagg
   FROM ( SELECT __va_providers.id AS va_provider_id,
            XMLELEMENT(NAME "siteservice:image", XMLATTRIBUTES(__vaviews.vappversionid AS versionid, __vaviews.va_version_archived AS archived, __vaviews.va_version_enabled AS enabled, __vaviews.va_version_expireson AS expireson,
                CASE
                    WHEN __vaviews.va_version_expireson <= now() THEN true
                    ELSE false
                END AS isexpired, __vaviews.imglst_private AS private, __vaviews.vmiinstanceid AS id, __vaviews.vmiinstance_guid AS identifier, __vaviews.vmiinstance_version AS version, good_vmiinstanceid(va_provider_images.*) AS goodid), vmiflavor_hypervisor_xml.hypervisor::text::xml, XMLELEMENT(NAME "virtualization:os", XMLATTRIBUTES(oses.id AS id, __vaviews.osversion AS version, oses.os_family_id AS family_id), oses.name), XMLELEMENT(NAME "virtualization:arch", XMLATTRIBUTES(archs.id AS id), archs.name), XMLELEMENT(NAME "virtualization:format", __vaviews.format), XMLELEMENT(NAME "virtualization:url", XMLATTRIBUTES(
                CASE
                    WHEN __vaviews.imglst_private = true THEN 'true'::text
                    ELSE NULL::text
                END AS protected),
                CASE
                    WHEN __vaviews.imglst_private = false THEN __vaviews.uri
                    ELSE NULL::text
                END), XMLELEMENT(NAME "virtualization:size", XMLATTRIBUTES(
                CASE
                    WHEN __vaviews.imglst_private = true THEN 'true'::text
                    ELSE NULL::text
                END AS protected),
                CASE
                    WHEN __vaviews.imglst_private = false THEN __vaviews.size
                    ELSE NULL::bigint
                END), XMLELEMENT(NAME "siteservice:mpuri", ((((('//'::text || (( SELECT config.data
                   FROM config
                  WHERE config.var = 'ui-host'::text))) || '/store/vm/image/'::text) || __vaviews.vmiinstance_guid::text) || ':'::text) || good_vmiinstanceid(va_provider_images.*)::text) || '/'::text), array_to_string(array_agg(DISTINCT site_service_imageocciids_to_xml(va_provider_images.va_provider_id, va_provider_images.vmiinstanceid, va_provider_images.vowide_vmiinstanceid)::text), ''::text)::xml, XMLELEMENT(NAME "application:application", XMLATTRIBUTES(__vaviews.appid AS id, __vaviews.appcname AS cname, __vaviews.imglst_private AS imagelistsprivate, applications.deleted AS deleted, applications.moderated AS moderated), XMLELEMENT(NAME "application:name", __vaviews.appname)), vmiinst_cntxscripts_to_xml(__vaviews.vmiinstanceid)) AS x
           FROM __va_providers
             JOIN va_provider_images va_provider_images ON va_provider_images.va_provider_id = __va_providers.id
             JOIN __vaviews __vaviews ON __vaviews.vmiinstanceid = va_provider_images.vmiinstanceid
             JOIN applications ON applications.id = __vaviews.appid
             LEFT JOIN vmiflavor_hypervisor_xml ON vmiflavor_hypervisor_xml.vmiflavourid = __vaviews.vmiflavourid
             LEFT JOIN archs ON archs.id = __vaviews.archid
             LEFT JOIN oses ON oses.id = __vaviews.osid
             LEFT JOIN vmiformats ON vmiformats.name::text = __vaviews.format
          WHERE __vaviews.va_version_published
          GROUP BY __va_providers.id, __vaviews.vappversionid, __vaviews.va_version_archived, __vaviews.va_version_enabled, __vaviews.va_version_expireson, __vaviews.imglst_private, __vaviews.vmiinstanceid, __vaviews.vmiinstance_guid, __vaviews.vmiinstance_version, (good_vmiinstanceid(va_provider_images.*)), (vmiflavor_hypervisor_xml.hypervisor::text), oses.id, archs.id, __vaviews.osversion, __vaviews.format, __vaviews.uri, __vaviews.size, __vaviews.appid, __vaviews.appcname, __vaviews.appname, applications.deleted, applications.moderated) siteimages
  GROUP BY siteimages.va_provider_id
WITH DATA;

-- View indexes:
CREATE UNIQUE INDEX idx_site_service_images_xml_id ON public.site_service_images_xml USING btree (va_provider_id);


-- View indexes:
CREATE UNIQUE INDEX idx_site_services_xml_id ON public.site_services_xml USING btree (id);
CREATE INDEX idx_site_services_xml_sitename ON public.site_services_xml USING btree (sitename);
CREATE INDEX idx_site_services_xml_sitename_textops ON public.site_services_xml USING btree (sitename text_pattern_ops);
CREATE INDEX idx_site_services_xml_sitename_trgmops ON public.site_services_xml USING gin (sitename gin_trgm_ops);

DROP MATERIALIZED VIEW public.va_provider_endpoints;
CREATE MATERIALIZED VIEW public.va_provider_endpoints
TABLESPACE pg_default
AS SELECT nextval('va_provider_endpoints_id_seq'::regclass) AS id,
    t.pkey AS va_provider_id,
    ((t.j ->> 'info'::text)::jsonb) ->> 'GLUE2EndpointURL'::text AS endpoint_url,
    ((t.j ->> 'info'::text)::jsonb) ->> 'GLUE2EndpointImplementor'::text AS deployment_type
   FROM egiis.vapj g
     LEFT JOIN egiis.tvapj t ON g.pkey = t.pkey AND (g.lastseen - t.lastseen) < '00:10:00'::interval
  WHERE LOWER(((t.j ->> 'info'::text)::jsonb) ->> 'GLUE2EndpointInterfaceName'::text) = ANY(cloud_service_names())
WITH DATA;

-- View indexes:
CREATE UNIQUE INDEX idx_va_provider_endpoints_id ON public.va_provider_endpoints USING btree (id);
CREATE INDEX idx_va_provider_endpoints_va_provider_id ON public.va_provider_endpoints USING btree (va_provider_id);
CREATE INDEX idx_va_provider_endpoints_va_provider_id_textops ON public.va_provider_endpoints USING btree (va_provider_id text_pattern_ops);
CREATE INDEX idx_va_provider_endpoints_va_provider_id_trgmops ON public.va_provider_endpoints USING gin (va_provider_id gin_trgm_ops);


--------------- VA PROVIDER FILTERING SUPPORT ---------------

CREATE SCHEMA va_providers;
ALTER SCHEMA va_providers OWNER TO appdb;

CREATE OR REPLACE FUNCTION va_providers."any"(mid text)
 RETURNS text
 LANGUAGE sql
 STABLE
AS $function$
	SELECT public.any('va_providers', $1);
$function$;
ALTER FUNCTION va_providers."any"(text) OWNER TO appdb;

CREATE TABLE va_providers."any"(
	id TEXT NOT NULL PRIMARY KEY,
	"any" TEXT
);
ALTER TABLE va_providers."any" OWNER TO appdb;

CREATE TABLE IF NOT EXISTS rankedvaproviders (
	LIKE va_providers,
	rank INT NOT NULL DEFAULT 0
);
ALTER TABLE rankedvaproviders OWNER TO appdb;

CREATE OR REPLACE FUNCTION public.filtervaproviders(fltstr text, m_from text, m_where text)
 RETURNS SETOF rankedvaproviders
 LANGUAGE plpgsql
AS $function$
DECLARE h TEXT;
BEGIN
	SELECT filteritems(fltstr, m_from, m_where, 'rankedvaproviders') INTO h;
	RETURN QUERY EXECUTE 'SELECT * FROM cache.filtercache_' || h || ' ORDER BY rank DESC, sitename ASC';
END;
$function$;
ALTER FUNCTION filtervaproviders(text, text, text) OWNER TO appdb;

CREATE OR REPLACE FUNCTION public.filtervaproviders(fltstr text[], m_from text[], m_where text[])
 RETURNS SETOF rankedvaproviders
 LANGUAGE plpgsql
AS $function$
DECLARE h TEXT;
DECLARE i INT;
DECLARE j INT;
DECLARE ids rankedidstxt[];
BEGIN
	ids := NULL::rankedidstxt[];
	FOR i IN 1..ARRAY_LENGTH(fltstr, 1) LOOP
		IF i = ARRAY_LENGTH(fltstr ,1) THEN
			IF ids IS NULL AND i = 1 THEN
				RETURN QUERY SELECT * FROM filtervaproviders(fltstr[i], m_from[i], m_where[i]);
			ELSE
				RETURN QUERY SELECT
					va_providers.*,
					f.rank + rids.rank
				FROM filtervaproviders(fltstr[i], m_from[i], m_where[i]) AS f 
				INNER JOIN UNNEST(ids) AS rids ON rids.id = f.id
				INNER JOIN va_providers ON va_providers.id = rids.id;
			END IF;
		ELSE
			IF ids IS NULL THEN
				SELECT array_agg((f.id, f.rank)::rankedidstxt ORDER BY f.rank) FROM filtervaproviders(fltstr[i]::text, m_from[i]::text, m_where[i]::text) AS f INTO ids;
			ELSE
				SELECT array_agg((f.id, f.rank + rids.rank)::rankedidstxt ORDER BY f.rank) FROM filtervaproviders(fltstr[i]::text, m_from[i]::text, m_where[i]::text) AS f INNER JOIN UNNEST(ids) AS rids ON rids.id = f.id INTO ids;		
			END IF;
		END IF;
	END LOOP;
END;
$function$;
ALTER FUNCTION filtervaproviders(text[], text[], text[]) OWNER TO appdb;

CREATE OR REPLACE FUNCTION public.rankvaprovider(m_id __va_providers, m_query text)
 RETURNS integer
 LANGUAGE plpgsql
AS $function$
DECLARE rank INT;
DECLARE lrank INT;
DECLARE args TEXT[];
DECLARE arg TEXT;
DECLARE field TEXT;
DECLARE fields TEXT[];
DECLARE vals TEXT[];
DECLARE val TEXT;
DECLARE ops TEXT[];
DECLARE tmp TEXT[];
DECLARE i INT;
DECLARE j INT;
DECLARE kk INT;
DECLARE k TEXT;
DECLARE r RECORD;
DECLARE m_country TEXT;
BEGIN
	RAISE NOTICE 'in ranvaprovider function';
	IF m_query IS NULL OR TRIM(m_query) = '' THEN RETURN 0; END IF;
	m_query := fltstr_nbs(m_query);
	SELECT countries.name FROM countries WHERE countries.id = m_id.country_id INTO m_country;
	fields = '{id, name, type, countryname}'::TEXT[];
	rank := 0;
	args := string_to_array(m_query, ' ');
	FOR i IN 1..array_length(args, 1) LOOP
		arg := args[i];		
		LOOP
			IF SUBSTRING(arg,1,1) = '+' OR 
			SUBSTRING(arg,1,1) = '-' OR 
			SUBSTRING(arg,1,1) = '=' OR 
			SUBSTRING(arg,1,1) = '<' OR 
			SUBSTRING(arg,1,1) = '>' OR 
			SUBSTRING(arg,1,1) = '~' OR 
			SUBSTRING(arg,1,1) = '$' OR 
			SUBSTRING(arg,1,1) = '&' THEN
				ops = array_append(ops, SUBSTRING(arg,1,1));
				arg = SUBSTRING(arg,2); 
			ELSE
				EXIT;
			END IF;
		END LOOP;
		IF SUBSTRING(arg,1,12) = 'country.name' THEN arg := 'vaprovider.countryname' || SUBSTRING(arg,13); END IF;
		IF SUBSTRING(arg,1,11) = 'country.any' THEN arg := 'vaprovider.countryname' || SUBSTRING(arg,12); END IF;		
		IF NOT (SUBSTRING(arg,1,11) = 'vaprovider.' OR SUBSTRING(arg,1,4) = 'any.' OR instr(arg,'.') = 0) THEN CONTINUE; END IF;
		IF SUBSTR(arg,1,11) = 'vaprovider.' THEN arg = SUBSTRING(arg,6);
		ELSIF SUBSTR(arg,1,4) = 'any.' THEN arg = SUBSTRING(arg,5); END IF;
		tmp := string_to_array(arg, ':');
		field := NULL;
		IF array_length(tmp, 1) > 1 THEN
			IF tmp[1] <> 'any' THEN
				field := tmp[1];
			END IF;	
			val := '';
			FOR j IN 2..array_length(tmp, 1) LOOP
				val := val || tmp[j];
			END LOOP;
		ELSE
			val = tmp[1];
		END IF;
		IF NOT val IS NULL THEN
			FOR j IN 1..array_length(fields, 1) LOOP
				IF ops IS NULL OR ops = '{=}'::TEXT[] THEN
					vals := ('{' || val || ', %' || val || '%}')::TEXT[];
					FOR kk IN 1..array_length(vals, 1) LOOP
						k := vals[kk];
						RAISE NOTICE '%', k;
						lrank := 0;					
						IF fields[j] = 'name' THEN IF m_id.sitename ILIKE k THEN lrank := lrank + 4; END IF; END IF;
						IF fields[j] = 'type' THEN IF m_id.service_type ILIKE k THEN lrank := lrank + 4; END IF; END IF;
						IF fields[j] = 'id' THEN IF m_id.id::TEXT ILIKE k THEN lrank := lrank + 1; END IF; END IF;
						IF fields[j] = 'countryname' THEN IF m_country ILIKE k THEN lrank := lrank + 1; END IF; END IF;
						-- BONUS FOR SPECIFIC FIELD
						IF fields[j] = field THEN lrank = lrank * 2; END IF;
						rank := rank + lrank;
					END LOOP;
				END IF;
			END LOOP;
		END IF;
	END LOOP;
	RETURN rank;
END
$function$;
ALTER FUNCTION rankvaprovider(__va_providers, text) OWNER TO appdb;

CREATE OR REPLACE FUNCTION public.filteritems(fltstr text, m_from text, m_where text, itemtype text)
 RETURNS text
 LANGUAGE plpgsql
AS $function$
DECLARE h TEXT;
DECLARE t TEXT;
DECLARE rank TEXT;
DECLARE _rank TEXT;
DECLARE cols TEXT[];
DECLARE cachecount BIGINT;
BEGIN
	IF fltstr IS NULL THEN fltstr = ''; END IF;
	IF m_from IS NULL THEN m_from = ''; END IF;
	IF m_where IS NULL THEN m_where = ''; END IF;
	fltstr := TRIM(fltstr);
	m_from := TRIM(m_from);
	m_where := TRIM(m_where);
	IF itemtype IS NULL THEN itemtype = ''; END IF;
	IF itemtype = 'rankedapps' THEN
		t := 'applications';
		-- rank := 'rankapp(' || t || '.*, ''' || fltstr || ''') as rank';
		rank := 'rankapp';
		cols := (SELECT array_agg(column_name::text ORDER BY ordinal_position) FROM INFORMATION_SCHEMA.columns WHERE table_name = 'applications' AND table_schema = 'public');
	ELSIF itemtype = 'rankedppl' THEN
		t := 'researchers';
		-- rank := 'rankppl(' || t || '.*, ''' || fltstr || ''') as rank';
		rank := 'rankppl';
		cols := (SELECT array_agg(column_name::text ORDER BY ordinal_position) FROM INFORMATION_SCHEMA.columns WHERE table_name = 'researchers' AND table_schema = 'public');
	ELSIF itemtype = 'rankedvos' THEN
		t := 'vos';
		-- rank := 'rankvo(' || t || '.*, ''' || fltstr || ''') as rank';
		rank := 'rankvo';
		cols := (SELECT array_agg(column_name::text ORDER BY ordinal_position) FROM INFORMATION_SCHEMA.columns WHERE table_name = 'vos' AND table_schema = 'public');
	ELSIF itemtype = 'rankedsites' THEN
		t := 'sites';
		-- rank := 'ranksite(' || t || '.*, ''' || fltstr || ''') as rank';
		rank := 'ranksite';
		cols := (SELECT array_agg(column_name::text ORDER BY ordinal_position) FROM INFORMATION_SCHEMA.columns WHERE table_name = '__sites' AND table_schema = 'public');
	ELSIF itemtype = 'rankedvaproviders' THEN
		t := 'va_providers';
		rank := 'rankvaprovider';
		cols := (SELECT array_agg(column_name::text ORDER BY ordinal_position) FROM INFORMATION_SCHEMA.columns WHERE table_name = '__va_providers' AND table_schema = 'public');
	END IF;
	_rank := '0 as rank';
	h := MD5(m_from || ' ' || m_where);
	IF m_where = 'WHERE ()' THEN m_where = ''; END IF;
	IF EXISTS (SELECT * FROM config WHERE var = 'disable_filtercache' AND data::BOOLEAN IS TRUE) THEN
		DELETE FROM cache.filtercache WHERE hash = h;
	END IF;
	cachecount := 0;
	BEGIN
		EXECUTE 'SELECT COUNT(*) FROM cache.filtercache_' || h INTO cachecount;
	EXCEPTION
		WHEN OTHERS THEN
	END;
	IF (NOT EXISTS (SELECT hash FROM cache.filtercache WHERE hash = h AND invalid IS FALSE)) OR (cachecount = 0) THEN
		EXECUTE 'DROP TABLE IF EXISTS cache.filtercache_' || h || '; ' ||
			'CREATE TABLE IF NOT EXISTS cache.filtercache_' || h || ' AS SELECT DISTINCT ON (' || t || '.id) ' || t || '.*, ' || _rank || ' ' || m_from || ' ' || m_where || '; ' ||
			'UPDATE cache.filtercache_' || h || ' SET rank = ' || rank || '((' || array_to_string(cols,' ,') || '), ''' || REPLACE(fltstr,'''','''''') || ''')';
		IF NOT EXISTS (SELECT hash FROM cache.filtercache WHERE hash = h) THEN
			INSERT INTO cache.filtercache (hash, m_from, m_where, fltstr) SELECT h, m_from, m_where, fltstr WHERE NOT EXISTS (SELECT hash FROM cache.filtercache AS c WHERE c.hash = h);
		ELSE
			UPDATE cache.filtercache SET usecount = usecount+1, invalid = FALSE WHERE hash = h;
		END IF;
	ELSE
		UPDATE cache.filtercache SET usecount = usecount+1 WHERE hash = h;
	END IF;
	RETURN h;
END;
$function$;

CREATE OR REPLACE FUNCTION public.va_provider_to_xml(mid text[])
 RETURNS SETOF xml
 LANGUAGE sql
 STABLE
AS $function$
        SELECT va_provider_to_xml(id::text) FROM UNNEST(mid) AS id;
$function$;
ALTER FUNCTION va_provider_to_xml(text[]) OWNER TO appdb;

CREATE OR REPLACE FUNCTION public.va_provider_to_xml(mid text)
 RETURNS SETOF xml
 LANGUAGE plpgsql
AS $function$
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
                        service_type AS service_type,
                        service_status AS service_status,
                        service_status_date AS service_status_date
                ),
                xmlelement(name "provider:name", sitename)
        )
FROM
        va_providers
WHERE id = mid;
END;
$function$;

CREATE OR REPLACE FUNCTION public.va_provider_to_xml_ext(mid text)
 RETURNS SETOF xml
 LANGUAGE plpgsql
AS $function$
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
			service_type AS service_type,
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
				 xmlelement(name "provider_template:disc_size", disc_size),
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
						'https://appdb.egi.eu/store/vm/image/' || vmiinstances.guid::text || ':' || va_provider_images.vmiinstanceid::text AS base_mp_uri,
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
		va_providers.service_type,
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
$function$;

CREATE OR REPLACE FUNCTION public.va_provider_to_xml_ext(mid text[])
 RETURNS SETOF xml
 LANGUAGE plpgsql
AS $function$
BEGIN
        RETURN QUERY SELECT va_provider_to_xml_ext(id::text) FROM va_providers WHERE id::TEXT = ANY($1);
END;
$function$;
ALTER FUNCTION va_provider_to_xml_ext(text[]) OWNER TO appdb;

SELECT va_provider_to_xml(array_agg(va_providers.id)) FROM filtervaproviders('', 'FROM va_providers', '') AS va_providers INNER JOIN va_providers AS s ON s.id = va_providers.id

CREATE OR REPLACE FUNCTION public.count_vaprovider_matches(itemname text, cachetable text, private boolean DEFAULT false)
 RETURNS SETOF record
 LANGUAGE plpgsql
AS $function$
DECLARE q TEXT;
DECLARE allitems INT;
BEGIN
	IF itemname = 'country' THEN
		q := 'SELECT countries.name::TEXT AS count_text, COUNT(DISTINCT va_providers.id) AS count, countries.id AS count_id FROM ' || cachetable || ' AS va_providers LEFT JOIN countries ON countries.id = va_providers.country_id';
	ELSIF itemname = 'type' THEN
		q := 'SELECT va_providers.service_type::TEXT AS count_text, COUNT(DISTINCT va_providers.id) AS count, idx(cloud_service_types(), va_providers.service_type) AS count_id FROM ' || cachetable || ' AS va_providers';
	ELSE
		RAISE NOTICE 'Unknown va_provider property requested for logistics counting: %', itemname;
		RETURN;
	END IF;
	RETURN QUERY EXECUTE 'SELECT count_text, count, count_id::text FROM (' || q || ' GROUP BY count_text, count_id) AS t WHERE NOT count_text IS NULL';
END;
$function$;
ALTER FUNCTION count_vaprovider_matches(text, text, boolean) OWNER TO appdb;

CREATE OR REPLACE FUNCTION public.count_vaprovider_matches(itemname text, cachetable text[], private boolean DEFAULT false)
 RETURNS SETOF record
 LANGUAGE plpgsql
AS $function$
BEGIN
	RAISE NOTICE '%', '(SELECT * FROM va_providers WHERE id IN (SELECT id FROM ' || array_to_string(cachetable, ' INTERSECT SELECT id FROM ') || '))';
	RETURN QUERY SELECT * FROM count_vaprovider_matches(itemname, '(SELECT * FROM va_providers WHERE id IN (SELECT id FROM ' || array_to_string(cachetable, ' INTERSECT SELECT id FROM ') || '))', private) AS count_vaprovider_matches(count_text text, count bigint, count_id text);
END;
$function$;
ALTER FUNCTION count_vaprovider_matches(text, text[], boolean) OWNER TO appdb;

CREATE OR REPLACE FUNCTION public.vaprovider_logistics(m_fltstr text, m_from text, m_where text)
 RETURNS xml
 LANGUAGE plpgsql
AS $function$
DECLARE h TEXT[];
DECLARE hh TEXT;
DECLARE fl TEXT[];
DECLARE fr TEXT[];
DECLARE w TEXT[];
DECLARE i INT;
DECLARE len INT;
BEGIN
        IF m_fltstr IS NULL THEN m_fltstr := ''; END IF;
        IF m_from IS NULL THEN m_from := ''; END IF;
        IF m_where IS NULL THEN m_where := ''; END IF;
		m_fltstr := TRIM(m_fltstr);
		m_from := TRIM(m_from);
		m_where := TRIM(m_where);
		IF SUBSTRING(m_fltstr, 1, 1) = '{' THEN
			fl := m_fltstr::text[];
			fr := m_from::text[];
			w := m_where::text[];
		ELSE
			fl := ('{"' || REPLACE(m_fltstr, '"', '\"') || '"}')::text[];
			fr := ('{"' || REPLACE(m_from, '"', '\"') || '"}')::text[];
			w := ('{"' ||  REPLACE(m_where, '"', '\"') || '"}')::text[];
		END IF;
		h := NULL::TEXT[];
		IF m_fltstr = '' THEN
			len := 0;
			hh := MD5(m_from || ' ' || m_where);
			IF NOT EXISTS (SELECT hash FROM cache.filtercache WHERE hash = hh) THEN
				PERFORM filtervaproviders(m_fltstr, m_from, m_where);
			END IF;
			h := ARRAY['cache.filtercache_' || hh];
		ELSE
			len := ARRAY_LENGTH(fl, 1);
		END IF;
		FOR i IN 1..len LOOP
			m_fltstr = TRIM(fl[i]);
			m_from = TRIM(fr[i]);
			m_where = TRIM(w[i]);
			hh := MD5(m_from || ' ' || m_where);
			IF NOT EXISTS (SELECT hash FROM cache.filtercache WHERE hash = hh) THEN
				PERFORM filtervaproviders(m_fltstr, m_from, m_where);
			END IF;
			hh := 'cache.filtercache_' || hh;
			h := array_append(h, hh);
		END LOOP;
        RETURN xmlelement(name "provider:logistics",
                xmlconcat(
               (SELECT xmlagg(xmlelement(name "logistics:cloud_service_type", xmlattributes(t.name as "text", t.count as "count", t.id::text::text::text as "id"))) FROM count_vaprovider_matches('type', h) as t(name TEXT, count bigint, id text)),
			(SELECT xmlagg(xmlelement(name "logistics:country", xmlattributes(t.name as "text", t.count as "count", t.id::text::text::text as "id"))) FROM count_vaprovider_matches('country', h) as t(name TEXT, count bigint, id text)),
			(SELECT xmlagg(xmlelement(name "logistics:phonebook", xmlattributes(t.name as "text", t.count as "count", t.id::text::text::text as "id"))) FROM 
(
WITH c AS (SELECT * FROM cached_ids(h) AS id)
SELECT l AS "name", COUNT(DISTINCT va_providers.id) AS count, n::text AS id FROM 
(
WITH RECURSIVE t(n) AS (
	VALUES (1)
	UNION ALL
	SELECT n+1 FROM t WHERE n < 28
)
SELECT 
CASE 
WHEN n<=26 THEN 
	SUBSTRING('ABCDEFGHIJKLMNOPQRSTUVWXYZ',n,1)
WHEN n=27 THEN 
	'0-9'
ELSE 
	'#'
END AS l,
CASE 
WHEN n<=26 THEN 
	'^' || SUBSTRING('ABCDEFGHIJKLMNOPQRSTUVWXYZ',n,1) || '.+'
WHEN n=27 THEN 
	'^[0-9].+'
ELSE 
	'^[^A-Za-z0-9].+'
END AS p,
n
FROM t) AS q
INNER JOIN va_providers ON va_providers.sitename ~* p
WHERE va_providers.id IN (SELECT id::text FROM c)
GROUP BY l, n
ORDER BY n
) AS t
)
));
END;
$function$;
ALTER FUNCTION vaprovider_logistics(text, text, text) OWNER TO appdb;

INSERT INTO version (major,minor,revision,notes) 
SELECT 8, 22, 99, E'Support for OpenStack native cloud endpoints (aka va_providers)'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=22 AND revision=99);

COMMIT;
