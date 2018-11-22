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
Previous version: 8.20.3
New version: 8.20.4
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

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
			RETURN QUERY SELECT xmlelement(name "person:person", xmlattributes('http://www.w3.org/2001/XMLSchema-instance' AS "xmlns:xsi", 'true' as "xsi:nil", mname AS metatype),'');
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
					m_ngis := '<regional:provider xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:nil="true" id="0" />';
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
					m_contacts = '<person:contact xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:nil="true" id="0" />';
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
						m_contactitems := '<application:contactItem xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:nil="true" id="0" />';
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

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 20, 4, E'Add xmlns:xsi to elements using xsi:nil in order to avoid XML errors inside the database (researcher_to_xml)'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=20 AND revision=4);

COMMIT;	
