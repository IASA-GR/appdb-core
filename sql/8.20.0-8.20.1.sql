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
Previous version: 8.20.0
New version: 8.20.1
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

DROP FUNCTION IF EXISTS openaire.xml_search_results(openaire.organizations);
CREATE OR REPLACE FUNCTION openaire.xml_search_results(openaire.organizations)
  RETURNS xml AS
$BODY$
BEGIN
	-- Insert record into public schema if it has not been imported yet
	IF NOT EXISTS (SELECT 1 FROM public.organizations WHERE name = $1.name AND countryid IS NOT DISTINCT FROM (SELECT id FROM countries WHERE LOWER(isocode) = LOWER($1.country))) THEN
		INSERT INTO public.organizations (name, shortname, websiteurl, countryid, identifier, sourceid, ext_identifier)
			VALUES ($1.name, $1.shortname, $1.websiteurl, (SELECT id FROM countries WHERE LOWER(isocode) = LOWER($1.country)), NULL, 2, $1.ext_identifier);
	END IF;
	-- Mark public record as deleted if not found in harvested data
--	IF NOT EXISTS (SELECT 1 FROM openaire.organizations WHERE name = $1.name AND countryid IS NOT DISTINCT FROM (SELECT id FROM countries WHERE LOWER(isocode) = LOWER($1.country))) THEN
--		UPDATE projects SET deleted = TRUE WHERE name = $1.name AND countryid IS NOT DISTINCT FROM (SELECT id FROM countries WHERE LOWER(isocode) = LOWER($1.country));
--	END IF;
	RETURN
		(SELECT XMLELEMENT(
			name "record",
			XMLATTRIBUTES(x.id AS id, x.guid AS guid),
			XMLELEMENT(name "property", XMLATTRIBUTES('legalname' AS name), x.name),
			XMLELEMENT(name "property", XMLATTRIBUTES('legalshortname' AS name), x.shortname),
			XMLELEMENT(name "property", XMLATTRIBUTES('country_iso' AS name), (SELECT isocode FROM countries WHERE id = x.countryid))
		) FROM public.organizations AS x WHERE
			name = $1.name AND
			countryid = (SELECT id FROM countries WHERE LOWER(isocode) IS NOT DISTINCT FROM LOWER($1.country)) AND
			NOT (deleted OR moderated)
		);
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION openaire.xml_search_results(openaire.organizations)
  OWNER TO appdb;

CREATE OR REPLACE FUNCTION public.trfn_organizations_appdb_identifier()
 RETURNS trigger
 LANGUAGE plpgsql
AS $function$
BEGIN
    IF TG_OP = 'INSERT' THEN
        IF TG_LEVEL = 'ROW' THEN
            IF TG_WHEN = 'AFTER' THEN
                UPDATE organizations SET identifier = 'openaire:organization:appdb______::' || NEW.guid::TEXT WHERE id = NEW.id;
            END IF;
        END IF;
    END IF;
    RETURN NULL;
END;$function$;

DROP TRIGGER IF EXISTS rtr_organizations_after_appdb_identifier ON organizations;
CREATE TRIGGER rtr_organizations_after_appdb_identifier
AFTER INSERT ON organizations
FOR EACH ROW
EXECUTE PROCEDURE trfn_organizations_appdb_identifier();

DROP FUNCTION IF EXISTS openaire.xml_search_results(openaire.projects);
CREATE OR REPLACE FUNCTION openaire.xml_search_results(openaire.projects)
  RETURNS xml AS
$BODY$
DECLARE fundid INT;
DECLARE oldfundid INT;
DECLARE fundids TEXT[];
DECLARE fundnames TEXT[];
DECLARE funddescs TEXT[];
DECLARE contractid INT;
DECLARE i INT;
BEGIN
	fundid := NULL;
	contractid := NULL;
	-- Insert record into public schema if it has not been imported yet
	IF NOT EXISTS (SELECT 1 FROM public.projects WHERE code = $1.code) THEN
		-- IMPORT CONTRACT INFO
		contractid := (SELECT id FROM contracttypes WHERE name = $1.contractname AND groupname = $1.contractgroup AND title = $1.contracttype LIMIT 1);
		IF contractid IS NULL THEN
			INSERT INTO contracttypes (name, groupname, title) VALUES ($1.contractname, $1.contractgroup, $1.contracttype) RETURNING id INTO contractid;
		END IF;

		-- IMPORT FUNDING TREE
		fundids := ARRAY[$1.fundingid3, $1.fundingid2, $1.fundingid1, $1.fundingid0];
		fundnames := ARRAY[$1.fundingname3, $1.fundingname2, $1.fundingname1, $1.fundingname0];
		funddescs := ARRAY[$1.fundingdesc3, $1.fundingdesc2, $1.fundingdesc1, $1.fundingdesc0];
		fundid := NULL;
		oldfundid := NULL;
		FOR i IN 1..4 LOOP
			IF COALESCE(TRIM(fundids[i]), '') <> '' THEN
				oldfundid := fundid;
				fundid := (SELECT id FROM fundings WHERE TRIM(identifier) = TRIM(fundids[i]));
				IF fundid IS NULL THEN
					INSERT INTO fundings (name, description, parentid, identifier) VALUES (
						fundnames[i],
						funddescs[i],
						fundid,
						fundids[i]
					) RETURNING id INTO fundid;
				END IF;
				UPDATE fundings SET parentid = oldfundid WHERE id = fundid;	-- Make sure the funding tree is properly linked...
			END IF;
		END LOOP;

		-- IMPORT PROJECT
		INSERT INTO public.projects (code, acronym, title, startdate, enddate, callidentifier, websiteurl, keywords, duration, contracttypeid, fundingid, sourceid, ext_identifier)
			VALUES ($1.code, $1.acronym, $1.title,
				CASE WHEN TRIM($1.startdate) = '' THEN NULL::date ELSE TRIM($1.startdate)::date END,
				CASE WHEN TRIM($1.enddate) = '' THEN NULL::date ELSE TRIM($1.enddate)::date END,
				$1.callidentifier, $1.websiteurl, $1.keywords, $1.duration, contractid, fundid, 2, $1.ext_identifier);
	END IF;
	-- Mark public record as deleted if not found in harvested data
--	IF NOT EXISTS (SELECT 1 FROM openaire.projects WHERE code = $1.code) THEN
--		UPDATE projects SET deleted = TRUE WHERE code = $1.code;
--	END IF;
	RETURN
		(SELECT XMLELEMENT(
			name "record",
			XMLATTRIBUTES(x.id AS id, x.guid AS guid),
			XMLELEMENT(name "property", XMLATTRIBUTES('acronym' AS name), x.acronym),
			XMLELEMENT(name "property", XMLATTRIBUTES('title' AS name), x.title),
			XMLELEMENT(name "property", XMLATTRIBUTES('ga' AS name), x.code)
		) FROM public.projects AS x WHERE code = $1.code AND NOT (deleted OR moderated));
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION openaire.xml_search_results(openaire.projects)
  OWNER TO appdb;

CREATE OR REPLACE FUNCTION public.trfn_projects_appdb_identifier()
 RETURNS trigger
 LANGUAGE plpgsql
AS $function$
BEGIN
    IF TG_OP = 'INSERT' THEN
        IF TG_LEVEL = 'ROW' THEN
            IF TG_WHEN = 'AFTER' THEN
                UPDATE projects SET identifier = 'openaire:project:appdb______::' || NEW.guid::TEXT WHERE id = NEW.id;
            END IF;
        END IF;
    END IF;
    RETURN NULL;
END;$function$;

DROP TRIGGER IF EXISTS rtr_projects_after_appdb_identifier ON projects;
CREATE TRIGGER rtr_projects_after_appdb_identifier
AFTER INSERT ON projects
FOR EACH ROW
EXECUTE PROCEDURE trfn_projects_appdb_identifier();

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 20, 1, E'Update openAIRE search functions for projects and organizations'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=20 AND revision=1);

COMMIT;	
