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
Previous version: 8.22.3
New version: 8.22.4
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

CREATE OR REPLACE FUNCTION public.trfn_va_sec_check_queue()
  RETURNS trigger AS
$BODY$
DECLARE newoutcome TEXT;
BEGIN
	IF (TG_OP = 'INSERT') OR (TG_OP = 'UPDATE') THEN
		IF NEW.state IS DISTINCT FROM NULL THEN
			NEW.state := TRIM(LOWER(NEW.state));
			IF NEW.state NOT IN ('queued', 'sent', 'closed', 'aborted') THEN
				RAISE EXCEPTION 'invalid state,must be one of `queued'', `sent'', `closed'', `aborted''.';
				RETURN NULL;
			END IF;
		END IF;
		-- set report_outcome by parsing report_data if no report_outcome has been provided
		IF (XTRIM(COALESCE(NEW.report_data, '')) <> '') AND (NEW.report_outcome IS NULL) THEN
			newoutcome := NULL;
			BEGIN -- handle possible XML errors
				newoutcome := (SELECT UNNEST(XPATH('//OUTCOME/text()', NEW.report_data::XML)) LIMIT 1);
			EXCEPTION WHEN OTHERS THEN
				-- RAISE LOG 'could not parse report_data as valid XML (id=%), will try BASE64 next. Error: %', NEW.id, SQLERRM;
				BEGIN -- try to interpret report_data as BASE64-encoded XML
					newoutcome := (SELECT UNNEST(XPATH('//OUTCOME/text()', (decode(NEW.report_data, 'base64'))::TEXT::XML)) LIMIT 1);
				EXCEPTION WHEN OTHERS THEN
					-- RAISE LOG 'could not parse report_data as valid BASE64-encoded XML (id=%). Error: %', NEW.id, SQLERRM;
					newoutcome := NULL;
				END;
			END;
			IF newoutcome IS DISTINCT FROM NULL THEN
				NEW.report_outcome := newoutcome;
			END IF;
		END IF;
		RETURN NEW;
	ELSE
		RETURN OLD;
	END IF;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION public.trfn_va_sec_check_queue()
  OWNER TO appdb;


INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 22, 4, E'Only parse XML report data in va_sec_check_queue when report outcome has not been provided. Fall back to BASE64-encoded XML if parsing initially fails'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=22 AND revision=4);

COMMIT;	
