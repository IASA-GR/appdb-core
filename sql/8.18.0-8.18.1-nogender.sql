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
Previous version: 8.18.0
New version: 8.18.1
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

DROP MATERIALIZED VIEW IF EXISTS vvv11;
DROP FUNCTION IF EXISTS fulltext_vec(vvv1);
DROP VIEW IF EXISTS vvv1;

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
				array_to_string(
					array_agg(
						DISTINCT xmlelement(name "application", applications.name)::text
					),
				'')::xml
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
LEFT OUTER JOIN applications ON applications.id = researchers_apps.appid
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

-- Function: public.trfn_researchers()

-- DROP FUNCTION public.trfn_researchers();

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
ALTER FUNCTION public.trfn_researchers()
  OWNER TO appdb;

-- Function: public.rankppl(researchers, text)

-- DROP FUNCTION public.rankppl(researchers, text);

CREATE OR REPLACE FUNCTION public.rankppl(
    m_id researchers,
    m_query text)
  RETURNS integer AS
$BODY$
DECLARE rank INT;
DECLARE lrank INT;
DECLARE args TEXT[];
DECLARE arg TEXT;
DECLARE field TEXT;
DECLARE fields TEXT[];
DECLARE vals TEXT[];
DECLARE val TEXT;
DECLARE ops TEXT[];
DECLARE tmp TEXT[];
DECLARE i INT;
DECLARE j INT;
DECLARE kk INT;
DECLARE k TEXT;
DECLARE r RECORD;
DECLARE m_country TEXT;
BEGIN
	IF m_query IS NULL OR TRIM(m_query) = '' THEN RETURN 0; END IF;
	m_query := fltstr_nbs(m_query);
	SELECT countries.name FROM countries WHERE countries.id = m_id.countryid INTO m_country;
	fields = '{id, name, institution, countryname}'::TEXT[];
	rank := 0;
	args := string_to_array(m_query, ' ');
	FOR i IN 1..array_length(args, 1) LOOP
		arg := args[i];		
		LOOP
			IF SUBSTRING(arg,1,1) = '+' OR 
			SUBSTRING(arg,1,1) = '-' OR 
			SUBSTRING(arg,1,1) = '=' OR 
			SUBSTRING(arg,1,1) = '<' OR 
			SUBSTRING(arg,1,1) = '>' OR 
			SUBSTRING(arg,1,1) = '~' OR 
			SUBSTRING(arg,1,1) = '$' OR 
			SUBSTRING(arg,1,1) = '&' THEN
				ops = array_append(ops, SUBSTRING(arg,1,1));
				arg = SUBSTRING(arg,2); 
			ELSE
				EXIT;
			END IF;
		END LOOP;
		IF SUBSTRING(arg,1,12) = 'country.name' THEN arg := 'person.countryname' || SUBSTRING(arg,13); END IF;
		IF SUBSTRING(arg,1,11) = 'country.any' THEN arg := 'person.countryname' || SUBSTRING(arg,12); END IF;		
		IF NOT (SUBSTRING(arg,1,7) = 'person.' OR SUBSTRING(arg,1,4) = 'any.' OR instr(arg,'.') = 0) THEN CONTINUE; END IF;
		IF SUBSTR(arg,1,7) = 'person.' THEN arg = SUBSTRING(arg,8);
		ELSIF SUBSTR(arg,1,4) = 'any.' THEN arg = SUBSTRING(arg,5); END IF;
		tmp := string_to_array(arg, ':');
		field := NULL;
		IF array_length(tmp, 1) > 1 THEN
			IF tmp[1] <> 'any' THEN
				field := tmp[1];
			END IF;	
			val := '';
			FOR j IN 2..array_length(tmp, 1) LOOP
				val := val || tmp[j];
			END LOOP;
		ELSE
			val = tmp[1];
		END IF;
		IF NOT val IS NULL THEN
			FOR j IN 1..array_length(fields, 1) LOOP
				IF ops IS NULL OR ops = '{=}'::TEXT[] THEN
					vals := ('{' || val || ', %' || val || '%}')::TEXT[];
					FOR kk IN 1..array_length(vals, 1) LOOP
						k := vals[kk];
						lrank := 0;					
						IF fields[j] = 'name' THEN IF m_id.name ILIKE k THEN lrank := lrank + 4; END IF; END IF;
						IF fields[j] = 'institution' THEN IF m_id.institution ILIKE k THEN lrank := lrank + 3; END IF; END IF;
						IF fields[j] = 'id' THEN IF m_id.id::TEXT ILIKE k THEN lrank := lrank + 1; END IF; END IF;						
						IF fields[j] = 'countryname' THEN IF m_country ILIKE k THEN lrank := lrank + 1; END IF; END IF;
						-- BONUS FOR SPECIFIC FIELD
						IF fields[j] = field THEN lrank = lrank * 2; END IF;
						rank := rank + lrank;
					END LOOP;
				END IF;
			END LOOP;
		END IF;
	END LOOP;
	RETURN rank;
END
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION public.rankppl(researchers, text)
  OWNER TO appdb;

-- Function: public.relation_item_to_xml(uuid)

-- DROP FUNCTION public.relation_item_to_xml(uuid);

CREATE OR REPLACE FUNCTION public.relation_item_to_xml(guid uuid)
  RETURNS xml AS
$BODY$
WITH rt AS (
       SELECT entitytype AS t FROM entityguids WHERE guid = $1
) SELECT 
       CASE WHEN rt.t = 'software' OR rt.t = 'vappliance' OR rt.t = 'swappliance' THEN (
               SELECT
               XMLELEMENT(
                       name "application:application",
                       XMLATTRIBUTES(
                               applications.id,
                               applications.name,
                               applications.cname,
                               '1' AS sourceid
                       ), applications.description
               ) AS x FROM applications WHERE guid = $1)
       WHEN rt.t = 'organization' THEN (
               SELECT XMLELEMENT(
			name "organization:organization",
			XMLATTRIBUTES(
				organizations.id,
				organizations.name,
				organizations.shortname,
				organizations.sourceid
			), XMLELEMENT( name "organization:url", XMLATTRIBUTES( 'website' AS type ), organizations.websiteurl ), country_to_xml(organizations.countryid) 
               ) AS x FROM organizations WHERE guid = $1)
       WHEN rt.t = 'person' THEN (
               SELECT XMLELEMENT(
			name "person:person",
			XMLATTRIBUTES(
				researchers.id,
				researchers.cname,
				'1' AS sourceid
			), 
			XMLELEMENT( name "person:firstname", researchers.firstname ), 
			XMLELEMENT( name "person:lastname", researchers.lastname )
               ) AS x FROM researchers WHERE guid = $1
       )
       WHEN rt.t = 'project' THEN (
               SELECT XMLELEMENT(
			name "project:project",   
			XMLATTRIBUTES(
				projects.id,
				projects.code,
				projects.sourceid
			),
			XMLELEMENT( name "project:acronym", projects.acronym ),
			XMLELEMENT( name "project:title", projects.title ),
			XMLELEMENT( name "project:url", XMLATTRIBUTES( 'website' AS type ), projects.websiteurl ),
			XMLELEMENT( name "project:startdate", projects.startdate ),
			XMLELEMENT( name "project:enddate", projects.enddate )
               ) AS x FROM projects WHERE guid = $1
       ) 
       WHEN rt.t = 'vo' THEN (
               SELECT XMLELEMENT(
			name "vo:vo",
			XMLATTRIBUTES( 
				vos.id,
				vos.name,
				vos.alias,
				vos.scope,
				vos.status,
				vos.validated AS validatedOn,
				vos.sourceid
			),
			vos.description
	       ) AS x FROM vos WHERE guid = $1
       )
       WHEN rt.t = 'publication' THEN (
               SELECT XMLELEMENT( name "publication:publication" ) AS x FROM appdocuments WHERE guid = $1
       )
       END FROM rt;
$BODY$
  LANGUAGE sql STABLE
  COST 100;
ALTER FUNCTION public.relation_item_to_xml(uuid)
  OWNER TO appdb;

-- Function: public.researcher_to_xml(integer[], text, integer)

-- DROP FUNCTION public.researcher_to_xml(integer[], text, integer);

CREATE OR REPLACE FUNCTION public.researcher_to_xml(
    mid integer[],
    mname text DEFAULT ''::text,
    mappid integer DEFAULT NULL::integer)
  RETURNS SETOF xml AS
$BODY$
DECLARE myxml XML[];
DECLARE ppl RECORD;
DECLARE m_ngis TEXT;
DECLARE m_contacts TEXT;
DECLARE m_contactitems TEXT;
DECLARE i INT;
BEGIN
	IF mname = 'simpleindex' THEN
		RETURN QUERY SELECT xmlelement(name "person:person", xmlattributes(id, nodissemination, '' as metatype,
		CASE WHEN deleted THEN 'true' END as deleted), xmlelement(name "person:firstname", TRIM(firstname)), 
		xmlelement(name "person:lastname", TRIM(lastname))) FROM researchers WHERE id = ANY(mid) ORDER BY idx(mid, id);
	ELSE
		IF mid IS NULL OR (array_length(mid, 1) > 0 AND mid[1] IS NULL) THEN
			RETURN QUERY SELECT xmlelement(name "person:person", xmlattributes('true' as "xsi:nil", mname AS metatype),'');
		ELSE
			myxml := NULL::XML[];
			FOR ppl IN SELECT researchers.id,
			researchers.guid,
			researchers.firstname,
			researchers.lastname,
			researchers.nodissemination,
			researchers.deleted,
			researchers.institution AS institute,
			researchers.dateinclusion AS registeredon,
			researchers.lastupdated AS lastupdated,
			(array_agg(researcherimages.image))[1] AS image,
			researchers.cname,
			researchers.hitcount,
			countries AS country,
			array_agg(DISTINCT ngis) AS ngis,
			positiontypes AS "role",
			array_agg(DISTINCT contacts) AS contacts,
			--array_agg(DISTINCT contacttypes) AS contacttypes,
			CASE WHEN mname = 'contact' AND NOT mappid IS NULL THEN
				array_agg(DISTINCT appcontact_items)
			ELSE				
				NULL::appcontact_items[]
			END AS contactitems
			FROM researchers
			INNER JOIN countries ON countries.id = researchers.countryid
			LEFT OUTER JOIN ngis ON ngis.countryid = researchers.countryid
			INNER JOIN positiontypes ON positiontypes.id = researchers.positiontypeid
			LEFT OUTER JOIN contacts ON contacts.researcherid = researchers.id
			LEFT OUTER JOIN contacttypes ON contacttypes.id = contacts.contacttypeid
			LEFT OUTER JOIN appcontact_items ON appcontact_items.researcherid = researchers.id AND appcontact_items.appid = mappid
			LEFT OUTER JOIN researcherimages ON researcherimages.researcherid = researchers.id
			WHERE researchers.id = ANY(mid)
			GROUP BY researchers.id,
			countries,
			positiontypes
			ORDER BY idx(mid, researchers.id) LOOP
				m_ngis := '';
				IF ppl.ngis IS NULL OR (array_length(ppl.ngis, 1) > 0 AND ppl.ngis[1] IS NULL) THEN
					m_ngis := '<regional:provider xsi:nil="true" id="0" />';
				ELSE
					FOR i IN 1..array_length(ppl.ngis, 1) LOOP
						m_ngis := m_ngis || xmlelement(
							name "regional:provider", 
							xmlattributes(
							(ppl.ngis[i]).id AS id, 
							(ppl.ngis[i]).countryid AS countryid,
							CASE WHEN (ppl.ngis[i]).countryid IS NULL THEN 'EIRO' ELSE 'NGI' END AS "type",
							(ppl.ngis[i]).european AS european
							), 
							xmlelement(name "regional:name", 
							(ppl.ngis[i]).name),
							xmlelement(name "regional:description", 
							(ppl.ngis[i]).description),
							xmlelement(name "regional:url", 
							(ppl.ngis[i]).url),
							CASE WHEN NOT (ppl.ngis[i]).logo IS NULL THEN
							xmlelement(name "regional:logo", 'http://'||(SELECT data FROM config WHERE var='ui-host')||'/ngi/getlogo?id='||(ppl.ngis[i]).id::TEXT)
							END
						)::TEXT;
					END LOOP;
				END IF;
				m_contacts := '';
				IF ppl.contacts IS NULL OR (array_length(ppl.contacts, 1) > 0 AND ppl.contacts[1] IS NULL) THEN
					m_contacts = '<person:contact xsi:nil="true" id="0" />';
				ELSE
					FOR i IN 1..array_length(ppl.contacts, 1) LOOP
						m_contacts := m_contacts || xmlelement(name "person:contact",
							xmlattributes(
							(SELECT description FROM contacttypes WHERE id = (ppl.contacts[i]).contacttypeid) AS "type",
							(ppl.contacts[i]).id AS id,
							(ppl.contacts[i]).isprimary AS "primary"),
							(ppl.contacts[i]).data
						)::TEXT;
					END LOOP;
				END IF;
				m_contactitems := '';
				IF mname = 'contact' AND NOT mappid IS NULL THEN
					IF ppl.contactitems IS NULL OR (array_length(ppl.contactitems, 1) > 0 AND ppl.contactitems[1] IS NULL) THEN
						m_contactitems := '<application:contactItem xsi:nil="true" id="0" />';
					ELSE
						FOR i IN 1..array_length(ppl.contactitems, 1) LOOP
							m_contactitems := m_contactitems || xmlelement(name "application:contactItem",
							xmlattributes(
							CASE WHEN (ppl.contactitems[i]).itemid IS NULL THEN 0 ELSE (ppl.contactitems[i]).itemid END AS id,
							(ppl.contactitems[i]).itemtype AS "type",
							(ppl.contactitems[i]).note AS "note"
							), (ppl.contactitems[i]).item
							)::TEXT;
						END LOOP;
					END IF;
				ELSE
					m_contactitems := NULL;
				END IF;
				myxml := array_append(myxml, (SELECT xmlelement(
				name "person:person",
				xmlattributes(ppl.id,
				ppl.guid,
				ppl.nodissemination,
				mname AS metatype,
				ppl.cname,
				ppl.hitcount AS hitcount,
				CASE WHEN ppl.deleted THEn 'true' END as deleted), E'\n\t',
				xmlelement(name "person:firstname", ppl.firstname), E'\n\t',
				xmlelement(name "person:lastname", ppl.lastname), E'\n\t',
				xmlelement(name "person:registeredOn", ppl.registeredon), E'\n\t',
				xmlelement(name "person:lastUpdated", ppl.lastupdated), E'\n\t',
				xmlelement(name "person:institute", ppl.institute), E'\n\t',
				xmlelement(name "regional:country", xmlattributes(
					(ppl.country).id AS id,
					(ppl.country).isocode AS isocode,
					(ppl.country).regionid AS regionid),
					(ppl.country).name
				), E'\n\t',
				m_ngis::XML, E'\n\t',
				xmlelement(name "person:role", 
				xmlattributes((ppl.role).id AS id,
				(ppl.role).description AS "type")), E'\n\t',
				m_contacts::XML, E'\n\t',
				xmlelement(name "person:permalink",'http://'||(SELECT data FROM config WHERE var='ui-host')||'/?p='||encode(CAST('/people/details?id='||ppl.id::text AS bytea),'base64')), E'\n\t',
				CASE WHEN NOT ppl.image IS NULL THEN
					xmlelement(name "person:image",'http://'||(SELECT data FROM config WHERE var='ui-host')||'/people/getimage?id='||ppl.id::text)
				END, E'\n\t',
				m_contactitems::XML
				)));
			END LOOP;
			RETURN QUERY SELECT unnest(myxml);
		END IF;
	END IF;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION public.researcher_to_xml(integer[], text, integer)
  OWNER TO appdb;

-- Function: public.researcher_to_xml_ext(integer, text, integer)

-- DROP FUNCTION public.researcher_to_xml_ext(integer, text, integer);

CREATE OR REPLACE FUNCTION public.researcher_to_xml_ext(
    mid integer,
    mname text DEFAULT ''::text,
    muserid integer DEFAULT NULL::integer)
  RETURNS SETOF xml AS
$BODY$
BEGIN
RETURN QUERY
WITH relations AS(
        SELECT $1 as id, xmlagg(x) as "xml" FROM subject_relations_to_xml((SELECT guid FROM researchers WHERE id = $1)) as x
)
SELECT xmlelement(name "person:person", xmlattributes(
researchers.id as id, researchers.guid, researchers.nodissemination as nodissemination, $2 as metatype, cname, hitcount as hitcount, CASE WHEN deleted IS TRUE THEN 'true' END as deleted), E'\n\t',
xmlelement(name "person:firstname", researchers.firstname), E'\n\t',
xmlelement(name "person:lastname", researchers.lastname), E'\n\t',
xmlelement(name "person:registeredOn", researchers.dateinclusion), E'\n\t',
xmlelement(name "person:lastUpdated", researchers.lastupdated),E'\n\t',
xmlelement(name "person:institute", researchers.institution), E'\n\t',
country_to_xml(countries.id), E'\n\t',
ngis.ngi, E'\n\t',
relations.xml,
xmlelement(name "person:role", xmlattributes(positiontypes.id as id, positiontypes.description as "type")), E'
\n\t',
conts.contact, E'\n\t',
xmlelement(name "person:permalink",'http://'||(SELECT data FROM config WHERE var='ui-host')||'/?p='||encode(CAST('/people/details?id='||researchers.id::text AS bytea),'base64')), E'\n\t',
CASE WHEN NOT researcherimages.image IS NULL THEN
xmlelement(name "person:image",'http://'||(SELECT data FROM config WHERE var='ui-host')||'/people/getimage?id='||researchers.id::text)
END,
apps.app::xml, E'\n\t',
pubs.pub, E'\n\t',
CASE WHEN researchers.deleted IS TRUE AND (NOT muserid IS NULL) /*AND ((SELECT positiontypeid FROM researchers AS deleters WHERE deleters.id = muserid) IN (5,7))*/ THEN
(
xmlelement(name "person:deletedOn",ppl_del_infos.deletedon)::text || researcher_to_xml(ppl_del_infos.deletedby, 'deleter2')::text
)::xml
END,
vos.vo,
vocontacts.vocontact,
privgroups_to_xml((SELECT guid FROM researchers WHERE id = $1)),E'\n\t',
CASE WHEN NOT muserid IS NULL THEN
perms_to_xml(researchers.guid,muserid)
END
) as researcher FROM researchers
INNER JOIN countries ON countries.id = researchers.countryid
INNER JOIN positiontypes ON positiontypes.id = researchers.positiontypeid
LEFT OUTER JOIN relations ON relations.id = researchers.id
LEFT OUTER JOIN (SELECT researcherid, xmlagg(xmlelement(name "person:contact", xmlattributes(contacttypes.description as type, contacts.id as id, contacts.isprimary as primary), contacts.data)) AS contact FROM contacts INNER 
JOIN contacttypes ON contacttypes.id = contacts.contacttypeid GROUP BY researcherid) AS conts ON conts.researcherid = researchers.id
LEFT OUTER JOIN (SELECT DISTINCT ON (researcherid, array_agg(appid)) researcherid, xmlagg(app_to_xml(appid)) AS app FROM (SELECT researcherid, appid FROM researchers_apps INNER JOIN applications ON applications.id = researchers_apps.appid AND (applications.deleted OR applications.moderated) IS FALSE UNION SELECT owner, id FROM applications WHERE (deleted OR moderated) IS FALSE) AS t GROUP BY researcherid) AS apps ON apps.researcherid = researchers.id
LEFT OUTER JOIN (SELECT authorid, xmlagg(appdocument_to_xml(docid)) AS pub FROM (SELECT DISTINCT authorid, docid FROM intauthors INNER JOIN appdocuments ON appdocuments.id = docid INNER JOIN applications ON applications.id = 
appdocuments.appid AND NOT (applications.deleted OR applications.moderated)) AS T GROUP BY authorid) AS pubs ON pubs.authorid = researchers.id
LEFT OUTER JOIN (SELECT countryid, xmlagg(ngi_to_xml(id)) AS ngi FROM ngis GROUP BY countryid) AS ngis ON ngis.countryid = researchers.countryid
LEFT OUTER JOIN ppl_del_infos ON ppl_del_infos.researcherid = researchers.id
LEFT OUTER JOIN researcherimages ON researcherimages.researcherid = researchers.id
LEFT OUTER JOIN (
        SELECT
                researcherid,
				array_to_string(
                array_agg(DISTINCT 
                        xmlelement(
                                name "vo:vo",
                                xmlattributes(
                                        void AS id,
                                        (SELECT name FROM vos WHERE id = void) AS name,
                                        (SELECT domains.name FROM domains WHERE id = (SELECT domainid FROM vos WHERE id = void)) AS discipline,
                                        'member' AS relation,
                                        member_since
                                )
                        )::text
                ),'')::xml AS vo
        FROM vo_members
        GROUP BY researcherid
) AS vos ON vos.researcherid = researchers.id
LEFT OUTER JOIN (
        SELECT
                researcherid,
				array_to_string(
                array_agg(DISTINCT 
                        xmlelement(
                                name "vo:vo",
                                xmlattributes(
                                        void AS id,
                                        (SELECT name FROM vos WHERE id = void) AS name,
                                        (SELECT domains.name FROM domains WHERE id = (SELECT domainid FROM vos WHERE id = void)) AS discipline,
                                        'contact' AS relation,
                                        "role"
                                )
                        )::text
                ),'')::xml AS vocontact
        FROM vo_contacts
        GROUP BY researcherid
) AS vocontacts ON vocontacts.researcherid = researchers.id
CROSS JOIN (
        SELECT /*xmlelement(name "privilege:actor", xmlattributes(), */xmlagg(x)/*)*/ AS priv FROM (SELECT privs_to_xml((SELECT guid FROM researchers WHERE id = $1)) AS x UNION ALL SELECT privgroups_to_xml((SELECT guid FROM researchers WHERE id = $1))) AS privs1
) AS privs
WHERE researchers.id=mid;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION public.researcher_to_xml_ext(integer, text, integer)
  OWNER TO appdb;

-- Function: public.trfn_researchers_cache_delta()

-- DROP FUNCTION public.trfn_researchers_cache_delta();

CREATE OR REPLACE FUNCTION public.trfn_researchers_cache_delta()
  RETURNS trigger AS
$BODY$
DECLARE rec RECORD;
BEGIN
        IF TG_OP = 'INSERT' OR (TG_OP = 'UPDATE' AND (NEW.firstname, NEW.lastname, NEW.dateinclusion, NEW.institution, NEW.countryid, NEW.positiontypeid, NEW.name, NEW.lastlogin, NEW.nodissemination, NEW.deleted) IS DISTINCT FROM (OLD.firstname, OLD.lastname, OLD.dateinclusion, OLD.institution, OLD.countryid, OLD.positiontypeid, OLD.name, OLD.lastlogin, OLD.nodissemination, OLD.deleted) ) THEN
                rec := NEW;
                PERFORM pg_notify('cache_delta', rec || '|researchers');
        ELSIF TG_OP = 'DELETE' THEN
                PERFORM pg_notify('cache_delta', zerorec('researchers') || '|researchers');
        END IF;
        RETURN NULL;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION public.trfn_researchers_cache_delta()
  OWNER TO appdb;

ALTER TABLE researchers DROP COLUMN gender;
ALTER TABLE rankedppl DROP COLUMN gender;

DELETE FROM cache.filtercache;

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 18, 1, E'drop gender-related info from database'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=18 AND revision=1);

COMMIT;
