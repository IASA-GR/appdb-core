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
Previous version: 8.15.3
New version: 8.15.4
Author: wvkarag@kadath.priv.iasa.gr
*/

START TRANSACTION;

CREATE OR REPLACE FUNCTION public.ppl_vo_xml_report_counts(IN mid integer)
  RETURNS TABLE(cnt bigint, relation text) AS
$BODY$
SELECT 
COUNT(*) AS cnt,
relation
FROM (
SELECT * FROM (
SELECT DISTINCT ON (void) void, vos.name, 'member'::text AS relation, member_since
FROM vo_members 
INNER JOIN vos ON vos.id = vo_members.void
WHERE researcherid = $1
) AS tt0
UNION ALL
SELECT * FROM (
SELECT DISTINCT ON (void, relation) void, vos.name, LOWER(REPLACE(role::text,'VO ', '')) AS relation, NULL::timestamp AS member_since
FROM vo_contacts
INNER JOIN vos ON vos.id = vo_contacts.void
WHERE researcherid = $1
) AS tt1
) AS t
GROUP BY relation 
ORDER BY relation;
$BODY$
  LANGUAGE sql STABLE
  COST 100
  ROWS 1000;
ALTER FUNCTION public.ppl_vo_xml_report_counts(integer)
  OWNER TO appdb;

CREATE OR REPLACE FUNCTION public.ppl_vo_xml_report(
    mid integer,
    lim bigint DEFAULT '9223372036854775807'::bigint,
    ofs bigint DEFAULT (0)::bigint,
    listmode integer DEFAULT 0)
  RETURNS SETOF xml AS
$BODY$
SELECT
	XMLELEMENT(
		name "appdb:list",
		XMLATTRIBUTES(
			ccc.cnt AS "count",
			'vo' AS datatype,
			$1 AS userid,
			-- t.relation,
			'vos_' || t.relation AS key
		),
		CASE $4 WHEN 1 THEN
			REPLACE(array_to_string(array_agg((SELECT * FROM vo_to_xml(void)) ORDER BY name), ''), '<vo:vo ', '<vo:vo relation="' || t.relation || '" ')::xml
		WHEN 2 THEN
			REPLACE(array_to_string(array_agg((SELECT * FROM vo_to_xml_ext(void)) ORDER BY name), ''), '<vo:vo ', '<vo:vo relation="' || t.relation || '" ')::xml
		ELSE
			REPLACE(array_to_string(array_agg(vo_to_xml(void) ORDER BY name), ''), '<vo:vo ', '<vo:vo relation="' || t.relation || '" ')::xml
		END
	)
FROM (
SELECT * FROM (
SELECT DISTINCT ON (void) void, vos.name, 'member'::text AS relation, member_since
FROM vo_members
INNER JOIN vos ON vos.id = vo_members.void AND NOT vos.deleted
WHERE researcherid = $1
LIMIT $2 OFFSET $3
) AS tt0
UNION ALL
SELECT * FROM (
SELECT DISTINCT ON (void, relation) void, vos.name, LOWER(REPLACE(role::text,'VO ', '')) AS relation, NULL::timestamp AS member_since
FROM vo_contacts
INNER JOIN vos ON vos.id = vo_contacts.void AND NOT vos.deleted
WHERE researcherid = $1
LIMIT $2 OFFSET $3
) AS tt1
) AS t
INNER JOIN ppl_vo_xml_report_counts($1) AS ccc ON ccc.relation = t.relation
GROUP BY t.relation, ccc.cnt
ORDER BY t.relation
$BODY$
  LANGUAGE sql STABLE
  COST 100
  ROWS 1000;
ALTER FUNCTION public.ppl_vo_xml_report(integer, bigint, bigint, integer)
  OWNER TO appdb;

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 15, 4, E'Fix dupliace VO entries in people VO XML report'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=15 AND revision=4);

COMMIT;
