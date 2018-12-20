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
Previous version: 8.22.10
New version: 8.23.0
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

CREATE SEQUENCE IF NOT EXISTS va_provider_managers_id_seq;
ALTER SEQUENCE va_provider_managers_id_seq OWNER TO appdb;

DROP FUNCTION public.group_hash(va_provider_templates);
DROP MATERIALIZED VIEW public.va_provider_templates;

DROP MATERIALIZED VIEW site_service_images_xml;   
DROP MATERIALIZED VIEW site_services_xml;  
DROP FUNCTION public.good_vmiinstanceid(va_provider_images);
DROP MATERIALIZED VIEW public.va_provider_images;
DROP MATERIALIZED VIEW public.va_provider_managers;
CREATE MATERIALIZED VIEW public.va_provider_managers
AS
SELECT 
	q.id,
	q.pkey,
	q.va_provider_id,
	q.product_name,
	q.product_version,
	hypervisors.value AS hypervisor,
	q.hypervisor_version
FROM (
	SELECT nextval('va_provider_managers_id_seq'::regclass) AS id,
		t.pkey AS va_provider_id,
		jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'managers'::text)::jsonb) ->> 'GLUE2ManagerID'::text AS pkey,
		jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'managers'::text)::jsonb) ->> 'GLUE2ManagerProductName'::text AS product_name,
		jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'managers'::text)::jsonb) ->> 'GLUE2ManagerProductVersion'::text AS product_version,
		jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'managers'::text)::jsonb) ->> 'GLUE2CloudComputingManagerHypervisorName'::text AS hypervisor,	
		jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'managers'::text)::jsonb) ->> 'GLUE2CloudComputingManagerHypervisorVersion'::text AS hypervisor_version	
	FROM egiis.vapj g
	LEFT JOIN egiis.tvapj t ON g.pkey = t.pkey
	WHERE LOWER(((t.j ->> 'info'::text)::jsonb) ->> 'GLUE2EndpointInterfaceName'::text) ~ '(occi|openstack|opennebula|synnefo|wnodes|cloudstack|vcloud)'::text
) AS q
LEFT JOIN hypervisors ON LOWER(q.hypervisor) ~ LOWER(hypervisors.name)
WITH DATA;
ALTER MATERIALIZED VIEW va_provider_managers OWNER TO appdb;

CREATE UNIQUE INDEX idx_va_provider_managers_id
    ON public.va_provider_managers USING btree
    (id);

CREATE SEQUENCE IF NOT EXISTS va_provider_shares_id_seq;
ALTER SEQUENCE va_provider_shares_id_seq OWNER TO appdb;

DROP MATERIALIZED VIEW public.va_provider_shares;
CREATE MATERIALIZED VIEW public.va_provider_shares
AS
SELECT 
	q.id,
	q.pkey,
	q.va_provider_id,
	q.vo,
	(SELECT id FROM vos WHERE LOWER(vos.name) = LOWER(q.vo) AND NOT vos.deleted LIMIT 1) AS void
FROM (
	SELECT nextval('va_provider_shares_id_seq'::regclass) AS id,
		t.pkey AS va_provider_id,
		jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'shares'::text)::jsonb) ->> 'GLUE2ShareID'::text AS pkey,
		jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'shares'::text)::jsonb) ->> 'ShareVO'::text AS vo
	FROM egiis.vapj g
	LEFT JOIN egiis.tvapj t ON g.pkey = t.pkey
	WHERE LOWER(((t.j ->> 'info'::text)::jsonb) ->> 'GLUE2EndpointInterfaceName'::text) ~ '(occi|openstack|opennebula|synnefo|wnodes|cloudstack|vcloud)'::text
) AS q
WITH DATA;
ALTER MATERIALIZED VIEW va_provider_shares OWNER TO appdb;

CREATE UNIQUE INDEX idx_va_provider_shares_id
    ON public.va_provider_shares USING btree
    (id);

CREATE MATERIALIZED VIEW public.va_provider_templates
TABLESPACE pg_default
AS
SELECT
	q.id,
	q.va_provider_id,
	q.pkey,
	q.resource_name,
	q.memsize,
	q.logical_cpus,
	q.physical_cpus,
	CASE WHEN q.logical_cpus > '1' THEN
		'multicpu-singlecore'
	ELSE
		'singlecpu-singlecore'
	END AS cpu_multiplicity,
	q.resource_manager,
	q.computing_manager,
	q.os_family,
	q.connectivity_in,
	q.connectivity_out,
	q.connectivity_ports_in,
	q.connectivity_ports_out,
	q.connectivity_info,
	q.cpu_model,
	q.resource_id,
	q.disc_size,
	q.tmp_storage_size,
	(SELECT id FROM vos WHERE LOWER(vos.name) = LOWER(q.vo) AND NOT vos.deleted LIMIT 1) AS void,
	va_provider_shares.id AS shareid,
	va_provider_managers.id AS managerid
FROM (
	SELECT nextval('va_provider_templates_id_seq'::regclass) AS id,
	g.pkey AS va_provider_id,
	jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'templates'::text)::jsonb) ->> 'GLUE2CloudComputingInstanceTypeTemplateID'::text AS pkey,	
	jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'templates'::text)::jsonb) ->> 'GLUE2EntityName'::text AS resource_name,
	jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'templates'::text)::jsonb) ->> 'GLUE2CloudComputingInstanceTypeRAM'::text AS memsize,
	jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'templates'::text)::jsonb) ->> 'GLUE2CloudComputingInstanceTypeCPU'::text AS logical_cpus,
	jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'templates'::text)::jsonb) ->> 'GLUE2CloudComputingInstanceTypeCPU'::text AS physical_cpus,
	jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'templates'::text)::jsonb) ->> 'GLUE2CloudComputingInstanceTypeCloudComputingManagerForeignKey'::text AS resource_manager,
	jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'templates'::text)::jsonb) ->> 'GLUE2CloudComputingInstanceTypeCloudComputingManagerForeignKey'::text AS computing_manager,
	NULL AS os_family,
	jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'templates'::text)::jsonb) ->> 'GLUE2CloudComputingInstanceTypeNetworkIn'::text AS connectivity_in,
	jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'templates'::text)::jsonb) ->> 'GLUE2CloudComputingInstanceTypeNetworkOut'::text AS connectivity_out,
	jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'templates'::text)::jsonb) ->> 'GLUE2CloudComputingInstanceTypeNetworkPortsIn'::text AS connectivity_ports_in,
	jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'templates'::text)::jsonb) ->> 'GLUE2CloudComputingInstanceTypeNetworkPortsOut'::text AS connectivity_ports_out,
	jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'templates'::text)::jsonb) ->> 'GLUE2CloudComputingInstanceTypeNetworkInfo'::text AS connectivity_info,
	'virtual model' AS cpu_model,
	jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'templates'::text)::jsonb) ->> 'GLUE2ResourceID'::text AS resource_id,
	jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'templates'::text)::jsonb) ->> 'GLUE2CloudComputingInstanceTypeDisk'::text AS disc_size,
	jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'templates'::text)::jsonb) ->> 'GLUE2CloudComputingInstanceTypeEphemeralStorage'::text AS tmp_storage_size,	
	jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'templates'::text)::jsonb) ->> 'InstanceTypeVO'::text AS vo,
	jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'templates'::text)::jsonb) ->> 'ManagerName'::text AS manager_name
	FROM egiis.vapj g
	     LEFT JOIN egiis.tvapj t ON t.pkey = g.pkey
	WHERE LOWER(((t.j ->> 'info'::text)::jsonb) ->> 'GLUE2EndpointInterfaceName'::text) ~ '(occi|openstack|opennebula|synnefo|wnodes|cloudstack|vcloud)'::text AND 
		COALESCE(btrim(((t.j ->> 'info'::text)::jsonb) ->> 'GLUE2EndpointServiceForeignKey'::text), ''::text) <> ''::text AND 
		COALESCE(((g.j ->> 'info'::text)::jsonb) ->> 'SiteEndpointInProduction'::text, 'FALSE'::text)::boolean IS DISTINCT FROM false
) AS q
LEFT OUTER JOIN va_provider_managers ON va_provider_managers.va_provider_id = q.va_provider_id AND LOWER(va_provider_managers.product_name) = LOWER(q.manager_name)
LEFT OUTER JOIN va_provider_shares ON va_provider_shares.va_provider_id = q.va_provider_id AND LOWER(va_provider_shares.vo) = LOWER(q.vo)
WITH DATA;

ALTER TABLE public.va_provider_templates
    OWNER TO appdb;


CREATE UNIQUE INDEX idx_va_provider_templates_id
    ON public.va_provider_templates USING btree
    (id)
    TABLESPACE pg_default;
CREATE INDEX idx_va_provider_templates_va_provider_id_textops
    ON public.va_provider_templates USING btree
    (va_provider_id COLLATE pg_catalog."default" text_pattern_ops)
    TABLESPACE pg_default;
CREATE INDEX idx_va_provider_templates_va_provider_id_trgmops
    ON public.va_provider_templates USING gin
    (va_provider_id COLLATE pg_catalog."default" gin_trgm_ops)
    TABLESPACE pg_default;

CREATE OR REPLACE FUNCTION public.group_hash(
	v va_provider_templates)
    RETURNS text
    LANGUAGE 'sql'

    COST 100
    STABLE 
AS $BODY$
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
$BODY$;

ALTER FUNCTION public.group_hash(va_provider_templates)
    OWNER TO appdb;	
   
DROP MATERIALIZED VIEW public.va_provider_endpoints;

CREATE MATERIALIZED VIEW public.va_provider_endpoints
TABLESPACE pg_default
AS
SELECT nextval('va_provider_endpoints_id_seq'::regclass) AS id,
	t.pkey AS va_provider_id,
	((t.j ->> 'info'::text)::jsonb) ->> 'GLUE2EndpointURL'::text AS endpoint_url,
	CASE 
		WHEN LOWER(((t.j ->> 'info'::text)::jsonb) ->> 'GLUE2EndpointImplementor'::text) ~ 'occi' THEN 'occi'
		WHEN LOWER(((t.j ->> 'info'::text)::jsonb) ->> 'GLUE2EndpointImplementor'::text) ~ 'openstack' THEN 'openstack'
		WHEN LOWER(((t.j ->> 'info'::text)::jsonb) ->> 'GLUE2EndpointImplementor'::text) ~ 'opennebula' THEN 'opennebula'
		WHEN LOWER(((t.j ->> 'info'::text)::jsonb) ->> 'GLUE2EndpointImplementor'::text) ~ 'synnefo' THEN 'synnefo'
		WHEN LOWER(((t.j ->> 'info'::text)::jsonb) ->> 'GLUE2EndpointImplementor'::text) ~ 'wnodes' THEN 'wnodes'
		WHEN LOWER(((t.j ->> 'info'::text)::jsonb) ->> 'GLUE2EndpointImplementor'::text) ~ 'cloudstack' THEN 'cloudstack'
		WHEN LOWER(((t.j ->> 'info'::text)::jsonb) ->> 'GLUE2EndpointImplementor'::text) ~ 'vcloud' THEN 'vcloud'
		ELSE NULL
	END AS deployment_type	
   	FROM egiis.vapj g
    LEFT JOIN egiis.tvapj t ON g.pkey = t.pkey
WHERE LOWER(((t.j ->> 'info'::text)::jsonb) ->> 'GLUE2EndpointInterfaceName'::text) ~ '(occi|openstack|opennebula|synnefo|wnodes|cloudstack|vcloud)'::text
WITH DATA;

ALTER TABLE public.va_provider_endpoints
    OWNER TO appdb;


CREATE UNIQUE INDEX idx_va_provider_endpoints_id
    ON public.va_provider_endpoints USING btree
    (id)
    TABLESPACE pg_default;
CREATE INDEX idx_va_provider_endpoints_va_provider_id
    ON public.va_provider_endpoints USING btree
    (va_provider_id COLLATE pg_catalog."default")
    TABLESPACE pg_default;
CREATE INDEX idx_va_provider_endpoints_va_provider_id_textops
    ON public.va_provider_endpoints USING btree
    (va_provider_id COLLATE pg_catalog."default" text_pattern_ops)
    TABLESPACE pg_default;
CREATE INDEX idx_va_provider_endpoints_va_provider_id_trgmops
    ON public.va_provider_endpoints USING gin
    (va_provider_id COLLATE pg_catalog."default" gin_trgm_ops)
    TABLESPACE pg_default;	

CREATE MATERIALIZED VIEW public.va_provider_images
TABLESPACE pg_default
AS
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
        END AS vowide_vmiinstanceid,
        xx.shareid,
        xx.managerid
   FROM (
   		SELECT x.id,
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
            END AS vowide_vmiinstanceid,
            -- x.vo,
            -- (SELECT id FROM vos WHERE LOWER(vos.name) = LOWER(x.vo) AND NOT vos.deleted LIMIT 1) AS void,
            s.id as shareid,
            m.id as managerid
       FROM ( 
       		SELECT nextval('va_provider_images_id_seq'::regclass) AS id,
                g.pkey AS va_provider_id,
                (jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'images'::text)::jsonb) -> 'ImageVmiInstanceId'::text)::text AS vmiinstanceid,
                jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'images'::text)::jsonb) ->> 'ImageContentType'::text AS content_type,
                jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'images'::text)::jsonb) ->> 'GLUE2CloudComputingImageTemplateID'::text AS va_provider_image_id,
                jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'images'::text)::jsonb) ->> 'GLUE2CloudComputingImageMarketPlaceURL'::text AS mp_uri,
                (jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'images'::text)::jsonb) -> 'ImageVoVmiInstanceId'::text)::text AS vowide_vmiinstanceid,
                jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'images'::text)::jsonb) ->> 'ImageVoVmiInstanceVO'::text AS vo,
                jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'images'::text)::jsonb) ->> 'ShareVO'::text AS vo2,
				jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'images'::text)::jsonb) ->> 'ManagerName'::text AS manager_name
               FROM egiis.vapj g
                 LEFT JOIN egiis.tvapj t ON g.pkey = t.pkey
              WHERE LOWER(((t.j ->> 'info'::text)::jsonb) ->> 'GLUE2EndpointInterfaceName'::text) ~ '(occi|openstack|opennebula|synnefo|wnodes|cloudstack|vcloud)'::text AND 
              	COALESCE(btrim(((t.j ->> 'info'::text)::jsonb) ->> 'GLUE2EndpointServiceForeignKey'::text), ''::text) <> ''::text AND 
              	COALESCE(((g.j ->> 'info'::text)::jsonb) ->> 'SiteEndpointInProduction'::text, 'FALSE'::text)::boolean IS DISTINCT FROM false
    	) x
    	LEFT JOIN va_provider_managers AS m ON m.va_provider_id = x.va_provider_id AND LOWER(m.product_name) = LOWER(x.manager_name)
    	LEFT JOIN va_provider_shares AS s ON s.va_provider_id = x.va_provider_id AND CASE WHEN COALESCE(btrim(x.vo), '') <> '' THEN LOWER(s.vo) = LOWER(x.vo) ELSE LOWER(s.vo) = LOWER(x.vo2) END
	) xx
WITH DATA;

ALTER TABLE public.va_provider_images
    OWNER TO appdb;


CREATE UNIQUE INDEX idx_va_provider_images_id
    ON public.va_provider_images USING btree
    (id)
    TABLESPACE pg_default;
CREATE INDEX idx_va_provider_images_va_provider_id
    ON public.va_provider_images USING btree
    (va_provider_id COLLATE pg_catalog."default")
    TABLESPACE pg_default;
CREATE INDEX idx_va_provider_images_va_provider_id_textops
    ON public.va_provider_images USING btree
    (va_provider_id COLLATE pg_catalog."default" text_pattern_ops)
    TABLESPACE pg_default;
CREATE INDEX idx_va_provider_images_va_provider_id_trgmops
    ON public.va_provider_images USING gin
    (va_provider_id COLLATE pg_catalog."default" gin_trgm_ops)
    TABLESPACE pg_default;
CREATE INDEX idx_va_provider_images_vmiinstanceid
    ON public.va_provider_images USING btree
    (vmiinstanceid)
    TABLESPACE pg_default;
CREATE INDEX idx_va_provider_images_vowide_vmiinstanceid
    ON public.va_provider_images USING btree
    (vowide_vmiinstanceid)
    TABLESPACE pg_default;	
   
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
    XMLELEMENT(NAME "site:service", 
    XMLATTRIBUTES(
    	-- 'occi' AS type,
    	(SELECT LOWER(deployment_type) FROM va_provider_endpoints WHERE va_provider_endpoints.va_provider_id = __va_providers.id) AS type, 
	    __va_providers.id AS id, 
	    __va_providers.hostname AS host, 
	    COUNT(DISTINCT good_vmiinstanceid(va_provider_images.*)) AS instances, 
	    __va_providers.beta AS beta, 
	    __va_providers.in_production AS in_production, 
	    __va_providers.service_downtime::integer AS service_downtime,
	    __va_providers.service_status AS service_status, 
	    __va_providers.service_status_date AS service_status_date
    ), xmlagg(XMLELEMENT(NAME "siteservice:image", XMLATTRIBUTES(va_provider_images.vmiinstanceid AS id, good_vmiinstanceid(va_provider_images.*) AS goodid)))) AS x
   FROM __va_providers
     LEFT JOIN va_provider_images ON va_provider_images.va_provider_id = __va_providers.id AND (va_provider_images.vmiinstanceid IN ( SELECT __vaviews.vmiinstanceid
           FROM __vaviews))     
  GROUP BY __va_providers.id, __va_providers.hostname, __va_providers.beta, __va_providers.in_production, __va_providers.service_downtime, __va_providers.sitename, __va_providers.service_status, __va_providers.service_status_date
WITH DATA;

-- View indexes:
CREATE UNIQUE INDEX idx_site_services_xml_id ON public.site_services_xml USING btree (id);
CREATE INDEX idx_site_services_xml_sitename ON public.site_services_xml USING btree (sitename);
CREATE INDEX idx_site_services_xml_sitename_textops ON public.site_services_xml USING btree (sitename text_pattern_ops);
CREATE INDEX idx_site_services_xml_sitename_trgmops ON public.site_services_xml USING gin (sitename gin_trgm_ops);

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
				-- 'occi' AS type,
				(SELECT LOWER(deployment_type) FROM va_provider_endpoints WHERE va_provider_endpoints.va_provider_id = va_providers.id) AS type, 
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
	GROUP BY va_providers.id, hostname, va_providers.id, hostname, beta, in_production
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
    	-- 'occi' as type,
    	(SELECT LOWER(deployment_type) FROM va_provider_endpoints WHERE va_provider_endpoints.va_provider_id = va_providers.id) AS type, 
    	va_providers.id as id , hostname as host, va_providers.beta as beta, va_providers.in_production as in_production, va_providers.service_downtime::int as service_downtime, va_providers.service_status, va_providers.service_status_date, va_providers.node_monitored as monitored, va_providers.ngi as ngi
    ),
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
		xmlelement(name "vo:vo",
		 	XMLATTRIBUTES((SELECT void FROM va_provider_shares s WHERE s.id = va_provider_templates.shareid) AS id),
			(SELECT vo FROM va_provider_shares s WHERE s.id = va_provider_templates.shareid)
		),
		xmlelement(name "virtualization:hypervisor",
		 	XMLATTRIBUTES(COALESCE((SELECT id FROM hypervisors WHERE value = (SELECT hypervisor FROM va_provider_managers m WHERE m.id = va_provider_templates.managerid)), 0) AS id),  
		 	(SELECT COALESCE(hypervisor::text, 'unknown') FROM va_provider_managers m WHERE m.id = va_provider_templates.managerid)
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
		xmlelement(name "provider_template:connectivity_ports_in", connectivity_ports_in),
		xmlelement(name "provider_template:connectivity_ports_out", connectivity_ports_out),
		xmlelement(name "provider_template:connectivity_info", connectivity_info),
		xmlelement(name "provider_template:cpu_model", cpu_model),
		xmlelement(name "provider_template:disc_size", disc_size),
		xmlelement(name "provider_template:tmp_storage_size", tmp_storage_size),
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
$function$
;

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
			service_status AS service_status,
			service_status_date AS service_status_date
		),
		xmlelement(name "provider:name", sitename),
		xmlelement(name "provider:shares",
			(SELECT 
				xmlagg(
					xmlelement(name "vo:vo", XMLATTRIBUTES(s.void AS id), s.vo)
					-- vo_to_xml(s.void)
				)
			FROM va_provider_shares s
			WHERE s.va_provider_id = mid
			)
		)
	)
FROM
	va_providers
WHERE id = mid;
END;
$function$
;

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
			service_downtime::int AS service_downtime,
			service_status AS service_status,
			service_status_date AS service_status_date
		),
		xmlelement(name "provider:name", sitename),
		xmlelement(name "provider:shares",
			(SELECT 
				xmlagg(
					xmlelement(name "vo:vo", XMLATTRIBUTES(s.void AS id), s.vo)
					-- vo_to_xml(s.void)
				)
			FROM va_provider_shares s
			WHERE s.va_provider_id = mid
			)
		),
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
				 xmlelement(name "vo:vo",
				 	XMLATTRIBUTES((SELECT void FROM va_provider_shares s WHERE s.id = va_provider_templates.shareid) AS id),
				 	(SELECT vo FROM va_provider_shares s WHERE s.id = va_provider_templates.shareid)
				 ),
				 xmlelement(name "virtualization:hypervisor",
				 	XMLATTRIBUTES(COALESCE((SELECT id FROM hypervisors WHERE value = (SELECT hypervisor FROM va_provider_managers m WHERE m.id = va_provider_templates.managerid)), 0) AS id),  
				 	(SELECT COALESCE(hypervisor::text, 'unknown') FROM va_provider_managers m WHERE m.id = va_provider_templates.managerid)
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
				 xmlelement(name "provider_template:connectivity_ports_in", connectivity_ports_in),
				 xmlelement(name "provider_template:connectivity_ports_out", connectivity_ports_out),
				 xmlelement(name "provider_template:connectivity_info", connectivity_info),
				 xmlelement(name "provider_template:cpu_model", cpu_model),
				 xmlelement(name "provider_template:disc_size", disc_size),
				 xmlelement(name "provider_template:tmp_storage_size", tmp_storage_size),
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
						(SELECT COALESCE(hypervisor::text, 'unknown') FROM va_provider_managers m WHERE m.id = va_provider_images.managerid) AS hypervisor,
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
			--LEFT OUTER JOIN vowide_image_list_images ON vowide_image_list_images.id = va_provider_images.vowide_vmiinstanceid
			--LEFT OUTER JOIN vowide_image_lists ON vowide_image_lists.id = vowide_image_list_images.vowide_image_list_id
			LEFT OUTER JOIN vos ON vos.id = (SELECT void FROM va_provider_shares WHERE id = va_provider_images.shareid) --vowide_image_lists.void			
			WHERE va_provider_id = va_providers.id /*AND ((
				vowide_image_lists.state IN ('published'::e_vowide_image_state, 'obsolete'::e_vowide_image_state)
			) OR (
				vowide_image_lists.state IS NULL
			)) */ 
			
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
$function$
;

CREATE OR REPLACE FUNCTION public.refresh_sites(va_sync_scopes text DEFAULT 'FedCloud'::text, forced boolean DEFAULT false)
 RETURNS integer
 LANGUAGE plpgsql
AS $function$ 
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
--  deltime := '1 minute';

    IF doSites THEN
        -- TRUNCATE TABLE gocdb.site_contacts;  -- OBSOLETED

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
            ((t.j->>'info')::jsonb->>'GLUE2EndpointServiceForeignKey')::text AS serviceid
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
		REFRESH MATERIALIZED VIEW CONCURRENTLY va_provider_managers;
		REFRESH MATERIALIZED VIEW CONCURRENTLY va_provider_shares;
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
$function$
;
	
INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 23, 0, E'Move to GLUE2.1 schema w/ proper support for Cloud Computing information'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=23 AND revision=0);

COMMIT;

