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
Previous version: 8.12.35
New version: 8.12.36
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

CREATE OR REPLACE FUNCTION public.site_service_imageocciids_to_xml(providerid text, vmiinstanceid integer, _vowide_vmiinstanceid integer)
 RETURNS xml
 LANGUAGE sql
 STABLE
AS $function$
SELECT xmlagg(siteimageoccids.x) FROM (
SELECT XMLELEMENT(NAME "siteservice:occi",
	XMLATTRIBUTES(va_provider_images.va_provider_image_id AS id, va_provider_images.id AS providerimageid, vowide_image_list_images.id AS voimageid , vowide_image_lists.state AS voimagestate),
--	vo_to_xml(vowide_image_lists.void)
	XMLELEMENT(
        NAME "vo:vo",
        XMLATTRIBUTES(
            vowide_image_lists.void AS id,
            (SELECT name FROM vos WHERE id = vowide_image_lists.void) AS name
        )
    )                  
) as x
FROM va_providers
INNER JOIN va_provider_images ON va_provider_images.va_provider_id = va_providers.id
LEFT OUTER JOIN vowide_image_list_images ON vowide_image_list_images.id = va_provider_images.vowide_vmiinstanceid
LEFT OUTER JOIN vowide_image_lists ON vowide_image_lists.id = vowide_image_list_images.vowide_image_list_id
WHERE va_providers.id = $1 AND va_provider_images.vmiinstanceid = $2 AND NOT $3 IS DISTINCT FROM vowide_vmiinstanceid
) as siteimageoccids
$function$;

CREATE OR REPLACE FUNCTION public.site_service_imageocciids_to_xml(providerid text, vmiinstanceid integer)
 RETURNS xml
 LANGUAGE sql 
 STABLE
AS $function$SELECT xmlagg(siteimageoccids.x) FROM (
SELECT XMLELEMENT(NAME "siteservice:occi",
	XMLATTRIBUTES(va_provider_images.va_provider_image_id AS id, va_provider_images.id AS providerimageid, vowide_image_list_images.id AS voimageid , vowide_image_lists.state AS voimagestate),
	--vo_to_xml(vowide_image_lists.void)
    XMLELEMENT(
        NAME "vo:vo",
        XMLATTRIBUTES(
            vowide_image_lists.void AS id,
            (SELECT name FROM vos WHERE id = vowide_image_lists.void) AS name
        )
    )              
) as x
FROM va_providers
INNER JOIN va_provider_images ON va_provider_images.va_provider_id = va_providers.id
LEFT OUTER JOIN vowide_image_list_images ON vowide_image_list_images.id = va_provider_images.vowide_vmiinstanceid
LEFT OUTER JOIN vowide_image_lists ON vowide_image_lists.id = vowide_image_list_images.vowide_image_list_id
WHERE va_providers.id = $1 AND va_provider_images.vmiinstanceid = $2
) as siteimageoccids
$function$;

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 12, 36, E'site_service_imageocciids_to_xml performance'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=12 AND revision=36);

COMMIT;
