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
Previous version: 8.20.4
New version: 8.20.5
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

START TRANSACTION;

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
		m_categories = '<application:category xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:nil="true" id="0" />';
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
		m_disciplines = '<discipline:discipline xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:nil="true" id="0" />';
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
		m_urls = '<application:url xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:nil="true" />';
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


CREATE OR REPLACE FUNCTION public.app_licenses_to_xml(mid integer)
  RETURNS xml AS
$BODY$
DECLARE x XML;
BEGIN
	x := (
	SELECT XMLAGG(
		XMLELEMENT(
			name "application:license",
			xmlattributes(
				app_licenses.licenseid AS id,
				licenses.name AS name,
				licenses.group AS "group"
			), XMLELEMENT(name "license:title", CASE WHEN app_licenses.licenseid = 0 THEN app_licenses.title ELSE licenses.title END),
			XMLELEMENT(name "license:url", CASE WHEN app_licenses.licenseid = 0 THEN app_licenses.link ELSE licenses.link END),
			XMLELEMENT(name "license:comment",
				XMLATTRIBUTES(
					CASE WHEN app_licenses.comment IS NULL THEN 'http://www.w3.org/2001/XMLSchema-instance' END AS "xmlns:xsi",
					CASE WHEN app_licenses.comment IS NULL THEN 'true' END AS "xsi:nil"
				), app_licenses.comment
			)
		)
	) FROM app_licenses
	LEFT OUTER JOIN licenses ON licenses.id = app_licenses.licenseid
	WHERE appid = mid
	);
	RETURN x;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION public.app_licenses_to_xml(integer)
  OWNER TO appdb;

CREATE OR REPLACE FUNCTION public.appratings_to_xml(mid integer)
  RETURNS xml AS
$BODY$SELECT xmlelement(name "application:rating", xmlattributes(
id as "id",
moderated as "moderated"), E'\n\t',
CASE WHEN rating IS NULL THEN
xmlelement(name "rating:rating", xmlattributes('http://www.w3.org/2001/XMLSchema-instance' AS "xmlns:xsi", 'true' as "xsi:nil"))
ELSE
xmlelement(name "rating:rating", "rating")
END, E'\n\t',
xmlelement(name "rating:comment", CASE WHEN moderated IS TRUE THEN '' ELSE "comment" END), E'\n\t',
xmlelement(name "rating:submittedOn", submittedon), E'\n\t',
CASE WHEN submitterid IS NULL THEN
xmlelement(name "rating:submitter", xmlattributes('external'AS type, submitteremail as email), submittername)
ELSE
xmlelement(name "rating:submitter", xmlattributes('internal' AS type), researcher_to_xml(submitterid))
END)
FROM appratings WHERE id = $1
$BODY$
  LANGUAGE sql VOLATILE
  COST 100;
ALTER FUNCTION public.appratings_to_xml(integer)
  OWNER TO appdb;

CREATE OR REPLACE FUNCTION public.category_to_xml(
    mid integer,
    appid integer DEFAULT NULL::integer)
  RETURNS xml AS
$BODY$SELECT CASE WHEN $1 IS NULL THEN (SELECT xmlelement(name "application:category", xmlattributes(
'http://www.w3.org/2001/XMLSchema-instance' AS "xmlns:xsi", 'true' as "xsi:nil", 0 as id))) ELSE (
SELECT xmlelement(name "application:category", xmlattributes(
	id as id,
	CASE WHEN parentid IS NULL THEN NULL ELSE parentid END as parentid,
	CASE WHEN ord > 0 THEN ord ELSE NULL END as "order",
	CASE WHEN $2 IS NULL THEN NULL ELSE (SELECT isprimary FROM appcategories WHERE appcategories.appid = $2 AND appcategories.categoryid = $1) END as "primary"), name)
FROM categories
WHERE id = $1) END;$BODY$
  LANGUAGE sql VOLATILE
  COST 100;
ALTER FUNCTION public.category_to_xml(integer, integer)
  OWNER TO appdb;

CREATE OR REPLACE FUNCTION public.discipline_to_xml(mid integer)
  RETURNS xml AS
$BODY$
SELECT CASE WHEN $1 IS NULL THEN (SELECT xmlelement(name "discipline:discipline", xmlattributes(
'http://www.w3.org/2001/XMLSchema-instance' AS "xmlns:xsi", 'true' as "xsi:nil", 0 as id))) ELSE (
SELECT xmlelement(name "discipline:discipline", xmlattributes(
	id as id,
	parentid as parentid,
	CASE WHEN ord > 0 THEN ord ELSE NULL END AS "order"
), name) FROM disciplines WHERE id = $1) END;
$BODY$
  LANGUAGE sql STABLE
  COST 100;
ALTER FUNCTION public.discipline_to_xml(integer)
  OWNER TO appdb;

CREATE OR REPLACE FUNCTION public.privs_to_xml(user_guid uuid)
  RETURNS SETOF xml AS
$BODY$
SELECT
	xmlelement(
		name "privilege:action",
		xmlattributes(
			"system" as "system",
			actionid as "id",
			description as "description"
		),
		xmlagg(
			xmlelement(
				name "privilege:target",
				xmlattributes(
					CASE WHEN object IS NULL AND NOT actionid IS NULL THEN 'http://www.w3.org/2001/XMLSchema-instance' END AS "xmlns:xsi",
					CASE WHEN object IS NULL AND NOT actionid IS NULL THEN true END AS "xsi:nil",
					CASE WHEN NOT object IS NULL THEN object END AS suid,
					CASE WHEN NOT object IS NULL THEN
						CASE targets.type
							WHEN 'app' THEN 'application'
							WHEN 'ppl' THEN 'person'
							WHEN 'grp' THEN 'group'
						END
					END AS type,
					CASE WHEN NOT object IS NULL THEN targets.id END AS id
				),
				CASE WHEN NOT object IS NULL THEN targets.name END
			) ORDER BY object NULLS FIRST
		)
	)
FROM (
	SELECT DISTINCT "system", actor, actionid, object
	FROM permissions
) AS permissions
INNER JOIN actions ON actions.id = permissions.actionid
LEFT OUTER JOIN targets ON targets.guid = object
WHERE
	actor = user_guid
	AND NOT COALESCE(targets.hidden, FALSE)
GROUP BY
	"system",
	actionid,
	description
ORDER BY actionid
;
$BODY$
  LANGUAGE sql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION public.privs_to_xml(uuid)
  OWNER TO appdb;

CREATE OR REPLACE FUNCTION public.subdiscipline_to_xml(mid integer)
  RETURNS xml AS
$BODY$SELECT CASE WHEN $1 IS NULL THEN (SELECT xmlelement(name "discipline:subdiscipline", xmlattributes(
'http://www.w3.org/2001/XMLSchema-instance' AS "xmlns:xsi", 'true' as "xsi:nil", 0 as id))) ELSE (SELECT xmlelement(name "discipline:subdiscipline", xmlattributes(
id as id), name) FROM subdomains WHERE id = $1) END $BODY$
  LANGUAGE sql VOLATILE
  COST 100;
ALTER FUNCTION public.subdiscipline_to_xml(integer)
  OWNER TO appdb;

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 20, 5, E'Ensure xmlns:xsi is defined on elements that make use of xsi:nil (multiple functions)'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=20 AND revision=5);

COMMIT;
	
