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
Previous version: 8.12.31
New version: 8.12.32
Author: wvkarag@lovecraft.priv.iasa.gr
*/
START TRANSACTION;

CREATE OR REPLACE FUNCTION public.editable_app_ids(mid integer)
 RETURNS SETOF int
 LANGUAGE sql
 STABLE
AS $function$
SELECT DISTINCT applications.id AS appid
   FROM applications
     LEFT JOIN permissions ON permissions.object = applications.guid
     LEFT JOIN actions ON actions.id = permissions.actionid
  WHERE actions.id = ANY(app_metadata_actions())
AND actor = (SELECT guid FROM researchers WHERE id = $1)
UNION 
SELECT DISTINCT applications.id AS appid
   FROM applications     
     LEFT JOIN permissions ON permissions.object IS NULL
     LEFT JOIN actions ON actions.id = permissions.actionid
  WHERE actions.id = ANY(app_metadata_actions())
AND actor = (SELECT guid FROM researchers WHERE id = $1);
$function$;
ALTER FUNCTION editable_app_ids(int) OWNER TO appdb;

CREATE INDEX idx_mail_subscriptions_flt ON mail_subscriptions USING GIN(flt gin_trgm_ops);

CREATE OR REPLACE FUNCTION followed_app_ids(mid int)
RETURNS SETOF int
AS
$$
SELECT DISTINCT id FROM applications AS appid WHERE id IN (
	SELECT REGEXP_REPLACE(REGEXP_REPLACE(flt, ' +id:SYSTAG_FOLLOW.*', ''), '.+:', '')::int 
	FROM mail_subscriptions 
	WHERE flt LIKE '%id:SYSTAG_FOLLOW'
	AND researcherid = $1
);
$$ LANGUAGE sql STABLE;
ALTER FUNCTION followed_app_ids(int) OWNER TO appdb;

CREATE INDEX idx_applications_owner ON applications(owner);

CREATE OR REPLACE FUNCTION app_xml_report(mid int, lim int = NULL, ofs int = NULL) 
RETURNS SETOF xml
AS
$$
SELECT 
	XMLELEMENT(
		name "appdb:list",
		XMLATTRIBUTES(
			'application' AS datatype,
			$1 AS userid,
			metatype,
			association
		),
		array_to_string(array_agg(app_to_xml(appid)), '')::xml
	)
FROM (
SELECT * FROM (
SELECT * FROM (
SELECT applications.id AS appid, applications.metatype AS metatype, 'editable'::text AS association
FROM applications 
INNER JOIN editable_app_ids($1) AS e ON e.e = applications.id
WHERE NOT applications.deleted AND NOT applications.moderated
ORDER BY applications.name ASC
) AS tt0
LIMIT $2 OFFSET $3
) AS t0
UNION ALL
SELECT * FROM (
SELECT * FROM (
SELECT applications.id, applications.metatype, 'followed'::text
FROM applications 
INNER JOIN followed_app_ids($1) AS f ON f.f = applications.id
WHERE NOT applications.deleted AND NOT applications.moderated
ORDER BY applications.name ASC
) AS tt1
LIMIT $2 OFFSET $3
) AS t1
UNION ALL
SELECT * FROM (
SELECT * FROM (
SELECT appbookmarks.appid, applications.metatype, 'bookmarked'::text
FROM appbookmarks
INNER JOIN applications ON applications.id = appbookmarks.appid
WHERE researcherid = $1
AND NOT applications.deleted AND NOT applications.moderated
ORDER BY applications.name ASC
) AS tt2
LIMIT $2 OFFSET $3
) AS t2
UNION ALL
SELECT * FROM (
SELECT * FROM (
SELECT appid, applications.metatype, 'associated'::text
FROM researchers_apps
INNER JOIN applications ON applications.id = researchers_apps.appid
WHERE researcherid = $1
AND NOT applications.deleted AND NOT applications.moderated
ORDER BY applications.name ASC
) AS tt3
LIMIT $2 OFFSET $3
) AS t3
) AS t
GROUP BY association, metatype ORDER BY association, metatype;
$$ LANGUAGE SQL STABLE;
ALTER FUNCTION app_xml_report(int, int, int) OWNER TO appdb;

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 12, 32, E'Performance improvements'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=12 AND revision=32);

COMMIT;
