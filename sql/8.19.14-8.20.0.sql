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
Previous version: 8.19.14
New version: 8.20.0
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

CREATE OR REPLACE FUNCTION openaire.xml_search_results(openaire.projects)
RETURNS XML AS
$$
SELECT
	CASE WHEN EXISTS (SELECT 1 FROM public.projects WHERE code = $1.code) THEN
		(SELECT XMLELEMENT(
			name "record",
			XMLATTRIBUTES(x.id AS id, x.guid AS guid),
			XMLELEMENT(name "property", XMLATTRIBUTES('acronym' AS name), x.acronym),
			XMLELEMENT(name "property", XMLATTRIBUTES('title' AS name), x.title),
			XMLELEMENT(name "property", XMLATTRIBUTES('ga' AS name), x.code)
		) FROM public.projects AS x WHERE code = $1.code)
	ELSE
		XMLELEMENT(
			name "record",
			XMLATTRIBUTES($1.id AS id),
			XMLELEMENT(name "property", XMLATTRIBUTES('acronym' AS name), $1.acronym),
			XMLELEMENT(name "property", XMLATTRIBUTES('title' AS name), $1.title),
			XMLELEMENT(name "property", XMLATTRIBUTES('ga' AS name), $1.code)
		)
	END;

$$ LANGUAGE SQL STABLE;
ALTER FUNCTION openaire.xml_search_results(openaire.projects) OWNER TO appdb;

CREATE OR REPLACE FUNCTION openaire.xml_search_results(openaire.organizations)
RETURNS XML AS
$$
SELECT
	CASE WHEN EXISTS (SELECT 1 FROM public.organizations WHERE name = $1.name AND countryid = (SELECT id FROM countries WHERE LOWER(isocode) = LOWER($1.country))) THEN
		(SELECT XMLELEMENT(
			name "record",
			XMLATTRIBUTES(x.id AS id, x.guid AS guid),
			XMLELEMENT(name "property", XMLATTRIBUTES('legalname' AS name), x.name),
			XMLELEMENT(name "property", XMLATTRIBUTES('legalshortname' AS name), x.shortname),
			XMLELEMENT(name "property", XMLATTRIBUTES('country_iso' AS name), (SELECT isocode FROM countries WHERE id = x.countryid))
		) FROM public.organizations AS x WHERE name = $1.name AND countryid = (SELECT id FROM countries WHERE LOWER(isocode) = LOWER($1.country)))
	ELSE
		XMLELEMENT(
			name "record",
			XMLATTRIBUTES($1.id AS id),
			XMLELEMENT(name "property", XMLATTRIBUTES('legalname' AS name), $1.name),
			XMLELEMENT(name "property", XMLATTRIBUTES('legalshortname' AS name), $1.shortname),
			XMLELEMENT(name "property", XMLATTRIBUTES('country_iso' AS name), $1.country)
		)
	END

$$ LANGUAGE SQL STABLE;
ALTER FUNCTION openaire.xml_search_results(openaire.organizations) OWNER TO appdb;

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 20, 0, E'create openaire schema functions to mimic old harvest schema'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=20 AND revision=0);

COMMIT;	
