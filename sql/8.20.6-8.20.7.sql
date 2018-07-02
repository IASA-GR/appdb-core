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
Previous version: 8.20.6
New version: 8.20.7
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

CREATE OR REPLACE FUNCTION public.perms_to_xml(object uuid, userid integer)
 RETURNS xml
 LANGUAGE sql STABLE
AS $function$
SELECT
        XMLELEMENT(
                name "user:permissions",
                XMLATTRIBUTES(
                        $2 as "userid"
                ),
                xmlagg(
                        XMLELEMENT(
                                name "privilege:action",
                                XMLATTRIBUTES(
                                        "system",
                                        actionid as "id"),
                                description
                        )
                ORDER by actionid)
        )
FROM (
        SELECT DISTINCT
                "system",
                actionid,
                description
        FROM permissions
        INNER JOIN actions ON actions.id = permissions.actionid
        INNER JOIN researchers ON researchers.guid = permissions.actor
        WHERE researchers.id = $2 AND (object = $1 OR object IS NULL)
	AND CASE WHEN EXISTS (SELECT 1 FROM applications WHERE guid = $1) THEN
                actions.id = ANY(app_actions())
	WHEN EXISTS (SELECT 1 FROM researchers WHERE guid = $1) THEN
                actions.id = ANY(ppl_actions())
        ELSE
                NOT (actions.id = ANY(ppl_actions()) OR actions.id = ANY(app_actions()))
        END
) AS t;
$function$;

DROP FUNCTION IF EXISTS researcher_to_xml_ext(int, text, int);
CREATE OR REPLACE FUNCTION public.researcher_to_xml_ext(
    mid integer,
    mname text DEFAULT ''::text,
    muserid integer DEFAULT NULL::integer)
  RETURNS xml AS
$BODY$
SELECT
	XMLELEMENT(
		name "person:person",
		XMLATTRIBUTES(
			researchers.id as id,
			researchers.guid,
			researchers.nodissemination as nodissemination,
			$2 as metatype,
			cname,
			hitcount as hitcount,
			CASE WHEN deleted IS TRUE THEN 'true' END as deleted
		),
		XMLELEMENT(name "person:firstname", researchers.firstname),
		XMLELEMENT(name "person:lastname", researchers.lastname),
		XMLELEMENT(name "person:registeredOn", researchers.dateinclusion),
		XMLELEMENT(name "person:lastUpdated", researchers.lastupdated),
		XMLELEMENT(name "person:institute", researchers.institution),
		country_to_xml(countries.id),
		ngis.ngi,
		relations.xml,
		XMLELEMENT(
			name "person:role",
			XMLATTRIBUTES(
				positiontypes.id as id,
				positiontypes.description as "type"
			)
		),
		conts.contact,
		XMLELEMENT(
			name "person:permalink",'http://' || (SELECT data FROM config WHERE var='ui-host') || '/?p=' || encode(CAST('/people/details?id=' || researchers.id::text AS bytea), 'base64')
		),
		CASE WHEN NOT researcherimages.image IS NULL THEN
			XMLELEMENT(name "person:image",'http://' || (SELECT data FROM config WHERE var='ui-host') || '/people/getimage?id=' || researchers.id::text)
		END,
		apps.app::xml,
		pubs.pub,
		CASE WHEN researchers.deleted IS TRUE AND (NOT $3 IS NULL) THEN (
			XMLELEMENT(
				name "person:deletedOn",
				ppl_del_infos.deletedon
			)::text || researcher_to_xml(ppl_del_infos.deletedby, 'deleter2')::TEXT
		)::XML END,
		vos.vo,
		vocontacts.vocontact,
		-- privs.priv,
		privgroups_to_xml((SELECT guid FROM researchers WHERE id = $1)),
		CASE WHEN NOT $3 IS NULL THEN
			perms_to_xml(researchers.guid, $3)
		END
	) AS researcher
FROM researchers
INNER JOIN countries ON countries.id = researchers.countryid
INNER JOIN positiontypes ON positiontypes.id = researchers.positiontypeid
LEFT OUTER JOIN (
	SELECT
		researcherid,
		xmlagg(
			XMLELEMENT(
				name "person:contact",
				XMLATTRIBUTES(
					contacttypes.description as type,
					contacts.id as id,
					contacts.isprimary as primary
				),
				contacts.data
			)
		) AS contact
	FROM contacts
	INNER JOIN contacttypes ON contacttypes.id = contacts.contacttypeid
	WHERE researcherid = $1
	GROUP BY researcherid
) AS conts ON conts.researcherid = researchers.id
LEFT OUTER JOIN (
	SELECT DISTINCT ON (researcherid, array_agg(appid))
		researcherid,
		xmlagg(app_to_xml(appid)) AS app
	FROM (
		SELECT
			researcherid,
			appid
		FROM researchers_apps
		INNER JOIN applications ON applications.id = researchers_apps.appid AND (applications.deleted OR applications.moderated) IS FALSE
		UNION
		SELECT
			owner,
			id
		FROM applications
		WHERE NOT (deleted OR moderated)
	) AS t
	WHERE researcherid = $1
	GROUP BY researcherid
) AS apps ON apps.researcherid = researchers.id
LEFT OUTER JOIN (
	SELECT
		authorid,
		xmlagg(appdocument_to_xml(docid)) AS pub
	FROM (
		SELECT DISTINCT
			authorid,
			docid
		FROM intauthors
		INNER JOIN appdocuments ON appdocuments.id = docid
		INNER JOIN applications ON applications.id = appdocuments.appid AND NOT (applications.deleted OR applications.moderated)
	) AS t
	WHERE authorid = $1
	GROUP BY authorid
) AS pubs ON pubs.authorid = researchers.id
LEFT OUTER JOIN (
	SELECT
		countryid,
		xmlagg(ngi_to_xml(id)) AS ngi
	FROM ngis
	GROUP BY countryid
) AS ngis ON ngis.countryid = researchers.countryid
LEFT OUTER JOIN ppl_del_infos ON ppl_del_infos.researcherid = researchers.id
LEFT OUTER JOIN researcherimages ON researcherimages.researcherid = researchers.id
LEFT OUTER JOIN (
        SELECT
                researcherid,
		array_to_string(
			array_agg(DISTINCT
				XMLELEMENT(
					name "vo:vo",
					XMLATTRIBUTES(
						void AS id,
						(SELECT name FROM vos WHERE id = void) AS name,
						(SELECT domains.name FROM domains WHERE id = (SELECT domainid FROM vos WHERE id = void)) AS discipline,
						'member' AS relation,
						member_since
					)
				)::TEXT
			), ''
		)::XML AS vo
        FROM vo_members
	WHERE researcherid = $1
        GROUP BY researcherid
) AS vos ON vos.researcherid = researchers.id
LEFT OUTER JOIN (
        SELECT
                researcherid,
		array_to_string(
			array_agg(DISTINCT
				XMLELEMENT(
					name "vo:vo",
					XMLATTRIBUTES(
						void AS id,
						(SELECT name FROM vos WHERE id = void) AS name,
						(SELECT domains.name FROM domains WHERE id = (SELECT domainid FROM vos WHERE id = void)) AS discipline,
						'contact' AS relation,
						"role"
					)
				)::TEXT
			), ''
		)::XML AS vocontact
        FROM vo_contacts
	WHERE researcherid = $1
        GROUP BY researcherid
) AS vocontacts ON vocontacts.researcherid = researchers.id
LEFT OUTER JOIN (
        SELECT $1 as id, xmlagg(x) as "xml" FROM subject_relations_to_xml((SELECT guid FROM researchers WHERE id = $1)) as x
) AS relations ON relations.id = researchers.id
/*CROSS JOIN (
        SELECT
		-- XMLELEMENT(name "privilege:actor", XMLATTRIBUTES(),
			xmlagg(x)
		-- )
		AS priv
	FROM (
		SELECT privs_to_xml((SELECT guid FROM researchers WHERE id = $1)) AS x
		UNION ALL
		SELECT privgroups_to_xml((SELECT guid FROM researchers WHERE id = $1))
	) AS privs1
) AS privs*/
WHERE researchers.id = $1;
$BODY$
  LANGUAGE sql STABLE;
ALTER FUNCTION public.researcher_to_xml_ext(integer, text, integer)
  OWNER TO appdb;

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 20, 7, E'Performance improvements for researcher_to_xml_ext and perms_to_xml'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=20 AND revision=7);

COMMIT;	
