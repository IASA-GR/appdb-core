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
Previous version: 8.23.2
New version: 8.23.3
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;
ALTER TABLE vapp_versions DROP CONSTRAINT chk_expireson;
ALTER TABLE vapp_versions ADD CONSTRAINT chk_expireson CHECK((expireson::timestamp with time zone - now()) <= '366 days'::interval);

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 23, 3, E'Add one more day to VA version expiry check constraint, to avoid rounding mishaps'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=23 AND revision=3);
COMMIT;
