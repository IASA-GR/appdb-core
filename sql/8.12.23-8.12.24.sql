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
Previous version: 8.12.23
New version: 8.12.24
Author: nakos@kelsius.localdomain
*/

START TRANSACTION;

-- Function: vapp_image_providers_to_xml(integer)

-- DROP FUNCTION vapp_image_providers_to_xml(integer);

CREATE OR REPLACE FUNCTION vapp_image_providers_to_xml(_appid integer)
  RETURNS SETOF xml AS
$BODY$
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
					va_provider_images.va_provider_image_id as occi_id,
					vowide_image_lists.void,
					vos.name as voname,
					va_provider_images.vmiinstanceid as vmiinstanceid
				)
			)::text
		),'')::xml
)
FROM 
	applications
	INNER JOIN vaviews ON vaviews.appid = applications.id
	INNER JOIN __va_provider_images AS va_provider_images ON va_provider_images.vmiinstanceid = vaviews.vmiinstanceid
	LEFT OUTER JOIN hypervisors ON hypervisors.vmiflavourid = vaviews.vmiflavourid
	LEFT OUTER JOIN archs ON archs.id = vaviews.archid
	LEFT OUTER JOIN oses ON oses.id = vaviews.osid
	LEFT OUTER JOIN vmiformats ON vmiformats.name::text = vaviews.format
	LEFT OUTER JOIN app_vos ON app_vos.appid = applications.id
	LEFT OUTER JOIN vowide_image_list_images ON vowide_image_list_images.id = va_provider_images.vowide_vmiinstanceid
	LEFT OUTER JOIN vowide_image_lists ON vowide_image_lists.id = vowide_image_list_images.vowide_image_list_id AND (vowide_image_lists.state::text = 'published' OR vowide_image_lists.state::text = 'obsolete')
	LEFT OUTER JOIN vos ON vos.id = vowide_image_lists.void
WHERE  
	vaviews.vmiinstanceid = get_good_vmiinstanceid(va_provider_images.vmiinstanceid) AND
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
$BODY$
  LANGUAGE sql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION vapp_image_providers_to_xml(integer)
  OWNER TO appdb;


INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 12, 24, E'Extract VO name in function vapp_image_providers_to_xml'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=12 AND revision=24);

COMMIT;
