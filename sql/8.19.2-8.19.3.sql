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
Previous version: 8.19.2
New version: 8.19.3
Author: wvkarag@lovecraft.priv.iasa.gr
*/

CREATE OR REPLACE FUNCTION public.pidhandle(applications)
 RETURNS text
 LANGUAGE sql
AS $function$
SELECT
        COALESCE((SELECT data FROM config WHERE var = 'handleprefix'), '') || '/' || suffix
FROM pidhandles
WHERE
        ((entrytype = 'software') OR (entrytype = 'vappliance')) AND
        (entryid = $1.id) AND
        ((result & 1)::BOOLEAN) AND -- marked as registered
        (NOT ((result & 8)::BOOLEAN)) -- not marked as to-be-deleted
$function$;

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 19, 3, E'Also return HANDLEs for VAs in pidhandle function'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=19 AND revision=3);
