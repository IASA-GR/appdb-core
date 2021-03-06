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
Previous version: 8.13.14
New version: 8.13.15
Author: wvkarag@lovecraft.priv.iasa.gr
*/
START TRANSACTION;

CREATE OR REPLACE FUNCTION public.app_vo_stats_to_xml(mvoid integer DEFAULT NULL::integer, datefrom date DEFAULT NULL::date, dateto date DEFAULT NULL::date)
 RETURNS SETOF xml
 LANGUAGE sql
 STABLE
AS $function$
WITH thestats AS (
        SELECT SUM(delta) AS s,
        delta < 0 AS isdel,
        metatype,
        void
        FROM (
        SELECT 
            t1.void,
            t1.metatype,
            t1.theday,
            t1.cnt,
            t1.cnt - t2.cnt AS delta,
            t2.theday AS thepreviousday
        FROM 
        stats.app_vo_stats AS t1
        INNER JOIN stats.app_vo_stats AS t2 ON 
            t2.void = t1.void AND 
            t2.metatype = t1.metatype AND 
            t2.theday = (
                SELECT MAX(t3.theday) AS theday
                FROM stats.app_vo_stats AS t3
                WHERE t3.void = t1.void AND 
                t3.metatype = t1.metatype AND 
                t3.theday < t1.theday
            )
        WHERE 
            ((COALESCE($2, NOW()::date)::date <= t1.theday) AND (t1.theday < COALESCE($3, NOW()::date)::date))
        AND
            ((t1.void = $1) OR ($1 IS NULL))
        ORDER BY t1.void ASC, t1.theday ASC, t1.metatype ASC
        ) AS t
        GROUP BY delta < 0, metatype, void
        ORDER BY void, metatype
)

SELECT x::xml FROM (

SELECT ord1, ord2, x::text FROM (
    SELECT 
        1 as ord1,
        ROW_NUMBER() OVER() AS ord2,
        XMLELEMENT(
            name "appdb:appstats",
            XMLATTRIBUTES(          
                'daily' AS "stats",
                void,
                vos.name AS voname,
                CASE metatype
                    WHEN 0 THEN 'software item'
                    WHEN 1 THEN 'virtual appliance'
                    WHEN 2 THEN 'software appliance'
                END AS "type",
                cnt AS "count",
                theday AS "when"
            )
        ) AS x
    FROM stats.app_vo_stats
    INNER JOIN vos ON (NOT deleted) AND (vos.id = void)
    WHERE 
        ((COALESCE($2, NOW()::date)::date <= theday) AND (theday < COALESCE($3, NOW()::date)::date))
    AND 
        ((void = $1) OR ($1 IS NULL))
    ORDER BY void, theday DESC, metatype ASC
) AS x1

UNION

SELECT
    2 AS ord1,
    n AS ord2,
    XMLELEMENT(
        name "appdb:appstats",
        XMLATTRIBUTES(
            'period' AS "stats",
            vos.id AS void,
            vos.name AS voname,
            COALESCE($2, NOW()::date)::date AS "from",
            COALESCE($3, NOW()::date)::date AS "to",
            'daily' AS "granularity",
            COALESCE((SELECT s FROM thestats WHERE thestats.void = vos.id AND thestats.metatype = n AND NOT isdel), 0) AS "additions",
            COALESCE((SELECT ABS(s) FROM thestats WHERE thestats.void = vos.id AND thestats.metatype = n AND isdel), 0) AS "removals",
            CASE WHEN n = 1 THEN
                (SELECT COUNT(DISTINCT appid) FROM (
                    SELECT DISTINCT
                        appid, 
                        vmiinstanceid,
                        va_version_createdon
                    FROM vaviews
                    INNER JOIN vowide_image_list_images ON vowide_image_list_images.vapplistid = vaviews.vapplistid
                    INNER JOIN vowide_image_lists ON vowide_image_lists.id = vowide_image_list_images.vowide_image_list_id 
                    WHERE 
                        vowide_image_lists.void = vos.id AND 
                        ((COALESCE($2, NOW()::date)::date <= vowide_image_lists.published_on) AND (vowide_image_lists.published_on < COALESCE($3, NOW()::date)::date)) AND  
                        vowide_image_lists.state <> 'draft'::e_vowide_image_state AND
                        va_version_published  AND 
                        ((COALESCE($2, NOW()::date)::date <= va_version_createdon) AND (va_version_createdon < COALESCE($3, NOW()::date)::date)) 
                    ORDER BY appid
                ) AS t_vaupdates)
            END AS "vmi_updates",
            CASE n
                WHEN 0 THEN 'software item'
                WHEN 1 THEN 'virtual appliance'
                WHEN 2 THEN 'software appliance'
            END AS "type"
        )
    )::text
    FROM UNNEST(ARRAY[0,1,2]) AS n
    INNER JOIN vos ON (NOT deleted) AND ((vos.id = $1) OR ($1 IS NULL))

) AS x
ORDER BY x.ord1, x.ord2
;
$function$;


CREATE OR REPLACE FUNCTION public.app_cat_stats_to_xml(mcatid integer DEFAULT NULL::integer, datefrom date DEFAULT NULL::date, dateto date DEFAULT NULL::date)
 RETURNS SETOF xml
 LANGUAGE sql
 STABLE
AS $function$
WITH thestats AS (
        SELECT SUM(delta) AS s,
        delta < 0 AS isdel,
        metatype,
        categoryid
        FROM (
        SELECT 
            t1.categoryid,
            t1.metatype,
            t1.theday,
            t1.cnt,
            t1.cnt - t2.cnt AS delta,
            t2.theday AS thepreviousday
        FROM 
        stats.app_cat_stats AS t1
        INNER JOIN stats.app_cat_stats AS t2 ON 
            t2.categoryid = t1.categoryid AND 
            t2.metatype = t1.metatype AND 
            t2.theday = (
                SELECT MAX(t3.theday) AS theday
                FROM stats.app_cat_stats AS t3
                WHERE t3.categoryid = t1.categoryid AND 
                t3.metatype = t1.metatype AND 
                t3.theday < t1.theday
            )
        WHERE 
            ((COALESCE($2, NOW()::date)::date <= t1.theday) AND (t1.theday < COALESCE($3, NOW()::date)::date))
        AND
            ((t1.categoryid = $1) OR ($1 IS NULL))
        ORDER BY t1.categoryid ASC, t1.theday ASC, t1.metatype ASC
        ) AS t
        GROUP BY delta < 0, metatype, categoryid
        ORDER BY categoryid, metatype
)

SELECT x::xml FROM (

SELECT ord1, ord2, x::text FROM (
    SELECT 
        1 as ord1,
        ROW_NUMBER() OVER() AS ord2,
        XMLELEMENT(
            name "appdb:appstats",
            XMLATTRIBUTES(          
                'daily' AS "stats",
                categoryid,
                categories.name AS category_name,
                CASE metatype
                    WHEN 0 THEN 'software item'
                    WHEN 1 THEN 'virtual appliance'
                    WHEN 2 THEN 'software appliance'
                END AS "type",
                cnt AS "count",
                theday AS "when"
            )
        ) AS x
    FROM stats.app_cat_stats
    INNER JOIN categories ON categories.id = categoryid
    WHERE 
        ((COALESCE($2, NOW()::date)::date <= theday) AND (theday < COALESCE($3, NOW()::date)::date))
    AND 
        ((categoryid = $1) OR ($1 IS NULL))
    ORDER BY categoryid, theday DESC, metatype ASC
) AS x1

UNION

SELECT
    2 AS ord1,
    n AS ord2,
    XMLELEMENT(
        name "appdb:appstats",
        XMLATTRIBUTES(
            'period' AS "stats",
            categories.id AS categoryid,
            categories.name AS category_name,
            COALESCE($2, NOW()::date)::date AS "from",
            COALESCE($3, NOW()::date)::date AS "to",
            'daily' AS "granularity",
            COALESCE((SELECT s FROM thestats WHERE thestats.categoryid = categories.id AND thestats.metatype = n AND NOT isdel), 0) AS "additions",
            COALESCE((SELECT ABS(s) FROM thestats WHERE thestats.categoryid = categories.id AND thestats.metatype = n AND isdel), 0) AS "removals",
            CASE n
                WHEN 0 THEN 'software item'
                WHEN 1 THEN 'virtual appliance'
                WHEN 2 THEN 'software appliance'
            END AS "type"
        )
    )::text
    FROM UNNEST(ARRAY[0,1,2]) AS n
    INNER JOIN categories ON (categories.id = $1) OR ($1 IS NULL)
) AS x
ORDER BY x.ord1, x.ord2
;
$function$;

ALTER FUNCTION app_cat_stats_to_xml(int, date, date) OWNER TO appdb;

CREATE OR REPLACE FUNCTION public.app_disc_stats_to_xml(mcatid integer DEFAULT NULL::integer, datefrom date DEFAULT NULL::date, dateto date DEFAULT NULL::date)
 RETURNS SETOF xml
 LANGUAGE sql
 STABLE
AS $function$
WITH thestats AS (
        SELECT SUM(delta) AS s,
        delta < 0 AS isdel,
        metatype,
        disciplineid
        FROM (
        SELECT 
            t1.disciplineid,
            t1.metatype,
            t1.theday,
            t1.cnt,
            t1.cnt - t2.cnt AS delta,
            t2.theday AS thepreviousday
        FROM 
        stats.app_disc_stats AS t1
        INNER JOIN stats.app_disc_stats AS t2 ON 
            t2.disciplineid = t1.disciplineid AND 
            t2.metatype = t1.metatype AND 
            t2.theday = (
                SELECT MAX(t3.theday) AS theday
                FROM stats.app_disc_stats AS t3
                WHERE t3.disciplineid = t1.disciplineid AND 
                t3.metatype = t1.metatype AND 
                t3.theday < t1.theday
            )
        WHERE 
            ((COALESCE($2, NOW()::date)::date <= t1.theday) AND (t1.theday < COALESCE($3, NOW()::date)::date))
        AND
            ((t1.disciplineid = $1) OR ($1 IS NULL))
        ORDER BY t1.disciplineid ASC, t1.theday ASC, t1.metatype ASC
        ) AS t
        GROUP BY delta < 0, metatype, disciplineid
        ORDER BY disciplineid, metatype
)

SELECT x::xml FROM (

SELECT ord1, ord2, x::text FROM (
    SELECT 
        1 as ord1,
        ROW_NUMBER() OVER() AS ord2,
        XMLELEMENT(
            name "appdb:appstats",
            XMLATTRIBUTES(          
                'daily' AS "stats",
                disciplineid,
                disciplines.name AS discipline_name,
                CASE metatype
                    WHEN 0 THEN 'software item'
                    WHEN 1 THEN 'virtual appliance'
                    WHEN 2 THEN 'software appliance'
                END AS "type",
                cnt AS "count",
                theday AS "when"
            )
        ) AS x
    FROM stats.app_disc_stats
    INNER JOIN disciplines ON disciplines.id = disciplineid
    WHERE 
        ((COALESCE($2, NOW()::date)::date <= theday) AND (theday < COALESCE($3, NOW()::date)::date))
    AND 
        ((disciplineid = $1) OR ($1 IS NULL))
    ORDER BY disciplineid, theday DESC, metatype ASC
) AS x1

UNION

SELECT
    2 AS ord1,
    n AS ord2,
    XMLELEMENT(
        name "appdb:appstats",
        XMLATTRIBUTES(
            'period' AS "stats",
            disciplines.id AS disciplineid,
            disciplines.name AS discipline_name,
            COALESCE($2, NOW()::date)::date AS "from",
            COALESCE($3, NOW()::date)::date AS "to",
            'daily' AS "granularity",
            COALESCE((SELECT s FROM thestats WHERE thestats.disciplineid = disciplines.id AND thestats.metatype = n AND NOT isdel), 0) AS "additions",
            COALESCE((SELECT ABS(s) FROM thestats WHERE thestats.disciplineid = disciplines.id AND thestats.metatype = n AND isdel), 0) AS "removals",
            CASE n
                WHEN 0 THEN 'software item'
                WHEN 1 THEN 'virtual appliance'
                WHEN 2 THEN 'software appliance'
            END AS "type"
        )
    )::text
    FROM UNNEST(ARRAY[0,1,2]) AS n
    INNER JOIN disciplines ON (disciplines.id = $1) OR ($1 IS NULL)
) AS x
ORDER BY x.ord1, x.ord2
;
$function$;
ALTER FUNCTION app_disc_stats_to_xml(int, date, date) OWNER TO appdb;

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 13, 15, E'Added category and discipline versions of the app_vo_stats_to_xml'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=13 AND revision=15);

COMMIT;
