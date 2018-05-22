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
Previous version: 8.19.8
New version: 8.19.9
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
    '"' || REPLACE(COALESCE(array_to_string(array_agg(DISTINCT applications.name), ','), ''), '"', E'”') || '",'
    '"' || CASE WHEN $3 IS NULL THEN '' ELSE REPLACE(COALESCE(array_to_string(array_agg(DISTINCT contacts.data), ','), ''), '"', E'”') END || '"'
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
		),
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
		END
	)::text
END
AS "researcher"
FROM researchers
LEFT OUTER JOIN countries ON countries.id = researchers.countryid
LEFT OUTER JOIN positiontypes ON positiontypes.id = researchers.positiontypeid
LEFT OUTER JOIN researchers_apps ON researchers_apps.researcherid = researchers.id
LEFT OUTER JOIN applications ON applications.id = researchers_apps.appid AND ((applications.deleted OR applications.moderated) IS DISTINCT FROM TRUE)
LEFT OUTER JOIN contacts ON contacts.researcherid = researchers.id AND contacts.contacttypeid = 7
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

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 19, 9, E'Do not return deleted app info from person export function'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=19 AND revision=9);

COMMIT;	
