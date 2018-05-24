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
Previous version: 8.19.10
New version: 8.19.11
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

-- Function: public.trfn_vapp_versions_pidhandle()

-- DROP FUNCTION public.trfn_vapp_versions_pidhandle();

CREATE OR REPLACE FUNCTION public.trfn_vapp_versions_pidhandle()
  RETURNS trigger AS
$BODY$
DECLARE vav_suffix TEXT;
BEGIN
	IF  TG_WHEN = 'AFTER' THEN
		IF TG_OP = 'INSERT' THEN
			vav_suffix := NULL;
			IF NEW.published AND NOT NEW.archived THEN
				vav_suffix = 'latest';
			ELSIF NEW.published AND NEW.archived THEN
				vav_suffix = 'previous/' || (NEW.id)::TEXT;
			-- ELSE
				-- IGNORE all other cases
			END IF;
			IF NOT vav_suffix IS NULL THEN
				INSERT INTO pidhandles (url, suffix, entrytype, entryid) VALUES (
					'http://' || (SELECT data FROM config WHERE var = 'ui-host') || '/store/vappliance/' || (SELECT cname FROM applications WHERE id = (SELECT appid FROM vapplications WHERE id = NEW.vappid)) || '/vaversion/' || vav_suffix,
					NEW.guid,
					'vappliance_version'::e_entity,
					NEW.id
				);
			END IF;
		ELSIF TG_OP = 'UPDATE' THEN
			vav_suffix := NULL;
			IF NEW.published AND NOT NEW.archived THEN
				vav_suffix = 'latest';
			ELSIF NEW.published AND NEW.archived THEN
				vav_suffix = 'previous/' || (NEW.id)::TEXT;
			-- ELSE
				-- IGNORE all other cases
			END IF;
			IF NOT vav_suffix IS NULL THEN
				IF EXISTS (SELECT 1 FROM pidhandles WHERE suffix = NEW.guid::TEXT) THEN
					UPDATE pidhandles SET
						url = 'http://' || (SELECT data FROM config WHERE var = 'ui-host') || '/store/vappliance/' || (SELECT cname FROM applications WHERE id = (SELECT appid FROM vapplications WHERE id = NEW.vappid)) || '/vaversion/' || vav_suffix,
						result = result | 4
					WHERE suffix = NEW.guid::TEXT;
				ELSE
					INSERT INTO pidhandles (url, suffix, entrytype, entryid) VALUES (
					'http://' || (SELECT data FROM config WHERE var = 'ui-host') || '/store/vappliance/' || (SELECT cname FROM applications WHERE id = (SELECT appid FROM vapplications WHERE id = NEW.vappid)) || '/vaversion/' || vav_suffix,
					NEW.guid,
					'vappliance_version'::e_entity,
					NEW.id
				);
				END IF;
			END IF;
		ELSIF TG_OP = 'DELETE' THEN
			UPDATE pidhandles SET result = result | 8 WHERE suffix = OLD.guid;
		END IF;
		RETURN NULL;
	END IF;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION public.trfn_vapp_versions_pidhandle()
  OWNER TO appdb;

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 19, 11, E'Prevent NULL violation for URL column in trfn_vapp_versions_pidhandle'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=19 AND revision=11);

COMMIT;
