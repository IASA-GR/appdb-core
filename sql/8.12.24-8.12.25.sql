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
Previous version: 8.12.24
New version: 8.12.25
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

CREATE OR REPLACE FUNCTION count_site_matches(
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
		LEFT JOIN __va_provider_images AS va_provider_images ON va_provider_images.va_provider_id = va_providers.id
		LEFT JOIN vaviews ON vaviews.vmiinstanceid = va_provider_images.vmiinstanceid
		LEFT JOIN applications ON applications.id = vaviews.appid
		LEFT JOIN appdisciplines ON appdisciplines.appid = applications.id
		LEFT JOIN disciplines ON disciplines.id = appdisciplines.disciplineid';
		-- q := 'SELECT disciplines.name::TEXT AS count_text, COUNT(DISTINCT sites.id) AS count, disciplines.id AS count_id FROM ' || cachetable || ' AS sites' || CASE WHEN NOT private THEN ' LEFT JOIN app_vos ON app_vos.appid = applications.id LEFT JOIN vos ON vos.id = app_vos.void AND vos.deleted IS FALSE' ELSE '' END || ' LEFT JOIN appdisciplines ON appdisciplines.appid = applications.id LEFT JOIN disciplines ON disciplines.id = appdisciplines.disciplineid' || CASE WHEN NOT private THEN ' OR disciplines.id = vos.domainid' ELSE '' END;
	ELSIF itemname = 'category' THEN
		q := 'SELECT categories.name::TEXT AS count_text, COUNT(DISTINCT sites.id) AS count, categories.id AS count_id
		FROM ' || cachetable || ' AS sites LEFT JOIN va_providers ON va_providers.sitename = sites.name LEFT JOIN __va_provider_images AS va_provider_images ON va_provider_images.va_provider_id = va_providers.id
		LEFT JOIN vaviews ON vaviews.vmiinstanceid = va_provider_images.vmiinstanceid LEFT JOIN applications ON applications.id = vaviews.appid LEFT JOIN categories ON categories.id = ANY(applications.categoryid)';
		--q := 'SELECT categories.name::TEXT AS count_text, COUNT(DISTINCT applications.id) AS count, categories.id AS count_id FROM ' || cachetable || ' AS applications LEFT JOIN categories ON categories.id = ANY(applications.categoryid)';
	ELSIF itemname = 'arch' THEN
		q := 'SELECT archs.name::TEXT AS count_text, COUNT(DISTINCT sites.id) AS count, archs.id AS count_id FROM ' || cachetable || ' AS sites LEFT JOIN va_providers ON va_providers.sitename = sites.name
		LEFT JOIN __va_provider_images AS va_provider_images ON va_provider_images.va_provider_id = va_providers.id
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
		LEFT JOIN __va_provider_images AS va_provider_images ON va_provider_images.va_provider_id = va_providers.id
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
		LEFT JOIN __va_provider_images AS va_provider_images ON va_provider_images.va_provider_id = va_providers.id
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
		LEFT JOIN __va_provider_images AS va_provider_images ON va_provider_images.va_provider_id = va_providers.id
		LEFT JOIN vaviews ON vaviews.vmiinstanceid = va_provider_images.vmiinstanceid
		LEFT JOIN applications ON applications.id = vaviews.appid
		LEFT JOIN vapplications ON vapplications.appid = applications.id
		LEFT JOIN vapp_versions ON vapp_versions.vappid = vapplications.id AND published AND enabled AND NOT archived AND status = ''verified''
		LEFT JOIN vmis ON vmis.vappid = vapplications.id
		LEFT JOIN vmiflavours ON vmiflavours.vmiid = vmis.id
		LEFT JOIN hypervisors ON hypervisors.name::text = ANY(vmiflavours.hypervisors::TEXT[])';
	ELSIF itemname = 'vo' THEN
		q := 'SELECT vos.name::TEXT AS count_text, COUNT(DISTINCT sites.id) AS count, vos.id AS count_id FROM ' || cachetable || ' AS sites
		LEFT JOIN va_providers ON va_providers.sitename = sites.name
		LEFT JOIN __va_provider_images AS va_provider_images ON va_provider_images.va_provider_id = va_providers.id AND va_provider_images.vowide_vmiinstanceid IS NOT NULL
		LEFT JOIN vowide_image_list_images ON vowide_image_list_images.ID = va_provider_images.vowide_vmiinstanceid and vowide_image_list_images.state = ''up-to-date''::e_vowide_image_state
		LEFT JOIN vowide_image_lists ON vowide_image_lists.id = vowide_image_list_images.vowide_image_list_id AND vowide_image_list_images.state <> ''draft''::e_vowide_image_state
		LEFT JOIN vos ON vos.id = vowide_image_lists.void AND vos.deleted IS FALSE';
	ELSIF itemname = 'middleware' THEN
		q := 'SELECT middlewares.name::TEXT AS count_text, COUNT(DISTINCT sites.id) AS count, middlewares.id AS count_id FROM ' || cachetable || ' AS sites
		LEFT JOIN va_providers ON va_providers.sitename = sites.name
		LEFT JOIN __va_provider_images AS va_provider_images ON va_provider_images.va_provider_id = va_providers.id
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
		LEFT JOIN __va_provider_images AS va_provider_images ON va_provider_images.va_provider_id = va_providers.id
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
ALTER FUNCTION count_site_matches(text, text, boolean)
  OWNER TO appdb;
COMMENT ON FUNCTION count_site_matches(text, text, boolean) IS 'not to be called directly; used by site_logistics function';

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 12, 25, E'Performance improvements for site logistics'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=12 AND revision=25);

COMMIT;
