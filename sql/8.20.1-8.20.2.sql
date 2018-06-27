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
Previous version: 8.20.1
New version: 8.20.2
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

INSERT INTO relationverbs(name, dname, rname) VALUES ('funding', 'fund', 'funded by');
INSERT INTO relationtypes (target_type, verbid, subject_type, description, actionid)
	VALUES ('software', (SELECT id FROM relationverbs WHERE name = 'funding'), 'project', 'A project funded a software', 40);
INSERT INTO relationtypes (target_type, verbid, subject_type, description, actionid)
	VALUES ('vappliance', (SELECT id FROM relationverbs WHERE name = 'funding'), 'project', 'A project funded a software', 40);
INSERT INTO relationtypes (target_type, verbid, subject_type, description, actionid)
	VALUES ('swappliance', (SELECT id FROM relationverbs WHERE name = 'funding'), 'project', 'A project funded a software', 40);

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 20, 2, E'Add project funding verbs and relation types'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=20 AND revision=2);

COMMIT;	
