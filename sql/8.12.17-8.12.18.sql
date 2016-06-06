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
Previous version: 8.12.17
New version: 8.12.18
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

-- IF THIS FUNCTION FAILS, RE-RUN THE SCRIPT AS THE SUPERUSER (postgres), AFTER HAVING CREATED THE perlplu EXTENSION:
-- # createlang perlplu <DATABASENAME>
CREATE OR REPLACE FUNCTION unidecode(TEXT) RETURNS TEXT AS 
$$
	use Text::Unidecode; 
	return unidecode($_[0]);
$$ LANGUAGE plperlu IMMUTABLE STRICT;

CREATE OR REPLACE FUNCTION normalize_cname(s text)
  RETURNS text AS
$BODY$
BEGIN
	s := LOWER(unidecode(s));
	s := REPLACE(trim(regexp_replace(translate(
    lower($1),
    'áàâãäåāăąèééêëēĕėęěìíîïìĩīĭḩóôõöōŏőùúûüũūŭůäàáâãåæçćĉčöòóôõøüùúûßéèêëýñîìíïş',
    'aaaaaaaaaeeeeeeeeeeiiiiiiiihooooooouuuuuuuuaaaaaaeccccoooooouuuuseeeeyniiiis'
), '[^a-z0-9\-]+', ' ', 'g')),' ', '-');
	s := REGEXP_REPLACE(s, '[^A-Za-z0-9]',' ','g');
	-- s := REPLACE(s, ',', ' ');
	s := REGEXP_REPLACE(s, ' +', ' ', 'g');	
	s := REPLACE(s, ' ', '.');	
	s := REGEXP_REPLACE(s, '^\.', '');
	s := REGEXP_REPLACE(s, '\.$', '');	
	RETURN s;
END;
$BODY$
  LANGUAGE plpgsql IMMUTABLE
  COST 100;
ALTER FUNCTION normalize_cname(text)
  OWNER TO appdb;

CREATE OR REPLACE FUNCTION trfn_researchers()
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
				IF NEW.accounttype = 1 THEN 
					NEW.gender = 'robot';
				END IF;
            ELSIF TG_WHEN = 'AFTER' THEN
                INSERT INTO news (timestamp, subjectguid, action) VALUES (NOW(), NEW.guid, 'insert');
                FOR i IN 0..5 LOOP
					PERFORM subscribe_to_notification(NEW.id, i);
				END LOOP;
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
				IF ROW(NEW.firstname, NEW.lastname, NEW.institution, NEW.countryid, NEW.positiontypeid, NEW.gender) IS DISTINCT FROM 
				ROW(OLD.firstname, OLD.lastname, OLD.institution, OLD.countryid, OLD.positiontypeid, OLD.gender) THEN
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
                IF (NEW.gender <> OLD.gender) THEN mFields := array_append(mFields,'gender'); END IF;
                IF NOT mFields IS NULL THEN
                    INSERT INTO news (timestamp, subjectguid, action, fields) VALUES (NOW(), NEW.guid, 'update', mFields);
                END IF;
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
				FOR i IN 4..5 LOOP
					PERFORM unsubscribe_from_notification(NEW.id, i);
					PERFORM subscribe_to_notification(NEW.id, i);
				END LOOP;
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
ALTER FUNCTION trfn_researchers()
  OWNER TO appdb;

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 12, 18, E'Use Perl Text::Unidecode to normalize cnames'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=12 AND revision=18);

COMMIT;
