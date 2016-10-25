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
Previous version: 8.13.4
New version: 8.13.5
Author: wvkarag@lovecraft.priv.iasa.gr
*/
-- Function: public.app_to_xml_list(integer[])

-- DROP FUNCTION public.app_to_xml_list(integer[]);

START TRANSACTION;

CREATE OR REPLACE FUNCTION public.app_to_xml_list(ids integer[])
  RETURNS SETOF xml AS
$BODY$
SELECT 
XMLELEMENT(
name "application:application",
XMLATTRIBUTES(
applications.id AS id, applications.rating, applications.ratingcount AS "ratingCount",
applications.cname,
applications.metatype,
applications.hitcount,
applications.moderated,
applications.deleted,
applications.guid
), 
XMLELEMENT(name "application:name", applications.name),
XMLELEMENT(name "application:category", XMLATTRIBUTES(c.id, TRUE AS primary), c.name),
CASE WHEN NOT (SELECT logo FROM applogos WHERE appid = applications.id) IS NULL THEN
	XMLELEMENT(name "application:logo", 'https://' || (SELECT data FROM config WHERE var = 'ui-host') || '/apps/getlogo?id=' || applications.id::text)
END
)
FROM applications 
INNER JOIN LATERAL (SELECT id, name FROM categories WHERE id = ANY(applications.categoryid)
AND EXISTS (SELECT * FROM appcategories WHERE isprimary AND appid = applications.id AND categoryid = categories.id)
) AS c ON true
WHERE applications.id = ANY(ids)
ORDER BY idx(ids, applications.id)
$BODY$
  LANGUAGE sql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION public.app_to_xml_list(integer[])
  OWNER TO appdb;

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 13, 5, E'Honor ordering in app_to_xml_list'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=13 AND revision=5);

COMMIT;
