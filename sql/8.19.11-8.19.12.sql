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
Previous version: 8.19.11
New version: 8.19.12
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

CREATE OR REPLACE FUNCTION public.export_researcher(
    mid integer,
    format text DEFAULT 'csv'::text,
    muserid integer DEFAULT NULL::integer)
  RETURNS text AS
$BODY$
SELECT CASE WHEN $2 = 'csv' THEN
'"' || REPLACE(COALESCE(researchers.firstname, ''), '"', E'”') || '",' ||
    '"' || REPLACE(COALESCE(researchers.lastname, ''), '"', E'”') || '",'
    '"' || REPLACE(COALESCE(researchers.dateinclusion::text, ''), '"', E'”') || '",'
    '"' || REPLACE(COALESCE(researchers.institution, ''), '"', E'”') || '",'
    '"' || REPLACE(COALESCE(countries.name, ''), '"', E'”') || '",'
    '"' || REPLACE(COALESCE(positiontypes.description, ''), '"', E'”') || '",'
    '"' || REPLACE(COALESCE('http://' || (SELECT data FROM config WHERE var='ui-host' LIMIT 1) || '?p=' || encode(('/ppl/details?id=' || researchers.id::text)::bytea, 'base64'::text), ''), '"', E'”') || '",'
    '"' || REPLACE(COALESCE(array_to_string(array_agg(DISTINCT applications.name), ','), ''), '"', E'”') /*|| '",'
    '"' || CASE WHEN $3 IS NULL THEN '' ELSE REPLACE(COALESCE(array_to_string(array_agg(DISTINCT contacts.data), ','), ''), '"', E'”') END*/ || '"'
ELSE
	xmlelement(name "researcher",
		xmlelement(name "firstname", researchers.firstname),
		xmlelement(name "lastname", researchers.lastname),
		xmlelement(name "registered", researchers.dateinclusion),	
		xmlelement(name "institution", researchers.institution),	
		xmlelement(name "country", countries.name),	
		xmlelement(name "role", positiontypes.description),	
		xmlelement(name "permalink", 'http://' || (SELECT data FROM config WHERE var='ui-host' LIMIT 1) || '?p=' || encode(('/ppl/details?id=' || researchers.id::text)::bytea, 'base64'::text)),
		xmlelement(name "applications",
			xmlconcat(
				COALESCE(array_to_string(
					array_agg(
						DISTINCT xmlelement(name "application", applications.name)::text
					),
				''),'')::xml
			)
		)/*,
		CASE WHEN $3 IS NULL THEN
			'<contacts/>'::xml
		ELSE
			xmlelement(name "contacts",
				xmlconcat(
					array_to_string(
						array_agg(
							DISTINCT xmlelement(name "contact", contacts.data)::text
						),
					'')::xml
				)
			)
		END */
	)::text
END
AS "researcher"
FROM researchers
LEFT OUTER JOIN countries ON countries.id = researchers.countryid
LEFT OUTER JOIN positiontypes ON positiontypes.id = researchers.positiontypeid
LEFT OUTER JOIN researchers_apps ON researchers_apps.researcherid = researchers.id
LEFT OUTER JOIN applications ON applications.id = researchers_apps.appid AND ((applications.deleted OR applications.moderated) IS DISTINCT FROM TRUE)
-- LEFT OUTER JOIN contacts ON contacts.researcherid = researchers.id AND contacts.contacttypeid = 7
WHERE researchers.id = $1 
GROUP BY researchers.firstname,
    researchers.lastname,
    researchers.dateinclusion,
    researchers.institution,
    countries.name,
    positiontypes.description,
    researchers.id
$BODY$
  LANGUAGE sql VOLATILE
  COST 100;
ALTER FUNCTION public.export_researcher(integer, text, integer)
  OWNER TO appdb;
  
CREATE OR REPLACE FUNCTION public.trfn_researchers()
  RETURNS trigger AS
$BODY$
DECLARE mFields TEXT[];
DECLARE i INT;
DECLARE newCname TEXT;
BEGIN
    mFields := NULL::TEXT[];
    IF TG_OP = 'INSERT' THEN
        IF TG_LEVEL = 'ROW' THEN
            IF TG_WHEN = 'BEFORE' THEN
                NEW.firstname := trim(NEW.firstname);
                NEW.lastname := trim(NEW.lastname);
                NEW.name := NEW.firstname||' '||NEW.lastname;
            ELSIF TG_WHEN = 'AFTER' THEN
                -- INSERT INTO news (timestamp, subjectguid, action) VALUES (NOW(), NEW.guid, 'insert');
                -- FOR i IN 0..5 LOOP
			-- PERFORM subscribe_to_notification(NEW.id, i);
		-- END LOOP;
		IF (NEW.cname IS NULL) THEN
			newCname := TRIM(normalize_cname(NEW.name));
			IF (newCname = '') OR (newCname IS NULL) THEN
				newCname = normalize_cname(NEW.guid);
			END IF;
			IF EXISTS (
				SELECT * FROM researcher_cnames WHERE value = newCname AND enabled
			) THEN
				newCname := newCname || '.' || NEW.ID::text;
			END IF;
			INSERT INTO researcher_cnames (researcherid, value) VALUES (NEW.id, newCname);
		ELSE
			INSERT INTO researcher_cnames (researcherid, value) VALUES (NEW.id, NEW.cname);
		END IF;
            END IF;
        END IF;
        RETURN NEW;
    ELSIF TG_OP = 'UPDATE' THEN
        IF TG_LEVEL = 'ROW' THEN
            IF TG_WHEN = 'BEFORE' THEN
                NEW.firstname := TRIM(NEW.firstname);
                NEW.lastname := TRIM(NEW.lastname);
                NEW.name := NEW.firstname||' '||NEW.lastname;
				IF ROW(NEW.firstname, NEW.lastname, NEW.institution, NEW.countryid, NEW.positiontypeid) IS DISTINCT FROM 
				ROW(OLD.firstname, OLD.lastname, OLD.institution, OLD.countryid, OLD.positiontypeid) THEN
					NEW.lastupdated = NOW();
				ELSE
					NEW.lastupdated = OLD.lastupdated;
				END IF;
            ELSIF TG_WHEN = 'AFTER' THEN
                IF (NEW.firstname <> OLD.firstname) THEN mFields := array_append(mFields,'firstname'); END IF;
                IF (NEW.lastname <> OLD.lastname) THEN mFields := array_append(mFields,'lastname'); END IF;
                IF (NEW.institution <> OLD.institution) THEN mFields := array_append(mFields,'institute'); END IF;
                IF (NEW.countryid <> OLD.countryid) THEN mFields := array_append(mFields,'country'); END IF;
                IF (NEW.positiontypeid <> OLD.positiontypeid) THEN mFields := array_append(mFields,'role'); END IF;
                --IF NOT mFields IS NULL THEN
                    --INSERT INTO news (timestamp, subjectguid, action, fields) VALUES (NOW(), NEW.guid, 'update', mFields);
                --END IF;
                IF (NEW.countryid <> OLD.countryid) THEN
					-- INVALIDATE NATIONAL REPRESENTATIVE GROUP MEMBERSHIP ON COUNTRY CHANGE
					DELETE FROM actor_group_members WHERE actorid = NEW.guid AND groupid = -3 AND payload = OLD.countryid::TEXT;
                    IF EXISTS (SELECT * FROM researchers_apps WHERE researcherid = NEW.id) THEN
                        -- DELETE OLD SYSTEM TAGS, IF THERE ARE NO MORE CONTACTS FROM OLD COUNTRY LEFT
                        DELETE FROM app_tags 
                            WHERE researcherid IS NULL AND
                            appid IN (SELECT appid FROM researchers_apps WHERE researcherid = NEW.id)
                            AND lower(tag) = lower((SELECT name FROM countries WHERE id = OLD.countryid))
                            AND NOT EXISTS (SELECT * FROM appcountries WHERE appid IN (SELECT appid FROM researchers_apps WHERE researcherid = NEW.id) AND id = OLD.countryid);
                        -- DELETE POSSIBLY EXISTING USER TAGS THAT MATCH THE NEW SYSTEM TAG
                        DELETE FROM app_tags 
                            WHERE appid IN (SELECT appid FROM researchers_apps WHERE researcherid = NEW.id)
                            AND lower(tag) = lower((SELECT name FROM countries WHERE id = NEW.countryid));
                        -- INSERT THE NEW SYSTEM TAG
                        INSERT INTO app_tags (appid, researcherid, tag) 
                            SELECT DISTINCT researchers_apps.appid, NULL::int, countries.name 
                                FROM researchers_apps 
                                INNER JOIN researchers ON researchers.id = researchers_apps.researcherid 
                                INNER JOIN countries ON countries.id = researchers.countryid
                                WHERE researchers.id = NEW.id;
                    END IF;
                END IF;
		-- REFRESH ROLE BASED NOTIFICATION SUBSCRIPTIONS
		--FOR i IN 4..5 LOOP
			--PERFORM unsubscribe_from_notification(NEW.id, i);
			--PERFORM subscribe_to_notification(NEW.id, i);
		--END LOOP;
		IF NEW.deleted IS TRUE AND OLD.deleted IS FALSE THEN
			UPDATE researcher_cnames SET enabled = FALSE WHERE researcherid = NEW.id;
		END IF;
            END IF;
        END IF;
        RETURN NEW;
    ELSIF TG_OP = 'DELETE' THEN
        IF TG_LEVEL = 'ROW' THEN
            IF TG_WHEN = 'AFTER' THEN
		-- NOTIFY invalidate_cache, 'permissions';
            END IF;
        END IF;
        RETURN OLD;
    END IF;
END;$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION public.trfn_researchers()
  OWNER TO appdb;

ALTER TABLE researchers ALTER COLUMN nodissemination DROP DEFAULT;
ALTER TABLE researchers ALTER COLUMN nodissemination SET DEFAULT TRUE;
UPDATE researchers SET nodissemination = TRUE;

DELETE FROM news WHERE subjectguid IN (SELECT guid FROM researchers);
CREATE UNIQUE INDEX idx_id_aggregate_news ON aggregate_news (id);
CREATE INDEX idx_news_timestamp ON news(timestamp);

DELETE FROM mail_subscriptions WHERE NOT flt LIKE '%SYSTAG_FOLLOW%';
ALTER TABLE mail_subscriptions ADD COLUMN addedon TIMESTAMP DEFAULT NOW();
UPDATE mail_subscriptions SET addedon = NULL;
ALTER TABLE mail_subscriptions ADD COLUMN lastupdated TIMESTAMP;
UPDATE mail_subscriptions SET lastupdated = NULL;

CREATE OR REPLACE FUNCTION public.trfn_mail_subscriptions_lastupdated()
 RETURNS trigger
 LANGUAGE plpgsql
AS $function$
BEGIN
        NEW.lastupdated := NOW();
        RETURN NEW;
END;
$function$;
ALTER FUNCTION trfn_mail_subscriptions_lastupdated() OWNER TO appdb;

CREATE TRIGGER rtr_mail_subscriptions_90_lastupdated
BEFORE UPDATE ON mail_subscriptions
FOR EACH ROW EXECUTE PROCEDURE trfn_mail_subscriptions_lastupdated();  

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 19, 12, E'Clean-up mail_subscriptions and news'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=19 AND revision=12);
	
COMMIT;

REFRESH MATERIALIZED VIEW CONCURRENTLY aggregate_news;
