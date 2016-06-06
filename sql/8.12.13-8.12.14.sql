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
Previous version: 8.12.13
New version: 8.12.14
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

CREATE OR REPLACE FUNCTION clear_egiaai_user_info(
	puid text
) RETURNS VOID AS
$$
BEGIN
	DELETE FROM egiaai.vo_members WHERE egiaai.vo_members.puid = $1;
	DELETE FROM egiaai.vo_contact WHERE egiaai.vo_members.puid = $1;
END
$$ LANGUAGE plpgsql VOLATILE;
ALTER FUNCTION clear_egiaai_user_info(text) OWNER TO appdb;

CREATE OR REPLACE FUNCTION add_egiaai_user_vocontact_info(
	puid text,
	fullname text,
	vo text,
	role text,
	email text
) RETURNS VOID AS
$$
INSERT INTO egiaai.vo_contacts (name, puid, vo, role, email) VALUES ($2, $1, $3, $4, $5);
$$ LANGUAGE sql VOLATILE;
ALTER FUNCTION add_egiaai_user_vocontact_info(text,text,text,text,text) OWNER TO appdb;

CREATE OR REPLACE FUNCTION add_egiaai_user_vomember_info(
	puid text,
	uservo text,
	vo text
) RETURNS VOID AS
$$
INSERT INTO egiaai.vo_members (uservo, puid, vo) VALUES ($2, $1, $3);
$$ LANGUAGE sql VOLATILE;
ALTER FUNCTION add_egiaai_user_vomember_info(text,text,text) OWNER TO appdb;

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 12, 14, E'Added functions for EGI AAI user info manipulation'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=12 AND revision=14);

COMMIT;

