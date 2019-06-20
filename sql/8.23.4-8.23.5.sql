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
Previous version: 8.23.4
New version: 8.23.5
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

INSERT INTO actor_groups (id, name) VALUES
	(-24, 'EGI VM Endorsment Dashboard Administrators'),
	(-25, 'EGI VM Endorsment Dashboard Security Officers')
;

SELECT groupid, x.actorid::uuid, NULL AS payload
FROM UNNEST(ARRAY[-24, -25]) AS groupid
CROSS JOIN (
		SELECT guid AS actorid FROM researchers
		WHERE id IN (520, 551)
) AS x;

REFRESH MATERIALIZED VIEW CONCURRENTLY _actor_group_members;
REFRESH MATERIALIZED VIEW CONCURRENTLY _actor_group_members2;
REFRESH MATERIALIZED VIEW CONCURRENTLY permissions;

INSERT INTO version (major,minor,revision,notes) 
SELECT 8, 23, 5, E'Added two new actor groups for endorsments dashboard'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=23 AND revision=5);

COMMIT;
