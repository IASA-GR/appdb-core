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
Previous version: 8.15.0
New version: 8.15.1
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

ALTER TABLE vapp_versions ADD COLUMN publishedby INT REFERENCES researchers(id);
ALTER TABLE vapp_versions ADD COLUMN publishedon timestamp;
ALTER TABLE vapp_versions ADD COLUMN enabledby INT REFERENCES researchers(id);
ALTER TABLE vapp_versions ADD COLUMN enabledon timestamp;

CREATE INDEX idx_vapp_versions_enabledby ON vapp_versions(enabledby);
CREATE INDEX idx_vapp_versions_enabledon ON vapp_versions(enabledon);
CREATE INDEX idx_vapp_versions_publishedby ON vapp_versions(publishedby);
CREATE INDEX idx_vapp_versions_publishedon ON vapp_versions(publishedon);

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 15, 1, E'Added columns to vapp_versions (enabledby, enabledon, publishedby, publishedon)'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=15 AND revision=1);

COMMIT;	
