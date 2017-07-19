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
Previous version: 8.15.1
New version: 8.15.2
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

CREATE FUNCTION trfn_egiaai_vo_members_set_last_updated() 
RETURNS TRIGGER
AS
$$
BEGIN
	NEW.last_updated = NOW();
	RETURN NEW;
END;
$$ LANGUAGE plpgsql;
ALTER FUNCTION trfn_egiaai_vo_members_set_last_updated() OWNER TO appdb;

CREATE TRIGGER rtr_egiaai_vo_members_10_set_last_updated
BEFORE INSERT ON egiaai.vo_members
FOR EACH ROW
EXECUTE PROCEDURE trfn_egiaai_vo_members_set_last_updated();

ALTER TRIGGER rtr_egiaai_vo_members_10_set_last_updated OWNER TO appdb;

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 15, 2, E'Set last_updated to NOW() via trigger when inserting new rows to egiaai.vo_members'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=15 AND revision=2);

COMMIT;	
