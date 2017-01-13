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
Previous version: 8.13.15
New version: 8.13.16
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

CREATE OR REPLACE FUNCTION public.vo_disc_stats_to_xml(mdisciplineid integer DEFAULT NULL::integer, datefrom date DEFAULT NULL::date, dateto date DEFAULT NULL::date)
 RETURNS SETOF xml
 LANGUAGE sql
 STABLE
AS $function$
WITH thestats AS (
        SELECT SUM(delta) AS s,
        delta < 0 AS isdel,
        disciplineid
        FROM (
        SELECT 
            t1.disciplineid,
            t1.theday,
            t1.cnt,
            t1.cnt - t2.cnt AS delta,
            t2.theday AS thepreviousday
        FROM 
        stats.vo_disc_stats AS t1
        INNER JOIN stats.vo_disc_stats AS t2 ON 
            t2.disciplineid = t1.disciplineid AND 
            t2.theday = (
                SELECT MAX(t3.theday) AS theday
                FROM stats.vo_disc_stats AS t3
                WHERE t3.disciplineid = t1.disciplineid AND 
                t3.theday < t1.theday
            )
        WHERE 
            ((COALESCE($2, NOW()::date)::date <= t1.theday) AND (t1.theday < COALESCE($3, NOW()::date)::date))
        AND
            ((t1.disciplineid = $1) OR ($1 IS NULL))
        ORDER BY t1.disciplineid ASC, t1.theday ASC
        ) AS t
        GROUP BY delta < 0, disciplineid
        ORDER BY disciplineid
)

SELECT x::xml FROM (

SELECT ord1, ord2, x::text FROM (
    SELECT 
        1 as ord1,
        ROW_NUMBER() OVER() AS ord2,
        XMLELEMENT(
            name "appdb:vostats",
            XMLATTRIBUTES(          
                'daily' AS "stats",
                disciplineid,
                disciplines.name AS discipline_name,
                cnt AS "count",
                theday AS "when"
            )
        ) AS x
    FROM stats.vo_disc_stats
    INNER JOIN disciplines ON (disciplines.id = disciplineid)
    WHERE 
        ((COALESCE($2, NOW()::date)::date <= theday) AND (theday < COALESCE($3, NOW()::date)::date))
    AND 
        ((disciplineid = $1) OR ($1 IS NULL))
    ORDER BY disciplineid, theday DESC
) AS x1

UNION

SELECT
    2 AS ord1,
    n AS ord2,
    XMLELEMENT(
        name "appdb:vostats",
        XMLATTRIBUTES(
            'period' AS "stats",
            disciplines.id AS disciplineid,
            disciplines.name AS discipline_name,
            COALESCE($2, NOW()::date)::date AS "from",
            COALESCE($3, NOW()::date)::date AS "to",
            'daily' AS "granularity",
            COALESCE((SELECT s FROM thestats WHERE thestats.disciplineid = disciplines.id AND NOT isdel), 0) AS "additions",
            COALESCE((SELECT ABS(s) FROM thestats WHERE thestats.disciplineid = disciplines.id AND isdel), 0) AS "removals"
        )
    )::text
    FROM UNNEST(ARRAY[0,1,2]) AS n
    INNER JOIN disciplines ON (disciplines.id = $1) OR ($1 IS NULL)

) AS x
ORDER BY x.ord1, x.ord2
;
$function$;
ALTER FUNCTION vo_disc_stats_to_xml(int, date, date) OWNER TO appdb;

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 13, 16, E'Added function vo_disc_stats_to_xml'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=13 AND revision=16);

COMMIT;
