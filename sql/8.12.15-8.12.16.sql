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
Previous version: 8.12.15
New version: 8.12.16
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

ALTER TABLE user_accounts ADD COLUMN idptrace TEXT[];

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 12, 16, E'Added IdP trace column to user account table'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=12 AND revision=16);

COMMIT;
