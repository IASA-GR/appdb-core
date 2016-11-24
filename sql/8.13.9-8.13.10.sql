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
Previous version: 8.13.9
New version: 8.13.10
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

CREATE OR REPLACE FUNCTION public.app_xml_report_counts(
    mid integer
)
  RETURNS TABLE(cnt bigint,metatype int, association text) AS 
$BODY$
SELECT 
COUNT(*) AS cnt,
metatype,
association
FROM (
SELECT * FROM (
SELECT applications.id AS appid, applications.metatype AS metatype, 'editable'::text AS association
FROM applications 
INNER JOIN editable_app_ids($1) AS e ON e.e = applications.id
WHERE NOT applications.deleted AND NOT applications.moderated
ORDER BY applications.name ASC
) AS tt0
UNION ALL
SELECT * FROM (
SELECT applications.id, applications.metatype, 'followed'::text
FROM applications 
INNER JOIN followed_app_ids($1) AS f ON f.f = applications.id
WHERE NOT applications.deleted AND NOT applications.moderated
ORDER BY applications.name ASC
) AS tt1
UNION ALL
SELECT * FROM (
SELECT appbookmarks.appid, applications.metatype, 'bookmarked'::text
FROM appbookmarks
INNER JOIN applications ON applications.id = appbookmarks.appid
WHERE researcherid = $1
AND NOT applications.deleted AND NOT applications.moderated
ORDER BY applications.name ASC
) AS tt2
UNION ALL
SELECT * FROM (
SELECT appid, applications.metatype, 'associated'::text
FROM researchers_apps
INNER JOIN applications ON applications.id = researchers_apps.appid
WHERE researcherid = $1
AND NOT applications.deleted AND NOT applications.moderated
ORDER BY applications.name ASC
) AS tt3
UNION ALL
SELECT * FROM (
SELECT applications.id, applications.metatype, 'owned'::text
FROM applications 
WHERE (owner = $1 OR addedby = $1)
AND NOT applications.deleted AND NOT applications.moderated
) AS tt4
) AS t
GROUP BY association, metatype ORDER BY association, metatype;
$BODY$
  LANGUAGE sql STABLE
  COST 100
  ROWS 1000;
ALTER FUNCTION public.app_xml_report_counts(integer)
  OWNER TO appdb;

CREATE OR REPLACE FUNCTION app_xml_report(
    mid integer,
    lim bigint DEFAULT 9223372036854775807::bigint,
    ofs bigint DEFAULT 0::bigint,
    listmode integer DEFAULT 0)
  RETURNS SETOF xml AS
$BODY$
SELECT 
        XMLELEMENT(
                name "appdb:list",
                XMLATTRIBUTES(
                        ccc.cnt AS "count",
                        'application' AS datatype,
                        $1 AS userid,
                        t.metatype,
                        t.association,
                        CASE t.metatype
                                WHEN 0 THEN 'software'
                                WHEN 1 THEN 'vappliance'
                                WHEN 2 THEN 'swappliance' 
                        END || '_' || t.association AS "key"
                ),
                CASE $4 WHEN 1 THEN
                        array_to_string(array_agg((SELECT * FROM app_to_xml_list(ARRAY[appid])) ORDER BY name), '')::xml
                WHEN 2 THEN
                        array_to_string(array_agg((SELECT * FROM app_to_xml_ext(appid)) ORDER BY name), '')::xml
                ELSE
                        array_to_string(array_agg(app_to_xml(appid) ORDER BY name), '')::xml
                END
        )
FROM (
SELECT * FROM (
SELECT * FROM (
SELECT 
ROW_NUMBER() OVER (PARTITION BY applications.metatype ORDER BY applications.name) AS r,
applications.name, applications.id AS appid, applications.metatype AS metatype, 'editable'::text AS association
FROM applications 
INNER JOIN editable_app_ids($1) AS e ON e.e = applications.id
WHERE NOT applications.deleted AND NOT applications.moderated
ORDER BY applications.metatype, applications.name ASC
) AS tt0
WHERE r <= COALESCE($2, 9223372036854775807) AND r > COALESCE($3, 0)
) AS t0
UNION ALL
SELECT * FROM (
SELECT * FROM (
SELECT 
ROW_NUMBER() OVER (PARTITION BY applications.metatype ORDER BY applications.name) AS r,
applications.name, applications.id, applications.metatype, 'followed'::text
FROM applications 
INNER JOIN followed_app_ids($1) AS f ON f.f = applications.id
WHERE NOT applications.deleted AND NOT applications.moderated
ORDER BY applications.name ASC
) AS tt1
WHERE r <= COALESCE($2, 9223372036854775807) AND r > COALESCE($3, 0)
) AS t1
UNION ALL
SELECT * FROM (
SELECT * FROM (
SELECT ROW_NUMBER() OVER (PARTITION BY applications.metatype ORDER BY applications.name) AS r,
applications.name, appbookmarks.appid, applications.metatype, 'bookmarked'::text
FROM appbookmarks
INNER JOIN applications ON applications.id = appbookmarks.appid
WHERE researcherid = $1
AND NOT applications.deleted AND NOT applications.moderated
ORDER BY applications.name ASC
) AS tt2
WHERE r <= COALESCE($2, 9223372036854775807) AND r > COALESCE($3, 0)
) AS t2
UNION ALL
SELECT * FROM (
SELECT * FROM (
SELECT ROW_NUMBER() OVER (PARTITION BY applications.metatype ORDER BY applications.name) AS r,
applications.name, appid, applications.metatype, 'associated'::text
FROM researchers_apps
INNER JOIN applications ON applications.id = researchers_apps.appid
WHERE researcherid = $1
AND NOT applications.deleted AND NOT applications.moderated
ORDER BY applications.name ASC
) AS tt3
WHERE r <= COALESCE($2, 9223372036854775807) AND r > COALESCE($3, 0)
) AS t3
UNION ALL
SELECT * FROM (
SELECT * FROM (
SELECT ROW_NUMBER() OVER (PARTITION BY applications.metatype ORDER BY applications.name) AS r,
applications.name, applications.id, applications.metatype, 'owned'::text
FROM applications
WHERE (owner = $1 OR addedby = $1)
AND NOT applications.deleted AND NOT applications.moderated
ORDER BY applications.name ASC
) AS tt4
WHERE r <= COALESCE($2, 9223372036854775807) AND r > COALESCE($3, 0)
) AS t4
) AS t
INNER JOIN app_xml_report_counts($1) AS ccc ON ccc.metatype = t.metatype AND ccc.association = t.association
GROUP BY t.association, t.metatype, ccc.cnt
ORDER BY t.association, t.metatype;
$BODY$
  LANGUAGE sql STABLE
  COST 100
  ROWS 1000;
ALTER FUNCTION public.app_xml_report(integer, bigint, bigint, integer)
  OWNER TO appdb;

CREATE OR REPLACE FUNCTION public.app_to_xml_list(ids integer[])
  RETURNS SETOF xml AS
$BODY$
SELECT 
XMLELEMENT(
name "application:application",
XMLATTRIBUTES(
applications.id AS id, applications.rating, applications.ratingcount AS "ratingCount",
applications.cname,
applications.metatype,
applications.hitcount,
applications.lastupdated,
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
$BODY$
  LANGUAGE sql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION public.app_to_xml_list(integer[])
  OWNER TO appdb;

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 13, 10, E'Fixed missing owned s/w in app_xml_report function'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=13 AND revision=10);

COMMIT;
