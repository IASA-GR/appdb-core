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
Previous version: 8.22.0
New version: 8.22.1
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

CREATE OR REPLACE FUNCTION public.edit_vowide_image_list(
    _void integer,
    _userid integer)
  RETURNS integer AS
$BODY$
DECLARE listid INT;
BEGIN
	BEGIN
		listid := (SELECT id FROM vowide_image_lists WHERE void = $1 AND state = 'draft');
		IF listid IS NULL THEN
			listid := (SELECT id FROM vowide_image_lists WHERE void = $1 AND state = 'published');
			IF listid IS NULL THEN
				INSERT INTO vowide_image_lists (void, alteredby) VALUES ($1, $2) RETURNING id INTO listid;
			ELSE
				INSERT INTO vowide_image_lists (void, guid, expires_on, notes, title, alteredby) SELECT void, guid, expires_on, notes, title, $2 FROM vowide_image_lists WHERE id = listid RETURNING id INTO listid;
			END IF;
		END IF;
		RETURN listid;
	EXCEPTION
		WHEN OTHERS THEN
			RAISE WARNING '[edit_vowide_image_list] Ignoring error: %', SQLERRM;
			RETURN NULL;
	END;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION public.edit_vowide_image_list(integer, integer)
  OWNER TO appdb;
COMMENT ON FUNCTION public.edit_vowide_image_list(integer, integer) IS 'Returns the id of the draft image list for the specified VO. If one does not exists, it will be cloned from the published one, or created from scratch';

CREATE OR REPLACE FUNCTION public.trfn_vowide_image_list_images()
  RETURNS trigger AS
$BODY$
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
                                        SELECT vmiinstanceid FROM vaviewsall WHERE vapplistid = NEW.vapplistid
                                )
                        ));
			IF NEW.guid IS NULL THEN
				RAISE WARNING '[trfn_vowide_image_list_images] new image guid is NULL! NEW RECORD = : %', NEW;
			END IF;
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
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION public.trfn_vowide_image_list_images()
  OWNER TO appdb;

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 22, 1, E'Regression bug fixes related to VO-wide image list editing functionality'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=22 AND revision=1);

COMMIT;	
