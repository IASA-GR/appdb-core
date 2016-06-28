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
Previous version: 8.12.28
New version: 8.12.29
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

CREATE OR REPLACE FUNCTION rankapp_post_old(h text)
  RETURNS SETOF rankedapps AS
$BODY$
DECLARE f TEXT;
DECLARE f2 TEXT;
BEGIN
f := app_fld_lst();
f2 := 'ranked.' || REGEXP_REPLACE(f, ', ', ', ranked.', 'g');
RETURN QUERY
EXECUTE '
WITH ranked2 AS (
WITH ranked AS (
SELECT 
	*
FROM 
	cache.filtercache_' || h || ' AS applications 
WHERE 
	(deleted OR moderated) IS FALSE 
),
allvisits AS (
SELECT 
	COUNT(*) AS count 
FROM 
app_api_log
),
appvisits AS (
SELECT 
	COUNT(*) AS count, 
	appid 
FROM 
	app_api_log 
GROUP BY 
	appid
)
SELECT ' || f2 || ',
	ranked.rank, 
	CASE WHEN MAX(rank) OVER () = 0 THEN
		0
	ELSE
		((COALESCE(rating, 0) + 1) * (COALESCE(appvisits.count::float * 100 / (SELECT allvisits.count FROM allvisits), 0) + 1) * (ranked.rank) * 100 / MAX(rank) OVER ())::int END 
		AS socialrank
FROM 
	ranked 
LEFT OUTER JOIN appvisits ON appvisits.appid = ranked.id 
)
SELECT ' || f || ',
	CASE WHEN (SELECT MAX(rank) FROM ranked2) = 0 THEN
		0
	ELSE
		rank * 100 / (SELECT MAX(rank) FROM ranked2)
	END
FROM
	ranked2
ORDER BY 
	rank DESC,
	socialrank DESC,
	name ASC ';

END;
$BODY$
  LANGUAGE plpgsql STABLE
  COST 100
  ROWS 1000;
COMMENT ON FUNCTION rankapp_post_old(text) IS 'Older, slower version using CTEs';
ALTER FUNCTION rankapp_post_old(text)
  OWNER TO appdb;


CREATE OR REPLACE FUNCTION rankapp_post(h text)
  RETURNS SETOF rankedapps AS
$BODY$
DECLARE f TEXT;
DECLARE f2 TEXT;
BEGIN
f := app_fld_lst();
RETURN QUERY
EXECUTE '
SELECT ' || f || ',
	CASE WHEN (SELECT MAX(rank) FROM cache.filtercache_' || h || ' WHERE NOT deleted AND NOT moderated) = 0 THEN
		0
	ELSE
		rank * 100 / (SELECT MAX(rank) FROM cache.filtercache_' || h || ' WHERE NOT deleted AND NOT moderated)
	END AS rank
FROM
	cache.filtercache_' || h || ' AS applications
WHERE 
	NOT deleted AND NOT moderated
ORDER BY 
	rank DESC,
	CASE WHEN (SELECT MAX(rank) FROM cache.filtercache_' || h || ' WHERE NOT deleted AND NOT moderated) = 0 THEN
		0
	ELSE
		((COALESCE(rating, 0) + 1) * (COALESCE((SELECT COUNT(appid ORDER BY appid) AS count FROM app_api_log WHERE appid = applications.id GROUP BY appid) * 100 / ((SELECT COUNT(*) AS count FROM app_api_log)), 0) + 1) * (rank) * 100 / (SELECT MAX(rank) FROM cache.filtercache_' || h || ' WHERE NOT deleted AND NOT moderated))::int END DESC,
	name ASC';
END;
$BODY$
  LANGUAGE plpgsql STABLE
  COST 100
  ROWS 1000;
ALTER FUNCTION rankapp_post(text)
  OWNER TO appdb;

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 12, 29, E'Improved post-ranking application function speed'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=12 AND revision=29);

COMMIT;
