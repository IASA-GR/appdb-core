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
Previous version: 8.14.7
New version: 8.14.8
Author: wvkarag@kadath.priv.iasa.gr
*/

START TRANSACTION;

DROP FUNCTION public.clear_egiaai_user_info(text);

CREATE OR REPLACE FUNCTION public.clear_egiaai_user_info(puid text, extended bool DEFAULT FALSE)
  RETURNS void AS
$BODY$
DECLARE rid int;
BEGIN
	IF $2 THEN
		rid := (SELECT DISTINCT researcherid FROM user_accounts WHERE accountid = $1 AND account_type = 'egi-aai');
		DELETE FROM egiaai.vo_members WHERE egiaai.vo_members.puid IN (SELECT DISTINCT accountid FROM user_accounts WHERE account_type = 'egi-aai' AND researcherid = rid) OR egiaai.vo_members.puid = $1;
		DELETE FROM egiaai.vo_contacts WHERE egiaai.vo_contacts.puid IN (SELECT DISTINCT accountid FROM user_accounts WHERE account_type = 'egi-aai' AND researcherid = rid) OR egiaai.vo_contacts.puid = $1;
	ELSE
		DELETE FROM egiaai.vo_members WHERE egiaai.vo_members.puid = $1;
		DELETE FROM egiaai.vo_contacts WHERE egiaai.vo_contacts.puid = $1;
	END IF;
END
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION public.clear_egiaai_user_info(text, bool)
  OWNER TO appdb;

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 14, 8, E'Add optional parameter to clear_egiaai_user_info function, to control clearing based on researcher id from given puid'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=14 AND revision=8);

COMMIT;
