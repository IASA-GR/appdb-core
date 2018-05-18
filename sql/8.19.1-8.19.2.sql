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
Previous version: 8.19.1
New version: 8.19.2
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

CREATE OR REPLACE FUNCTION public.app_to_json(mid integer)
  RETURNS text AS
$BODY$
SELECT '{"application": {' ||
	'"id": ' || to_json(applications.id) || ', ' ||
	'"handle": ' || to_json(applications.pidhandle) || ', ' ||
	'"name": ' || to_json(applications.name::text) || ', ' ||
	'"cname": ' || to_json(applications.cname::text) || ', ' ||
	'"description": ' || COALESCE(to_json(applications.description::text), 'null') ||', ' ||
	'"rating": ' || COALESCE(to_json(applications.rating), 'null') || ', ' ||
	'"tool": '|| to_json(applications.tool) || ', ' ||
	'"discipline": [' || array_to_string(array_agg(DISTINCT '{' ||
		'"id": ' || to_json(disciplines.id) || ', ' ||
		'"name": ' || to_json(disciplines.name::text) || '}'),',') || '], ' ||
	'"category": [' || array_to_string(array_agg(DISTINCT '{' ||
		'"id": '|| to_json(categories.id) || ', ' ||
		'"name": ' || to_json(categories.name::text) || ', ' ||
		'"isPrimary": ' || to_json(appcategories.isprimary) || ', ' ||
		'"parentid": ' || COALESCE(to_json(categories.parentid::text), 'null') || '}'),',') || ']}}'
FROM public.applications
LEFT OUTER JOIN public.disciplines ON public.disciplines.id = ANY(public.applications.disciplineid)
LEFT OUTER JOIN public.appcategories ON public.appcategories.categoryid = ANY(public.applications.categoryid) AND public.appcategories.appid = $1
LEFT OUTER JOIN public.categories ON public.categories.id = public.appcategories.categoryid
WHERE public.applications.id = $1
GROUP BY public.applications.id, public.applications.name, public.applications.description, public.applications.rating, public.applications.tool;
$BODY$
  LANGUAGE sql STABLE
  COST 100;
ALTER FUNCTION public.app_to_json(integer)
  OWNER TO appdb;

-- Function: public.__app_to_xml(integer[])

-- DROP FUNCTION public.__app_to_xml(integer[]);

CREATE OR REPLACE FUNCTION public.__app_to_xml(m_id integer[])
  RETURNS SETOF xml AS
$BODY$
DECLARE m_categories TEXT;
DECLARE m_disciplines TEXT;
DECLARE m_urls TEXT;
DECLARE m_tags TEXT;
DECLARE m_va TEXT;
DECLARE m_va_hyper TEXT;
DECLARE m_va_os TEXT;
DECLARE m_va_arch TEXT;
DECLARE i INT;
DECLARE j INT;
DECLARE apps RECORD;
DECLARE myxml XML[];
DECLARE isprimarycat BOOL;
BEGIN
myxml := NULL::XML[];
FOR apps IN
-- PREPARE q1 AS
SELECT applications.*,
applications.pidhandle,
lastupdated BETWEEN NOW() - (SELECT data FROM config WHERE var='app_validation_period' LIMIT 1)::INTERVAL AND NOW() as "validated",
app_popularities.popularity,
array_agg(DISTINCT categories) as categories,
array_agg(DISTINCT appcategories) as appcategories,
array_agg(DISTINCT disciplines) as disciplines,
statuses.name as status_name,
array_agg(DISTINCT app_urls) as urls,
applications.keywords AS tags,
hitcounts.count as hitcount,
(array_agg(applogos.logo))[1] AS logo,
array_agg(DISTINCT vapp_versions) AS vapp_versions,
array_agg(DISTINCT vmiflavours.hypervisors::TEXT) AS vmihyper,
array_agg(DISTINCT vmiflavours.osid::TEXT) AS vmios,
array_agg(DISTINCT vmiflavours.archid::TEXT) AS vmiarch,
vapplications.imglst_private
FROM applications
LEFT OUTER JOIN appcategories ON appcategories.appid = applications.id
LEFT OUTER JOIN categories ON categories.id = appcategories.categoryid
LEFT OUTER JOIN appdisciplines ON appdisciplines.appid = applications.id
LEFT OUTER JOIN disciplines ON disciplines.id = appdisciplines.disciplineid
LEFT OUTER JOIN statuses ON statuses.id = applications.statusid
LEFT OUTER JOIN app_urls ON app_urls.appid = applications.id
LEFT OUTER JOIN hitcounts ON hitcounts.appid = applications.id
LEFT OUTER JOIN app_popularities ON app_popularities.appid = applications.id
LEFT OUTER JOIN applogos ON applogos.appid = applications.id
LEFT OUTER JOIN vapplications ON vapplications.appid = applications.id
LEFT OUTER JOIN vapp_versions ON vapp_versions.vappid = vapplications.id
	AND published IS TRUE
	AND enabled IS TRUE
	AND archived IS FALSE
	AND status = 'verified'
LEFT OUTER JOIN vmis ON vmis.vappid = vapplications.id
LEFT OUTER JOIN vmiflavours ON vmiflavours.vmiid = vmis.id
WHERE applications.id = ANY($1)
GROUP BY applications.id,
statuses.name,
hitcounts.count,
app_popularities.popularity,
imglst_private
ORDER BY idx($1, applications.id)
-- EXECUTE q1(ARRAY[787])
LOOP
	/*IF NOT apps.tags[1] IS NULL THEN
		m_tags = '';
		FOR i IN 1..array_length(apps.tags, 1) LOOP
			m_tags := m_tags || xmlelement(name "application:tag",
				xmlattributes(false AS system), apps.tags[i]
			);
		END LOOP;Added helper function to return the count of sites supporting the images of a given vappliance id
	END IF; */
	IF apps.categories[1] IS NULL THEN
		m_categories = '<application:category xsi:nil="true" id="0" />';
	ELSE
		m_categories = '';
		FOR i IN 1..array_length(apps.categories, 1) LOOP
			isprimarycat := FALSE;
			FOR j IN 1..array_length(apps.appcategories, 1) LOOP
				IF (apps.appcategories[j]).categoryid = (apps.categories[i]).id THEN
					isprimarycat := (apps.appcategories[j]).isprimary;
					EXIT;
				END IF;
			END LOOP;
			m_categories := m_categories || xmlelement(name "application:category",
			xmlattributes(
				(apps.categories[i]).id AS "id",
				isprimarycat AS "primary",
				CASE (apps.categories[i]).parentid WHEN NULL THEN NULL ELSE (apps.categories[i]).parentid END AS "parentid"
			),
			(apps.categories[i]).name)::TEXT;
		END LOOP;
	END IF;
	IF apps.disciplines[1] IS NULL THEN
		m_disciplines = '<discipline:discipline xsi:nil="true" id="0" />';
	ELSE
		m_disciplines = '';
		FOR i IN 1..array_length(apps.disciplines, 1) LOOP
			IF NOT apps.disciplines[i] IS NULL THEN
				m_disciplines := m_disciplines || xmlelement(name "discipline:discipline",
				xmlattributes(
					(apps.disciplines[i]).id AS "id",
					(apps.disciplines[i]).parentid AS "parentid"
				),
				(apps.disciplines[i]).name)::TEXT;
			END IF;
		END LOOP;
	END IF;
	IF apps.urls[1] IS NULL THEN
		m_urls = '<application:url xsi:nil="true" />';
	ELSE
		m_urls = '';
		FOR i IN 1..array_length(apps.urls, 1) LOOP
			m_urls := m_urls || xmlelement(name "application:url",
			xmlattributes((apps.urls[i]).id AS "id",
			(apps.urls[i]).description AS "type",
			(apps.urls[i]).ord AS "ord",
			(apps.urls[i]).title AS "title"),
			(apps.urls[i]).url)::TEXT;
		END LOOP;
	END IF;
	IF NOT apps.vapp_versions[1] IS NULL THEN
		m_va = '';
		FOR i IN 1..array_length(apps.vapp_versions, 1) LOOP
			m_va_hyper = '';
			FOR j IN 1..array_length(apps.vmihyper, 1) LOOP
				IF NOT apps.vmihyper[j] IS NULL THEN
					m_va_hyper := m_va_hyper || xmlelement(
						name "virtualization:hypervisor",
						xmlattributes(
							array_to_string((SELECT array_agg(id::TEXT) FROM hypervisors WHERE name = ANY(apps.vmihyper[j]::TEXT[])), ',') AS id
						),
						array_to_string(apps.vmihyper[j]::TEXT[], ',')
					);
				END IF;
			END LOOP;
			m_va_os = '';
			FOR j IN 1..array_length(apps.vmios, 1) LOOP
				IF NOT apps.vmios[j] IS NULL THEN
					m_va_os := m_va_os || xmlelement(
						name "virtualization:os",
						xmlattributes(
							apps.vmios[j] AS id
						), (SELECT name FROM oses WHERE id = apps.vmios[j]::int)
					);
				END IF;
			END LOOP;
			m_va_arch = '';
			FOR j IN 1..array_length(apps.vmiarch, 1) LOOP
				IF NOT apps.vmiarch[j] IS NULL THEN
					m_va_arch := m_va_arch || xmlelement(
						name "virtualization:arch",
						xmlattributes(
							apps.vmiarch[j]::TEXT AS id
						), (SELECT name FROM archs WHERE id = apps.vmiarch[j]::int)
					);
				END IF;
			END LOOP;
			m_va = m_va || xmlelement(name "virtualization:appliance",
				xmlattributes(
					(apps.vapp_versions[i]).vappid AS "id",
					(apps.vapp_versions[i]).id AS "versionid",
					(apps.vapp_versions[i]).version AS "version",
					(apps.vapp_versions[i]).createdon AS "createdOn",
					(apps.vapp_versions[i]).expireson AS "expiresOn",
					NOW() > (apps.vapp_versions[i]).expireson AS "expired",
					apps.imglst_private AS imageListPrivate
				),
				m_va_hyper::XML, m_va_os::XML, m_va_arch::XML
			);
		END LOOP;
	END IF;
	myxml := array_append(myxml, (SELECT xmlelement(name "application:application",
xmlattributes(apps.id,
apps.pidhandle AS "handle",
apps.tool,
apps.rating,
apps.ratingcount as "ratingCount",
apps.popularity,
apps.cname,
apps.metatype,
CASE WHEN apps.metatype = 2 THEN
(SELECT COUNT(context_script_assocs.scriptid) FROM context_script_assocs INNER JOIN contexts ON contexts.id = context_script_assocs.contextid WHERE contexts.appid = apps.id)
ELSE (SELECT relcount FROM app_release_count WHERE appid = apps.id)
END AS relcount,
apps.hitcount,
(SELECT vappliance_site_count(apps.id) ) as "sitecount",
apps.validated,
apps.moderated,
apps.deleted,
apps.guid),
xmlelement(name "application:name", apps.name), E'\n\t',
xmlelement(name "application:description", apps.description),E'\n\t',
xmlelement(name "application:abstract", apps.abstract),E'\n\t',
xmlelement(name "application:addedOn", apps.dateadded),E'\n\t',
xmlelement(name "application:lastUpdated", apps.lastupdated),E'\n\t',
m_categories::XML, E'\n\t',
m_disciplines::XML, E'\n\t',
xmlelement(name "application:status", xmlattributes(apps.statusid AS "id"), apps.status_name),  E'\n\t',
m_urls::XML, E'\n\t',
m_va::XML, E'\n\t',
xmlelement(name "application:permalink",'http://'||(SELECT data FROM config WHERE var='ui-host')||'/?p='||encode(CAST('/apps/details?id='||apps.id::text AS bytea),'base64')), E'\n\t',
CASE WHEN NOT apps.logo IS NULL THEN
xmlelement(name "application:logo",'http://'||(SELECT data FROM config WHERE var='ui-host')||'/apps/getlogo?id='||apps.id::text)
END, E'\n\t'-- ,
-- xmlelement(name "application:tags", array_to_string(apps.tags,','))
-- m_tags::XML, E'\n\t'
)));
END LOOP;
RETURN QUERY SELECT unnest(myxml);
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION public.__app_to_xml(integer[])
  OWNER TO appdb;


-- Function: public.app_to_xml_list(integer[])

-- DROP FUNCTION public.app_to_xml_list(integer[]);

CREATE OR REPLACE FUNCTION public.app_to_xml_list(ids integer[])
  RETURNS SETOF xml AS
$BODY$
SELECT
XMLELEMENT(
name "application:application",
XMLATTRIBUTES(
applications.id AS id,
applications.pidhandle AS handle,
applications.rating,
applications.ratingcount AS "ratingCount",
applications.cname,
applications.metatype,
applications.hitcount,
applications.moderated,
applications.deleted,
applications.guid
),
XMLELEMENT(name "application:name", applications.name),
XMLELEMENT(name "application:category", XMLATTRIBUTES(c.id, TRUE AS primary), c.name),
CASE WHEN NOT (SELECT logo FROM applogos WHERE appid = applications.id) IS NULL THEN
	XMLELEMENT(name "application:logo", 'https://' || (SELECT data FROM config WHERE var = 'ui-host') || '/apps/getlogo?id=' || applications.id::text)
END
)
FROM applications
INNER JOIN LATERAL (SELECT id, name FROM categories WHERE id = ANY(applications.categoryid)
AND EXISTS (SELECT * FROM appcategories WHERE isprimary AND appid = applications.id AND categoryid = categories.id)
) AS c ON true
WHERE applications.id = ANY(ids)
ORDER BY idx(ids, applications.id)
$BODY$
  LANGUAGE sql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION public.app_to_xml_list(integer[])
  OWNER TO appdb;

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 19, 2, E'Add Handle PID info in other app XML representations and app JSON representation functions'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=19 AND revision=2);

COMMIT;	
