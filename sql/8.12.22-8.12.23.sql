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
Previous version: 8.12.22
New version: 8.12.23
Author: wvkarag@lovecraft.priv.iasa.gr
*/

CREATE OR REPLACE FUNCTION public.get_good_vmiinstanceid(_vmiinstanceid integer)
 RETURNS integer
 LANGUAGE sql
AS $function$
SELECT CASE WHEN goodid IS NULL THEN $1 ELSE goodid END FROM (
        SELECT max(t1.id) as goodid FROM vmiinstances AS t1
        INNER JOIN vmiinstances AS t2 ON t1.checksum = t2.checksum AND t1.guid = t2.guid AND t2.id = $1
        INNER JOIN vapplists ON t1.id = vapplists.vmiinstanceid
        INNER JOIN vapp_versions ON vapplists.vappversionid = vapp_versions.id 
        WHERE vapp_versions.published
) AS t
$function$ STABLE;
ALTER FUNCTION public.get_good_vmiinstanceid(integer) OWNER TO appdb;

DROP MATERIALIZED VIEW IF EXISTS site_service_images_xml ;
CREATE MATERIALIZED VIEW site_service_images_xml AS 
	SELECT siteimages.va_provider_id,
	xmlagg(siteimages.x) AS xmlagg
	FROM (
		SELECT 
			va_providers.id AS va_provider_id,
			-- xmlagg(
				XMLELEMENT(
					NAME "siteservice:image", 
					XMLATTRIBUTES(
						__vaviews.vappversionid AS versionid, 
						__vaviews.va_version_archived AS archived, 
						__vaviews.va_version_enabled AS enabled, 
						__vaviews.va_version_expireson AS expireson,
						CASE
							WHEN __vaviews.va_version_expireson <= now() THEN true
							ELSE false
						END AS isexpired, 
						__vaviews.imglst_private AS private, 
						__vaviews.vmiinstanceid AS id, 
						__vaviews.vmiinstance_guid AS identifier, 
						__vaviews.vmiinstance_version AS version, 
						va_provider_images.good_vmiinstanceid AS goodid
					), 
					(vmiflavor_hypervisor_xml.hypervisor::text)::xml, 
					XMLELEMENT(
						NAME "virtualization:os", 
						XMLATTRIBUTES(
							oses.id AS id, 
							__vaviews.osversion AS version, 
							oses.os_family_id AS family_id
						),
						oses.name
					), 
					XMLELEMENT(
						NAME "virtualization:arch", 
						XMLATTRIBUTES(archs.id AS id),
						archs.name
					), 
					XMLELEMENT(NAME "virtualization:format", __vaviews.format), 
					XMLELEMENT(
						NAME "virtualization:url", 
						XMLATTRIBUTES(
							CASE
								WHEN __vaviews.imglst_private = true THEN 'true'::text
								ELSE NULL::text
							END AS protected
						),
						CASE
							WHEN __vaviews.imglst_private = false THEN __vaviews.uri
							ELSE NULL::text
						END
					), 
					XMLELEMENT(
						NAME "virtualization:size", 
						XMLATTRIBUTES(
							CASE
								WHEN __vaviews.imglst_private = true THEN 'true'::text
								ELSE NULL::text
							END AS protected
						),
						CASE
							WHEN __vaviews.imglst_private = false THEN __vaviews.size
							ELSE NULL::bigint
						END
					), 
					XMLELEMENT(
						NAME "siteservice:mpuri", 
						'//' || (SELECT data FROM config WHERE var = 'ui-host') || '/store/vm/image/' || __vaviews.vmiinstance_guid::text || ':' || va_provider_images.good_vmiinstanceid::text || '/'
					),
					array_to_string(array_agg(DISTINCT site_service_imageocciids_to_xml(
						va_provider_images.va_provider_id, 
						va_provider_images.vmiinstanceid, 
						va_provider_images.vowide_vmiinstanceid
					)::text), '')::xml, 
					XMLELEMENT(NAME "application:application", XMLATTRIBUTES(__vaviews.appid AS id, __vaviews.appcname AS cname, __vaviews.imglst_private AS imagelistsprivate, applications.deleted AS deleted, applications.moderated AS moderated), XMLELEMENT(NAME "application:name", __vaviews.appname)), vmiinst_cntxscripts_to_xml(__vaviews.vmiinstanceid))
			
--) 
AS x
           FROM va_providers
             JOIN va_provider_images AS va_provider_images ON va_provider_images.va_provider_id = va_providers.id
             JOIN vaviews AS __vaviews ON __vaviews.vmiinstanceid = va_provider_images.vmiinstanceid
             JOIN applications ON applications.id = __vaviews.appid
             LEFT JOIN vmiflavor_hypervisor_xml ON vmiflavor_hypervisor_xml.vmiflavourid = __vaviews.vmiflavourid
             LEFT JOIN archs ON archs.id = __vaviews.archid
             LEFT JOIN oses ON oses.id = __vaviews.osid
             LEFT JOIN vmiformats ON vmiformats.name::text = __vaviews.format
          WHERE __vaviews.va_version_published
          GROUP BY va_providers.id,
          __vaviews.vappversionid,
          __vaviews.va_version_archived,
		  __vaviews.va_version_enabled,
		  __vaviews.va_version_expireson,
		  __vaviews.imglst_private,
		  __vaviews.vmiinstanceid,
		  __vaviews.vmiinstance_guid,
		  __vaviews.vmiinstance_version,
		  va_provider_images.good_vmiinstanceid,
		  vmiflavor_hypervisor_xml.hypervisor::text,
		  oses.id,
		  archs.id,
		  __vaviews.osversion,
		  __vaviews.format,
		  __vaviews.uri,
		  __vaviews.size,
		  __vaviews.appid,
		  __vaviews.appcname,
		  __vaviews.appname,
		  applications.deleted,
		  applications.moderated
          ) siteimages
  GROUP BY siteimages.va_provider_id
WITH NO DATA;
ALTER MATERIALIZED VIEW site_service_images_xml OWNER TO appdb;
REFRESH MATERIALIZED VIEW site_service_images_xml;

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 12, 23, E''
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=12 AND revision=23);
