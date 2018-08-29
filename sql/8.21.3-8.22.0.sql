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
Previous version: 8.21.3
New version: 8.22.0
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

UPDATE countries SET name = 'Afghanistan' WHERE id = 72;
UPDATE countries SET name = 'Bonaire, Saint Eustatius and Saba' WHERE id = 90;
UPDATE countries SET name = 'Democratic Republic of the Congo' WHERE id = 106;
UPDATE countries SET name = 'Republic of the Congo' WHERE id = 105;
UPDATE countries SET name = 'Republic of Cabo Verde' WHERE id = 98;
UPDATE countries SET name = 'Gibraltar' WHERE id = 128;
UPDATE countries SET name = 'South Georgia and the South Sandwich Islands' WHERE id = 223;
UPDATE countries SET name = 'Saint Kitts and Nevis' WHERE id = 207;
UPDATE countries SET name = 'Saint Vincent and the Grenadines' WHERE id = 212;
UPDATE countries SET name = 'United States of America' WHERE id = 30;
UPDATE countries SET name = 'Faroe Islands' WHERE id = 119;

DELETE FROM countries WHERE id = 184;

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 22, 0, E'Bring country info up-to-date'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=22 AND revision=0);

COMMIT;	
