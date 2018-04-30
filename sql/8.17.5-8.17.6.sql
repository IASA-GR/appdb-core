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
Previous version: 8.17.5
New version: 8.17.6
Author: nakos.al@iasa.gr
*/

START TRANSACTION;

-- Function: public.add_egiaai_user_site_contact_info(text, text, text)
CREATE OR REPLACE FUNCTION public.add_egiaai_user_site_contact_info(
    puid text,
    site_pkey text,
    role text)
  RETURNS void AS
$BODY$
INSERT INTO gocdb.site_contacts(site_pkey, accountid, account_type, role) 
SELECT $2, $1, 'egi-aai'::e_account_type, $3
WHERE NOT EXISTS (
        SELECT * FROM gocdb.site_contacts
        WHERE 
                site_pkey = $2 AND
                accountid = $1 AND
                account_type = 'egi-aai'::e_account_type AND
                role = $3
)
$BODY$
  LANGUAGE sql VOLATILE
  COST 100;
ALTER FUNCTION public.add_egiaai_user_site_contact_info(text, text, text)
  OWNER TO appdb;


-- Function: public.clear_egiaai_user_info(text, boolean)
CREATE OR REPLACE FUNCTION public.clear_egiaai_user_info(
    puid text,
    extended boolean DEFAULT false)
  RETURNS void AS
$BODY$
DECLARE rid int;
BEGIN
        IF $2 THEN
                rid := (SELECT DISTINCT researcherid FROM user_accounts WHERE accountid = $1 AND account_type = 'egi-aai');
                DELETE FROM egiaai.vo_members WHERE egiaai.vo_members.puid IN (SELECT DISTINCT accountid FROM user_accounts WHERE account_type = 'egi-aai' AND researcherid = rid) OR egiaai.vo_members.puid = $1;
                DELETE FROM egiaai.vo_contacts WHERE egiaai.vo_contacts.puid IN (SELECT DISTINCT accountid FROM user_accounts WHERE account_type = 'egi-aai' AND researcherid = rid) OR egiaai.vo_contacts.puid = $1;
		DELETE FROM gocdb.site_contacts WHERE (gocdb.site_contacts.account_type = 'egi-aai'::e_contact_type) AND (gocdb.site_contacts.accountid IN (SELECT DISTINCT accountid FROM user_accounts WHERE account_type = 'egi-aai' AND researcherid = rid) OR gocdb.site_contacts.account_type = $1);
        ELSE
                DELETE FROM egiaai.vo_members WHERE egiaai.vo_members.puid = $1;
                DELETE FROM egiaai.vo_contacts WHERE egiaai.vo_contacts.puid = $1;
		DELETE FROM gocdb.site_contacts WHERE (gocdb.site_contacts.account_type = 'egi-aai'::e_contact_type) AND (gocdb.site_contacts.accountid = $1);
        END IF;
END
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION public.clear_egiaai_user_info(text, boolean)
  OWNER TO appdb;

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 17, 6, E'Add function to insert site contact information from egi aai entitlements'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=17 AND revision=6);

COMMIT;