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
Previous version: 8.18.4
New version: 8.18.5
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;
 
DROP VIEW IF EXISTS cd_log_cantor CASCADE;
CREATE VIEW cd_log_cantor AS
SELECT 	
	created_on, 
	cd_instance_id, 
	action, 
	subject, 
	payload, 
	actor_id, 
	cd_id,
	CASE WHEN NOT lag((action, subject, payload, actor_id, cd_id, cd_logs.comments)) OVER (ORDER BY id DESC, cd_id, cd_instance_id) IS DISTINCT FROM (action, subject, payload, actor_id, cd_id, cd_logs.comments) THEN
		lag(id) OVER (ORDER BY id DESC, cd_id, cd_instance_id)
	ELSE 
		id
	END AS pid,
	id,
	cd_logs.comments
FROM cd_logs
WHERE cd_task_instance_id IS NULL;
ALTER VIEW cd_log_cantor OWNER TO appdb;

CREATE OR REPLACE FUNCTION cd_log_cantor_id(mx int) RETURNS INT AS
$$
DECLARE xx INT;
DECLARE oldxx INT;
BEGIN
	xx := mx;
	oldxx := NULL;
	WHILE EXISTS (SELECT 1 FROM cd_log_cantor WHERE id = xx) LOOP
		oldxx := xx;
		xx := (SELECT pid FROM cd_log_cantor WHERE id = xx);
		IF NOT oldxx IS DISTINCT FROM xx THEN EXIT; END IF;
	END LOOP;
	RETURN xx;
END;
$$ LANGUAGE plpgsql STABLE;
ALTER FUNCTION cd_log_cantor_id(int) OWNER TO appdb;

DROP VIEW IF EXISTS cd_log_partitions;
CREATE VIEW cd_log_partitions AS
SELECT DISTINCT
	MAX(ARRAY_LENGTH(mid, 1)) OVER (PARTITION BY pid) AS cnt,
	ARRAY_TO_STRING(ARRAY_AGG(cd_instance_id) OVER (PARTITION BY pid), ';') AS cd_instance_ids,
	FIRST_VALUE(tt.comments) OVER (PARTITION BY pid) AS "comments",
	cd_id, 
	min_id,
	max_id, 
	min_created_on, 
	max_created_on,
	action, 
	subject, 
	payload, 
	actor_id, 
	pid AS partition_id
FROM (
	SELECT 
		ARRAY_AGG(t.id) OVER (PARTITION BY pid ORDER BY t.id DESC, cd_id, cd_instance_id) AS mid,
		t.cd_id,
		MAX(t.id) OVER (PARTITION BY pid) AS max_id,
		MIN(t.id) OVER (PARTITION BY pid) AS min_id,
		MAX(t.created_on) OVER (PARTITION BY pid) AS max_created_on,
		MIN(t.created_on) OVER (PARTITION BY pid) AS min_created_on,
		action, 
		subject, 
		payload, 
		actor_id, 
		pid,
		cd_instance_id,
		t.comments
	FROM (
		SELECT 
			created_on, 
			cd_instance_id, 
			action, 
			subject, 
			payload, 
			actor_id, 
			cd_id, 
			cd_log_cantor_id(pid) AS pid, 
			id, 
			c.comments 
		FROM cd_log_cantor AS c
	) AS t
) AS tt;
ALTER VIEW cd_log_partitions OWNER TO appdb;

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 18, 5, E'Create partitioned view for cd_logs'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=18 AND revision=5);

COMMIT;	
