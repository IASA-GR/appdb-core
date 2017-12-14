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
Previous version: 8.16.5
New version: 8.16.6
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

CREATE SCHEMA IF NOT EXISTS apilog;
ALTER SCHEMA apilog OWNER TO appdb;

DROP TABLE IF EXISTS apilog.actions;
CREATE TABLE apilog.actions (
	id UUID PRIMARY KEY NOT NULL DEFAULT uuid_generate_v4(),
	target TEXT NOT NULL,
	targetid TEXT NOT NULL,
	event TEXT NOT NULL,
	userid INT NOT NULL,
	username TEXT NOT NULL,
	usercontact TEXT,
	apiver TEXT NOT NULL,
	tstamp TIMESTAMP NOT NULL DEFAULT NOW(),
	oldval XML,
	newval XML,
	disposition TEXT
);
TRUNCATE TABLE apilog.actions;
ALTER TABLE apilog.actions OWNER TO appdb;

DROP FUNCTION IF EXISTS hashistory(applications);
CREATE OR REPLACE FUNCTION hashistory(applications) RETURNS BOOLEAN AS
$$
SELECT EXISTS(SELECT 1 FROM apilog.actions WHERE target = 'application' AND targetid::int = $1.id)
$$ LANGUAGE sql STABLE STRICT;
ALTER FUNCTION hashistory(applications) OWNER TO appdb;

CREATE TABLE apilog.t_apphistory(id UUID, event TEXT, userid INT, username TEXT, usercontact TEXT, apiver TEXT, tstamp TIMESTAMP, oldval XML, newval XML);

DROP FUNCTION IF EXISTS nextid(apilog.t_apphistory);
CREATE FUNCTION nextid(apilog.t_apphistory) RETURNS UUID AS
$$
SELECT id FROM apilog.actions
WHERE target = 'application' AND targetid = (SELECT targetid FROM apilog.actions WHERE id = $1.id) AND tstamp > $1.tstamp
ORDER BY tstamp ASC
LIMIT 1
$$ LANGUAGE SQL STABLE;

DROP FUNCTION IF EXISTS previd(apilog.t_apphistory);
CREATE FUNCTION previd(apilog.t_apphistory) RETURNS UUID AS
$$
SELECT id FROM apilog.actions
WHERE target = 'application' AND targetid = (SELECT targetid FROM apilog.actions WHERE id = $1.id) AND tstamp < $1.tstamp
ORDER BY tstamp DESC
LIMIT 1
$$ LANGUAGE SQL STABLE;

DROP FUNCTION IF EXISTS history(applications);
CREATE OR REPLACE FUNCTION history(applications) RETURNS SETOF apilog.t_apphistory AS
$$
SELECT apilog.actions.id, apilog.actions.event, apilog.actions.userid, apilog.actions.username, apilog.actions.usercontact, apilog.actions.apiver, apilog.actions.tstamp,
apilog.actions.oldval,
apilog.actions.newval
FROM applications
LEFT OUTER JOIN apilog.actions ON target = 'application' AND targetid::int = $1.id
WHERE applications.id = $1.id
$$ LANGUAGE sql STABLE CALLED ON NULL INPUT;
ALTER FUNCTION history(applications) OWNER TO appdb;

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 16, 6, E'Move api history log from file to DB'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=16 AND revision=6);

COMMIT;	
