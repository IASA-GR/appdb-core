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
Previous version: 8.22.12
New version: 8.22.13
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

CREATE OR REPLACE FUNCTION openaire.xml_search_results(openaire.organizations)
 RETURNS xml
 LANGUAGE plpgsql
AS $function$
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
--      IF NOT EXISTS (SELECT 1 FROM openaire.organizations WHERE name = $1.name AND countryid IS NOT DISTINCT FROM (SELECT id FROM countries WHERE LOWER(isocode) = LOWER($1.country))) THEN
--              UPDATE projects SET deleted = TRUE WHERE name = $1.name AND countryid IS NOT DISTINCT FROM (SELECT id FROM countries WHERE LOWER(isocode) = LOWER($1.country));
--      END IF;
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
                        (countryid IS NOT DISTINCT FROM (SELECT id FROM countries WHERE LOWER(isocode) IS NOT DISTINCT FROM LOWER($1.country)))

                );
END;
$function$;

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 22, 13, E'Fix bug which did not import organization records from the openaire schema into the public schema, when the coutryid was NULL'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=22 AND revision=13);

COMMIT;	
