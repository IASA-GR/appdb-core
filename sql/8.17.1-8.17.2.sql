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
Previous version: 8.17.1
New version: 8.17.2
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

DROP FUNCTION public.group_hash(va_provider_templates);
DROP MATERIALIZED VIEW public.site_service_images_xml;
DROP MATERIALIZED VIEW public.site_services_xml;
DROP FUNCTION public.good_vmiinstanceid(va_provider_images);

-- Materialized View: public.va_provider_images

DROP MATERIALIZED VIEW public.va_provider_images;

CREATE MATERIALIZED VIEW public.va_provider_images AS 
 SELECT xx.id,
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
                     LEFT JOIN egiis.tvapj t ON g.pkey = t.pkey AND (g.lastseen - t.lastseen < '10 minutes')
                  WHERE (((t.j ->> 'info'::text)::jsonb) ->> 'GLUE2EndpointInterfaceName'::text) = 'OCCI'::text 
                        -- AND COALESCE(btrim(((t.j ->> 'info'::text)::jsonb) ->> 'GLUE2ComputingEndpointComputingServiceForeignKey'::text), ''::text) <> ''::text 
                        AND COALESCE(((g.j ->> 'info'::text)::jsonb) ->> 'SiteEndpointInProduction'::text, 'FALSE'::text)::boolean IS DISTINCT FROM false
                ) x) xx
WITH DATA;

ALTER TABLE public.va_provider_images
  OWNER TO appdb;

-- Index: public.idx_va_provider_images_id

-- DROP INDEX public.idx_va_provider_images_id;

CREATE UNIQUE INDEX idx_va_provider_images_id
  ON public.va_provider_images
  USING btree
  (id);

-- Index: public.idx_va_provider_images_va_provider_id

-- DROP INDEX public.idx_va_provider_images_va_provider_id;

CREATE INDEX idx_va_provider_images_va_provider_id
  ON public.va_provider_images
  USING btree
  (va_provider_id COLLATE pg_catalog."default");

-- Index: public.idx_va_provider_images_va_provider_id_textops

-- DROP INDEX public.idx_va_provider_images_va_provider_id_textops;

CREATE INDEX idx_va_provider_images_va_provider_id_textops
  ON public.va_provider_images
  USING btree
  (va_provider_id COLLATE pg_catalog."default" text_pattern_ops);

-- Index: public.idx_va_provider_images_va_provider_id_trgmops

-- DROP INDEX public.idx_va_provider_images_va_provider_id_trgmops;

CREATE INDEX idx_va_provider_images_va_provider_id_trgmops
  ON public.va_provider_images
  USING gin
  (va_provider_id COLLATE pg_catalog."default" gin_trgm_ops);

-- Index: public.idx_va_provider_images_vmiinstanceid

-- DROP INDEX public.idx_va_provider_images_vmiinstanceid;

CREATE INDEX idx_va_provider_images_vmiinstanceid
  ON public.va_provider_images
  USING btree
  (vmiinstanceid);

-- Index: public.idx_va_provider_images_vowide_vmiinstanceid

-- DROP INDEX public.idx_va_provider_images_vowide_vmiinstanceid;

CREATE INDEX idx_va_provider_images_vowide_vmiinstanceid
  ON public.va_provider_images
  USING btree
  (vowide_vmiinstanceid);

-- Materialized View: public.va_provider_endpoints

DROP MATERIALIZED VIEW public.va_provider_endpoints;

CREATE MATERIALIZED VIEW public.va_provider_endpoints AS 
 SELECT nextval('va_provider_endpoints_id_seq'::regclass) AS id,
    t.pkey AS va_provider_id,
    ((t.j ->> 'info'::text)::jsonb) ->> 'GLUE2EndpointURL'::text AS endpoint_url,
    ((t.j ->> 'info'::text)::jsonb) ->> 'GLUE2EndpointImplementor'::text AS deployment_type
   FROM egiis.vapj g
     LEFT JOIN egiis.tvapj t ON g.pkey = t.pkey AND (g.lastseen - t.lastseen < '10 minutes')
  WHERE (((t.j ->> 'info'::text)::jsonb) ->> 'GLUE2EndpointInterfaceName'::text) = 'OCCI'::text
WITH DATA;

ALTER TABLE public.va_provider_endpoints
  OWNER TO appdb;

-- Index: public.idx_va_provider_endpoints_id

-- DROP INDEX public.idx_va_provider_endpoints_id;

CREATE UNIQUE INDEX idx_va_provider_endpoints_id
  ON public.va_provider_endpoints
  USING btree
  (id);

-- Index: public.idx_va_provider_endpoints_va_provider_id

-- DROP INDEX public.idx_va_provider_endpoints_va_provider_id;

CREATE INDEX idx_va_provider_endpoints_va_provider_id
  ON public.va_provider_endpoints
  USING btree
  (va_provider_id COLLATE pg_catalog."default");

-- Index: public.idx_va_provider_endpoints_va_provider_id_textops

-- DROP INDEX public.idx_va_provider_endpoints_va_provider_id_textops;

CREATE INDEX idx_va_provider_endpoints_va_provider_id_textops
  ON public.va_provider_endpoints
  USING btree
  (va_provider_id COLLATE pg_catalog."default" text_pattern_ops);

-- Index: public.idx_va_provider_endpoints_va_provider_id_trgmops

-- DROP INDEX public.idx_va_provider_endpoints_va_provider_id_trgmops;

CREATE INDEX idx_va_provider_endpoints_va_provider_id_trgmops
  ON public.va_provider_endpoints
  USING gin
  (va_provider_id COLLATE pg_catalog."default" gin_trgm_ops);

-- Materialized View: public.va_provider_templates

DROP MATERIALIZED VIEW public.va_provider_templates;

CREATE MATERIALIZED VIEW public.va_provider_templates AS 
 SELECT nextval('va_provider_templates_id_seq'::regclass) AS id,
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
     LEFT JOIN egiis.tvapj t ON t.pkey = g.pkey AND (g.lastseen - t.lastseen < '10 minutes')
  WHERE (((t.j ->> 'info'::text)::jsonb) ->> 'GLUE2EndpointInterfaceName'::text) = 'OCCI'::text AND COALESCE(btrim(((t.j ->> 'info'::text)::jsonb) ->> 'GLUE2ComputingEndpointComputingServiceForeignKey'::text), ''::text) <> ''::text AND COALESCE(((g.j ->> 'info'::text)::jsonb) ->> 'SiteEndpointInProduction'::text, 'FALSE'::text)::boolean IS DISTINCT FROM false
WITH DATA;

ALTER TABLE public.va_provider_templates
  OWNER TO appdb;

-- Index: public.idx_va_provider_templates_id

-- DROP INDEX public.idx_va_provider_templates_id;

CREATE UNIQUE INDEX idx_va_provider_templates_id
  ON public.va_provider_templates
  USING btree
  (id);

-- Index: public.idx_va_provider_templates_va_provider_id_textops

-- DROP INDEX public.idx_va_provider_templates_va_provider_id_textops;

CREATE INDEX idx_va_provider_templates_va_provider_id_textops
  ON public.va_provider_templates
  USING btree
  (va_provider_id COLLATE pg_catalog."default" text_pattern_ops);

-- Index: public.idx_va_provider_templates_va_provider_id_trgmops

-- DROP INDEX public.idx_va_provider_templates_va_provider_id_trgmops;

CREATE INDEX idx_va_provider_templates_va_provider_id_trgmops
  ON public.va_provider_templates
  USING gin
  (va_provider_id COLLATE pg_catalog."default" gin_trgm_ops);

-- Function: public.good_vmiinstanceid(va_provider_images)

CREATE OR REPLACE FUNCTION public.good_vmiinstanceid(va_provider_images)
  RETURNS integer AS
$BODY$
--      SELECT get_good_vmiinstanceid($1.vmiinstanceid)
        SELECT CASE WHEN goodid IS NULL THEN $1.vmiinstanceid ELSE goodid END FROM (
                        SELECT max(t1.id) as goodid FROM vmiinstances AS t1
                        INNER JOIN vmiinstances AS t2 ON t1.checksum = t2.checksum AND t1.guid = t2.guid AND t2.id = $1.vmiinstanceid
                        INNER JOIN vapplists ON t1.id = vapplists.vmiinstanceid
                        INNER JOIN vapp_versions ON vapplists.vappversionid = vapp_versions.id 
                        WHERE vapp_versions.published
        ) AS t
$BODY$
  LANGUAGE sql STABLE
  COST 100;
ALTER FUNCTION public.good_vmiinstanceid(va_provider_images)
  OWNER TO appdb;

-- Materialized View: public.site_services_xml



CREATE MATERIALIZED VIEW public.site_services_xml AS 
 SELECT __va_providers.id,
    __va_providers.sitename,
    XMLELEMENT(NAME "site:service", XMLATTRIBUTES('occi' AS type, __va_providers.id AS id, __va_providers.hostname AS host, count(DISTINCT good_vmiinstanceid(va_provider_images.*)) AS instances, __va_providers.beta AS beta, __va_providers.in_production AS in_production, __va_providers.service_downtime::integer AS service_downtime, __va_providers.service_status AS service_status, __va_providers.service_status_date AS service_status_date), xmlagg(XMLELEMENT(NAME "siteservice:image", XMLATTRIBUTES(va_provider_images.vmiinstanceid AS id, good_vmiinstanceid(va_provider_images.*) AS goodid)))) AS x
   FROM __va_providers
     LEFT JOIN va_provider_images ON va_provider_images.va_provider_id = __va_providers.id AND (va_provider_images.vmiinstanceid IN ( SELECT __vaviews.vmiinstanceid
           FROM __vaviews))
  GROUP BY __va_providers.id, __va_providers.hostname, __va_providers.beta, __va_providers.in_production, __va_providers.service_downtime, __va_providers.sitename, __va_providers.service_status, __va_providers.service_status_date
WITH DATA;

ALTER TABLE public.site_services_xml
  OWNER TO appdb;

-- Index: public.idx_site_services_xml_id

-- DROP INDEX public.idx_site_services_xml_id;

CREATE UNIQUE INDEX idx_site_services_xml_id
  ON public.site_services_xml
  USING btree
  (id COLLATE pg_catalog."default");

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

-- Materialized View: public.site_service_images_xml

CREATE MATERIALIZED VIEW public.site_service_images_xml AS 
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
  GROUP BY siteimages.va_provider_id
WITH DATA;

ALTER TABLE public.site_service_images_xml
  OWNER TO appdb;

-- Index: public.idx_site_service_images_xml_id

-- DROP INDEX public.idx_site_service_images_xml_id;

CREATE UNIQUE INDEX idx_site_service_images_xml_id
  ON public.site_service_images_xml
  USING btree
  (va_provider_id COLLATE pg_catalog."default");

-- Function: public.group_hash(va_provider_templates)

CREATE OR REPLACE FUNCTION public.group_hash(v va_provider_templates)
  RETURNS text AS
$BODY$
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
$BODY$
  LANGUAGE sql STABLE
  COST 100;
ALTER FUNCTION public.group_hash(va_provider_templates)
  OWNER TO appdb;


INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 17, 2, E'Bugfix: do not show outdated VA provider images, endpoints and templates'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=17 AND revision=2);

COMMIT;
