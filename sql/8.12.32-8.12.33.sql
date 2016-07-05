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
Previous version: 8.12.32
New version: 8.12.33
Author: wvkarag@lovecraft.priv.iasa.gr
*/

CREATE INDEX idx_app_vos_appid ON app_vos(appid);
CREATE INDEX idx_app_vos_void ON app_vos(void);

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 12, 33, E'Add missing indexes to app_vos table'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=12 AND revision=33);
