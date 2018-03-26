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
Previous version: 8.17.2
New version: 8.17.3
Author: wvkarag@lovecraft.priv.iasa.gr
*/
START TRANSACTION;

ALTER TABLE apilog.t_apphistory ADD COLUMN displosition TEXT;


CREATE OR REPLACE FUNCTION public.history(applications)
 RETURNS SETOF apilog.t_apphistory
 LANGUAGE sql
 STABLE
AS $function$
SELECT apilog.actions.id, apilog.actions.event, apilog.actions.userid, apilog.actions.username, apilog.actions.usercontact, apilog.actions.apiver, apilog.actions.tstamp,
apilog.actions.oldval,
apilog.actions.newval,
apilog.actions.disposition
FROM applications
LEFT OUTER JOIN apilog.actions ON target = 'application' AND targetid::int = $1.id
WHERE applications.id = $1.id
AND EXISTS (SELECT 1 FROM apilog.actions WHERE target = 'application' AND targetid = $1.id::TEXT)
$function$;

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 17, 3, E'Add missing column to apilog.t_apphistory table; do not return empty tuple when there is no app history'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=17 AND revision=3);

COMMIT;
