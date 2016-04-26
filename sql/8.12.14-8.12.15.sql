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
Previous version: 8.12.14
New version: 8.12.15
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

CREATE OR REPLACE FUNCTION query_vowide_img_list_perm(
	m_researcherid int,
	m_voguid uuid,
	m_actionid int
) RETURNS BOOLEAN
AS
$$
BEGIN
IF EXISTS (
SELECT * 
FROM egiaai.vo_contacts 
WHERE role IN ('VO MANAGER', 'VO DEPUTY', 'VO EXPERT', 'VO SHIFTER') AND
puid IN (
	SELECT accountid 
	FROM user_accounts
	WHERE user_accounts.researcherid = $1 AND user_accounts.account_type = 'egi-aai'::e_account_type AND stateid = 1
)) THEN
	RETURN TRUE;
ELSE
	RETURN EXISTS (
		SELECT * 
		FROM permissions 
		WHERE actor = (SELECT guid FROM researchers WHERE id = $1) AND object = $2 AND actionid = $3
	);
END IF;
END;
$$ LANGUAGE plpgsql STABLE;
ALTER FUNCTION query_vowide_img_list_perm(int, uuid, int) OWNER TO appdb;

CREATE OR REPLACE FUNCTION query_vowide_img_list_view_perm(
	m_researcherid int,
	m_voguid uuid
) RETURNS BOOLEAN AS 
$$SELECT query_vowide_img_list_perm($1, $2, 36)$$ LANGUAGE sql;
ALTER FUNCTION query_vowide_img_list_view_perm(int, uuid) OWNER TO appdb;

CREATE OR REPLACE FUNCTION query_vowide_img_list_manage_perm(
	m_researcherid int,
	m_voguid uuid
) RETURNS BOOLEAN AS 
$$SELECT query_vowide_img_list_perm($1, $2, 37)$$ LANGUAGE sql;
ALTER FUNCTION query_vowide_img_list_view_perm(int, uuid) OWNER TO appdb;

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 12, 15, E'Added function to query VO-wide image list permissions, giving priority to EGI AAI'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=12 AND revision=15);

COMMIT;
