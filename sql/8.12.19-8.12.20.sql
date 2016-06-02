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
Previous version: 8.12.19
New version: 8.12.20
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

-- Function: add_egiaai_user_vomember_info(text, text, text)

-- DROP FUNCTION add_egiaai_user_vomember_info(text, text, text);

CREATE OR REPLACE FUNCTION add_egiaai_user_vomember_info(
    puid text,
    uservo text,
    vo text)
  RETURNS void AS
$BODY$
INSERT INTO egiaai.vo_members (uservo, puid, vo) 
SELECT $2, $1, $3
WHERE NOT EXISTS (
	SELECT * FROM egiaai.vo_members 
	WHERE 
		uservo = $2 AND
		puid = $1 AND
		vo = $3
)
$BODY$
  LANGUAGE sql VOLATILE
  COST 100;
ALTER FUNCTION add_egiaai_user_vomember_info(text, text, text)
  OWNER TO appdb;

-- Function: add_egiaai_user_vocontact_info(text, text, text, text, text)

-- DROP FUNCTION add_egiaai_user_vocontact_info(text, text, text, text, text);

CREATE OR REPLACE FUNCTION add_egiaai_user_vocontact_info(
    puid text,
    fullname text,
    vo text,
    role text,
    email text)
  RETURNS void AS
$BODY$

INSERT INTO egiaai.vo_contacts (name, puid, vo, role, email) 
SELECT $2, $1, $3, $4, $5
WHERE 
NOT EXISTS (
	SELECT * FROM egiaai.vo_contacts
	WHERE 
		name = $2 AND
		puid = $1 AND
		vo = $3 AND
		role = $4 AND
		email = $5
)
$BODY$
  LANGUAGE sql VOLATILE
  COST 100;
ALTER FUNCTION add_egiaai_user_vocontact_info(text, text, text, text, text)
  OWNER TO appdb;


INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 12, 20, E'Only insert into egiaai vo_contacts/vo_members when entry does not exist'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=12 AND revision=20);

COMMIT;
