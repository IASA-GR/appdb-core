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

DROP TRIGGER IF EXISTS tr_gocdb_va_providers_99_refresh_permissions ON gocdb.va_providers;

DROP FUNCTION IF EXISTS group_hash(va_provider_templates);
-- DROP TABLE IF EXISTS va_provider_templates;
DROP SEQUENCE IF EXISTS va_provider_templates_id_seq CASCADE;
CREATE SEQUENCE va_provider_templates_id_seq;
ALTER SEQUENCE va_provider_templates_id_seq OWNER TO appdb;
DROP MATERIALIZED VIEW site_services_xml;
DROP MATERIALIZED VIEW site_service_images_xml;
DROP FUNCTION IF EXISTS good_vmiinstanceid(va_provider_images);
-- DROP TABLE IF EXISTS va_provider_images;
DROP SEQUENCE IF EXISTS va_provider_images_id_seq CASCADE;
CREATE SEQUENCE va_provider_images_id_seq;
ALTER SEQUENCE va_provider_images_id_seq OWNER TO appdb;
-- DROP TABLE IF EXISTS va_provider_endpoints;
DROP SEQUENCE IF EXISTS va_provider_endpoints_id_seq CASCADE;
CREATE SEQUENCE va_provider_endpoints_id_seq;
ALTER SEQUENCE va_provider_endpoints_id_seq OWNER TO appdb;

DROP TABLE IF EXISTS vapj CASCADE;
CREATE TABLE vapj (
	pkey TEXT NOT NULL PRIMARY KEY,
	j JSONB NOT NULL,
	h TEXT NOT NULL,
	lastseen TIMESTAMP DEFAULT NOW()
);
ALTER TABLE vapj OWNER TO appdb;
CREATE INDEX idx_vapj_info ON vapj USING gin (((j ->> 'info')::jsonb));
CREATE INDEX idx_vapj_lastseen ON vapj USING btree(lastseen);

CREATE OR REPLACE FUNCTION trfn_vapj_upsert() RETURNS TRIGGER AS
$$
BEGIN
	IF EXISTS (SELECT * FROM vapj WHERE TRIM(pkey) = TRIM(NEW.pkey)) THEN		
		IF NEW.h = (SELECT h FROM vapj WHERE pkey = NEW.pkey) THEN
			-- RAISE NOTICE 'existing unmodded entry, updating lastseen for %', pkey;
			UPDATE vapj
				SET lastseen = NOW()
				WHERE pkey = NEW.pkey;
			RETURN NULL;
		ELSE
			-- RAISE NOTICE 'existing modded entry, updating data and lastseen for %', pkey;
			UPDATE vapj 
				SET j = NEW.j, h = NEW.h, lastseen = NOW()
				WHERE pkey = NEW.pkey;
			RETURN NULL;
		END IF;
	ELSE
		-- RAISE NOTICE 'new entry';
		NEW.lastseen = NOW();
		RETURN NEW;
	END IF;
END;
$$
LANGUAGE plpgsql;
ALTER FUNCTION trfn_vapj_upsert() OWNER TO appdb;

DROP TRIGGER IF EXISTS rtr_vapj_10_upsert ON vapj;
CREATE TRIGGER rtr_vapj_10_upsert BEFORE INSERT ON vapj
FOR EACH ROW EXECUTE PROCEDURE trfn_vapj_upsert();

DROP TABLE IF EXISTS tvapj CASCADE;
CREATE TABLE tvapj (
	pkey TEXT NOT NULL PRIMARY KEY,
	j JSONB NOT NULL,
	h TEXT NOT NULL,
	lastseen TIMESTAMP DEFAULT NOW()
);
ALTER TABLE tvapj OWNER TO appdb;
CREATE INDEX idx_tvapj_info ON tvapj USING gin (((j ->> 'info')::jsonb));
CREATE INDEX idx_tvapj_lastseen ON tvapj USING btree(lastseen);

CREATE OR REPLACE FUNCTION trfn_tvapj_upsert() RETURNS TRIGGER AS
$$
BEGIN
	IF EXISTS (SELECT 1 FROM tvapj WHERE pkey = NEW.pkey) THEN
		IF NEW.h = (SELECT h FROM tvapj WHERE pkey = NEW.pkey) THEN
			RETURN NULL;
		ELSE
			UPDATE tvapj 
				SET j = NEW.j, h = NEW.h, lastseen = NOW()
				WHERE pkey = NEW.pkey;
			RETURN NULL;
		END IF;
	ELSE
		NEW.lastseen = NOW();
		RETURN NEW;
	END IF;
END;
$$
LANGUAGE plpgsql;
ALTER FUNCTION trfn_tvapj_upsert() OWNER TO appdb;

DROP TRIGGER IF EXISTS rtr_tvapj_10_upsert ON tvapj;
CREATE TRIGGER rtr_tvapj_10_upsert BEFORE INSERT ON tvapj
FOR EACH ROW EXECUTE PROCEDURE trfn_tvapj_upsert();

CREATE OR REPLACE FUNCTION trfn_gocdb_va_providers_upsert() RETURNS TRIGGER AS
$$
BEGIN
	-- RAISE NOTICE 'processing pkey: %;', NEW.pkey;
	IF EXISTS (SELECT 1 FROM gocdb.va_providers WHERE pkey = NEW.pkey) THEN
		-- RAISE NOTICE 'existing pkey: %;', NEW.pkey;
		UPDATE gocdb.va_providers SET 
		  hostname = NEW.hostname,
		  gocdb_url = NEW.gocdb_url,
		  host_dn = NEW.host_dn,
		  host_os = NEW.host_os,
		  host_arch = NEW.host_arch,
		  beta = NEW.beta,
		  service_type = NEW.service_type,
		  host_ip = NEW.host_ip,
		  in_production = NEW.in_production,
		  node_monitored = NEW.node_monitored,
		  sitename = NEW.sitename,
		  country_name = NEW.country_name,
		  country_code = NEW.country_code,
		  roc_name = NEW.roc_name,
		  url = NEW.url,
		  serviceid = NEW.serviceid
		WHERE pkey = NEW.pkey;
		RETURN NULL;
	ELSE		
		-- RAISE NOTICE 'new pkey: %;', NEW.pkey;
		RETURN NEW;
	END IF;
END;
$$
LANGUAGE plpgsql;
ALTER FUNCTION trfn_gocdb_va_providers_upsert() OWNER TO appdb;

DROP TRIGGER IF EXISTS rtr_gocdb_va_providers_10_upsert ON gocdb.va_providers;
CREATE TRIGGER rtr_gocdb_va_providers_10_upsert BEFORE INSERT ON gocdb.va_providers
FOR EACH ROW EXECUTE PROCEDURE trfn_gocdb_va_providers_upsert();

DROP MATERIALIZED VIEW IF EXISTS va_provider_templates;
CREATE MATERIALIZED VIEW va_provider_templates AS 
SELECT
  nextval('va_provider_templates_id_seq'::regclass) AS id,
  g.pkey AS va_provider_id,
  (jsonb_array_elements(((t.j->>'info')::jsonb->>'templates')::jsonb)->>'GLUE2EntityName')::text AS resource_name,
  (jsonb_array_elements(((t.j->>'info')::jsonb->>'templates')::jsonb)->>'GLUE2ExecutionEnvironmentMainMemorySize')::text AS memsize,
  (jsonb_array_elements(((t.j->>'info')::jsonb->>'templates')::jsonb)->>'GLUE2ExecutionEnvironmentLogicalCPUs')::text AS logical_cpus,
  (jsonb_array_elements(((t.j->>'info')::jsonb->>'templates')::jsonb)->>'GLUE2ExecutionEnvironmentPhysicalCPUs')::text AS physical_cpus,
  (jsonb_array_elements(((t.j->>'info')::jsonb->>'templates')::jsonb)->>'GLUE2ExecutionEnvironmentCPUMultiplicity')::text AS cpu_multiplicity,
  (jsonb_array_elements(((t.j->>'info')::jsonb->>'templates')::jsonb)->>'GLUE2ResourceManagerForeignKey')::text AS resource_manager,
  (jsonb_array_elements(((t.j->>'info')::jsonb->>'templates')::jsonb)->>'GLUE2ExecutionEnvironmentComputingManagerForeignKey')::text AS computing_manager,
  (jsonb_array_elements(((t.j->>'info')::jsonb->>'templates')::jsonb)->>'GLUE2ExecutionEnvironmentOSFamily')::text AS os_family,
  (jsonb_array_elements(((t.j->>'info')::jsonb->>'templates')::jsonb)->>'GLUE2ExecutionEnvironmentConnectivityIn')::text AS connectivity_in,
  (jsonb_array_elements(((t.j->>'info')::jsonb->>'templates')::jsonb)->>'GLUE2ExecutionEnvironmentConnectivityOut')::text AS connectivity_out,
  (jsonb_array_elements(((t.j->>'info')::jsonb->>'templates')::jsonb)->>'GLUE2ExecutionEnvironmentCPUModel')::text AS cpu_model,
  (jsonb_array_elements(((t.j->>'info')::jsonb->>'templates')::jsonb)->>'GLUE2ResourceID')::text AS resource_id,
  /*CASE (SELECT TRIM((jsonb_array_elements(((t.j->>'info')::jsonb->>'templates')::jsonb)->>'GLUE2ExecutionEnvironmentDiskSize')::text))
	WHEN '0' THEN 'unlimited'
	WHEN NULL::text THEN 'unspecified'
	ELSE
		((SELECT jsonb_array_elements(((t.j->>'info')::jsonb->>'templates')::jsonb)->>'GLUE2ExecutionEnvironmentDiskSize')::text) || ' GiB'
  END AS disc_size*/
  (jsonb_array_elements(((t.j->>'info')::jsonb->>'templates')::jsonb)->>'GLUE2ExecutionEnvironmentDiskSize')::text AS disc_size
FROM vapj AS g
LEFT OUTER JOIN tvapj AS t ON t.pkey = g.pkey
WHERE 
	(COALESCE(TRIM(((t.j->>'info')::jsonb->>'GLUE2ComputingEndpointComputingServiceForeignKey')::text), '') <> '') AND
	(COALESCE(((g.j->>'info')::jsonb->>'in_production')::text, 'FALSE')::boolean IS DISTINCT FROM FALSE);
ALTER MATERIALIZED VIEW va_provider_templates OWNER TO appdb;
CREATE UNIQUE INDEX "idx_va_provider_templates_id" ON va_provider_templates USING btree (id);
CREATE INDEX "idx_va_provider_templates_va_provider_id_textops" ON va_provider_templates USING btree (va_provider_id text_pattern_ops);
CREATE INDEX "idx_va_provider_templates_va_provider_id_trgmops" ON va_provider_templates USING gin (va_provider_id gin_trgm_ops);

DROP MATERIALIZED VIEW IF EXISTS va_provider_endpoints;
CREATE MATERIALIZED VIEW va_provider_endpoints AS
SELECT
  nextval('va_provider_endpoints_id_seq'::regclass) AS id,
  t.pkey AS va_provider_id,
  ((t.j->>'info')::jsonb->>'GLUE2EndpointURL')::text AS endpoint_url,
  ((t.j->>'info')::jsonb->>'GLUE2EndpointImplementor')::text AS deployment_type
FROM vapj AS g
LEFT OUTER JOIN tvapj AS t ON g.pkey = t.pkey
WHERE ((t.j->>'info')::jsonb->>'GLUE2EndpointInterfaceName')::text = 'OCCI';
ALTER MATERIALIZED VIEW va_provider_endpoints OWNER TO appdb;
CREATE UNIQUE INDEX "idx_va_provider_endpoints_id" ON va_provider_endpoints USING btree (id);
CREATE INDEX "idx_va_provider_endpoints_va_provider_id" ON va_provider_endpoints USING btree (va_provider_id);
CREATE INDEX "idx_va_provider_endpoints_va_provider_id_textops" ON va_provider_endpoints USING btree (va_provider_id text_pattern_ops);
CREATE INDEX "idx_va_provider_endpoints_va_provider_id_trgmops" ON va_provider_endpoints USING gin (va_provider_id gin_trgm_ops);

DROP MATERIALIZED VIEW IF EXISTS va_provider_images;
CREATE MATERIALIZED VIEW va_provider_images AS
SELECT 
	id, va_provider_id,
	CASE WHEN TRIM(COALESCE(vmiinstanceid, '')) = '' THEN
		NULL::int
	ELSE
		vmiinstanceid::int
	END AS vmiinstanceid,
	content_type, va_provider_image_id, mp_uri, 
	CASE WHEN LOWER(vowide_vmiinstanceid::text) = 'null' THEN NULL::int ELSE vowide_vmiinstanceid::int END AS vowide_vmiinstanceid
FROM (
	SELECT 
		id, va_provider_id,
		CASE WHEN LOWER(vmiinstanceid) = 'null' THEN
			CASE LOWER(content_type)
				WHEN 'vo' THEN
					(
						SELECT vapplists.vmiinstanceid 
						FROM vowide_image_list_images 
						INNER JOIN vapplists ON vapplists.id = vowide_image_list_images.vapplistid 
						WHERE vowide_image_list_images.id::text = ((SELECT REGEXP_SPLIT_TO_ARRAY(REPLACE((REGEXP_MATCHES(mp_uri, ':[0-9]+[:/]*[0-9]*', '')::text[])[1], '/', ''), ':', '')::text[]))[2]
					)::text
				ELSE
					((SELECT REGEXP_SPLIT_TO_ARRAY(REPLACE((REGEXP_MATCHES(mp_uri, ':[0-9]+[:/]*[0-9]*', '')::text[])[1], '/', ''), ':', '')::text[]))[2]	
			END
		ELSE 
			vmiinstanceid
		END AS vmiinstanceid,
		CASE LOWER(content_type) WHEN 'vm' THEN 'va' ELSE LOWER(content_type) END AS content_type,
		va_provider_image_id, mp_uri, 
		CASE WHEN LOWER(vowide_vmiinstanceid) = 'null' THEN NULL::int ELSE vowide_vmiinstanceid::int END AS vowide_vmiinstanceid	
	FROM (
		SELECT 
		  nextval('va_provider_images_id_seq'::regclass) AS id,
		  g.pkey AS va_provider_id,
		  (jsonb_array_elements(((t.j->>'info')::jsonb->>'images')::jsonb)->'vmi_instance_id')::text AS vmiinstanceid,
		  (jsonb_array_elements(((t.j->>'info')::jsonb->>'images')::jsonb)->>'content_type')::text AS content_type,
		  (jsonb_array_elements(((t.j->>'info')::jsonb->>'images')::jsonb)->>'GLUE2EntityName')::text AS va_provider_image_id,
		  (jsonb_array_elements(((t.j->>'info')::jsonb->>'images')::jsonb)->>'GLUE2ApplicationEnvironmentRepository')::text AS mp_uri,
		  (jsonb_array_elements(((t.j->>'info')::jsonb->>'images')::jsonb)->'vowide_vmi_instance_id')::text AS vowide_vmiinstanceid
		FROM vapj AS g
		LEFT OUTER JOIN tvapj AS t ON g.pkey = t.pkey
		WHERE 
			(COALESCE(TRIM(((t.j->>'info')::jsonb->>'GLUE2ComputingEndpointComputingServiceForeignKey')::text), '') <> '') AND
			(COALESCE(((g.j->>'info')::jsonb->>'in_production')::text, 'FALSE')::boolean IS DISTINCT FROM FALSE)
	) AS x
) AS xx;
ALTER MATERIALIZED VIEW va_provider_images OWNER TO appdb;
CREATE UNIQUE INDEX "idx_va_provider_images_id" ON va_provider_images USING btree (id);
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
 SELECT __va_providers.id, __va_providers.sitename,
    XMLELEMENT(NAME "site:service", XMLATTRIBUTES('occi' AS type, __va_providers.id AS id, __va_providers.hostname AS host, count(DISTINCT good_vmiinstanceid(va_provider_images.*)) AS instances, __va_providers.beta AS beta, __va_providers.in_production AS in_production, __va_providers.service_downtime::integer AS service_downtime, __va_providers.service_status AS service_status, __va_providers.service_status_date AS service_status_date), xmlagg(XMLELEMENT(NAME "siteservice:image", XMLATTRIBUTES(va_provider_images.vmiinstanceid AS id, good_vmiinstanceid(va_provider_images.*) AS goodid)))) AS x
   FROM __va_providers
     LEFT JOIN va_provider_images ON va_provider_images.va_provider_id = __va_providers.id AND (va_provider_images.vmiinstanceid IN ( SELECT __vaviews.vmiinstanceid
           FROM __vaviews))
  GROUP BY __va_providers.id, __va_providers.hostname, __va_providers.beta, __va_providers.in_production, __va_providers.service_downtime, __va_providers.sitename, __va_providers.service_status, __va_providers.service_status_date;
ALTER MATERIALIZED VIEW site_services_xml OWNER TO appdb;
CREATE UNIQUE INDEX "idx_site_services_xml_id" ON site_services_xml USING btree (id);
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
CREATE UNIQUE INDEX "idx_site_service_images_xml_id" ON site_service_images_xml USING btree (va_provider_id);

ALTER TABLE gocdb.sites ALTER COLUMN shortname DROP NOT NULL;

DROP TABLE IF EXISTS sitej CASCADE;
CREATE TABLE sitej (
	pkey TEXT NOT NULL PRIMARY KEY,
	j JSONB NOT NULL,
	h TEXT NOT NULL,
	lastseen TIMESTAMP DEFAULT NOW()
);
ALTER TABLE sitej OWNER TO appdb;
CREATE INDEX idx_sitej_info ON sitej USING gin (((j ->> 'info')::jsonb));
CREATE INDEX idx_sitej_lastseen ON sitej USING btree(lastseen);

CREATE OR REPLACE FUNCTION trfn_sitej_upsert() RETURNS TRIGGER AS
$$
BEGIN
	IF EXISTS (SELECT 1 FROM sitej WHERE pkey = NEW.pkey) THEN
		
		IF NEW.h = (SELECT h FROM sitej WHERE pkey = NEW.pkey) THEN
			-- RAISE NOTICE 'existing unmodded entry, updating lastseen';
			UPDATE sitej
				SET lastseen = NOW()
				WHERE pkey = NEW.pkey;
			RETURN NULL;
		ELSE
			-- RAISE NOTICE 'existing modded entry, updating data and lastseen';
			UPDATE sitej 
				SET j = NEW.j, h = NEW.h, lastseen = NOW()
				WHERE pkey = NEW.pkey;
			RETURN NULL;
		END IF;
	ELSE
		-- RAISE NOTICE 'new entry';
		NEW.lastseen = NOW();
		RETURN NEW;
	END IF;
END;
$$
LANGUAGE plpgsql;
ALTER FUNCTION trfn_sitej_upsert() OWNER TO appdb;

DROP TRIGGER IF EXISTS rtr_sitej_10_upsert ON sitej;
CREATE TRIGGER rtr_sitej_10_upsert BEFORE INSERT ON sitej
FOR EACH ROW EXECUTE PROCEDURE trfn_sitej_upsert();

CREATE OR REPLACE VIEW __va_providers AS
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
     LEFT JOIN oses ON LOWER(oses.name) = LOWER(va_providers.host_os)
     LEFT JOIN archs ON LOWER(archs.name) = LOWER(va_providers.host_arch) OR (LOWER(va_providers.host_arch) = ANY(LOWER(archs.aliases::text)::text[]))
     LEFT JOIN countries ON countries.isocode = va_providers.country_code;

CREATE OR REPLACE FUNCTION trfn_gocdb_sites_upsert() RETURNS TRIGGER AS
$$
BEGIN
	-- RAISE NOTICE 'processing pkey: %;', NEW.pkey;
	IF EXISTS (SELECT 1 FROM gocdb.sites WHERE pkey = NEW.pkey) THEN
		-- RAISE NOTICE 'existing pkey: %;', NEW.pkey;
		UPDATE gocdb.sites SET 
		  name = NEW.name,
		  shortname = NEW.shortname,
		  officialname = NEW.officialname,
		  description = NEW.description,
		  portalurl = NEW.portalurl,
		  homeurl = NEW.homeurl,
		  contactemail = NEW.contactemail,
		  contacttel = NEW.contacttel,
		  alarmemail = NEW.alarmemail,
		  csirtemail = NEW.csirtemail,
		  giisurl = NEW.giisurl,
		  countrycode = NEW.countrycode,
		  country = NEW.country,
		  tier = NEW.tier,
		  subgrid = NEW.subgrid,
		  roc = NEW.roc,
		  prodinfrastructure = NEW.prodinfrastructure,
		  certstatus = NEW.certstatus,
		  timezone = NEW.timezone,
		  latitude = NEW.latitude,
		  longitude = NEW.longitude,
		  domainname = NEW.domainname,
		  siteip = NEW.siteip,
		  guid = uuid_generate_v5(uuid_namespace('ISO OID'::text), 'gocdb:sites:' || NEW.name),
		  updatedon = NOW(),
		  deleted = COALESCE(NEW.deleted, false),
		  deletedon = COALESCE(NEW.deletedon, NULL),
		  deletedby = COALESCE(NEW.deletedby, NULL)
		WHERE pkey = NEW.pkey;
		RETURN NULL;
	ELSE		
		-- RAISE NOTICE 'new pkey: %;', NEW.pkey;
		RETURN NEW;
	END IF;
END;
$$
LANGUAGE plpgsql;
ALTER FUNCTION trfn_gocdb_sites_upsert() OWNER TO appdb;

DROP TRIGGER IF EXISTS rtr_gocdb_sites_10_upsert ON gocdb.sites;
CREATE TRIGGER rtr_gocdb_sites_10_upsert BEFORE INSERT ON gocdb.sites
FOR EACH ROW EXECUTE PROCEDURE trfn_gocdb_sites_upsert();

CREATE OR REPLACE FUNCTION refresh_sites(va_sync_scopes TEXT DEFAULT 'FedCloud') RETURNS VOID AS
$$ 
-- DECLARE deltime TEXT;
DECLARE scopes TEXT[];
BEGIN
	scopes := ('{' || COALESCE(va_sync_scopes, '') || '}')::text[];
--	deltime := '1 minute';

	TRUNCATE TABLE gocdb.site_contacts;  -- OBSOLETED

	-- mark sites that weren't seen during the last json sync from the infosys service as deleted (key missing, or old timestamp)
	UPDATE gocdb.sites
	SET 
		deleted = TRUE, 
		deletedon = NOW(),
		deletedby = 'gocdb'
	WHERE pkey IN (
		SELECT pkey 
			FROM sitej
			-- WHERE ((NOW() - lastseen)::INTERVAL > deltime::INTERVAL)
			WHERE lastseen < (SELECT MAX(lastseen) FROM sitej)
	) OR pkey NOT IN (SELECT pkey FROM sitej);
	
	-- upsert json data into sites table
	INSERT INTO gocdb.sites
		(pkey, name, shortname, officialname, description, portalurl, homeurl, contactemail, contacttel, alarmemail, csirtemail, giisurl,
		countrycode, country, tier, subgrid, roc, prodinfrastructure, certstatus, timezone, latitude, longitude, domainname, siteip,
		deleted, deletedon, deletedby)
	SELECT
		g.pkey,
		((g.j->>'info')::jsonb->>'site_name')::text AS name,
		((g.j->>'info')::jsonb->>'shortname')::text AS shortname,
		((g.j->>'info')::jsonb->>'officialname')::text AS officialname,
		((g.j->>'info')::jsonb->>'description')::text AS description,
		((g.j->>'info')::jsonb->>'gocdb_portal_url')::text AS portalurl,
		((g.j->>'info')::jsonb->>'homeurl')::text AS homeurl,
		NULL::text AS contactemail,
		NULL::text AS contacttel,
		NULL::text AS alarmemail,
		NULL::text AS csirtemail,		
		((g.j->>'info')::jsonb->>'giisurl')::text AS giisurl,
		((g.j->>'info')::jsonb->>'countrycode')::text AS countrycode,
		((g.j->>'info')::jsonb->>'country')::text AS country,
		((g.j->>'info')::jsonb->>'tier')::text AS tier,
		((g.j->>'info')::jsonb->>'subgrid')::text AS subgrid,
		((g.j->>'info')::jsonb->>'roc')::text AS roc,
		((g.j->>'info')::jsonb->>'prodinfrastructure')::text AS prodinfrastructure,
		((g.j->>'info')::jsonb->>'certstatus')::text AS certstatus,
		((g.j->>'info')::jsonb->>'timezone')::text AS timezone,
		((g.j->>'info')::jsonb->>'latitude')::text AS latitude,
		((g.j->>'info')::jsonb->>'longtitude')::text AS longtitude,
		((g.j->>'info')::jsonb->>'domainname')::text AS domainname,
		NULL::text AS siteip,
		FALSE, NULL, NULL
	FROM sitej AS g
	-- WHERE (NOW() - g.lastseen)::INTERVAL <= deltime::INTERVAL;
	WHERE g.lastseen = (SELECT MAX(lastseen) FROM sitej);

	-- ******************
	-- VA PROVIDERS
	-- ******************

	-- ALTER TABLE gocdb.va_providers
		-- DISABLE TRIGGER tr_gocdb_va_providers_99_refresh_permissions;		

	-- remove entries that either
	-- 1) weren't seen during the last json sync from the infosys service (key missing, or old timestamp)
	-- 2) don't have at least one scope that matches the VA scopes given
	DELETE FROM gocdb.va_providers 
	WHERE pkey IN (
		SELECT pkey 
			FROM vapj
			-- WHERE ((NOW() - lastseen)::INTERVAL > deltime::INTERVAL)
			WHERE lastseen < (SELECT MAX(lastseen) FROM vapj)
	) OR (
		pkey NOT IN (SELECT pkey FROM vapj)
	) OR ( NOT (
		SELECT array_agg(s) && scopes
		FROM (SELECT jsonb_array_elements_text(((g.j->>'info')::jsonb->>'scopes')::jsonb)::text AS s FROM vapj AS g WHERE g.pkey = va_providers.pkey) AS ts
	));
	
	-- make sure any OS declared by the VA exists in our OSes table
	INSERT INTO oses (name)
		SELECT DISTINCT
			TRIM(((g.j->>'info')::jsonb->>'host_os')::text)
		FROM 
			vapj AS g
		WHERE 	
			(((g.j->>'info')::jsonb->>'host_os')::text IS DISTINCT FROM NULL) AND
			(TRIM(((g.j->>'info')::jsonb->>'host_os')::text) <> '') AND
			(LOWER(TRIM(((g.j->>'info')::jsonb->>'host_os')::text)) NOT IN (
				SELECT LOWER(name) FROM oses
			));

	INSERT INTO gocdb.va_providers
	SELECT 
		g.pkey,
		((g.j->>'info')::jsonb->>'hostname')::text AS hostname,
		((g.j->>'info')::jsonb->>'goc_portal_url')::text AS gocdb_url,
		((g.j->>'info')::jsonb->>'hostdn')::text AS host_dn,
		((g.j->>'info')::jsonb->>'host_os')::text AS host_os,
		((g.j->>'info')::jsonb->>'host_arch')::text AS host_arch,
		((g.j->>'info')::jsonb->>'beta')::text::boolean AS beta,
		((g.j->>'info')::jsonb->>'service_type')::text AS service_type,
		((g.j->>'info')::jsonb->>'host_ip')::text AS host_ip,
		((g.j->>'info')::jsonb->>'in_production')::text::boolean AS in_production,
		((g.j->>'info')::jsonb->>'node_monitored')::text::boolean AS node_monitored,
		((g.j->>'info')::jsonb->>'site_name')::text AS sitename,
		((g.j->>'info')::jsonb->>'country_name')::text AS country_name,
		((g.j->>'info')::jsonb->>'country_code')::text AS country_code,
		((g.j->>'info')::jsonb->>'roc_name')::text AS roc_name,
		((g.j->>'info')::jsonb->>'url')::text AS url,
		((t.j->>'info')::jsonb->>'GLUE2ComputingEndpointComputingServiceForeignKey')::text AS serviceid
	FROM 
		vapj AS g
	LEFT OUTER JOIN tvapj AS t ON t.pkey = g.pkey
	WHERE 
		-- ((NOW() - g.lastseen)::INTERVAL <= deltime::INTERVAL) AND
		(
			g.lastseen = (SELECT MAX(lastseen) FROM vapj)
		) AND (
			SELECT array_agg(s) && scopes
			FROM (SELECT jsonb_array_elements_text(((g.j->>'info')::jsonb->>'scopes')::jsonb)::text AS s) AS ts
		) 
	;
	
	-- refresh all related materialized views
	REFRESH MATERIALIZED VIEW CONCURRENTLY sites;
	REFRESH MATERIALIZED VIEW CONCURRENTLY va_providers;
	REFRESH MATERIALIZED VIEW CONCURRENTLY va_provider_endpoints;
	REFRESH MATERIALIZED VIEW CONCURRENTLY va_provider_images;
	REFRESH MATERIALIZED VIEW CONCURRENTLY va_provider_templates;

	REFRESH MATERIALIZED VIEW CONCURRENTLY _actor_group_members;
        REFRESH MATERIALIZED VIEW CONCURRENTLY _actor_group_members2;
        REFRESH MATERIALIZED VIEW CONCURRENTLY permissions;

	REFRESH MATERIALIZED VIEW CONCURRENTLY site_services_xml;
	REFRESH MATERIALIZED VIEW CONCURRENTLY site_service_images_xml;
	
	-- ALTER TABLE gocdb.va_providers
		-- ENABLE TRIGGER tr_gocdb_va_providers_99_refresh_permissions;
END;
$$ 
LANGUAGE plpgsql VOLATILE;
ALTER FUNCTION refresh_sites(text) OWNER TO appdb;

CREATE OR REPLACE FUNCTION public.count_site_matches(
    itemname text,
    cachetable text,
    private boolean DEFAULT false)
  RETURNS SETOF record AS
$BODY$
DECLARE q TEXT;
DECLARE allitems INT;
BEGIN
	IF itemname = 'country' THEN
		q := 'SELECT countries.name::TEXT AS count_text, COUNT(DISTINCT sites.id) AS count, countries.id AS count_id FROM ' || cachetable || ' AS sites LEFT JOIN countries ON countries.id = sites.countryid';
	ELSIF itemname = 'discipline' THEN
		q := 'SELECT disciplines.name::TEXT AS count_text, COUNT(DISTINCT sites.id) AS count, disciplines.id AS count_id FROM ' || cachetable || ' AS sites LEFT JOIN va_providers ON va_providers.sitename = sites.name
		LEFT JOIN va_provider_images AS va_provider_images ON va_provider_images.va_provider_id = va_providers.id
		LEFT JOIN vaviews ON vaviews.vmiinstanceid = va_provider_images.vmiinstanceid
		LEFT JOIN applications ON applications.id = vaviews.appid
		LEFT JOIN appdisciplines ON appdisciplines.appid = applications.id
		LEFT JOIN disciplines ON disciplines.id = appdisciplines.disciplineid';
		-- q := 'SELECT disciplines.name::TEXT AS count_text, COUNT(DISTINCT sites.id) AS count, disciplines.id AS count_id FROM ' || cachetable || ' AS sites' || CASE WHEN NOT private THEN ' LEFT JOIN app_vos ON app_vos.appid = applications.id LEFT JOIN vos ON vos.id = app_vos.void AND vos.deleted IS FALSE' ELSE '' END || ' LEFT JOIN appdisciplines ON appdisciplines.appid = applications.id LEFT JOIN disciplines ON disciplines.id = appdisciplines.disciplineid' || CASE WHEN NOT private THEN ' OR disciplines.id = vos.domainid' ELSE '' END;
	ELSIF itemname = 'category' THEN
		q := 'SELECT categories.name::TEXT AS count_text, COUNT(DISTINCT sites.id) AS count, categories.id AS count_id
		FROM ' || cachetable || ' AS sites LEFT JOIN va_providers ON va_providers.sitename = sites.name LEFT JOIN va_provider_images AS va_provider_images ON va_provider_images.va_provider_id = va_providers.id
		LEFT JOIN vaviews ON vaviews.vmiinstanceid = va_provider_images.vmiinstanceid LEFT JOIN applications ON applications.id = vaviews.appid LEFT JOIN categories ON categories.id = ANY(applications.categoryid)';
		--q := 'SELECT categories.name::TEXT AS count_text, COUNT(DISTINCT applications.id) AS count, categories.id AS count_id FROM ' || cachetable || ' AS applications LEFT JOIN categories ON categories.id = ANY(applications.categoryid)';
	ELSIF itemname = 'arch' THEN
		q := 'SELECT archs.name::TEXT AS count_text, COUNT(DISTINCT sites.id) AS count, archs.id AS count_id FROM ' || cachetable || ' AS sites LEFT JOIN va_providers ON va_providers.sitename = sites.name
		LEFT JOIN va_provider_images AS va_provider_images ON va_provider_images.va_provider_id = va_providers.id
		LEFT JOIN vaviews ON vaviews.vmiinstanceid = va_provider_images.vmiinstanceid
		LEFT JOIN applications ON applications.id = vaviews.appid
		LEFT JOIN vapplications ON vapplications.appid = applications.id
		LEFT JOIN vapp_versions ON vapp_versions.vappid = vapplications.id AND published AND enabled AND NOT archived AND status = ''verified''
		LEFT JOIN vmis ON vmis.vappid = vapplications.id
		LEFT JOIN vmiflavours ON vmiflavours.vmiid = vmis.id
		LEFT JOIN archs ON archs.id = vmiflavours.archid';
	ELSIF itemname = 'os' THEN
		q := 'SELECT oses.name::TEXT AS count_text, COUNT(DISTINCT sites.id) AS count, oses.id AS count_id FROM  ' || cachetable || ' AS sites
		LEFT JOIN va_providers ON va_providers.sitename = sites.name
		LEFT JOIN va_provider_images AS va_provider_images ON va_provider_images.va_provider_id = va_providers.id
		LEFT JOIN vaviews ON vaviews.vmiinstanceid = va_provider_images.vmiinstanceid
		LEFT JOIN applications ON applications.id = vaviews.appid
		LEFT JOIN vapplications ON vapplications.appid = applications.id
		LEFT JOIN vapp_versions ON vapp_versions.vappid = vapplications.id AND published AND enabled AND NOT archived AND status = ''verified''
		LEFT JOIN vmis ON vmis.vappid = vapplications.id
		LEFT JOIN vmiflavours ON vmiflavours.vmiid = vmis.id
		LEFT JOIN oses ON oses.id = vmiflavours.osid';
	ELSIF itemname = 'osfamily' THEN
		q := 'SELECT os_families.name::TEXT AS count_text, COUNT(DISTINCT sites.id) AS count, os_families.id AS count_id FROM ' || cachetable || ' AS sites
		LEFT JOIN va_providers ON va_providers.sitename = sites.name
		LEFT JOIN va_provider_images AS va_provider_images ON va_provider_images.va_provider_id = va_providers.id
		LEFT JOIN vaviews ON vaviews.vmiinstanceid = va_provider_images.vmiinstanceid
		LEFT JOIN applications ON applications.id = vaviews.appid
		LEFT JOIN vapplications ON vapplications.appid = applications.id
		LEFT JOIN vapp_versions ON vapp_versions.vappid = vapplications.id AND published AND enabled AND NOT archived AND status = ''verified''
		LEFT JOIN vmis ON vmis.vappid = vapplications.id
		LEFT JOIN vmiflavours ON vmiflavours.vmiid = vmis.id
		LEFT JOIN oses ON oses.id = vmiflavours.osid
		LEFT JOIN os_families ON os_families.id = oses.os_family_id';
	ELSIF itemname = 'hypervisor' THEN
		q :='SELECT hypervisors.name::TEXT AS count_text, COUNT(DISTINCT sites.id) AS count, hypervisors.id::int AS count_id FROM ' || cachetable || ' AS sites
		LEFT JOIN va_providers ON va_providers.sitename = sites.name
		LEFT JOIN va_provider_images AS va_provider_images ON va_provider_images.va_provider_id = va_providers.id
		LEFT JOIN vaviews ON vaviews.vmiinstanceid = va_provider_images.vmiinstanceid
		LEFT JOIN applications ON applications.id = vaviews.appid
		LEFT JOIN vapplications ON vapplications.appid = applications.id
		LEFT JOIN vapp_versions ON vapp_versions.vappid = vapplications.id AND published AND enabled AND NOT archived AND status = ''verified''
		LEFT JOIN vmis ON vmis.vappid = vapplications.id
		LEFT JOIN vmiflavours ON vmiflavours.vmiid = vmis.id
		LEFT JOIN hypervisors ON hypervisors.value = ANY(vmiflavours.hypervisors)';
	ELSIF itemname = 'vo' THEN
		q := 'SELECT vos.name::TEXT AS count_text, COUNT(DISTINCT sites.id) AS count, vos.id AS count_id FROM ' || cachetable || ' AS sites
		LEFT JOIN va_providers ON va_providers.sitename = sites.name
		LEFT JOIN va_provider_images AS va_provider_images ON va_provider_images.va_provider_id = va_providers.id AND va_provider_images.vowide_vmiinstanceid IS NOT NULL
		LEFT JOIN vowide_image_list_images ON vowide_image_list_images.ID = va_provider_images.vowide_vmiinstanceid and vowide_image_list_images.state = ''up-to-date''::e_vowide_image_state
		LEFT JOIN vowide_image_lists ON vowide_image_lists.id = vowide_image_list_images.vowide_image_list_id AND vowide_image_list_images.state <> ''draft''::e_vowide_image_state
		LEFT JOIN vos ON vos.id = vowide_image_lists.void AND vos.deleted IS FALSE';
	ELSIF itemname = 'middleware' THEN
		q := 'SELECT middlewares.name::TEXT AS count_text, COUNT(DISTINCT sites.id) AS count, middlewares.id AS count_id FROM ' || cachetable || ' AS sites
		LEFT JOIN va_providers ON va_providers.sitename = sites.name
		LEFT JOIN va_provider_images AS va_provider_images ON va_provider_images.va_provider_id = va_providers.id
		LEFT JOIN vaviews ON vaviews.vmiinstanceid = va_provider_images.vmiinstanceid
		LEFT JOIN applications ON applications.id = vaviews.appid
		LEFT JOIN app_middlewares ON app_middlewares.appid = applications.id
		LEFT JOIN middlewares ON middlewares.id = app_middlewares.middlewareid';
	ELSIF itemname = 'supports' THEN
		q := 'SELECT CASE WHEN va_providers.sitename IS NULL THEN ''none''
		ELSE ''occi'' END AS count_text, COUNT(DISTINCT sites.id) AS count,
		CASE WHEN va_providers.sitename IS NULL THEN 0 ELSE 1 END AS count_id
		FROM ' || cachetable || ' AS sites
		LEFT JOIN va_providers ON va_providers.sitename = sites.name and va_providers.in_production = true';
	ELSIF itemname = 'hasinstances' THEN
		q := 'SELECT CASE WHEN va_provider_images.vmiinstanceid IS NULL THEN ''none''
		ELSE ''virtual images'' END AS count_text, COUNT(DISTINCT sites.id) AS count,
		CASE WHEN va_provider_images.vmiinstanceid IS NULL THEN 0 ELSE 1 END AS count_id
		FROM ' || cachetable || ' AS sites
		LEFT JOIN va_providers ON va_providers.sitename = sites.name and va_providers.in_production = true
		LEFT JOIN va_provider_images AS va_provider_images ON va_provider_images.va_provider_id = va_providers.id
		LEFT JOIN vaviews ON vaviews.vmiinstanceid = va_provider_images.vmiinstanceid';
	ELSE
		RAISE NOTICE 'Unknown site property requested for logistics counting: %', itemname;
		RETURN;
	END IF;
	RETURN QUERY EXECUTE 'SELECT count_text, count, count_id::text FROM (' || q || ' GROUP BY count_text, count_id) AS t WHERE NOT count_text IS NULL';
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION public.count_site_matches(text, text, boolean)
  OWNER TO appdb;
COMMENT ON FUNCTION public.count_site_matches(text, text, boolean) IS 'not to be called directly; used by site_logistics function';

TRUNCATE TABLE gocdb.sites CASCADE;

SELECT refresh_sites();

CREATE OR REPLACE FUNCTION process_site_argo_status(dat jsonb[]) RETURNS VOID
AS
$$
DECLARE j jsonb;
DECLARE statust TIMESTAMP;
DECLARE statusv TEXT;
DECLARE epkey TEXT;
BEGIN
	FOREACH j IN ARRAY dat LOOP  
    	statust := (j->>'info')::jsonb->>'timestamp';
        statusv := (j->>'info')::jsonb->>'value';
        epkey := (j->>'info')::jsonb->>'endpoint_pkey';
        -- RAISE NOTICE 'status: %, ts: %, pkey: %', statusv, statust, epkey;
        UPDATE gocdb.va_providers 
        	SET service_status = statusv, service_status_date = statust 
        	WHERE (pkey = epkey) AND ((service_status_date < statust) OR (service_status_date IS NULL)) AND (LOWER(TRIM(COALESCE(statusv,''))) NOT IN ('', 'missing'));
    END LOOP;
END;
$$ LANGUAGE plpgsql VOLATILE;
ALTER FUNCTION process_site_argo_status(jsonb[]) OWNER TO appdb;

CREATE OR REPLACE FUNCTION process_site_downtimes(dat jsonb[]) RETURNS VOID
AS
$$
DECLARE j jsonb;
DECLARE dstart TIMESTAMP;
DECLARE dend TIMESTAMP;
DECLARE nowstart TIMESTAMP;
DECLARE nowend TIMESTAMP;
DECLARE epkey TEXT;
DECLARE dkey TEXT;
DECLARE active_dts TEXT[];
BEGIN
	active_dts := '{}'::TEXT[];
	UPDATE gocdb.va_providers SET service_downtime = 0::bit(2);
	
	FOREACH j IN ARRAY dat LOOP
		dkey := (j->>'info')::jsonb->>'downtime_pkey';
		dstart := (j->>'info')::jsonb->>'start_time';
		dend := (j->>'info')::jsonb->>'end_time';
		nowstart := (SELECT NOW() AT TIME ZONE 'UTC');
		nowend := (SELECT (NOW() AT TIME ZONE 'UTC') + '1 day'::INTERVAL);
		epkey := (j->>'info')::jsonb->>'endpoint_pkey';
		IF 
			(nowstart >= dstart) AND (nowstart <= dend)
		THEN
			-- Active downtime
			UPDATE gocdb.va_providers SET service_downtime = service_downtime | 2::bit(2) WHERE pkey = epkey;
			active_dts := array_append(active_dts, dkey);
		ELSIF
			((dend >= nowstart) AND (dend <= nowend)) OR
			((dstart >= nowstart) AND (dstart <= nowend)) OR
			((dstart <= nowstart) AND (dend >= nowend))
		THEN
			IF NOT (dkey = ANY(active_dts)) THEN -- this shouldn't happen if incoming data are sound, but better check just in case
				-- Down sometime between now and 24h from now
				UPDATE gocdb.va_providers SET service_downtime = service_downtime | 1::bit(2) WHERE pkey = epkey;
			END IF;
		ELSE
			RAISE NOTICE 'Downtime % is either in the past, or scheduled for more than 24 hours from now. Ignored', dkey;
		END IF;
	END LOOP;
END;
$$ LANGUAGE plpgsql VOLATILE;
ALTER FUNCTION process_site_downtimes(jsonb[]) OWNER TO appdb;

DROP TRIGGER IF EXISTS tr_gocdb_sites_99_create_uuid ON gocdb.sites;
CREATE TRIGGER tr_gocdb_sites_01_create_uuid
    BEFORE INSERT
    ON gocdb.sites
    FOR EACH ROW
    EXECUTE PROCEDURE gocdb.trfn_gocdb_sites_create_uuid();

INSERT INTO version (major,minor,revision,notes)
       SELECT 8, 16, 0, E'Refactor va providers, based on new JSON information system data'
       WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=16 AND revision=0);

COMMIT;
