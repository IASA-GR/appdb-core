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
Previous version: 8.23.5
New version: 8.23.6
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

--DROP FUNCTION IF EXISTS authn(va_providers);
CREATE OR REPLACE FUNCTION authn(va_providers) RETURNS TEXT AS
$$
SELECT
	UPPER(ARRAY_TO_STRING(REGEXP_MATCHES(
		(j->>'info')::jsonb->>'GLUE2EntityOtherInfo',
		E'AUTHN=([0-9A-Za-z-]+)',
		'i'
	), ', '))
FROM egiis.tvapj
WHERE pkey = $1.id
$$ LANGUAGE SQL STABLE;
ALTER FUNCTION authn(va_providers) OWNER TO appdb;

-- DROP FUNCTION IF EXISTS authn(__va_providers);
CREATE OR REPLACE FUNCTION authn(__va_providers) RETURNS TEXT AS
$$
SELECT
	UPPER(ARRAY_TO_STRING(REGEXP_MATCHES(
		(j->>'info')::jsonb->>'GLUE2EntityOtherInfo',
		E'AUTHN=([0-9A-Za-z-]+)',
		'i'
	), ', '))
FROM egiis.tvapj
WHERE pkey = $1.id
$$ LANGUAGE SQL STABLE;
ALTER FUNCTION authn(__va_providers) OWNER TO appdb;

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
$function$;

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
		va_providers.authn,
		va_providers.service_type,
		hostname,
		beta,
		in_production
) AS services
$function$;

CREATE OR REPLACE FUNCTION public.site_service_to_xml_ext(sitename text)
 RETURNS xml
 LANGUAGE sql
 STABLE
AS $function$
SELECT xmlagg(services.x) FROM (SELECT XMLELEMENT(NAME "site:service",
    XMLATTRIBUTES(
    		cloud_service_name_from_type(va_providers.service_type) AS type,

    	va_providers.id as id,
    	va_providers.authn,
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
$function$;

DROP MATERIALIZED VIEW site_services_xml;
CREATE MATERIALIZED VIEW site_services_xml AS
 SELECT
 	__va_providers.id,
 	__va_providers.authn,
    __va_providers.sitename,
    XMLELEMENT(NAME "site:service", XMLATTRIBUTES(cloud_service_name_from_type(__va_providers.service_type) AS type, __va_providers.id AS id, __va_providers.hostname AS host, count(DISTINCT good_vmiinstanceid(va_provider_images.*)) AS instances, __va_providers.beta AS beta, __va_providers.in_production AS in_production, __va_providers.service_downtime::integer AS service_downtime, __va_providers.service_status AS service_status, __va_providers.service_status_date AS service_status_date), xmlagg(XMLELEMENT(NAME "siteservice:image", XMLATTRIBUTES(va_provider_images.vmiinstanceid AS id, good_vmiinstanceid(va_provider_images.*) AS goodid)))) AS x
   FROM __va_providers
     LEFT JOIN va_provider_images ON va_provider_images.va_provider_id = __va_providers.id AND (va_provider_images.vmiinstanceid IN ( SELECT __vaviews.vmiinstanceid
           FROM __vaviews))
  GROUP BY __va_providers.id, __va_providers.authn, __va_providers.hostname, __va_providers.beta, __va_providers.in_production, __va_providers.service_downtime, __va_providers.sitename, __va_providers.service_status, __va_providers.service_status_date, __va_providers.service_type;
;
ALTER MATERIALIZED VIEW site_services_xml OWNER TO appdb;

CREATE UNIQUE INDEX "idx_site_services_xml_id" ON site_services_xml(id);
CREATE INDEX "idx_site_services_xml_sitename" ON site_services_xml (sitename);
CREATE INDEX "idx_site_services_xml_sitename_textops" ON site_services_xml(sitename text_pattern_ops);
CREATE INDEX "idx_site_services_xml_sitename_trgmops" ON site_services_xml USING gin(sitename gin_trgm_ops);

REFRESH MATERIALIZED VIEW site_services_xml;

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 23, 6, E'Added authn attribute to va_providers and site services xml'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=23 AND revision=6);

COMMIT;	
