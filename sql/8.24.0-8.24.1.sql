/*
 Copyright (C) 2015 - 2020 IASA - Institute of Accelerating Systems and Applications (http://www.iasa.gr)

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
Previous version: 8.24.0
New version: 8.24.1
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

DROP FUNCTION group_hash;

DROP MATERIALIZED VIEW public.va_provider_templates;
DROP MATERIALIZED VIEW public.site_service_images_xml;
DROP MATERIALIZED VIEW public.site_services_xml;
DROP FUNCTION public.good_vmiinstanceid(va_provider_images);
DROP MATERIALIZED VIEW public.va_provider_images;
DROP MATERIALIZED VIEW public.va_provider_shares;

-------------------

CREATE MATERIALIZED VIEW public.va_provider_shares
TABLESPACE pg_default
AS SELECT q.id,
    q.pkey,
    q.va_provider_id,
    q.vo,
    q.projectid,
    ( SELECT vos.id
           FROM vos
          WHERE lower(vos.name) = lower(q.vo) AND NOT vos.deleted
         LIMIT 1) AS void
   FROM ( SELECT nextval('va_provider_shares_id_seq'::regclass) AS id,
            t.pkey AS va_provider_id,
            jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'shares'::text)::jsonb) ->> 'GLUE2ShareID'::text AS pkey,
            jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'shares'::text)::jsonb) ->> 'ShareVO'::text AS vo,
            jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'shares'::text)::jsonb) ->> 'GLUE2CloudComputingShareProjectID'::text AS projectid
           FROM egiis.vapj g
             LEFT JOIN egiis.tvapj t ON g.pkey = t.pkey
          WHERE lower(((t.j ->> 'info'::text)::jsonb) ->> 'GLUE2EndpointInterfaceName'::text) ~ '(occi|openstack|opennebula|synnefo|wnodes|cloudstack|vcloud)'::text) q
WITH DATA;

-- View indexes:
CREATE UNIQUE INDEX idx_va_provider_shares_id ON public.va_provider_shares USING btree (id);

-----------------

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
        END AS vowide_vmiinstanceid,
    xx.shareid,
    xx.managerid
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
                END AS vowide_vmiinstanceid,
            s.id AS shareid,
            m.id AS managerid
           FROM ( SELECT nextval('va_provider_images_id_seq'::regclass) AS id,
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
                  WHERE lower(((t.j ->> 'info'::text)::jsonb) ->> 'GLUE2EndpointInterfaceName'::text) ~ '(occi|openstack|opennebula|synnefo|wnodes|cloudstack|vcloud)'::text AND 
                  COALESCE(btrim(((t.j ->> 'info'::text)::jsonb) ->> 'GLUE2EndpointServiceForeignKey'::text), ''::text) <> ''::text /*AND 
                  COALESCE(((g.j ->> 'info'::text)::jsonb) ->> 'SiteEndpointInProduction'::text, 'FALSE'::text)::boolean IS DISTINCT FROM false*/
             ) x
             LEFT JOIN va_provider_managers m ON m.va_provider_id = x.va_provider_id AND lower(m.product_name) = lower(x.manager_name)
             LEFT JOIN va_provider_shares s ON s.va_provider_id = x.va_provider_id AND
                CASE
                    WHEN COALESCE(btrim(x.vo), ''::text) <> ''::text THEN lower(s.vo) = lower(x.vo)
                    ELSE lower(s.vo) = lower(x.vo2)
                END) xx
WITH DATA;

-- View indexes:
CREATE UNIQUE INDEX idx_va_provider_images_id ON public.va_provider_images USING btree (id);
CREATE INDEX idx_va_provider_images_va_provider_id ON public.va_provider_images USING btree (va_provider_id);
CREATE INDEX idx_va_provider_images_va_provider_id_textops ON public.va_provider_images USING btree (va_provider_id text_pattern_ops);
CREATE INDEX idx_va_provider_images_va_provider_id_trgmops ON public.va_provider_images USING gin (va_provider_id gin_trgm_ops);
CREATE INDEX idx_va_provider_images_vmiinstanceid ON public.va_provider_images USING btree (vmiinstanceid);
CREATE INDEX idx_va_provider_images_vowide_vmiinstanceid ON public.va_provider_images USING btree (vowide_vmiinstanceid);


------------

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

-------------------------

CREATE MATERIALIZED VIEW public.site_services_xml
TABLESPACE pg_default
AS SELECT __va_providers.id,
    __va_providers.sitename,
    XMLELEMENT(NAME "site:service", XMLATTRIBUTES(( SELECT lower(va_provider_endpoints.deployment_type) AS lower
           FROM va_provider_endpoints
          WHERE va_provider_endpoints.va_provider_id = __va_providers.id) AS type, __va_providers.id AS id, __va_providers.hostname AS host, count(DISTINCT good_vmiinstanceid(va_provider_images.*)) AS instances, __va_providers.beta AS beta, __va_providers.in_production AS in_production, __va_providers.service_downtime::integer AS service_downtime, __va_providers.service_status AS service_status, __va_providers.service_status_date AS service_status_date), xmlagg(XMLELEMENT(NAME "siteservice:image", XMLATTRIBUTES(va_provider_images.vmiinstanceid AS id, good_vmiinstanceid(va_provider_images.*) AS goodid)))) AS x
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

----------------------


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

------------------

CREATE MATERIALIZED VIEW public.va_provider_templates
TABLESPACE pg_default
AS SELECT q.id,
    q.va_provider_id,
    q.pkey,
    q.resource_name,
    q.memsize,
    q.logical_cpus,
    q.physical_cpus,
        CASE
            WHEN q.logical_cpus > '1'::text THEN 'multicpu-singlecore'::text
            ELSE 'singlecpu-singlecore'::text
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
    ( SELECT vos.id
           FROM vos
          WHERE lower(vos.name) = lower(q.vo) AND NOT vos.deleted
         LIMIT 1) AS void,
    va_provider_shares.id AS shareid,
    va_provider_managers.id AS managerid
   FROM ( SELECT nextval('va_provider_templates_id_seq'::regclass) AS id,
            g.pkey AS va_provider_id,
            jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'templates'::text)::jsonb) ->> 'GLUE2CloudComputingInstanceTypeTemplateID'::text AS pkey,
            jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'templates'::text)::jsonb) ->> 'GLUE2EntityName'::text AS resource_name,
            jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'templates'::text)::jsonb) ->> 'GLUE2CloudComputingInstanceTypeRAM'::text AS memsize,
            jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'templates'::text)::jsonb) ->> 'GLUE2CloudComputingInstanceTypeCPU'::text AS logical_cpus,
            jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'templates'::text)::jsonb) ->> 'GLUE2CloudComputingInstanceTypeCPU'::text AS physical_cpus,
            jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'templates'::text)::jsonb) ->> 'GLUE2CloudComputingInstanceTypeCloudComputingManagerForeignKey'::text AS resource_manager,
            jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'templates'::text)::jsonb) ->> 'GLUE2CloudComputingInstanceTypeCloudComputingManagerForeignKey'::text AS computing_manager,
            NULL::text AS os_family,
            jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'templates'::text)::jsonb) ->> 'GLUE2CloudComputingInstanceTypeNetworkIn'::text AS connectivity_in,
            jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'templates'::text)::jsonb) ->> 'GLUE2CloudComputingInstanceTypeNetworkOut'::text AS connectivity_out,
            jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'templates'::text)::jsonb) ->> 'GLUE2CloudComputingInstanceTypeNetworkPortsIn'::text AS connectivity_ports_in,
            jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'templates'::text)::jsonb) ->> 'GLUE2CloudComputingInstanceTypeNetworkPortsOut'::text AS connectivity_ports_out,
            jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'templates'::text)::jsonb) ->> 'GLUE2CloudComputingInstanceTypeNetworkInfo'::text AS connectivity_info,
            'virtual model'::text AS cpu_model,
            jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'templates'::text)::jsonb) ->> 'GLUE2ResourceID'::text AS resource_id,
            jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'templates'::text)::jsonb) ->> 'GLUE2CloudComputingInstanceTypeDisk'::text AS disc_size,
            jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'templates'::text)::jsonb) ->> 'GLUE2CloudComputingInstanceTypeEphemeralStorage'::text AS tmp_storage_size,
            jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'templates'::text)::jsonb) ->> 'InstanceTypeVO'::text AS vo,
            jsonb_array_elements((((t.j ->> 'info'::text)::jsonb) ->> 'templates'::text)::jsonb) ->> 'ManagerName'::text AS manager_name
           FROM egiis.vapj g
             LEFT JOIN egiis.tvapj t ON t.pkey = g.pkey
          WHERE lower(((t.j ->> 'info'::text)::jsonb) ->> 'GLUE2EndpointInterfaceName'::text) ~ '(occi|openstack|opennebula|synnefo|wnodes|cloudstack|vcloud)'::text AND 
          COALESCE(btrim(((t.j ->> 'info'::text)::jsonb) ->> 'GLUE2EndpointServiceForeignKey'::text), ''::text) <> ''::text 
          /*AND COALESCE(((g.j ->> 'info'::text)::jsonb) ->> 'SiteEndpointInProduction'::text, 'FALSE'::text)::boolean IS DISTINCT FROM false*/
     ) q
     LEFT JOIN va_provider_managers ON va_provider_managers.va_provider_id = q.va_provider_id AND lower(va_provider_managers.product_name) = lower(q.manager_name)
     LEFT JOIN va_provider_shares ON va_provider_shares.va_provider_id = q.va_provider_id AND lower(va_provider_shares.vo) = lower(q.vo)
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
	COALESCE(v.disc_size, '') || '_' ||
	COALESCE(v.tmp_storage_size, '') || '_' ||
	COALESCE(v.connectivity_ports_in, '') || '_' ||
	COALESCE(v.connectivity_ports_out, '') || '_' ||
	COALESCE(v.managerid, 0) || '_' ||
	COALESCE(v.shareid, 0) || '_' ||
	COALESCE(e.deployment_type, '')
)	
FROM va_provider_endpoints e 
WHERE e.va_provider_id = v.va_provider_id
LIMIT 1;
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
				-- 'occi' AS type,
				(SELECT LOWER(deployment_type) FROM va_provider_endpoints WHERE va_provider_endpoints.va_provider_id = va_providers.id) AS type, 
				va_providers.id AS id,
				va_providers.authn,
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
		hostname, 
		va_providers.id, 
		va_providers.authn,
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
    	COALESCE((SELECT LOWER(deployment_type) FROM va_provider_endpoints WHERE va_provider_endpoints.va_provider_id = va_providers.id), cloud_service_name_from_type(va_providers.service_type)) AS type,     	    		
    	va_providers.id as id,
    	va_providers.authn,
    	hostname as host,
    	va_providers.beta as beta,
    	va_providers.in_production as in_production,
    	va_providers.service_downtime::int as service_downtime,
    	va_providers.service_type,
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
   GROUP BY
   		va_providers.id,
   		va_providers.authn,
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

---------------------------------------

CREATE OR REPLACE FUNCTION public.site_service_imageocciids_to_xml(providerid text, vmiinstanceid integer, _vowide_vmiinstanceid integer)
 RETURNS xml
 LANGUAGE sql
 STABLE
AS $function$
  SELECT xmlagg(siteimageoccids.x) FROM (
	  SELECT XMLELEMENT(NAME "siteservice:occi",
	      XMLATTRIBUTES(
	      	va_provider_images.va_provider_image_id AS id, va_provider_images.id AS providerimageid, vowide_image_list_images.id AS voimageid , vowide_image_lists.state AS voimagestate,
	      	va_provider_shares.projectid
	      ),
	      public.vo_to_xml(vowide_image_lists.void)                                                                                                                                          
	  ) as x                                                                                                                                                                          
	  FROM public.va_providers                                                                                                                                                               
	  INNER JOIN public.va_provider_images ON va_provider_images.va_provider_id = va_providers.id
	  INNER JOIN public.va_provider_shares ON va_provider_shares.id = va_provider_images.shareid   
	  LEFT OUTER JOIN public.vowide_image_list_images ON vowide_image_list_images.id = va_provider_images.vowide_vmiinstanceid                                                               
	  LEFT OUTER JOIN public.vowide_image_lists ON vowide_image_lists.id = vowide_image_list_images.vowide_image_list_id                                                                     
	  WHERE va_providers.id = $1 AND va_provider_images.vmiinstanceid = $2 AND NOT $3 IS DISTINCT FROM vowide_vmiinstanceid                                                           
  ) as siteimageoccids                                                                                                                                                            
  $function$
;

CREATE OR REPLACE FUNCTION public.site_service_imageocciids_to_xml(providerid text, vmiinstanceid integer)
 RETURNS xml
 LANGUAGE sql
 STABLE
AS $function$
  SELECT xmlagg(siteimageoccids.x) FROM (
	  SELECT XMLELEMENT(NAME "siteservice:occi",
	      XMLATTRIBUTES(va_provider_images.va_provider_image_id AS id, va_provider_images.id AS providerimageid, vowide_image_list_images.id AS voimageid , vowide_image_lists.state AS voimagestate,
	      	va_provider_shares.projectid
	      ),
	      public.vo_to_xml(vowide_image_lists.void)
	  ) as x
	  FROM public.va_providers
	  INNER JOIN public.va_provider_images ON va_provider_images.va_provider_id = va_providers.id
	  INNER JOIN public.va_provider_shares ON va_provider_shares.id = va_provider_images.shareid
	  LEFT OUTER JOIN public.vowide_image_list_images ON vowide_image_list_images.id = va_provider_images.vowide_vmiinstanceid
	  LEFT OUTER JOIN public.vowide_image_lists ON vowide_image_lists.id = vowide_image_list_images.vowide_image_list_id
	  WHERE va_providers.id = $1 AND va_provider_images.vmiinstanceid = $2
  ) as siteimageoccids
  $function$
;

--------------------------

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
			va_providers.authn,
			beta,
			in_production,
			node_monitored,
			service_downtime::int AS service_downtime,
			service_type AS service_type,			
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
			va_providers.authn,			
			beta,
			in_production,
			node_monitored,
			service_type AS service_type,			
			service_downtime::int AS service_downtime,
			service_status AS service_status,
			service_status_date AS service_status_date
		),
		xmlelement(name "provider:name", sitename),
		xmlelement(name "provider:shares",
			(SELECT 
				xmlagg(
					xmlelement(name "vo:vo", XMLATTRIBUTES(s.void AS id, s.projectid), s.vo)
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
						va_provider_shares.projectid,
						(SELECT COALESCE(hypervisor::text, 'unknown') FROM va_provider_managers m WHERE m.id = va_provider_images.managerid) AS hypervisor,
						vapp_versions.archived
					)
				)::text), '')::XML
			) FROM va_provider_images
			INNER JOIN va_provider_shares ON va_provider_shares.id = va_provider_images.shareid
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
			WHERE va_provider_images.va_provider_id = va_providers.id /*AND ((
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
		va_providers.authn,		
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
$function$
;

CREATE OR REPLACE FUNCTION public.swapp_image_providers_to_xml(_appid integer)
 RETURNS SETOF xml
 LANGUAGE sql
AS $function$
SELECT
	xmlelement(
		name "virtualization:image",
		xmlattributes(
			vaviews.vmiinstanceid,
			vaviews.vmiinstance_guid AS identifier,
			vaviews.vmiinstance_version,
			vaviews.va_version_archived AS archived,
			vaviews.va_version_enabled AS enabled,
			CASE WHEN vaviews.va_version_expireson >= NOW() THEN FALSE ELSE TRUE END AS isexpired
		),
		XMLELEMENT(NAME "application:application",
			XMLATTRIBUTES(applications.id AS id, applications.cname AS cname, applications.guid AS guid, applications.deleted, applications.moderated),
			XMLELEMENT(NAME "application:name", applications.name)
		),
		hypervisors.hypervisor::text::xml,
		XMLELEMENT(NAME "virtualization:os", XMLATTRIBUTES(oses.id AS id, vaviews.osversion AS version, oses.os_family_id as family_id), oses.name), 
		XMLELEMENT(NAME "virtualization:arch", XMLATTRIBUTES(archs.id AS id), archs.name),
		vmiinst_cntxscripts_to_xml(vaviews.vmiinstanceid),
		array_to_string(array_agg(DISTINCT 
			xmlelement(name "virtualization:provider",
				xmlattributes(
					va_provider_images.va_provider_id as provider_id,
					(SELECT va_providers.authn FROM va_providers WHERE id = va_provider_images.va_provider_id) as provider_authn,
					va_provider_images.va_provider_image_id as occi_id,
					vowide_image_lists.void,
					vos.name as voname,
					va_provider_shares.projectid,
					va_provider_images.vmiinstanceid as vmiinstanceid					
				)
			)::text
		),'')::xml
)
FROM contexts
	INNER JOIN context_script_assocs ON context_script_assocs.contextid = contexts.id
	INNER JOIN contextscripts AS cs ON cs.id = context_script_assocs.scriptid
	INNER JOIN vmiinstance_contextscripts AS vcs ON vcs.contextscriptid = cs.id
	INNER JOIN va_provider_images ON va_provider_images.good_vmiinstanceid = vcs.vmiinstanceid
	INNER JOIN va_provider_shares ON va_provider_shares.id = va_provider_images.shareid
	INNER JOIN vaviews ON vaviews.vmiinstanceid = vcs.vmiinstanceid
	INNER JOIN applications ON applications.id = vaviews.appid
	LEFT OUTER JOIN vmiflavor_hypervisor_xml AS hypervisors ON hypervisors.vmiflavourid = vaviews.vmiflavourid
	LEFT OUTER JOIN archs ON archs.id = vaviews.archid
	LEFT OUTER JOIN oses ON oses.id = vaviews.osid
	-- LEFT OUTER JOIN vmiformats ON vmiformats.name = vaviews.format
	LEFT OUTER JOIN app_vos ON app_vos.appid = applications.id
	LEFT OUTER JOIN vowide_image_list_images ON vowide_image_list_images.id = va_provider_images.vowide_vmiinstanceid
	LEFT OUTER JOIN vowide_image_lists ON vowide_image_lists.id = vowide_image_list_images.vowide_image_list_id AND (vowide_image_lists.state = 'published' OR vowide_image_lists.state = 'obsolete')
	LEFT OUTER JOIN vos ON vos.id = vowide_image_lists.void
WHERE  
	vaviews.va_version_published 
	-- AND contexts.appid = $1
GROUP BY 
	applications.id,
	vaviews.osversion,
	hypervisors.hypervisor::text,
	vaviews.vmiinstanceid, 
	vaviews.vmiflavourid, 
	vaviews.vmiinstance_guid,
	vaviews.vmiinstance_version,
	vaviews.va_version_archived,
	vaviews.va_version_enabled,
	vaviews.va_version_expireson,
	archs.id, 
	oses.id,
	-- vmiformats.id,
	app_vos.appid
$function$
;

CREATE OR REPLACE FUNCTION public.vapp_image_providers_to_xml(_appid integer)
 RETURNS SETOF xml
 LANGUAGE sql
AS $function$
 WITH hypervisors AS (
	 WITH x AS (
		 SELECT vmiflavours_2.id,
		    unnest(vmiflavours_2.hypervisors) AS y
		   FROM vmiflavours vmiflavours_2
		)
	 SELECT vmiflavours_1.id AS vmiflavourid,
	    xmlagg(XMLELEMENT(NAME "virtualization:hypervisor", XMLATTRIBUTES(( SELECT hypervisors_1.id
		   FROM public.hypervisors hypervisors_1
		  WHERE hypervisors_1.name::text = x.y::text) AS id), x.y)) AS hypervisor
	   FROM vmiflavours vmiflavours_1
	JOIN x ON x.id = vmiflavours_1.id
	GROUP BY vmiflavours_1.id
)
SELECT
	xmlelement(
		name "virtualization:image",
		xmlattributes(
			vaviews.vmiinstanceid,
			va_provider_images.good_vmiinstanceid,
			vaviews.vmiinstance_guid AS identifier,
			vaviews.vmiinstance_version,
			vaviews.va_version_archived AS archived,
			vaviews.va_version_enabled AS enabled,
			CASE WHEN vaviews.va_version_expireson >= NOW() THEN FALSE ELSE TRUE END AS isexpired
		),
		hypervisors.hypervisor::text::xml,
--		XMLELEMENT(NAME "virtualization:hypervisors", array_to_string(vaviews.hypervisors, ',')::xml),
		XMLELEMENT(NAME "virtualization:os", XMLATTRIBUTES(oses.id AS id, vaviews.osversion AS version, oses.os_family_id as family_id), oses.name),
		XMLELEMENT(NAME "virtualization:arch", XMLATTRIBUTES(archs.id AS id), archs.name),
		vmiinst_cntxscripts_to_xml(vaviews.vmiinstanceid),
--		XMLELEMENT(NAME "virtualization:location", vaviews.uri),
--		XMLELEMENT(NAME "virtualization:checksum", XMLATTRIBUTES(vaviews.checksumfunc AS checkfunc), vaviews.checksum),
--		XMLELEMENT(NAME "virtualization:osversion", vaviews.osversion),
		array_to_string(array_agg(DISTINCT
			xmlelement(name "virtualization:provider",
				xmlattributes(
					va_provider_images.va_provider_id as provider_id,
					va_providers.authn as provider_authn,					
					va_providers.sitename as name,
					va_providers.service_type,
					va_provider_images.va_provider_image_id as occi_id, --FIXME: this should be renamed to endpoint_id
					vowide_image_lists.void,
					vos.name as voname,
					va_provider_shares.projectid,
					va_provider_images.vmiinstanceid as vmiinstanceid
				)
			)::text
		),'')::xml
)
FROM
	applications
	INNER JOIN vaviews ON vaviews.appid = applications.id
	INNER JOIN va_provider_images AS va_provider_images ON va_provider_images.vmiinstanceid = vaviews.vmiinstanceid
	INNER JOIN va_provider_shares ON va_provider_shares.id = va_provider_images.shareid   
	INNER JOIN va_providers ON va_providers.id = va_provider_images.va_provider_id
	LEFT OUTER JOIN hypervisors ON hypervisors.vmiflavourid = vaviews.vmiflavourid
	LEFT OUTER JOIN archs ON archs.id = vaviews.archid
	LEFT OUTER JOIN oses ON oses.id = vaviews.osid
	LEFT OUTER JOIN vmiformats ON vmiformats.name::text = vaviews.format
	LEFT OUTER JOIN app_vos ON app_vos.appid = applications.id
	LEFT OUTER JOIN vowide_image_list_images ON vowide_image_list_images.id = va_provider_images.vowide_vmiinstanceid
	LEFT OUTER JOIN vowide_image_lists ON vowide_image_lists.id = vowide_image_list_images.vowide_image_list_id AND (vowide_image_lists.state::text = 'published' OR vowide_image_lists.state::text = 'obsolete')
	LEFT OUTER JOIN vos ON vos.id = vowide_image_lists.void
WHERE
	-- vaviews.vmiinstanceid = va_provider_images.good_vmiinstanceid AND
	vaviews.va_version_published AND
--	NOT vaviews.va_version_archived AND
	applications.id = $1
GROUP BY
	applications.id,
	--vaviews.uri,
	--vaviews.checksumfunc,
	--vaviews.checksum,
	vaviews.osversion,
	--vaviews.hypervisors,
	hypervisors.hypervisor::text,
	--vaviews.va_id,
	--vaviews.vappversionid,
	vaviews.vmiinstanceid,
	va_provider_images.good_vmiinstanceid,
	vaviews.vmiflavourid,
	vaviews.vmiinstance_guid,
	vaviews.vmiinstance_version,
	vaviews.va_version_archived,
	vaviews.va_version_enabled,
	vaviews.va_version_expireson,
	archs.id,
	oses.id,
	vmiformats.id;
$function$
;

CREATE OR REPLACE FUNCTION public.vapp_image_providers_to_xml2(_appid integer)
 RETURNS SETOF xml
 LANGUAGE sql
AS $function$
 WITH hypervisors AS (
	 WITH x AS (
		 SELECT vmiflavours_2.id,
		    unnest(vmiflavours_2.hypervisors) AS y
		   FROM vmiflavours vmiflavours_2
		)
	 SELECT vmiflavours_1.id AS vmiflavourid,
	    xmlagg(XMLELEMENT(NAME "virtualization:hypervisor", XMLATTRIBUTES(( SELECT hypervisors_1.id
		   FROM public.hypervisors hypervisors_1
		  WHERE hypervisors_1.name::text = x.y::text) AS id), x.y)) AS hypervisor
	   FROM vmiflavours vmiflavours_1
	JOIN x ON x.id = vmiflavours_1.id
	GROUP BY vmiflavours_1.id
)
SELECT
	xmlelement(
		name "virtualization:image",
		xmlattributes(
			vaviews.vmiinstanceid,
			vaviews.vmiinstance_guid AS identifier,
			vaviews.vmiinstance_version,
			vaviews.va_version_archived AS archived,
			vaviews.va_version_enabled AS enabled,
			CASE WHEN vaviews.va_version_expireson >= NOW() THEN FALSE ELSE TRUE END AS isexpired
		),
		hypervisors.hypervisor::text::xml,
--		XMLELEMENT(NAME "virtualization:hypervisors", array_to_string(vaviews.hypervisors, ',')::xml), 
		XMLELEMENT(NAME "virtualization:os", XMLATTRIBUTES(oses.id AS id, vaviews.osversion AS version, oses.os_family_id as family_id), oses.name), 
		XMLELEMENT(NAME "virtualization:arch", XMLATTRIBUTES(archs.id AS id), archs.name),
		vmiinst_cntxscripts_to_xml(vaviews.vmiinstanceid),
--		XMLELEMENT(NAME "virtualization:location", vaviews.uri),
--		XMLELEMENT(NAME "virtualization:checksum", XMLATTRIBUTES(vaviews.checksumfunc AS checkfunc), vaviews.checksum),
--		XMLELEMENT(NAME "virtualization:osversion", vaviews.osversion), 
		array_to_string(array_agg(DISTINCT 
			xmlelement(name "virtualization:provider",
				xmlattributes(
					va_provider_images.va_provider_id as provider_id,
					(SELECT va_providers.authn FROM va_providers WHERE id = va_provider_images.va_provider_id) as provider_authn,					
					va_provider_images.va_provider_image_id as occi_id,
					vowide_image_lists.void,
					vos.name as voname,
					va_provider_shares.projectid,
					va_provider_images.vmiinstanceid as vmiinstanceid
				)
			)::text
		),'')::xml
)
FROM 
	applications
	INNER JOIN vaviews ON vaviews.appid = applications.id
	INNER JOIN va_provider_images AS va_provider_images ON va_provider_images.vmiinstanceid = vaviews.vmiinstanceid
	INNER JOIN va_provider_shares ON va_provider_shares.id = va_provider_images.shareid   
	LEFT OUTER JOIN hypervisors ON hypervisors.vmiflavourid = vaviews.vmiflavourid
	LEFT OUTER JOIN archs ON archs.id = vaviews.archid
	LEFT OUTER JOIN oses ON oses.id = vaviews.osid
	LEFT OUTER JOIN vmiformats ON vmiformats.name::text = vaviews.format
	LEFT OUTER JOIN app_vos ON app_vos.appid = applications.id
	LEFT OUTER JOIN vowide_image_list_images ON vowide_image_list_images.id = va_provider_images.vowide_vmiinstanceid
	LEFT OUTER JOIN vowide_image_lists ON vowide_image_lists.id = vowide_image_list_images.vowide_image_list_id AND (vowide_image_lists.state::text = 'published' OR vowide_image_lists.state::text = 'obsolete')
	LEFT OUTER JOIN vos ON vos.id = vowide_image_lists.void
WHERE  
	-- vaviews.vmiinstanceid = va_provider_images.good_vmiinstanceid AND
	vaviews.va_version_published AND 
--	NOT vaviews.va_version_archived AND
	applications.id = $1
GROUP BY 
	applications.id, 
	--vaviews.uri,
	--vaviews.checksumfunc,
	--vaviews.checksum,
	vaviews.osversion,
	--vaviews.hypervisors,
	hypervisors.hypervisor::text,
	--vaviews.va_id,
	--vaviews.vappversionid,
	vaviews.vmiinstanceid, 
	vaviews.vmiflavourid, 
	vaviews.vmiinstance_guid,
	vaviews.vmiinstance_version,
	vaviews.va_version_archived,
	vaviews.va_version_enabled,
	vaviews.va_version_expireson,
	archs.id, 
	oses.id,
	vmiformats.id;
$function$
;

CREATE OR REPLACE FUNCTION public.authn(va_providers)
 RETURNS text
 LANGUAGE sql
 STABLE
AS $function$
SELECT
	UPPER((j->>'info')::jsonb->>'GLUE2EndpointAuthentication')
/*	
    --- OLDER GLUE2.0 convention ---
	UPPER(ARRAY_TO_STRING(REGEXP_MATCHES(
		(j->>'info')::jsonb->>'GLUE2EntityOtherInfo',
		E'AUTHN=([0-9A-Za-z-]+)',
		'i'
	), ', '))
*/
FROM egiis.tvapj
WHERE pkey = $1.id
$function$
;

INSERT INTO version (major,minor,revision,notes) 
        SELECT 8, 24, 1, E'Bugfixes after move to GLUE2.1 schema w/ proper support for Cloud Computing information'
        WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=24 AND revision=1);

COMMIT;

REFRESH MATERIALIZED VIEW vaviews;
REFRESH MATERIALIZED VIEW va_providers;
REFRESH MATERIALIZED VIEW va_provider_images;
REFRESH MATERIALIZED VIEW va_provider_endpoints;
REFRESH MATERIALIZED VIEW va_provider_shares;
REFRESH MATERIALIZED VIEW va_provider_templates;
REFRESH MATERIALIZED VIEW va_provider_managers;
REFRESH MATERIALIZED VIEW site_services_xml;
REFRESH MATERIALIZED VIEW site_service_images_xml;
