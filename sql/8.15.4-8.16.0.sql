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
Previous version: 8.15.4
New version: 8.16.0
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

DROP FUNCTION IF EXISTS group_hash(va_provider_templates);
DROP TABLE IF EXISTS va_provider_templates;
CREATE SEQUENCE va_provider_templates_id_seq;
ALTER SEQUENCE va_provider_templates_id_seq OWNER TO appdb;
DROP MATERIALIZED VIEW site_services_xml;
DROP MATERIALIZED VIEW site_service_images_xml;
DROP FUNCTION IF EXISTS good_vmiinstanceid(va_provider_images);
DROP TABLE IF EXISTS va_provider_images;
CREATE SEQUENCE va_provider_images_id_seq;
ALTER SEQUENCE va_provider_images_id_seq OWNER TO appdb;
DROP TABLE IF EXISTS va_provider_endpoints;
CREATE SEQUENCE va_provider_endpoints_id_seq;
ALTER SEQUENCE va_provider_endpoints_id_seq OWNER TO appdb;

DROP TABLE IF EXISTS vapj;
CREATE TABLE vapj (
	pkey TEXT NOT NULL PRIMARY KEY,
	j JSON NOT NULL,
	h TEXT NOT NULL,
	lastseen TIMESTAMP
);
ALTER TABLE vapj OWNER TO appdb;

DROP TABLE IF EXISTS tvapj;
CREATE TABLE tvapj (
	pkey TEXT NOT NULL PRIMARY KEY,
	j JSON NOT NULL,
	h TEXT NOT NULL
);
ALTER TABLE tvapj OWNER TO appdb;

DROP MATERIALIZED VIEW IF EXISTS va_provider_templates;
CREATE MATERIALIZED VIEW va_provider_templates AS
SELECT
  nextval('va_provider_templates_id_seq'::regclass) AS id,
  pkey AS va_provider_id,
  (json_array_elements(((j->>'info')::json->>'templates')::json)->>'GLUE2EntityName')::text AS resource_name,
  (json_array_elements(((j->>'info')::json->>'templates')::json)->>'GLUE2ExecutionEnvironmentMainMemorySize')::text AS memsize,
  (json_array_elements(((j->>'info')::json->>'templates')::json)->>'GLUE2ExecutionEnvironmentLogicalCPUs')::text AS logical_cpus,
  (json_array_elements(((j->>'info')::json->>'templates')::json)->>'GLUE2ExecutionEnvironmentPhysicalCPUs')::text AS physical_cpus,
  (json_array_elements(((j->>'info')::json->>'templates')::json)->>'GLUE2ExecutionEnvironmentCPUMultiplicity')::text AS cpu_multiplicity,
  (json_array_elements(((j->>'info')::json->>'templates')::json)->>'GLUE2ResourceManagerForeignKey')::text AS resource_manager,
  (json_array_elements(((j->>'info')::json->>'templates')::json)->>'GLUE2ExecutionEnvironmentComputingManagerForeignKey')::text AS computing_manager,
  (json_array_elements(((j->>'info')::json->>'templates')::json)->>'GLUE2ExecutionEnvironmentOSFamily')::text AS os_family,
  (json_array_elements(((j->>'info')::json->>'templates')::json)->>'GLUE2ExecutionEnvironmentConnectivityIn')::text AS connectivity_in,
  (json_array_elements(((j->>'info')::json->>'templates')::json)->>'GLUE2ExecutionEnvironmentConnectivityOut')::text AS connectivity_out,
  (json_array_elements(((j->>'info')::json->>'templates')::json)->>'GLUE2ExecutionEnvironmentCPUModel')::text AS cpu_model,
  (json_array_elements(((j->>'info')::json->>'templates')::json)->>'GLUE2ResourceID')::text AS resource_id,
  (json_array_elements(((j->>'info')::json->>'templates')::json)->>'GLUE2ExecutionEnvironmentDiskSize')::text AS disc_size
FROM tvapj;
ALTER MATERIALIZED VIEW va_provider_templates OWNER TO appdb;
CREATE INDEX "idx_va_provider_templates_va_provider_id" ON va_provider_templates USING btree (va_provider_id);
CREATE INDEX "idx_va_provider_templates_va_provider_id_textops" ON va_provider_templates USING btree (va_provider_id text_pattern_ops);
CREATE INDEX "idx_va_provider_templates_va_provider_id_trgmops" ON va_provider_templates USING gin (va_provider_id gin_trgm_ops);

DROP MATERIALIZED VIEW IF EXISTS va_provider_endpoints;
CREATE MATERIALIZED VIEW va_provider_endpoints AS
SELECT
  nextval('va_provider_endpoints_id_seq'::regclass) AS id,
  pkey AS va_provider_id,
  ((j->>'info')::json->>'GLUE2EndpointURL')::text AS endpoint_url,
  ((j->>'info')::json->>'GLUE2EndpointImplementor')::text AS deployment_type
FROM tvapj;
ALTER MATERIALIZED VIEW va_provider_endpoints OWNER TO appdb;
CREATE INDEX "idx_va_provider_endpoints_va_provider_id" ON va_provider_endpoints USING btree (va_provider_id);
CREATE INDEX "idx_va_provider_endpoints_va_provider_id_textops" ON va_provider_endpoints USING btree (va_provider_id text_pattern_ops);
CREATE INDEX "idx_va_provider_endpoints_va_provider_id_trgmops" ON va_provider_endpoints USING gin (va_provider_id gin_trgm_ops);

DROP MATERIALIZED VIEW IF EXISTS va_provider_images;
CREATE MATERIALIZED VIEW va_provider_images AS
SELECT
  nextval('va_provider_images_id_seq'::regclass) AS id,
  pkey AS va_provider_id,
  (json_array_elements(((j->>'info')::json->>'images')::json)->'vmi_instance_id')::text::int AS vmiinstanceid,
  (json_array_elements(((j->>'info')::json->>'images')::json)->>'content_type')::text AS content_type,
  (json_array_elements(((j->>'info')::json->>'images')::json)->>'GLUE2EntityName')::text AS va_provider_image_id,
  (json_array_elements(((j->>'info')::json->>'images')::json)->>'GLUE2ApplicationEnvironmentRepository')::text AS mp_uri,
  (json_array_elements(((j->>'info')::json->>'images')::json)->'vowide_vmi_instance_id')::text::int AS vowide_vmiinstanceid
FROM tvapj;
ALTER MATERIALIZED VIEW va_provider_images OWNER TO appdb;
CREATE INDEX "idx_va_provider_images_va_provider_id" ON va_provider_images USING btree(va_provider_id);
CREATE INDEX "idx_va_provider_images_va_provider_id_textops" ON va_provider_images USING btree(va_provider_id text_pattern_ops);
CREATE INDEX "idx_va_provider_images_va_provider_id_trgmops" ON va_provider_images USING gin(va_provider_id gin_trgm_ops);
CREATE INDEX "idx_va_provider_images_vmiinstanceid" ON va_provider_images USING btree(vmiinstanceid);
CREATE INDEX "idx_va_provider_images_vowide_vmiinstanceid" ON va_provider_images USING btree(vowide_vmiinstanceid);

CREATE OR REPLACE FUNCTION group_hash(v va_provider_templates)
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
$function$;
ALTER FUNCTION group_hash(va_provider_templates) OWNER TO appdb;

CREATE OR REPLACE FUNCTION good_vmiinstanceid(va_provider_images)
 RETURNS integer
 LANGUAGE sql
 STABLE
AS $function$
--      SELECT get_good_vmiinstanceid($1.vmiinstanceid)
        SELECT CASE WHEN goodid IS NULL THEN $1.vmiinstanceid ELSE goodid END FROM (
                        SELECT max(t1.id) as goodid FROM vmiinstances AS t1
                        INNER JOIN vmiinstances AS t2 ON t1.checksum = t2.checksum AND t1.guid = t2.guid AND t2.id = $1.vmiinstanceid
                        INNER JOIN vapplists ON t1.id = vapplists.vmiinstanceid
                        INNER JOIN vapp_versions ON vapplists.vappversionid = vapp_versions.id
                        WHERE vapp_versions.published
        ) AS t
$function$;
ALTER FUNCTION good_vmiinstanceid(va_provider_images) OWNER TO appdb;

CREATE MATERIALIZED VIEW site_services_xml AS
 SELECT __va_providers.sitename,
    XMLELEMENT(NAME "site:service", XMLATTRIBUTES('occi' AS type, __va_providers.id AS id, __va_providers.hostname AS host, count(DISTINCT good_vmiinstanceid(va_provider_images.*)) AS instances, __va_providers.beta AS beta, __va_providers.in_production AS in_production, __va_providers.service_downtime::integer AS service_downtime, __va_providers.service_status AS service_status, __va_providers.service_status_date AS service_status_date), xmlagg(XMLELEMENT(NAME "siteservice:image", XMLATTRIBUTES(va_provider_images.vmiinstanceid AS id, good_vmiinstanceid(va_provider_images.*) AS goodid)))) AS x
   FROM __va_providers
     LEFT JOIN va_provider_images ON va_provider_images.va_provider_id = __va_providers.id AND (va_provider_images.vmiinstanceid IN ( SELECT __vaviews.vmiinstanceid
           FROM __vaviews))
  GROUP BY __va_providers.id, __va_providers.hostname, __va_providers.beta, __va_providers.in_production, __va_providers.service_downtime, __va_providers.sitename, __va_providers.service_status, __va_providers.service_status_date;
ALTER MATERIALIZED VIEW site_services_xml OWNER TO appdb;
CREATE INDEX "idx_site_services_xml_sitename" ON site_services_xml USING btree (sitename);
CREATE INDEX "idx_site_services_xml_sitename_textops" ON site_services_xml USING btree (sitename text_pattern_ops);
CREATE INDEX "idx_site_services_xml_sitename_trgmops" ON site_services_xml USING gin (sitename gin_trgm_ops);

CREATE MATERIALIZED VIEW site_service_images_xml AS
 SELECT siteimages.va_provider_id,
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
  GROUP BY siteimages.va_provider_id;
ALTER MATERIALIZED VIEW site_service_images_xml OWNER TO appdb;

CREATE FUNCTION refresh_va_providers() RETURNS VOID AS
$$
BEGIN
	ALTER TABLE gocdb.va_providers
		DISABLE TRIGGER tr_gocdb_va_providers_99_refresh_permissions;

	TRUNCATE TABLE gocdb.va_providers;
	INSERT INTO gocdb.va_providers
	SELECT
		g.pkey,
		((g.j->>'info')::json->>'hostname')::text AS hostname,
		((g.j->>'info')::json->>'goc_portal_url')::text AS gocdb_url,
		((g.j->>'info')::json->>'hostdn')::text AS host_dn,
		((g.j->>'info')::json->>'host_os')::text AS host_os,
		((g.j->>'info')::json->>'host_arch')::text AS host_arch,
		((g.j->>'info')::json->'beta')::text::int::boolean AS beta,
		((g.j->>'info')::json->>'service_type')::text AS service_type,
		((g.j->>'info')::json->>'host_ip')::text AS host_ip,
		((g.j->>'info')::json->'in_production')::text::int::boolean AS in_production,
		((g.j->>'info')::json->'node_monitored')::text::int::boolean AS node_monitored,
		((g.j->>'info')::json->>'site_name')::text AS sitename,
		((g.j->>'info')::json->>'country_name')::text AS country_name,
		((g.j->>'info')::json->>'country_code')::text AS country_code,
		((g.j->>'info')::json->>'roc_name')::text AS roc_name,
		((g.j->>'info')::json->>'url')::text AS url,
		((t.j->>'info')::json->>'GLUE2EndpointServiceForeignKey')::text AS serviceid
	FROM vapj AS g
	LEFT OUTER JOIN tvapj AS t ON t.pkey = g.pkey
	WHERE (NOW() - lastseen)::INTERVAL < '6 hours'::INTERVAL;

	REFRESH MATERIALIZED VIEW va_providers;

	REFRESH MATERIALIZED VIEW va_provider_endpoints;
	REFRESH MATERIALIZED VIEW va_provider_images;
	REFRESH MATERIALIZED VIEW va_provider_templates;

	ALTER TABLE gocdb.va_providers
		ENABLE TRIGGER tr_gocdb_va_providers_99_refresh_permissions;

	REFRESH MATERIALIZED VIEW CONCURRENTLY _actor_group_members;
        REFRESH MATERIALIZED VIEW CONCURRENTLY _actor_group_members2;
        REFRESH MATERIALIZED VIEW CONCURRENTLY permissions;

	--
	-- These materialized views also depend on va providers, but are very slow to refresh
	-- Most probably we only want to refresh them when sync'ing sites instead
	--
	-- REFRESH MATERIALIZED VIEW site_services_xml;
	-- REFRESH MATERIALIZED VIEW site_service_images_xml;

END;
$$
LANGUAGE plpgsql VOLATILE;
ALTER FUNCTION refresh_va_providers OWNER TO appdb;

REFRESH MATERIALIZED VIEW site_services_xml;
REFRESH MATERIALIZED VIEW site_service_images_xml;

SELECT refresh_va_providers();

ROLLBACK;
INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 16, 0, E'Refactor va providers, based on new JSON information system data'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=16 AND revision=0);
