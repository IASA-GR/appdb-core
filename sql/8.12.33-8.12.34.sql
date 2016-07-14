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
Previous version: 8.12.33
New version: 8.12.34
Author: wvkarag@lovecraft.priv.iasa.gr
*/

CREATE OR REPLACE FUNCTION public.trfn_vowide_image_list_images()                                                                                                                                                          
 RETURNS trigger                                                                                                                                                                                                           
 LANGUAGE plpgsql
AS $function$
DECLARE rec RECORD;
BEGIN
        IF TG_WHEN = 'BEFORE' THEN
                IF TG_OP = 'INSERT' THEN
                        NEW.state := get_vowide_image_state(NEW.vapplistid);
                        NEW.guid := uuid_generate_v5(uuid_namespace('ISO OID'), 'vowide_image_list_image:' || (
                                SELECT name FROM vos WHERE id = (
                                        SELECT void FROM vowide_image_lists WHERE id = NEW.vowide_image_list_id
                                )
                        ) || ':' || (
                                SELECT guid FROM vmiinstances WHERE id = (
                                        SELECT vmiinstanceid FROM __vaviews WHERE vapplistid = NEW.vapplistid
                                )
                        ));
                        RETURN NEW;
                ELSIF TG_OP = 'UPDATE' THEN
                        RETURN NEW;
                ELSIF TG_OP = 'DELETE' THEN
                        RETURN OLD;
                END IF;
        ELSE
                RETURN NULL;
        END IF;
END;
$function$

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 12, 34, E'Regression bug fix related to updating an image in a vo-wide image list'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=12 AND revision=34);
