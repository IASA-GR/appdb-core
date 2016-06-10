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
Previous version: 8.12.21
New version: 8.12.22
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

CREATE OR REPLACE FUNCTION site_service_imageocciids_to_xml(
    providerid text,
    vmiinstanceid integer,
	_vowide_vmiinstanceid integer)
  RETURNS xml AS
$BODY$SELECT xmlagg(siteimageoccids.x) FROM (
SELECT XMLELEMENT(NAME "siteservice:occi",
	XMLATTRIBUTES(va_provider_images.va_provider_image_id AS id, va_provider_images.id AS providerimageid, vowide_image_list_images.id AS voimageid , vowide_image_lists.state AS voimagestate),
	vo_to_xml(vowide_image_lists.void)
) as x
FROM va_providers
INNER JOIN va_provider_images ON va_provider_images.va_provider_id = va_providers.id
LEFT OUTER JOIN vowide_image_list_images ON vowide_image_list_images.id = va_provider_images.vowide_vmiinstanceid
LEFT OUTER JOIN vowide_image_lists ON vowide_image_lists.id = vowide_image_list_images.vowide_image_list_id
WHERE va_providers.id = $1 AND va_provider_images.vmiinstanceid = $2 AND NOT $3 IS DISTINCT FROM vowide_vmiinstanceid
) as siteimageoccids
$BODY$
  LANGUAGE sql VOLATILE CALLED ON NULL INPUT
  COST 100;
ALTER FUNCTION site_service_imageocciids_to_xml(text, integer)
  OWNER TO appdb;

DROP FUNCTION site_service_images_to_xml(providerid text);

CREATE OR REPLACE VIEW vmiflavor_hypervisor_xml AS
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
GROUP BY vmiflavours_1.id;
ALTER VIEW vmiflavor_hypervisor_xml OWNER TO appdb;

DROP MATERIALIZED VIEW IF EXISTS site_service_images_xml;
CREATE MATERIALIZED VIEW site_service_images_xml AS
SELECT siteimages.va_provider_id, xmlagg(siteimages.x) FROM (
	SELECT
		va_providers.id AS va_provider_id,
		xmlagg(
		XMLELEMENT(NAME "siteservice:image", 
        XMLATTRIBUTES(
                vaviews.vappversionid as versionid,
                vaviews.va_version_archived as archived,
                vaviews.va_version_enabled as enabled,
                vaviews.va_version_expireson as expireson,
                CASE WHEN vaviews.va_version_expireson <= NOW() THEN TRUE ELSE FALSE END AS isexpired,
                vaviews.imglst_private as private,
                vaviews.vmiinstanceid as id,
                vaviews.vmiinstance_guid AS identifier,
                vaviews.vmiinstance_version as version,
                va_provider_images.good_vmiinstanceid as goodid
        ),
		vmiflavor_hypervisor_xml.hypervisor,
        XMLELEMENT(NAME "virtualization:os", XMLATTRIBUTES(oses.id AS id, vaviews.osversion AS version, oses.os_family_id as family_id), oses.name), 
        XMLELEMENT(NAME "virtualization:arch", XMLATTRIBUTES(archs.id AS id), archs.name),
        XMLELEMENT(NAME "virtualization:format", vaviews.format),
        XMLELEMENT(NAME "virtualization:url", XMLATTRIBUTES(CASE WHEN vaviews.imglst_private = TRUE THEN 'true' ELSE NULL END AS protected), 
                CASE WHEN vaviews.imglst_private = FALSE THEN vaviews.uri END),
        XMLELEMENT(NAME "virtualization:size",XMLATTRIBUTES(CASE WHEN vaviews.imglst_private = TRUE THEN 'true' ELSE NULL END AS protected), 
                CASE WHEN vaviews.imglst_private = FALSE THEN vaviews.size END),
        XMLELEMENT(NAME "siteservice:mpuri", va_provider_images.mp_uri),
		site_service_imageocciids_to_xml(va_provider_images.va_provider_id::TEXT,va_provider_images.vmiinstanceid::INTEGER, va_provider_images.vowide_vmiinstanceid),
        XMLELEMENT(NAME "application:application", XMLATTRIBUTES(vaviews.appid AS id, vaviews.appcname AS cname, vaviews.imglst_private as imageListsPrivate, applications.deleted, applications.moderated), 
                XMLELEMENT(NAME "application:name", vaviews.appname )),
        vmiinst_cntxscripts_to_xml(vaviews.vmiinstanceid)
	)) as x
FROM va_providers
INNER JOIN va_provider_images ON va_provider_images.va_provider_id = va_providers.id
INNER JOIN vaviews ON vaviews.vmiinstanceid = va_provider_images.vmiinstanceid
INNER JOIN applications ON applications.id = vaviews.appid
LEFT OUTER JOIN vmiflavor_hypervisor_xml ON vmiflavor_hypervisor_xml.vmiflavourid = vaviews.vmiflavourid
LEFT OUTER JOIN archs ON archs.id = vaviews.archid
LEFT OUTER JOIN oses ON oses.id = vaviews.osid
LEFT OUTER JOIN vmiformats ON vmiformats.name::text = vaviews.format
WHERE vaviews.va_version_published
GROUP BY va_providers.id
) AS siteimages
GROUP BY siteimages.va_provider_id WITH NO DATA;
ALTER MATERIALIZED VIEW site_service_images_xml OWNER TO appdb;

CREATE OR REPLACE FUNCTION site_service_images_to_xml(providerid text)
RETURNS xml AS
$BODY$
SELECT "xmlagg" FROM site_service_images_xml WHERE va_provider_id::text = $1;
$BODY$
  LANGUAGE sql STABLE
  COST 100;

REFRESH MATERIALIZED VIEW site_service_images_xml;

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 12, 22, E'Fixes and performance improvement for XML site details'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=12 AND revision=22);

COMMIT;
