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
Previous version: 8.16.4
New version: 8.16.5
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

CREATE OR REPLACE FUNCTION public.trfn_refresh_app_vos()
 RETURNS trigger
 LANGUAGE plpgsql
AS $function$
BEGIN
        PERFORM invalidate_filtercache();
        TRUNCATE TABLE app_vos;
        INSERT INTO app_vos SELECT DISTINCT appid, void FROM v_app_vos;
        IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN RETURN NEW; ELSE RETURN OLD; END IF;
END;
$function$;

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 16, 5, E'Invalidate filtercache where modifying VO-wide image lists'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=16 AND revision=5);

COMMIT;
