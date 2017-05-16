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
Previous version: 8.14.3
New version: 8.14.4
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

INSERT INTO actor_groups (id, name) VALUES (-20, 'EGI Main Dashboard Administrators');
INSERT INTO actor_groups (id, name) VALUES (-21, 'EGI VMOps Dashboard Administrators');

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 14, 4, E'Added two new actor groups for EGI Dashboard service'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=14 AND revision=4);

COMMIT;
