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

CREATE SCHEMA IF NOT EXISTS egiis;

ALTER SCHEMA egiis OWNER TO appdb;

DROP TRIGGER IF EXISTS tr_gocdb_va_providers_99_refresh_permissions ON gocdb.va_providers;

DROP FUNCTION IF EXISTS group_hash(va_provider_templates);
  DROP TABLE IF EXISTS va_provider_templates;
DROP SEQUENCE IF EXISTS va_provider_templates_id_seq CASCADE;
CREATE SEQUENCE va_provider_templates_id_seq;
ALTER SEQUENCE va_provider_templates_id_seq OWNER TO appdb;
DROP MATERIALIZED VIEW site_services_xml;
DROP MATERIALIZED VIEW site_service_images_xml;
DROP FUNCTION IF EXISTS good_vmiinstanceid(va_provider_images);
  DROP TABLE IF EXISTS va_provider_images;
DROP SEQUENCE IF EXISTS va_provider_images_id_seq CASCADE;
CREATE SEQUENCE va_provider_images_id_seq;
ALTER SEQUENCE va_provider_images_id_seq OWNER TO appdb;
  DROP TABLE IF EXISTS va_provider_endpoints;
DROP SEQUENCE IF EXISTS va_provider_endpoints_id_seq CASCADE;
CREATE SEQUENCE va_provider_endpoints_id_seq;
ALTER SEQUENCE va_provider_endpoints_id_seq OWNER TO appdb;

DROP TABLE IF EXISTS egiis.argo CASCADE;
CREATE TABLE egiis.argo(
    pkey TEXT NOT NULL,
    egroup TEXT NOT NULL,
    j JSONB NOT NULL,
    h TEXT NOT NULL,
    lastseen TIMESTAMP DEFAULT NOW(),
    CONSTRAINT pk_egiis_argo PRIMARY KEY (pkey, egroup)
);
ALTER TABLE egiis.argo OWNER TO appdb;
CREATE INDEX idx_argo_info ON egiis.argo USING gin (((j ->> 'info')::jsonb));
CREATE INDEX idx_argo_lastseen ON egiis.argo USING btree(lastseen);

CREATE OR REPLACE FUNCTION trfn_argo_upsert() RETURNS TRIGGER AS
$$
BEGIN
	IF EXISTS (SELECT 1 FROM egiis.argo WHERE (pkey = NEW.pkey) AND (egroup = NEW.egroup)) THEN		
		IF NEW.h = (SELECT h FROM egiis.argo WHERE (pkey = NEW.pkey) AND (egroup = NEW.egroup)) THEN
			-- RAISE NOTICE 'existing unmodded entry, updating lastseen for %', pkey;
			UPDATE egiis.argo
				SET lastseen = NOW()
				WHERE (pkey = NEW.pkey) AND (egroup = NEW.egroup);
			RETURN NULL;
		ELSE
			-- RAISE NOTICE 'existing modded entry, updating data and lastseen for %', pkey;
			UPDATE egiis.argo 
				SET j = NEW.j, h = NEW.h, lastseen = NOW()
				WHERE (pkey = NEW.pkey) AND (egroup = NEW.egroup);
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
ALTER FUNCTION trfn_argo_upsert() OWNER TO appdb;

DROP TRIGGER IF EXISTS rtr_argo_10_upsert ON egiis.argo;
CREATE TRIGGER rtr_argo_10_upsert BEFORE INSERT ON egiis.argo
FOR EACH ROW EXECUTE PROCEDURE trfn_argo_upsert();

DROP TABLE IF EXISTS egiis.downtimes CASCADE;
CREATE TABLE egiis.downtimes(
    pkey TEXT NOT NULL PRIMARY KEY,
    j JSONB NOT NULL,
    h TEXT NOT NULL,
	lastseen TIMESTAMP DEFAULT NOW()
);
ALTER TABLE egiis.downtimes OWNER TO appdb;
CREATE INDEX idx_downtimes_info ON egiis.downtimes USING gin (((j ->> 'info')::jsonb));
CREATE INDEX idx_downtimes_lastseen ON egiis.downtimes USING btree(lastseen);

CREATE OR REPLACE FUNCTION trfn_downtimes_upsert() RETURNS TRIGGER AS
$$
BEGIN
	IF EXISTS (SELECT 1 FROM egiis.downtimes WHERE pkey = NEW.pkey) THEN		
		IF NEW.h = (SELECT h FROM egiis.downtimes WHERE pkey = NEW.pkey) THEN
			-- RAISE NOTICE 'existing unmodded entry, updating lastseen for %', pkey;
			UPDATE egiis.downtimes
				SET lastseen = NOW()
				WHERE pkey = NEW.pkey;
			RETURN NULL;
		ELSE
			-- RAISE NOTICE 'existing modded entry, updating data and lastseen for %', pkey;
			UPDATE egiis.downtimes 
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
ALTER FUNCTION trfn_downtimes_upsert() OWNER TO appdb;

DROP TRIGGER IF EXISTS rtr_downtimes_10_upsert ON egiis.downtimes;
CREATE TRIGGER rtr_downtimes_10_upsert BEFORE INSERT ON egiis.downtimes
FOR EACH ROW EXECUTE PROCEDURE trfn_downtimes_upsert();

DROP TABLE IF EXISTS egiis.vapj CASCADE;
CREATE TABLE egiis.vapj (
	pkey TEXT NOT NULL PRIMARY KEY,
	j JSONB NOT NULL,
	h TEXT NOT NULL,
	lastseen TIMESTAMP DEFAULT NOW()
);
ALTER TABLE egiis.vapj OWNER TO appdb;
CREATE INDEX idx_vapj_info ON egiis.vapj USING gin (((j ->> 'info')::jsonb));
CREATE INDEX idx_vapj_lastseen ON egiis.vapj USING btree(lastseen);

CREATE OR REPLACE FUNCTION trfn_vapj_upsert() RETURNS TRIGGER AS
$$
BEGIN
	IF EXISTS (SELECT 1 FROM egiis.vapj WHERE pkey = NEW.pkey) THEN		
		IF NEW.h = (SELECT h FROM egiis.vapj WHERE pkey = NEW.pkey) THEN
			-- RAISE NOTICE 'existing unmodded entry, updating lastseen for %', pkey;
			UPDATE egiis.vapj
				SET lastseen = NOW()
				WHERE pkey = NEW.pkey;
			RETURN NULL;
		ELSE
			-- RAISE NOTICE 'existing modded entry, updating data and lastseen for %', pkey;
			UPDATE egiis.vapj 
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

DROP TRIGGER IF EXISTS rtr_vapj_10_upsert ON egiis.vapj;
CREATE TRIGGER rtr_vapj_10_upsert BEFORE INSERT ON egiis.vapj
FOR EACH ROW EXECUTE PROCEDURE trfn_vapj_upsert();

DROP TABLE IF EXISTS egiis.tvapj CASCADE;
CREATE TABLE egiis.tvapj (
	pkey TEXT NOT NULL PRIMARY KEY,
	j JSONB NOT NULL,
	h TEXT NOT NULL,
	lastseen TIMESTAMP DEFAULT NOW()
);
ALTER TABLE egiis.tvapj OWNER TO appdb;
CREATE INDEX idx_egiis_tvapj_info ON egiis.tvapj USING gin (((j ->> 'info')::jsonb));
CREATE INDEX idx_egiis_tvapj_lastseen ON egiis.tvapj USING btree(lastseen);

CREATE OR REPLACE FUNCTION trfn_egiis_tvapj_upsert() RETURNS TRIGGER AS
$$
BEGIN
	IF EXISTS (SELECT 1 FROM egiis.tvapj WHERE pkey = NEW.pkey) THEN
		IF NEW.h = (SELECT h FROM egiis.tvapj WHERE pkey = NEW.pkey) THEN
			UPDATE egiis.tvapj
                SET lastseen = NOW()
                WHERE pkey = NEW.pkey;
            RETURN NULL;
		ELSE
			UPDATE egiis.tvapj 
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
ALTER FUNCTION trfn_egiis_tvapj_upsert() OWNER TO appdb;

DROP TRIGGER IF EXISTS rtr_egiis_tvapj_10_upsert ON egiis.tvapj;
CREATE TRIGGER rtr_egiis_tvapj_10_upsert BEFORE INSERT ON egiis.tvapj
FOR EACH ROW EXECUTE PROCEDURE trfn_egiis_tvapj_upsert();

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
FROM egiis.vapj AS g
LEFT OUTER JOIN egiis.tvapj AS t ON t.pkey = g.pkey
WHERE 
    ((t.j->>'info')::jsonb->>'GLUE2EndpointInterfaceName')::text = 'OCCI' AND
	(COALESCE(TRIM(((t.j->>'info')::jsonb->>'GLUE2ComputingEndpointComputingServiceForeignKey')::text), '') <> '') AND
	(COALESCE(((g.j->>'info')::jsonb->>'SiteEndpointInProduction')::text, 'FALSE')::boolean IS DISTINCT FROM FALSE);
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
FROM egiis.vapj AS g
LEFT OUTER JOIN egiis.tvapj AS t ON g.pkey = t.pkey
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
--		CASE WHEN LOWER(vmiinstanceid) = 'null' THEN
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
--		ELSE 
--			vmiinstanceid
--		END 
        AS vmiinstanceid,
		CASE LOWER(content_type) WHEN 'va' THEN 'vm' ELSE LOWER(content_type) END AS content_type,    	
		va_provider_image_id, mp_uri, 
		CASE WHEN LOWER(vowide_vmiinstanceid) = 'null' THEN NULL::int ELSE vowide_vmiinstanceid::int END AS vowide_vmiinstanceid	
	FROM (
		SELECT 
		  nextval('va_provider_images_id_seq'::regclass) AS id,
		  g.pkey AS va_provider_id,
		  (jsonb_array_elements(((t.j->>'info')::jsonb->>'images')::jsonb)->'ImageVmiInstanceId')::text AS vmiinstanceid,
		  (jsonb_array_elements(((t.j->>'info')::jsonb->>'images')::jsonb)->>'ImageContentType')::text AS content_type,
		  (jsonb_array_elements(((t.j->>'info')::jsonb->>'images')::jsonb)->>'GLUE2EntityName')::text AS va_provider_image_id,
		  (jsonb_array_elements(((t.j->>'info')::jsonb->>'images')::jsonb)->>'GLUE2ApplicationEnvironmentRepository')::text AS mp_uri,
		  (jsonb_array_elements(((t.j->>'info')::jsonb->>'images')::jsonb)->'ImageVoVmiInstanceId')::text AS vowide_vmiinstanceid
		FROM egiis.vapj AS g
		LEFT OUTER JOIN egiis.tvapj AS t ON g.pkey = t.pkey
		WHERE
            ((t.j->>'info')::jsonb->>'GLUE2EndpointInterfaceName')::text = 'OCCI' AND
			(COALESCE(TRIM(((t.j->>'info')::jsonb->>'GLUE2ComputingEndpointComputingServiceForeignKey')::text), '') <> '') AND
			(COALESCE(((g.j->>'info')::jsonb->>'SiteEndpointInProduction')::text, 'FALSE')::boolean IS DISTINCT FROM FALSE)
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

DROP TABLE IF EXISTS egiis.sitej CASCADE;
CREATE TABLE egiis.sitej (
	pkey TEXT NOT NULL PRIMARY KEY,
	j JSONB NOT NULL,
	h TEXT NOT NULL,
	lastseen TIMESTAMP DEFAULT NOW()
);
ALTER TABLE egiis.sitej OWNER TO appdb;
CREATE INDEX idx_egiis_sitej_info ON egiis.sitej USING gin (((j ->> 'info')::jsonb));
CREATE INDEX idx_egiis_sitej_lastseen ON egiis.sitej USING btree(lastseen);

CREATE OR REPLACE FUNCTION trfn_egiis_sitej_upsert() RETURNS TRIGGER AS
$$
BEGIN
	IF EXISTS (SELECT 1 FROM egiis.sitej WHERE pkey = NEW.pkey) THEN
		
		IF NEW.h = (SELECT h FROM egiis.sitej WHERE pkey = NEW.pkey) THEN
			-- RAISE NOTICE 'existing unmodded entry, updating lastseen';
			UPDATE egiis.sitej
				SET lastseen = NOW()
				WHERE pkey = NEW.pkey;
			RETURN NULL;
		ELSE
			-- RAISE NOTICE 'existing modded entry, updating data and lastseen';
			UPDATE egiis.sitej 
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
ALTER FUNCTION trfn_egiis_sitej_upsert() OWNER TO appdb;

DROP TRIGGER IF EXISTS rtr_egiis_sitej_10_upsert ON egiis.sitej;
CREATE TRIGGER rtr_egiis_sitej_10_upsert BEFORE INSERT ON egiis.sitej
FOR EACH ROW EXECUTE PROCEDURE trfn_egiis_sitej_upsert();

CREATE OR REPLACE VIEW __sites AS
 SELECT sites.pkey AS id,
    sites.name,
    sites.shortname,
    sites.officialname,
    sites.description,
    sites.portalurl,
    sites.homeurl,
    sites.contactemail,
    sites.contacttel,
    sites.alarmemail,
    sites.csirtemail,
    sites.giisurl,
    countries.id AS countryid,
    sites.countrycode,
    sites.country AS countryname,
    regions.id AS regionid,
    regions.name AS regionname,
    sites.tier,
    sites.subgrid,
    sites.roc,
    sites.prodinfrastructure AS productioninfrastructure,
    sites.certstatus AS certificationstatus,
    sites.timezone,
    sites.latitude,
    sites.longitude,
    sites.domainname,
    sites.siteip AS ip,
    uuid_generate_v5(uuid_namespace('ISO OID'::text), 'gocdb:sites:' || sites.name) AS guid,
    'gocdb'::text AS datasource,
    sites.createdon,
    'gocdb'::text AS createdby,
    sites.updatedon,
    'gocdb'::text AS updatedby,
    sites.deleted,
    sites.deletedon,
    sites.deletedby
   FROM gocdb.sites
     LEFT JOIN countries ON countries.isocode = sites.countrycode
     LEFT JOIN regions ON regions.id = countries.regionid
  WHERE sites.certstatus NOT IN ('Closed', 'Suspended');
ALTER VIEW __sites OWNER TO appdb;    

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
     LEFT JOIN countries ON countries.isocode = va_providers.country_code
     INNER JOIN gocdb.sites ON LOWER(gocdb.sites.name) = LOWER(gocdb.va_providers.sitename)
   WHERE gocdb.sites.certstatus NOT IN ('Closed', 'Suspended');

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
		  -- guid = uuid_generate_v5(uuid_namespace('ISO OID'::text), 'gocdb:sites:' || NEW.name),
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

ALTER TABLE gocdb.sites DROP COLUMN IF EXISTS guid;

DROP TRIGGER IF EXISTS rtr_gocdb_sites_10_upsert ON gocdb.sites;
CREATE TRIGGER rtr_gocdb_sites_10_upsert BEFORE INSERT ON gocdb.sites
FOR EACH ROW EXECUTE PROCEDURE trfn_gocdb_sites_upsert();

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

CREATE OR REPLACE FUNCTION process_site_argo_status(dat jsonb[]) RETURNS VOID
AS
$$
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
        IF srvgrp = 'eu.egi.cloud.vm-management.occi' THEN
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
$$ LANGUAGE plpgsql VOLATILE RETURNS NULL ON NULL INPUT;
ALTER FUNCTION process_site_argo_status(jsonb[]) OWNER TO appdb;

CREATE OR REPLACE FUNCTION process_site_argo_status() RETURNS VOID AS
$$
	SELECT process_site_argo_status(array_agg(j)) FROM egiis.argo WHERE lastseen = (SELECT MAX(lastseen) FROM egiis.argo);
$$ LANGUAGE SQL VOLATILE;
ALTER FUNCTION process_site_argo_status() OWNER TO appdb;

CREATE OR REPLACE FUNCTION process_site_downtimes(dat jsonb[]) RETURNS VOID
AS
$$
DECLARE j jsonb;
DECLARE dstart TIMESTAMP WITHOUT TIME ZONE;
DECLARE dend TIMESTAMP WITHOUT TIME ZONE;
DECLARE nowstart TIMESTAMP WITHOUT TIME ZONE;
DECLARE nowend TIMESTAMP WITHOUT TIME ZONE;
DECLARE epkey TEXT;
DECLARE dkey TEXT;
DECLARE active_dts TEXT[];
BEGIN
	active_dts := '{}'::TEXT[];
	UPDATE gocdb.va_providers SET service_downtime = 0::bit(2);
	
	FOREACH j IN ARRAY dat LOOP
		dkey := (j->>'info')::jsonb->>'DowntimePKey';
		dstart := ((j->>'info')::jsonb->>'DowntimeFormatedStartDate')::TIMESTAMP;
		dend := ((j->>'info')::jsonb->>'DowntimeFormatedEndDate')::TIMESTAMP;
		nowstart := (SELECT NOW() AT TIME ZONE 'UTC');
		nowend := (SELECT (NOW() AT TIME ZONE 'UTC') + '1 day'::INTERVAL);
		epkey := (j->>'info')::jsonb->>'SiteEndpointPKey';
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
$$ LANGUAGE plpgsql VOLATILE RETURNS NULL ON NULL INPUT;
ALTER FUNCTION process_site_downtimes(jsonb[]) OWNER TO appdb;

CREATE OR REPLACE FUNCTION process_site_downtimes() RETURNS VOID AS
$$
	SELECT process_site_downtimes(array_agg(j)) FROM egiis.downtimes WHERE lastseen = (SELECT MAX(lastseen) FROM egiis.downtimes);
$$ LANGUAGE SQL VOLATILE;
ALTER FUNCTION process_site_downtimes() OWNER TO appdb;

DROP TRIGGER IF EXISTS tr_gocdb_sites_99_create_uuid ON gocdb.sites;
DROP FUNCTION IF EXISTS gocdb.trfn_gocdb_sites_create_uuid();

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
  
DROP TABLE IF EXISTS egiis.vapj2; CREATE TABLE egiis.vapj2 AS SELECT * FROM egiis.vapj;
DROP TABLE IF EXISTS egiis.tvapj2; CREATE TABLE egiis.tvapj2 AS SELECT * FROM egiis.tvapj;
DROP TABLE IF EXISTS egiis.sitej2; CREATE TABLE egiis.sitej2 AS SELECT * FROM egiis.sitej;
DROP TABLE IF EXISTS egiis.downtimes2; CREATE TABLE egiis.downtimes2 AS SELECT * FROM egiis.downtimes;
DROP TABLE IF EXISTS egiis.argo2; CREATE TABLE egiis.argo2 AS SELECT * FROM egiis.argo;

CREATE OR REPLACE FUNCTION egiis.vapj_changed() RETURNS BOOL AS
$$
SELECT EXISTS (
SELECT t1.pkey 
FROM egiis.vapj AS t1
WHERE 
        (t1.pkey NOT IN (SELECT pkey FROM egiis.vapj2 WHERE lastseen = (SELECT MAX(lastseen) FROM egiis.vapj2)))
UNION ALL
SELECT t2.pkey 
FROM egiis.vapj2 AS t2
WHERE
        (t2.pkey NOT IN (SELECT pkey FROM egiis.vapj WHERE lastseen = (SELECT MAX(lastseen) FROM egiis.vapj)))
UNION ALL
SELECT t1.pkey 
FROM egiis.vapj AS t1
INNER JOIN egiis.vapj2 AS t2 ON t1.pkey = t2.pkey
WHERE t1.h <> t2.h
);
$$ LANGUAGE sql STABLE;
ALTER FUNCTION egiis.vapj_changed() OWNER TO appdb;

CREATE OR REPLACE FUNCTION egiis.tvapj_changed() RETURNS BOOL AS
$$
SELECT EXISTS (
SELECT t1.pkey 
FROM egiis.tvapj AS t1
WHERE 
        (t1.pkey NOT IN (SELECT pkey FROM egiis.tvapj2 WHERE lastseen = (SELECT MAX(lastseen) FROM egiis.tvapj2)))
UNION ALL
SELECT t2.pkey 
FROM egiis.tvapj2 AS t2
WHERE
        (t2.pkey NOT IN (SELECT pkey FROM egiis.tvapj WHERE lastseen = (SELECT MAX(lastseen) FROM egiis.tvapj)))
UNION ALL
SELECT t1.pkey 
FROM egiis.tvapj AS t1
INNER JOIN egiis.tvapj2 AS t2 ON t1.pkey = t2.pkey
WHERE t1.h <> t2.h
);
$$ LANGUAGE sql STABLE;
ALTER FUNCTION egiis.tvapj_changed() OWNER TO appdb;

CREATE OR REPLACE FUNCTION egiis.sitej_changed() RETURNS BOOL AS
$$
SELECT EXISTS (
SELECT t1.pkey 
FROM egiis.sitej AS t1
WHERE 
        (t1.pkey NOT IN (SELECT pkey FROM egiis.sitej2 WHERE lastseen = (SELECT MAX(lastseen) FROM egiis.sitej2)))
UNION ALL
SELECT t2.pkey 
FROM egiis.sitej2 AS t2
WHERE
        (t2.pkey NOT IN (SELECT pkey FROM egiis.sitej WHERE lastseen = (SELECT MAX(lastseen) FROM egiis.sitej)))
UNION ALL
SELECT t1.pkey 
FROM egiis.sitej AS t1
INNER JOIN egiis.sitej2 AS t2 ON t1.pkey = t2.pkey
WHERE t1.h <> t2.h
);
$$ LANGUAGE sql STABLE;
ALTER FUNCTION egiis.sitej_changed() OWNER TO appdb;

CREATE OR REPLACE FUNCTION egiis.sitej_changes() RETURNS INT[3] AS
$$
DECLARE ret int[3];
BEGIN
	ret[1] := (SELECT COUNT(DISTINCT pkey) FROM (
		SELECT t1.pkey 
		FROM egiis.sitej AS t1
		WHERE 
			(t1.pkey NOT IN (SELECT pkey FROM egiis.sitej2 WHERE lastseen = (SELECT MAX(lastseen) FROM egiis.sitej2)))
	) AS tins);
	ret[2] := (SELECT COUNT(DISTINCT pkey) FROM (
		SELECT t1.pkey 
		FROM egiis.sitej AS t1
		INNER JOIN egiis.sitej2 AS t2 ON t1.pkey = t2.pkey
		WHERE t1.h <> t2.h
	) AS tupd);
	ret[3] := (SELECT COUNT(DISTINCT pkey) FROM (
		SELECT t2.pkey 
		FROM egiis.sitej2 AS t2
		WHERE
			(t2.pkey NOT IN (SELECT pkey FROM egiis.sitej WHERE lastseen = (SELECT MAX(lastseen) FROM egiis.sitej)))
	) AS tdel);
	RETURN ret;
END;
$$ LANGUAGE plpgsql STABLE;
ALTER FUNCTION egiis.sitej_changes() OWNER TO appdb;

CREATE OR REPLACE FUNCTION egiis.downtimes_changed() RETURNS BOOL AS
$$
SELECT EXISTS (
SELECT t1.pkey 
FROM egiis.downtimes AS t1
WHERE 
        (t1.pkey NOT IN (SELECT pkey FROM egiis.downtimes2 WHERE lastseen = (SELECT MAX(lastseen) FROM egiis.downtimes2)))
UNION ALL
SELECT t2.pkey 
FROM egiis.downtimes2 AS t2
WHERE
        (t2.pkey NOT IN (SELECT pkey FROM egiis.downtimes WHERE lastseen = (SELECT MAX(lastseen) FROM egiis.downtimes)))
UNION ALL
SELECT t1.pkey 
FROM egiis.downtimes AS t1
INNER JOIN egiis.downtimes2 AS t2 ON t1.pkey = t2.pkey
WHERE t1.h <> t2.h
);
$$ LANGUAGE sql STABLE;
ALTER FUNCTION egiis.downtimes_changed() OWNER TO appdb;

CREATE OR REPLACE FUNCTION egiis.argo_changed() RETURNS BOOL AS
$$
SELECT EXISTS (
SELECT t1.pkey 
FROM egiis.argo AS t1
WHERE 
        (t1.pkey NOT IN (SELECT pkey FROM egiis.argo2 AS t2 WHERE t2.egroup = t1.egroup AND lastseen = (SELECT MAX(lastseen) FROM egiis.argo2)))
UNION ALL
SELECT t2.pkey 
FROM egiis.argo2 AS t2
WHERE
        (t2.pkey NOT IN (SELECT pkey FROM egiis.argo AS t1 WHERE t1.egroup = t2.egroup AND lastseen = (SELECT MAX(lastseen) FROM egiis.argo)))
UNION ALL
SELECT t1.pkey 
FROM egiis.argo AS t1
INNER JOIN egiis.argo2 AS t2 ON t1.pkey = t2.pkey AND t1.egroup = t2.egroup
WHERE t1.h <> t2.h
);
$$ LANGUAGE sql STABLE;
ALTER FUNCTION egiis.argo_changed() OWNER TO appdb;  

CREATE OR REPLACE FUNCTION refresh_sites(va_sync_scopes TEXT DEFAULT 'FedCloud', forced BOOL DEFAULT FALSE) RETURNS INT AS
$$ 
-- DECLARE deltime TEXT;
DECLARE scopes TEXT[];
DECLARE doSites BOOL;
DECLARE doVap BOOL;
DECLARE doDT BOOL;
DECLARE doArgo BOOL;
BEGIN
	-- check if imported data has changed
    IF NOT forced THEN
        doSites := egiis.sitej_changed();
        doVap := egiis.vapj_changed() OR egiis.tvapj_changed();
        doDT := egiis.downtimes_changed();
        doArgo := egiis.argo_changed();
        IF NOT (doSites OR doVap OR doDT OR doArgo) THEN
            RETURN 0;
        END IF;
    ELSE
    	doSites := TRUE;
        doVap := TRUE;
        doDT := TRUE;
        doArgo := TRUE;
    END IF;
    
	scopes := ('{' || COALESCE(va_sync_scopes, '') || '}')::text[];
--	deltime := '1 minute';

	IF doSites THEN
        TRUNCATE TABLE gocdb.site_contacts;  -- OBSOLETED

        -- mark sites that weren't seen during the last json sync from the infosys service as deleted (key missing, or old timestamp)
        UPDATE gocdb.sites
        SET 
            deleted = TRUE, 
            deletedon = NOW(),
            deletedby = 'gocdb'
        WHERE pkey IN (
            SELECT pkey 
                FROM egiis.sitej
                -- WHERE ((NOW() - lastseen)::INTERVAL > deltime::INTERVAL)
                WHERE lastseen < (SELECT MAX(lastseen) FROM egiis.sitej)
        ) OR pkey NOT IN (SELECT pkey FROM egiis.sitej);

        -- upsert json data into sites table
        INSERT INTO gocdb.sites
            (pkey, name, shortname, officialname, description, portalurl, homeurl, contactemail, contacttel, alarmemail, csirtemail, giisurl,
            countrycode, country, tier, subgrid, roc, prodinfrastructure, certstatus, timezone, latitude, longitude, domainname, siteip,
            deleted, deletedon, deletedby)
        SELECT
            g.pkey,
            ((g.j->>'info')::jsonb->>'SiteName')::text AS name,
            ((g.j->>'info')::jsonb->>'SiteShortName')::text AS shortname,
            ((g.j->>'info')::jsonb->>'SiteOfficialName')::text AS officialname,
            ((g.j->>'info')::jsonb->>'SiteDescription')::text AS description,
            ((g.j->>'info')::jsonb->>'SiteGocdbPortalUrl')::text AS portalurl,
            ((g.j->>'info')::jsonb->>'SiteHomeUrl')::text AS homeurl,
            NULL::text AS contactemail,
            NULL::text AS contacttel,
            NULL::text AS alarmemail,
            NULL::text AS csirtemail,		
            ((g.j->>'info')::jsonb->>'SiteGiisUrl')::text AS giisurl,
            ((g.j->>'info')::jsonb->>'SiteCountryCode')::text AS countrycode,
            ((g.j->>'info')::jsonb->>'SiteCountry')::text AS country,
            ((g.j->>'info')::jsonb->>'SiteTier')::text AS tier,
            ((g.j->>'info')::jsonb->>'SiteSubgrid')::text AS subgrid,
            ((g.j->>'info')::jsonb->>'SiteRoc')::text AS roc,
            ((g.j->>'info')::jsonb->>'SiteProdInfrastructure')::text AS prodinfrastructure,
            ((g.j->>'info')::jsonb->>'SiteCertStatus')::text AS certstatus,
            ((g.j->>'info')::jsonb->>'SiteTimezone')::text AS timezone,
            ((g.j->>'info')::jsonb->>'SiteLatitude')::text AS latitude,
            ((g.j->>'info')::jsonb->>'SiteLongitude')::text AS longtitude,
            ((g.j->>'info')::jsonb->>'SiteDomainname')::text AS domainname,
            NULL::text AS siteip,
            FALSE, NULL, NULL
        FROM egiis.sitej AS g
        -- WHERE (NOW() - g.lastseen)::INTERVAL <= deltime::INTERVAL;
        WHERE g.lastseen = (SELECT MAX(lastseen) FROM egiis.sitej);
    END IF;

	-- ******************
	-- VA PROVIDERS
	-- ******************
    
    IF doVap THEN
        -- ALTER TABLE gocdb.va_providers
            -- DISABLE TRIGGER tr_gocdb_va_providers_99_refresh_permissions;		

        -- remove entries that either
        -- 1) weren't seen during the last json sync from the infosys service (key missing, or old timestamp)
        -- 2) don't have at least one scope that matches the VA scopes given
        DELETE FROM gocdb.va_providers 
        WHERE pkey IN (
            SELECT pkey 
                FROM egiis.vapj
                -- WHERE ((NOW() - lastseen)::INTERVAL > deltime::INTERVAL)
                WHERE lastseen < (SELECT MAX(lastseen) FROM egiis.vapj)
        ) OR (
            pkey NOT IN (SELECT pkey FROM egiis.vapj)
        ) OR ( NOT (
            SELECT array_agg(s) && scopes
            FROM (SELECT jsonb_array_elements_text(((g.j->>'info')::jsonb->>'SiteEndpointScopes')::jsonb)::text AS s FROM egiis.vapj AS g WHERE g.pkey = va_providers.pkey) AS ts
        ));

        -- make sure any OS declared by the VA exists in our OSes table
        INSERT INTO oses (name)
            SELECT DISTINCT
                TRIM(((g.j->>'info')::jsonb->>'SiteEndpointHostOS')::text)
            FROM 
                egiis.vapj AS g
            WHERE 	
                (((g.j->>'info')::jsonb->>'SiteEndpointHostOS')::text IS DISTINCT FROM NULL) AND
                (TRIM(((g.j->>'info')::jsonb->>'SiteEndpointHostOS')::text) <> '') AND
                (LOWER(TRIM(((g.j->>'info')::jsonb->>'SiteEndpointHostOS')::text)) NOT IN (
                    SELECT LOWER(name) FROM oses
                ));

        INSERT INTO gocdb.va_providers
        SELECT 
            g.pkey,
            ((g.j->>'info')::jsonb->>'SiteEndpointHostname')::text AS hostname,
            ((g.j->>'info')::jsonb->>'SiteEndpointGocPortalUrl')::text AS gocdb_url,
            ((g.j->>'info')::jsonb->>'SiteEndpointHostDN')::text AS host_dn,
            ((g.j->>'info')::jsonb->>'SiteEndpointHostOS')::text AS host_os,
            ((g.j->>'info')::jsonb->>'SiteEndpointHostArch')::text AS host_arch,
            ((g.j->>'info')::jsonb->>'SiteEndpointBeta')::text::boolean AS beta,
            ((g.j->>'info')::jsonb->>'SiteEndpointServiceType')::text AS service_type,
            ((g.j->>'info')::jsonb->>'SiteEndpointHostIP')::text AS host_ip,
            ((g.j->>'info')::jsonb->>'SiteEndpointInProduction')::text::boolean AS in_production,
            ((g.j->>'info')::jsonb->>'SiteEndpointNodeMonitored')::text::boolean AS node_monitored,
            ((g.j->>'info')::jsonb->>'SiteName')::text AS sitename,
            ((g.j->>'info')::jsonb->>'SiteEndpointCountryName')::text AS country_name,
            ((g.j->>'info')::jsonb->>'SiteEndpointCountryCode')::text AS country_code,
            ((g.j->>'info')::jsonb->>'SiteEndpointRocName')::text AS roc_name,
            ((g.j->>'info')::jsonb->>'SiteEndpointUrl')::text AS url,
            ((t.j->>'info')::jsonb->>'GLUE2ComputingEndpointComputingServiceForeignKey')::text AS serviceid
        FROM 
            egiis.vapj AS g
        LEFT OUTER JOIN egiis.tvapj AS t ON t.pkey = g.pkey
        WHERE 
            -- ((NOW() - g.lastseen)::INTERVAL <= deltime::INTERVAL) AND
            (
                g.lastseen = (SELECT MAX(lastseen) FROM egiis.vapj)
            ) AND (
                SELECT array_agg(s) && scopes
                FROM (SELECT jsonb_array_elements_text(((g.j->>'info')::jsonb->>'SiteEndpointScopes')::jsonb)::text AS s) AS ts
            ) 
        ;
    END IF;
    
    IF doArgo THEN
    	PERFORM process_site_argo_status();
    END IF;
    IF doDT THEN
    	PERFORM process_site_downtimes();
    END IF;
	
	-- refresh all related materialized views
    IF doSites THEN
		REFRESH MATERIALIZED VIEW CONCURRENTLY sites;
    END IF;
    IF doVap OR doDT OR doArgo THEN
		REFRESH MATERIALIZED VIEW CONCURRENTLY va_providers;
		REFRESH MATERIALIZED VIEW CONCURRENTLY va_provider_endpoints;
		REFRESH MATERIALIZED VIEW CONCURRENTLY va_provider_images;
		REFRESH MATERIALIZED VIEW CONCURRENTLY va_provider_templates;
	END IF;
    IF doSites OR doVap THEN
		REFRESH MATERIALIZED VIEW CONCURRENTLY _actor_group_members;
    	REFRESH MATERIALIZED VIEW CONCURRENTLY _actor_group_members2;
    	REFRESH MATERIALIZED VIEW CONCURRENTLY permissions;
    END IF;

	IF doSites OR doVap THEN
		REFRESH MATERIALIZED VIEW CONCURRENTLY site_services_xml;
		REFRESH MATERIALIZED VIEW CONCURRENTLY site_service_images_xml;
    END IF;
	
	-- ALTER TABLE gocdb.va_providers
		-- ENABLE TRIGGER tr_gocdb_va_providers_99_refresh_permissions;
    RETURN (doSites::int<<0) | (doVap::int<<1) | (doDT::int<<2) | (doArgo::int<<3);
END;
$$ 
LANGUAGE plpgsql VOLATILE;
ALTER FUNCTION refresh_sites(text, bool) OWNER TO appdb;

TRUNCATE TABLE gocdb.sites CASCADE;
SELECT refresh_sites();

INSERT INTO version (major,minor,revision,notes)
       SELECT 8, 16, 0, E'Refactor va providers, based on new JSON information system data. Add base_mp_uri and strict_base_mp_uri to va_providers_to_xml_ext function'
       WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=16 AND revision=0);      

COMMIT;
