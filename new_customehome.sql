SELECT * FROM app_xml_report(520,NULL,NULL,1);

CREATE OR REPLACE FUNCTION public.app_xml_report(
    mid integer,
    lim integer DEFAULT NULL::integer,
    ofs integer DEFAULT NULL::integer,
    listmode int DEFAULT 0)
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
			array_to_string(array_agg((SELECT * FROM app_to_xml_list(ARRAY[appid]))), '')::xml
		WHEN 2 THEN
			array_to_string(array_agg((SELECT * FROM app_to_xml_ext(appid))), '')::xml
		ELSE
			array_to_string(array_agg(app_to_xml(appid)), '')::xml
		END
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
INNER JOIN app_xml_report_counts($1) AS ccc ON ccc.metatype = t.metatype AND ccc.association = t.association
GROUP BY t.association, t.metatype, ccc.cnt
ORDER BY t.association, t.metatype;
$BODY$
  LANGUAGE sql STABLE
  COST 100
  ROWS 1000;
ALTER FUNCTION public.app_xml_report(integer, integer, integer, integer)
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
applications.id AS id, applications.rating, applications.ratingcount AS "ratingCount",
applications.cname,
applications.metatype,
applications.hitcount,
applications.moderated,
applications.deleted,
applications.guid
), 
XMLELEMENT(name "application:name", applications.name),
XMLELEMENT(name "application:category", XMLATTRIBUTES(c.id, TRUE AS primary), c.name)
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
) AS t
GROUP BY association, metatype ORDER BY association, metatype;
$BODY$
  LANGUAGE sql STABLE
  COST 100
  ROWS 1000;
ALTER FUNCTION public.app_xml_report_counts(integer)
  OWNER TO appdb;

SELECT * FROM app_xml_report_counts(520)
