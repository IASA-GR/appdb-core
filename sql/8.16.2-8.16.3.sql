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
Previous version: 8.16.2
New version: 8.16.3
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

ALTER TABLE vapp_versions DISABLE TRIGGER USER;

UPDATE vapp_versions
SET expireson = NOW()::date + '1 year'::INTERVAL
WHERE expireson - NOW() > '1 year'::INTERVAL;

ALTER TABLE vapp_versions ENABLE TRIGGER USER;

REFRESH MATERIALIZED VIEW vaviews;

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 16, 3, E'Limit vapp_version.expireson to a maximum of 1y from now'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=16 AND revision=3);

COMMIT;
