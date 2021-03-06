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
Previous version: 8.21.1
New version: 8.21.2
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

CREATE OR REPLACE FUNCTION XTRIM(TEXT) RETURNS TEXT AS
$$
	SELECT TRIM(REGEXP_REPLACE(REGEXP_REPLACE(TRIM($1), E'\n*$', ''), E'^\n*', ''));
$$ LANGUAGE SQL;
ALTER FUNCTION XTRIM(TEXT) OWNER TO appdb;
COMMENT ON FUNCTION XTRIM(TEXT) IS 'Trims newlines from the start and end of text, as well as white spaces (like TRIM does)';

CREATE OR REPLACE FUNCTION public.mods2doc(
    xin text,
    _appid integer DEFAULT NULL::integer)
  RETURNS text AS
$BODY$
DECLARE
	i INT;
	j INT;
	k INT;
	xmods XML[];
	xauths XML[];
	xtmp XML[];
	ttmp TEXT[];
	doc appdocuments;
	auth extauthors;
	intauth intauthors;
	dt TEXT;
	ret TEXT;
	auths extauthors[];
	intauths intauthors[];
	_auths authors[];
	_auth authors;
	atype TEXT;
BEGIN
	ret := '[';
	xin := REPLACE(xin, 'xmlns="http://www.loc.gov/mods/v3"', '');
	IF xpath_exists('//modsCollection/mods', xin::XML) THEN
		xmods := xpath('//modsCollection/mods', xin::XML);
		FOR i in 1..ARRAY_LENGTH(xmods, 1) LOOP
			auths := NULL::extauthors[];
			intauths := NULL::intauthors[];
			doc.appid := $2;
			doc.title := unescapexml(XTRIM((xpath('./titleInfo/title/text()', xmods[i]))[1]::TEXT));
			doc.year := unescapexml(XTRIM((xpath('./originInfo/dateIssued/text()', xmods[i]))[1]::TEXT));
			doc.volume := unescapexml(XTRIM((xpath('./part/detail[@type="volume"]/number/text()', xmods[i]))[1]::TEXT));
			IF xpath_exists('./relatedItem[@type="host"]/genre[text()="academic journal"]', xmods[i]) THEN
				doc.journal := (xpath('./relatedItem[@type="host"]/titleInfo/title/text()', xmods[i]))[1]::TEXT;
			ELSIF xpath_exists('./relatedItem[@type="host"]/genre[text()="conference publication"]', xmods[i]) THEN
				doc.conference := (xpath('./relatedItem[@type="host"]/titleInfo/title/text()', xmods[i]))[1]::TEXT;
			END IF;
			IF xpath_exists('./relatedItem[@type="host"]/genre[text()="academic journal"]', xmods[i]) THEN
				doc.doctypeid = 1;
			ELSIF xpath_exists('./genre[text()="thesis"]', xmods[i]) THEN
				doc.doctypeid = 6;
			ELSE
				doc.doctypeid = 8;
			END IF;
			IF xpath_exists('./part/detail[@type="page"]/number', xmods[i]) THEN
				doc.pagestart := (STRING_TO_ARRAY(XTRIM((xpath('./part/detail[@type="page"]/number/text()', xmods[i]))[1]::TEXT), ','))[1];
				ttmp := STRING_TO_ARRAY(XTRIM((xpath('./part/detail[@type="page"]/number/text()', xmods[i]))[1]::TEXT), ',');
				doc.pageend := ttmp[ARRAY_LENGTH(ttmp, 1)];
			ELSIF xpath_exists('./part/extent[@unit="page"]', xmods[i]) THEN
				doc.pagestart := XTRIM((xpath('./part/extent[@unit="page"]/start/text()', xmods[i]))[1]::TEXT);
				doc.pageend := XTRIM((xpath('./part/extent[@unit="page"]/end/text()', xmods[i]))[1]::TEXT);
			END IF;
			doc.publisher := XTRIM((xpath('./relatedItem[@type="host"]/originInfo/publisher/text()', xmods[i]))[1]::TEXT);
			doc.url := unescapexml(XTRIM((xpath('./location/url/text()', xmods[i]))[1]::TEXT));
			-- RAISE NOTICE 'doc: %', doc;
			IF xpath_exists('./name[@type="personal"]/role/roleTerm[@type="text" and text()="author"]', xmods[i]) THEN
				xauths := xpath('./name[@type="personal"]', xmods[i]);
				FOR j IN 1..ARRAY_LENGTH(xauths, 1) LOOP
					_auth := NULL::authors;
					auth := NULL::extauthors;
					intauth := NULL::intauthors;
					IF xpath_exists('./namePart', xauths[j]) THEN
						IF (xpath('./role/roleTerm[@type="text"]/text()', xauths[j]))[1]::TEXT = 'author' THEN
							IF xpath_exists('./namePart[@type="given"]', xauths[j]) THEN
								xtmp := xpath('./namePart[@type="given"]/text()', xauths[j]);
								atype := 'external';
								auth.main := FALSE;
								intauth.main := FALSE;
								auth.author := '';
								FOR k in 1..ARRAY_LENGTH(xtmp, 1) LOOP
									auth.author := auth.author || unescapexml(XTRIM(COALESCE(xtmp[k]::TEXT))) || ' ';
								END LOOP;
								auth.author := XTRIM(auth.author || unescapexml(XTRIM(COALESCE((xpath('./namePart[@type="family"]/text()', xauths[j]))[1]::TEXT))));
								IF (NOT ($2 IS NULL)) AND EXISTS (SELECT 1 FROM researchers_apps ra INNER JOIN researchers r ON r.id = ra.researcherid WHERE (ra.appid = $2) AND (r.name = auth.author)) THEN
									atype := 'internal';
									intauth.authorid = (SELECT ra.researcherid FROM researchers_apps ra INNER JOIN researchers r ON r.id = ra.researcherid WHERE (ra.appid = $2) AND (r.name = auth.author));
								END IF;
							END IF;
						-- ELSE
							-- RAISE NOTICE 'not an author';
						END IF;
					-- ELSE
						-- RAISE NOTICE 'no ./namePart';
					END IF;

					IF atype = 'external' THEN
						IF j = 1 THEN auth.main := TRUE; END IF;
						auths := auths || ARRAY[auth];
						_auth.fullname := auth.author;
						_auth.main := auth.main;
						_auths := _auths || ARRAY[_auth];
					ELSE
						IF j = 1 THEN intauth.main := TRUE; END IF;
						intauths := intauths || ARRAY[intauth];

						_auth.fullname := (SELECT name FROM researchers WHERE id = intauth.authorid);
						_auth.authorid := intauth.authorid;
						_auth.main := intauth.main;
						_auths := _auths || ARRAY[_auth];
					END IF;

					IF j = 1 THEN -- set main doc author
						doc.mainauthor := _auth.fullname;
					END IF;
				END LOOP; -- authors
			END IF;
			ret := ret || jsonb_insert(
			jsonb_insert(
				jsonb_insert(
					jsonb_insert(
						to_json(doc)::JSONB, '{extauthors}', COALESCE(to_json(auths)::JSONB, '[]'::JSONB)
					), '{intauthors}', COALESCE(to_json(intauths)::JSONB, '[]'::JSONB)
				), '{authors}', COALESCE(to_json(_auths)::JSONB, '[]'::JSONB)
			), '{doctype}', COALESCE(to_json((SELECT description FROM doctypes WHERE id = doc.doctypeid))::JSONB, NULL::JSONB)
			)::JSON::TEXT || ',';
		END LOOP; -- documents;
	-- ELSE
		-- RAISE NOTICE 'No modsCollection/mods found!';
	END IF;
	ret := ret || ']';
	IF ret = '[]' THEN
		ret := NULL;
	END IF;
	RETURN REPLACE(REGEXP_REPLACE(ret, ',]$', ']'), '": []', '": null');
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION public.mods2doc(text, integer)
  OWNER TO appdb;

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 21, 2, E'Added function to parse Metadata Object Description Schema (MODS) XML and populate app documents'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=21 AND revision=2);

COMMIT;
