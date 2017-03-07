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
Previous version: 8.14.1
New version: 8.14.2
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

CREATE OR REPLACE FUNCTION trfn_pending_accounts()
RETURNS TRIGGER AS
$BODY$
BEGIN
	IF TG_OP = 'INSERT' THEN
		IF TG_WHEN = 'BEFORE' THEN
			DELETE FROM 
				pending_accounts
			WHERE 
				researcherid = NEW.researcherid AND
				accountid = NEW.accountid AND
				account_type = NEW.account_type;			
		END IF;
		RETURN NEW;
	ELSIF TG_OP = 'UPDATE' THEN
		RETURN NEW;
	ELSE
		RETURN OLD;
	END IF;
END;
$BODY$
LANGUAGE plpgsql VOLATILE;
ALTER FUNCTION trfn_pending_accounts() OWNER TO appdb;

DROP TRIGGER IF EXISTS rtr_pending_accounts_before ON pending_accounts;
CREATE TRIGGER rtr_pending_accounts_before
BEFORE INSERT
ON pending_accounts
FOR EACH ROW
EXECUTE PROCEDURE trfn_pending_accounts();

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 14, 2, E'Remove previous pending account entries when a new one is created for same user + account'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=14 AND revision=2);

COMMIT;
