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
Previous version: 8.22.7
New version: 8.22.8
Author: wvkarag@lovecraft.priv.iasa.gr
*/

CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_projects_lower_code ON projects(LOWER(code));

CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_contracttypes_lower_name ON contracttypes (LOWER(name));
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_contracttypes_lower_groupname ON contracttypes (LOWER(groupname));
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_contracttypes_lower_title ON contracttypes (LOWER(title));

CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_funders_lower_name ON funders(LOWER(name));
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_funders_lower_shortname ON funders(LOWER(shortname));
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_funders_lower_jur ON funders(LOWER(jurisdiction));
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_funders_lower_name_textops ON funders (LOWER(name) text_pattern_ops);

CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_openaire_funders_lower_name ON openaire.funders(LOWER(name));
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_openaire_funders_lower_abbrev ON openaire.funders(LOWER(abbrev));

CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_fundings_trim_identifier ON fundings (TRIM(identifier));

CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_projects_moderated ON projects(moderated);
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_projects_lower_code_not_mod_or_del ON projects (LOWER(code)) WHERE NOT(deleted OR moderated);

CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_openaire_projects_lower_acronym ON openaire.projects (LOWER(acronym) text_pattern_ops);
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_openaire_projects_lower_code ON openaire.projects (LOWER(code) text_pattern_ops);
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_openaire_projects_lower_title ON openaire.projects (LOWER(title) text_pattern_ops);
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_openaire_projects_lower_title_gin ON openaire.projects USING GIN(LOWER(title) gin_trgm_ops);
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_openaire_projects_lower_acronym_gin ON openaire.projects USING GIN(LOWER(acronym) gin_trgm_ops);
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_openaire_projects_lower_code_gin ON openaire.projects USING GIN(LOWER(code) gin_trgm_ops);

CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_openaire_organizations_lower_name_gin ON openaire.organizations USING GIN(LOWER(name) gin_trgm_ops);
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_openaire_organizations_lower_shortname_gin ON openaire.organizations USING GIN(LOWER(shortname) gin_trgm_ops);
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_openaire_organizations_lower_name ON openaire.organizations (LOWER(name) text_pattern_ops);
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_openaire_organizations_lower_shortname ON openaire.organizations (LOWER(shortname) text_pattern_ops);

CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_organizations_lower_name ON organizations(LOWER(name));
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_organizations_lower_shortname ON organizations(LOWER(shortname));
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_organizations_lower_jur ON organizations(LOWER(jurisdiction));
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_organizations_name_not_del_or_mod ON organizations(LOWER(name)) WHERE NOT (deleted OR moderated);

-- Function: openaire.xml_search_results(openaire.organizations)

-- DROP FUNCTION openaire.xml_search_results(openaire.organizations);

CREATE OR REPLACE FUNCTION openaire.xml_search_results(openaire.organizations)
  RETURNS xml AS
$BODY$
BEGIN
	-- Insert record into public schema if it has not been imported yet
	IF NOT EXISTS (SELECT 1 FROM public.organizations WHERE LOWER(name) = LOWER($1.name) AND LOWER(jurisdiction) IS NOT DISTINCT FROM LOWER($1.country)) THEN
		INSERT INTO public.organizations (name, shortname, websiteurl, jurisdiction, countryid, identifier, sourceid, ext_identifier)
			VALUES (
				$1.name, 
				$1.shortname, 
				$1.websiteurl, 
				CASE $1.country WHEN 'UNKNOWN' THEN NULL ELSE $1.country END,
				(SELECT id FROM countries WHERE LOWER(isocode) = LOWER($1.country)), 
				NULL, 
				2, 
				$1.ext_identifier
			);
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
			(LOWER(name) = LOWER($1.name) AND NOT (deleted OR moderated)) 
			AND
			(countryid = (SELECT id FROM countries WHERE LOWER(isocode) IS NOT DISTINCT FROM LOWER($1.country)))
			
		);
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION openaire.xml_search_results(openaire.organizations)
  OWNER TO appdb;

-- Function: openaire.xml_search_results(openaire.projects)

-- DROP FUNCTION openaire.xml_search_results(openaire.projects);

CREATE OR REPLACE FUNCTION openaire.xml_search_results(openaire.projects)
  RETURNS xml AS
$BODY$
DECLARE fundid INT;
DECLARE oldfundid INT;
DECLARE fundids TEXT[];
DECLARE fundnames TEXT[];
DECLARE funddescs TEXT[];
DECLARE contractid INT;
DECLARE funderid INT;
DECLARE i INT;
BEGIN
        fundid := NULL;
        contractid := NULL;
        -- Insert record into public schema if it has not been imported yet
--	RAISE NOTICE '%', $1.code;
        IF NOT EXISTS (SELECT 1 FROM public.projects WHERE LOWER(code) = LOWER($1.code)) THEN
--		RAISE NOTICE '	CONTRACTS';
                -- IMPORT CONTRACT INFO
                contractid := (SELECT id FROM contracttypes WHERE LOWER(name) = LOWER($1.contractname) AND LOWER(groupname) = LOWER($1.contractgroup) AND LOWER(title) = LOWER($1.contracttype) LIMIT 1);
                IF contractid IS NULL THEN
                        INSERT INTO contracttypes (name, groupname, title) VALUES ($1.contractname, $1.contractgroup, $1.contracttype) RETURNING id INTO contractid;
                END IF;

--		RAISE NOTICE '	FUNDER';
                -- IMPORT FUNDER
                -- funderid := (SELECT id FROM public.funders f WHERE name = $1.fundername AND shortname = $1.fundershortname AND jurisdiction = $1.funderjurisdiction);
--RAISE NOTICE '%, %, %', $1.fundername, $1.fundershortname, $1.funderjurisdiction;
		funderid := (
			SELECT id FROM public.funders f 
			WHERE ((
				LOWER(f.name) = LOWER($1.fundername)
			) OR (
					(/*LOWER($1.fundername) LIKE LOWER(f.name) || '%') OR */(LOWER(f.name) LIKE LOWER($1.fundername) || '%')) AND (LOWER(f.shortname) = LOWER($1.fundershortname))
			)) AND (LOWER($1.funderjurisdiction) = LOWER(f.jurisdiction))
		);
                IF funderid IS NULL THEN
                        INSERT INTO funders (name, shortname, ext_identifier, jurisdiction, countryid, refid) 
			VALUES (
				$1.fundername, 
				$1.fundershortname, 
				$1.funderid, 
				$1.funderjurisdiction, (
					SELECT countries.id
					FROM countries WHERE LOWER(isocode) = LOWER($1.funderjurisdiction)
				), (
					SELECT refid 
					FROM openaire.funders f
					WHERE ((
						LOWER(f.name) = LOWER($1.fundername) 
					) OR (
						(/*LOWER($1.fundername) LIKE LOWER(f.name) || '%') OR */(LOWER(f.name) LIKE LOWER($1.fundername) || '%')) AND (LOWER(f.abbrev) = LOWER($1.fundershortname))
					)) AND (LOWER($1.funderjurisdiction) = LOWER((SELECT g.isocode FROM openaire.geonames g WHERE id = f.geonameid)))
				)
			)
                        RETURNING id INTO funderid;
		ELSE 
			UPDATE funders SET ext_identifier = $1.funderid WHERE id = funderid;
                END IF;

--		RAISE NOTICE '	FUNDING TREE';
		IF NOT $1.fundingtree IS NULL THEN
			-- IMPORT FUNDING TREE
			fundids := (SELECT ARRAY_AGG(ft) FROM (SELECT UNNEST($1.fundingtree) ft, generate_series(ARRAY_LOWER($1.fundingtree, 1), ARRAY_UPPER($1.fundingtree, 1) * ARRAY_LENGTH($1.fundingtree, 2)) x, ARRAY_LENGTH($1.fundingtree, 2) n) t
				WHERE x % n = 1
			);
			fundnames := (SELECT ARRAY_AGG(ft) FROM (SELECT UNNEST($1.fundingtree) ft, generate_series(ARRAY_LOWER($1.fundingtree, 1), ARRAY_UPPER($1.fundingtree, 1) * ARRAY_LENGTH($1.fundingtree, 2)) x, ARRAY_LENGTH($1.fundingtree, 2) n) t
				WHERE x % n = 2
			);
			funddescs := (SELECT ARRAY_AGG(ft) FROM (SELECT UNNEST($1.fundingtree) ft, generate_series(ARRAY_LOWER($1.fundingtree, 1), ARRAY_UPPER($1.fundingtree, 1) * ARRAY_LENGTH($1.fundingtree, 2)) x, ARRAY_LENGTH($1.fundingtree, 2) n) t
				WHERE x % n = 0
			);
			fundid := NULL;
			oldfundid := NULL;
--	RAISE NOTICE '--------------';
			IF NOT ARRAY_UPPER($1.fundingtree, 1) IS NULL THEN
				FOR i IN REVERSE ARRAY_UPPER($1.fundingtree, 1)..ARRAY_LOWER($1.fundingtree, 1) LOOP
					IF COALESCE(TRIM(fundids[i]), '') <> '' THEN
						oldfundid := fundid;
--		RAISE NOTICE 'i=%',i;
--		RAISE NOTICE 'fname=%',fundnames[i];
--		RAISE NOTICE 'fpid=%',oldfundid;
						fundid := (SELECT id FROM fundings WHERE TRIM(identifier) = TRIM(fundids[i]));
						IF fundid IS NULL THEN
							INSERT INTO fundings (name, description, parentid, identifier) VALUES (
								fundnames[i],
								funddescs[i],
								fundid,
								fundids[i]
							) RETURNING id INTO fundid;
						END IF;
						UPDATE fundings SET parentid = oldfundid WHERE id = fundid;     -- Make sure the funding tree is properly linked...
					END IF;
				END LOOP;
			END IF;
		END IF;

--		RAISE NOTICE '	PROJECT';
                -- IMPORT PROJECT
                INSERT INTO public.projects (code, acronym, title, startdate, enddate, callidentifier, websiteurl, keywords, duration, contracttypeid, funderid, fundingid, sourceid, ext_identifier)
                        VALUES ($1.code, $1.acronym, $1.title,
                                CASE WHEN TRIM($1.startdate) = '' THEN NULL::date ELSE TRIM($1.startdate)::date END,
                                CASE WHEN TRIM($1.enddate) = '' THEN NULL::date ELSE TRIM($1.enddate)::date END,
                                $1.callidentifier, $1.websiteurl, $1.keywords, $1.duration, contractid, funderid, fundid, 2, $1.ext_identifier);
        END IF;
--	RAISE NOTICE '	DONE';
        -- Mark public record as deleted if not found in harvested data
--      IF NOT EXISTS (SELECT 1 FROM openaire.projects WHERE code = $1.code) THEN
--              UPDATE projects SET deleted = TRUE WHERE code = $1.code;
--      END IF;
        RETURN
                (SELECT XMLELEMENT(
                        name "record",
                        XMLATTRIBUTES(x.id AS id, x.guid AS guid),
                        XMLELEMENT(name "property", XMLATTRIBUTES('acronym' AS name), x.acronym),
                        XMLELEMENT(name "property", XMLATTRIBUTES('title' AS name), x.title),
                        XMLELEMENT(name "property", XMLATTRIBUTES('ga' AS name), x.code)
                ) FROM public.projects AS x WHERE LOWER(code) = LOWER($1.code) AND NOT (deleted OR moderated));
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION openaire.xml_search_results(openaire.projects)
  OWNER TO appdb;

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 22, 8, E'openAIRE: Add missing indices and re-write some WHERE clauses to maximize index compatibility'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=22 AND revision=8);
