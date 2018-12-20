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
Previous version: 8.22.8
New version: 8.22.9
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

CREATE OR REPLACE VIEW index_usage AS
SELECT 

CASE 
WHEN STRPOS(index_size, ' kB') > 0 THEN
	REPLACE(index_size, ' kB', '')::INT * 1024
WHEN STRPOS(index_size, ' bytes') > 0 THEN
	REPLACE(index_size, ' bytes', '')::INT
WHEN STRPOS(index_size, ' MB') > 0 THEN
	REPLACE(index_size, ' MB', '')::INT * 1024 * 1024
ELSE
	index_size::INT
END AS index_size_bytes,
*

FROM (
SELECT
    --t.schemaname,
    t.tablename,
    indexname,
    c.reltuples AS num_rows,
    pg_size_pretty(pg_relation_size(quote_ident(t.tablename)::text)) AS table_size,
    pg_size_pretty(pg_relation_size(quote_ident(indexrelname)::text)) AS index_size,
    CASE WHEN indisunique THEN 'Y'
       ELSE 'N'
    END AS UNIQUE,
    idx_scan AS number_of_scans,
    idx_tup_read AS tuples_read,
    idx_tup_fetch AS tuples_fetched
FROM pg_tables t
LEFT OUTER JOIN pg_class c ON t.tablename=c.relname
LEFT OUTER JOIN
    ( SELECT c.relname AS ctablename, ipg.relname AS indexname, x.indnatts AS number_of_columns, idx_scan, idx_tup_read, idx_tup_fetch, indexrelname, indisunique FROM pg_index x
           JOIN pg_class c ON c.oid = x.indrelid
           JOIN pg_class ipg ON ipg.oid = x.indexrelid
           JOIN pg_stat_all_indexes psai ON x.indexrelid = psai.indexrelid AND psai.schemaname = 'public')
    AS foo
    ON t.tablename = foo.ctablename
 WHERE t.schemaname='public'
ORDER BY 1,2
-- ORDER BY number_of_scans ASC NULLS FIRST
) AS t;

ALTER VIEW index_usage OWNER TO appdb;


INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 22, 9, E'Added index_usage view'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=22 AND revision=9);

COMMIT;	
