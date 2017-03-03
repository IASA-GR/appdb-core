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
Previous version: 8.14.0
New version: 8.14.1
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

CREATE OR REPLACE FUNCTION public.store_stats_to_xml(
    datefrom date DEFAULT NULL::date,
    dateto date DEFAULT NULL::date)
  RETURNS SETOF xml AS
$BODY$
WITH thestats AS (
        SELECT SUM(delta) AS s,
        delta < 0 AS isdel,
        what
        FROM (
        SELECT 
            t1.what,
            t1.theday,
            t1.cnt,
            t1.cnt - t2.cnt AS delta,
            t2.theday AS thepreviousday
        FROM 
        stats.storestats AS t1
        INNER JOIN stats.storestats AS t2 ON 
            t2.what = t1.what AND 
            t2.theday = (
                SELECT MAX(t3.theday) AS theday
                FROM stats.storestats AS t3
                WHERE
                t3.what = t1.what AND 
                t3.theday < t1.theday
            )
        WHERE 
            ((COALESCE($1, NOW()::date)::date <= t1.theday) AND (t1.theday < COALESCE($2, NOW()::date)::date))
        ORDER BY t1.theday ASC, t1.what ASC
        ) AS t
        GROUP BY delta < 0, what
        ORDER BY what
)

SELECT x::xml FROM (

SELECT ord1, ord2, n, x::text FROM (
    SELECT 
        1 as ord1,
        ROW_NUMBER() OVER() AS ord2,
        what AS n,
        XMLELEMENT(
            name "appdb:storestats",
            XMLATTRIBUTES(          
                'daily' AS "stats",
                CASE what
                    WHEN 'app' THEN 'software item'
                    WHEN 'va' THEN 'virtual appliance'
                    WHEN 'sa' THEN 'software appliance'
                    WHEN 'ppl' THEN 'person'
		    WHEN 'vo' THEN 'VO'
		    WHEN 'vap' THEN 'VA provider'
		    WHEN 'site' THEN 'site'
		    WHEN 'ds' THEN 'dataset'
		    WHEN 'dsr' THEN 'dataset replica'
                END AS "type",
                cnt AS "count",
                theday AS "when"
            )
        ) AS x
    FROM stats.storestats
    WHERE 
        ((COALESCE($1, NOW()::date)::date <= theday) AND (theday < COALESCE($2, NOW()::date)::date))
    ORDER BY theday DESC, what ASC
) AS x1

UNION

SELECT ord1, ord2, n, x::text FROM (SELECT 
    2 AS ord1,
    ROW_NUMBER() OVER() AS ord2,
    n,
    XMLELEMENT(
        name "appdb:storestats",
        XMLATTRIBUTES(
            'period' AS "stats",
            COALESCE($1, NOW()::date)::date AS "from",
            COALESCE($2, NOW()::date)::date AS "to",
            'daily' AS "granularity",
            COALESCE((SELECT s FROM thestats WHERE thestats.what = n AND NOT isdel), 0) AS "additions",
            COALESCE((SELECT ABS(s) FROM thestats WHERE thestats.what = n AND isdel), 0) AS "removals",
            CASE n
		    WHEN 'app' THEN 'software item'
		    WHEN 'va' THEN 'virtual appliance'
		    WHEN 'sa' THEN 'software appliance'
		    WHEN 'ppl' THEN 'person'
		    WHEN 'vo' THEN 'VO'
		    WHEN 'vap' THEN 'VA provider'
		    WHEN 'site' THEN 'site'
		    WHEN 'ds' THEN 'dataset'
		    WHEN 'dsr' THEN 'dataset replica'
            END AS "type"
        )
    ) AS x
    FROM UNNEST(ARRAY['va', 'sa', 'app', 'ppl', 'vo', 'vap', 'site', 'ds', 'dsr']) AS n
    ) AS xt2
    ORDER BY n
) AS x
ORDER BY x.ord1, x.ord2
;
$BODY$
  LANGUAGE sql STABLE
  COST 100
  ROWS 1000;
ALTER FUNCTION public.store_stats_to_xml(date, date)
  OWNER TO appdb;

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 14, 1, E'Added store_stats_to_xml function'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=14 AND revision=1);

COMMIT;
