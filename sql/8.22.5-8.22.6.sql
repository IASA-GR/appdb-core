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
Previous version: 8.22.5
New version: 8.22.6
Author: wvkarag@kadath.priv.iasa.gr
*/

START TRANSACTION;

CREATE OR REPLACE FUNCTION public.validate_app_cname(
    text,
    integer DEFAULT NULL::integer)
  RETURNS text AS
$BODY$
	SELECT
		'{' ||
			'"value": ' || to_json(n.value) || ', ' ||
			'"metatype": ' || to_json(a.metatype) ||
		'}'
	FROM app_cnames n
	INNER JOIN applications a ON a.id = n.appid
	WHERE
		(NOT a.deleted) AND
		(value = normalize_cname($1)) AND
		(($2 IS NULL) OR ((NOT $2 IS NULL) AND ($2 <> n.appid)))
$BODY$
  LANGUAGE sql STABLE
  COST 100;
ALTER FUNCTION public.validate_app_cname(text, integer)
  OWNER TO appdb;

CREATE OR REPLACE FUNCTION public.validate_app_name(
    text,
    integer DEFAULT NULL::integer)
  RETURNS text AS
$BODY$
DECLARE
        p TEXT;
        err TEXT;
        reason TEXT;
        exids INT[];
        exnames TEXT[];
	exmetatypes INT[];
BEGIN
        -- check min length
        IF (LENGTH($1) < 3) OR (LENGTH($1) > 50) THEN
                err := 'Invalid length';
		RETURN '{"valid": false, "error": ' || to_json(err) || ', "reason": {"min": 3, "max": 50}}';
        END IF;

        -- check validity
        IF NOT $1 ~ '^[A-Za-z0-9 *.+,&!#@=_^(){}\[\]-]+$' THEN
                err := 'Invalid character';
                RETURN '{"valid": false, "error": ' || to_json(err) || ', "reason": ' || to_json('*.+,&!#@=_^(){}[]-') || '}';
        END IF;

        -- check similarity
        SELECT array_agg(id ORDER BY id), array_agg(name ORDER BY id), array_agg(metatype ORDER BY id) FROM app_name_available($1) INTO exids, exnames, exmetatypes;
        IF ARRAY_LENGTH(exids, 1) > 0 THEN
                IF ($2 IS NULL) OR (NOT $2 = ANY(exids)) THEN
                        err := 'Invalid name';
                        RETURN '{"valid": false, "error": ' || to_json(err) || ', "reason": {"ids":' || to_json(exids) || ', "names": ' || to_json(exnames) || ', "metatypes": ' || to_json(exmetatypes) || '}}';
                END IF;
        END IF;

        IF EXISTS (SELECT 1 FROM applications WHERE (name ILIKE '%' || $1 || '%') AND (NOT deleted) AND (($2 IS NULL) OR ((NOT $2 IS NULL) AND (id <> $2)))) THEN
                RETURN '{"valid": true, "warning": true}';
        END IF;

	SELECT validate_app_cname($1, $2) INTO p;
        IF NOT p IS NULL THEN
                err := 'Invalid cname';
                RETURN '{"valid": false, "error": ' || to_json(err) || ', "reason": ' || to_json(p) || '}';
        END IF;

        RETURN '{"valid": true}';
END;
$BODY$
  LANGUAGE plpgsql STABLE
  COST 100;
ALTER FUNCTION public.validate_app_name(text, integer)
  OWNER TO appdb;

CREATE OR REPLACE FUNCTION public.consume_app(xml_in text, http_method integer, userid integer) RETURNS text AS $BODY$
DECLARE
	i INT;
	rec RECORD;

	xtmp XML[];
	xtmp2 XML[];
	x XML;
	xapp XML;
	_appid INT;
	_appguid UUID;
	userguid uuid;
	jtmp JSON;
	_metatype INT;

	_intauths INT[];
	_extauths TEXT[];

	RM_POST CONSTANT INT := 2;
	RM_PUT CONSTANT INT := 4;
	RM_DELETE CONSTANT INT := 8;

	RE_OK CONSTANT INT := 0;
	RE_ACCESS_DENIED CONSTANT INT := 1;
	RE_ITEM_NOT_FOUND CONSTANT INT := 2;
	RE_INVALID_REPRESENTATION CONSTANT INT := 3;
	RE_INVALID_METHOD CONSTANT INT := 4;
	RE_INVALID_RESOURCE CONSTANT INT := 5;
	RE_BACKEND_ERROR CONSTANT INT := 6;
	RE_INVALID_OPERATION CONSTANT INT := 7;
BEGIN
	IF NOT $3 IS NULL THEN
		userguid := (SELECT guid FROM researchers WHERE id = $3);
	END IF;
	IF $2 NOT IN (RM_PUT, RM_POST, RM_DELETE) THEN
		RAISE EXCEPTION 'APPDB_REST_API_ERROR %', RE_INVALID_METHOD;
	END IF;

	BEGIN
		xtmp := xpath('//application:application', REPLACE($1, 'xmlns:appdb:', 'xmlns:appdb=')::XML, appdb_xpathns());
	EXCEPTION
		WHEN OTHERS THEN
		RAISE EXCEPTION 'APPDB_REST_API_ERROR %', RE_INVALID_REPRESENTATION;
	END;
	IF ARRAY_LENGTH(xtmp, 1) = 0 THEN
		RAISE NOTICE 'EXCEPTION1';
		RAISE EXCEPTION 'APPDB_REST_API_ERROR %', RE_INVALID_REPRESENTATION;
	END IF;

	xapp := xtmp[1];

	IF xpath_exists('./@metatype', xapp, appdb_xpathns()) THEN
		_metatype := COALESCE((((xpath('./@metatype', xapp, appdb_xpathns()))[1])::TEXT)::INT, 0);
	END IF;

	IF ($2 = RM_PUT AND ((NOT xpath_exists('./discipline:discipline', xapp, appdb_xpathns())) OR (NOT xpath_exists('./application:category', xapp, appdb_xpathns())))) THEN
		RAISE EXCEPTION 'APPDB_REST_API_ERROR %', RE_INVALID_REPRESENTATION;
	END IF;

	IF $2 = RM_POST THEN
		x := (xpath('./@id', xapp, appdb_xpathns()))[1];
		IF (x IS NULL) OR NOT (x::TEXT ~ '^[0-9]+$') THEN
			RAISE EXCEPTION 'APPDB_REST_API_ERROR %', RE_INVALID_REPRESENTATION;
		END IF;
		_appid := (x::TEXT)::INT;
		IF xpath_exists('./application:name/text()', xapp, appdb_xpathns()) THEN
			jtmp := validate_app_name(REGEXP_REPLACE(
				unescapexml(((xpath('./application:name/text()', xapp, appdb_xpathns()))[1])::TEXT),
				'-DELETED-.{8}-.{4}-.{4}-.{4}-.{12}',
				''
			), _appid)::JSON;
			IF NOT (jtmp->>'valid')::BOOLEAN THEN
				RAISE EXCEPTION 'APPDB_REST_API_ERROR %, %', RE_BACKEND_ERROR, jtmp->>'reason';
			END IF;
		END IF;
		UPDATE applications a
		SET
			name = 	CASE WHEN EXISTS (SELECT 1 FROM permissions WHERE actor = userguid AND actionid = 5 AND ((object IS NULL) OR object = a.guid)) THEN
				COALESCE(unescapexml(((xpath('./application:name/text()', xapp, appdb_xpathns()))[1])::TEXT), a.name)
			ELSE
				a.name
			END ,
			description = CASE WHEN EXISTS (SELECT 1 FROM permissions WHERE actor = userguid AND actionid = 6 AND ((object IS NULL) OR object = a.guid)) THEN
				COALESCE(unescapexml(((xpath('./application:description/text()', xapp, appdb_xpathns()))[1])::TEXT), a.description)
			ELSE
				a.description
			END,
			abstract = CASE WHEN EXISTS (SELECT 1 FROM permissions WHERE actor = userguid AND actionid = 7 AND ((object IS NULL) OR object = a.guid)) THEN
				COALESCE(unescapexml(((xpath('./application:abstract/text()', xapp, appdb_xpathns()))[1])::TEXT), a.abstract)
			ELSE
				a.abstract
			END,
			statusid = CASE WHEN EXISTS (SELECT 1 FROM permissions WHERE actor = userguid AND actionid = 9 AND ((object IS NULL) OR object = a.guid)) THEN
				COALESCE((((xpath('./application:status/@id', xapp, appdb_xpathns()))[1])::TEXT)::INT, a.statusid)
			ELSE
				a.statusid
			END,
			tool = COALESCE((((xpath('./@tool', xapp, appdb_xpathns()))[1])::TEXT)::BOOLEAN, a.tool),
			lastupdated = NOW(),
			tagpolicy = CASE WHEN $3 IN (a.addedby, a."owner") OR EXISTS (SELECT 1 FROM actor_group_members WHERE actorid = userguid AND groupid IN (-1, -2)) THEN
				COALESCE((((xpath('./@tagPolicy', xapp, appdb_xpathns()))[1])::TEXT)::INT, a.tagpolicy)
			ELSE
				a.tagpolicy
			END,
			"owner" = CASE WHEN EXISTS (SELECT 1 FROM permissions WHERE actor = userguid AND actionid = 23 AND ((object IS NULL) OR object = a.guid)) THEN
				COALESCE((((xpath('./application:owner/@id', xapp, appdb_xpathns()))[1])::TEXT)::INT, a."owner")
			ELSE
				a."owner"
			END,
			metatype = CASE WHEN _metatype IN (0,1,2) THEN
				_metatype
			ELSE
				a.metatype
			END
		WHERE id = _appid;
	ELSIF $2 = RM_PUT THEN
		IF EXISTS (SELECT 1 FROM permissions WHERE actor = userguid AND actionid = 3) THEN  -- Insert Software permission
			jtmp := validate_app_name(REGEXP_REPLACE(
				unescapexml(((xpath('./application:name/text()', xapp, appdb_xpathns()))[1])::TEXT),
				'-DELETED-.{8}-.{4}-.{4}-.{4}-.{12}',
				''
			))::JSON;
			IF NOT (jtmp->>'valid')::BOOLEAN THEN
				RAISE EXCEPTION 'APPDB_REST_API_ERROR %, %', RE_BACKEND_ERROR, jtmp->>'reason';
			END IF;
			INSERT INTO applications (name, description, abstract, statusid, dateadded, addedby, tool, tagpolicy, metatype, "owner", cname)
			VALUES (
				unescapexml(((xpath('./application:name/text()', xapp, appdb_xpathns()))[1])::TEXT),
				unescapexml(((xpath('./application:description/text()', xapp, appdb_xpathns()))[1])::TEXT),
				unescapexml(((xpath('./application:abstract/text()', xapp, appdb_xpathns()))[1])::TEXT),
				COALESCE((((xpath('./application:status/@id', xapp, appdb_xpathns()))[1])::TEXT)::INT, 6),
				NOW(),
				$3,
				COALESCE((((xpath('./@tool', xapp, appdb_xpathns()))[1])::TEXT)::BOOLEAN, FALSE),
				COALESCE((((xpath('./@tagPolicy', xapp, appdb_xpathns()))[1])::TEXT)::INT, 2),
				CASE WHEN _metatype IN (0,1,2) THEN _metatype ELSE 0 END,
				COALESCE((((xpath('./application:owner/@id', xapp, appdb_xpathns()))[1])::TEXT)::INT, $3),
				unescapexml(((xpath('./@cname', xapp, appdb_xpathns()))[1])::TEXT)
			) RETURNING id INTO _appid;
		ELSE
			RAISE EXCEPTION 'APPDB_REST_API_ERROR %', RE_ACCESS_DENIED;
		END IF;
	END IF;

	_appguid := (SELECT guid FROM applications WHERE id = _appid);

	IF can_mod_app_tags(_appid, $3) THEN
		IF xpath_exists('./application:tag[@xsi:nil="true"]', xapp, appdb_xpathns()) THEN
			DELETE FROM __app_tags WHERE appid = _appid;
		ELSIF xpath_exists('./application:tag', xapp, appdb_xpathns()) THEN
			xtmp := xpath('./application:tag/text()', xapp, appdb_xpathns());

			DELETE FROM __app_tags
			WHERE (appid = _appid) AND (NOT tag = ANY(unescapexml(xtmp::TEXT[])));

			INSERT INTO __app_tags (appid, researcherid, tag)
			SELECT _appid, $3, xtag
			FROM UNNEST(unescapexml(xtmp::TEXT[])) AS xtag
			WHERE (NOT xtag IS NULL) AND (NOT EXISTS (SELECT 1 FROM app_tags WHERE (appid = _appid) AND (xtag = tag)));
		END IF;
	END IF;

	IF EXISTS (SELECT 1 FROM permissions WHERE actor = userguid AND actionid = 26 AND ((object IS NULL) OR object = _appguid)) THEN
		IF xpath_exists('./application:category', xapp, appdb_xpathns()) THEN
			xtmp := xpath('./application:category/@id', xapp, appdb_xpathns());

			DELETE FROM appcategories
			WHERE (appid = _appid) AND (NOT categoryid = ANY((xtmp::TEXT[])::INT[]));

			-- Must set primary category before any other, or a trigger will try to set the first existing category as primary
			-- and a check condition might misfire
			x := (xpath('./application:category[@primary="true"]/@id', xapp, appdb_xpathns()))[1];
			IF NOT x IS NULL THEN
				IF EXISTS (SELECT 1 FROM appcategories WHERE (appid = _appid) AND (categoryid = (x::TEXT)::INT)) THEN
					UPDATE appcategories SET isprimary = TRUE WHERE appid = _appid AND categoryid = (x::TEXT)::INT;
				ELSE
					INSERT INTO appcategories (appid, categoryid, isprimary)
					VALUES (_appid, (x::TEXT)::INT, TRUE);
				END IF;
			END IF;

			INSERT INTO appcategories (appid, categoryid)
			SELECT _appid, xcat::INT
			FROM UNNEST(xtmp::TEXT[]) AS xcat
			WHERE (NOT xcat IS NULL) AND (NOT EXISTS (SELECT 1 FROM appcategories WHERE (appid = _appid) AND (categoryid = xcat::INT)));
		END IF;
	END IF;

	IF EXISTS (SELECT 1 FROM permissions WHERE actor = userguid AND actionid = 10 AND ((object IS NULL) OR object = _appguid)) THEN
		IF xpath_exists('./discipline:discipline', xapp, appdb_xpathns()) THEN
			xtmp := xpath('./discipline:discipline/@id', xapp, appdb_xpathns());

			DELETE FROM appdisciplines
			WHERE (appid = _appid) AND (NOT disciplineid = ANY((xtmp::TEXT[])::INT[]));

			INSERT INTO appdisciplines (appid, disciplineid)
			SELECT _appid, xdisc::INT
			FROM UNNEST(xtmp::TEXT[]) AS xdisc
			WHERE (NOT xdisc IS NULL) AND (NOT EXISTS (SELECT 1 FROM appdisciplines WHERE (appid = _appid) AND (disciplineid = xdisc::INT)));
		END IF;
	END IF;

	IF EXISTS (SELECT 1 FROM permissions WHERE actor = userguid AND actionid = 31 AND ((object IS NULL) OR object = _appguid)) THEN
		IF xpath_exists('./application:language[@xsi:nil="true"]', xapp, appdb_xpathns()) THEN
			DELETE FROM appproglangs WHERE (appid = _appid);
		ELSIF xpath_exists('./application:language', xapp, appdb_xpathns()) THEN
			xtmp := xpath('./application:language/@id', xapp, appdb_xpathns());

			DELETE FROM appproglangs
			WHERE (appid = _appid) AND (NOT proglangid = ANY((xtmp::TEXT[])::INT[]));

			INSERT INTO appproglangs (appid, proglangid)
			SELECT _appid, xlang::INT
			FROM UNNEST(xtmp::TEXT[]) AS xlang
			WHERE (NOT xlang IS NULL) AND (NOT EXISTS (SELECT 1 FROM appproglangs WHERE (appid = _appid) AND (proglangid = xlang::INT)));
		END IF;
	END IF;

	IF EXISTS (SELECT 1 FROM permissions WHERE actor = userguid AND actionid = 33 AND ((object IS NULL) OR object = _appguid)) THEN
		IF xpath_exists('./application:license[@xsi:nil="true"]', xapp, appdb_xpathns()) THEN
			DELETE FROM app_licenses WHERE (appid = _appid) ;
		ELSIF xpath_exists('./application:license', xapp, appdb_xpathns()) THEN
			xtmp := xpath('./application:license/@id', xapp, appdb_xpathns());

			DELETE FROM app_licenses
			WHERE (appid = _appid) AND (NOT licenseid = ANY((xtmp::TEXT[])::INT[]));

			INSERT INTO app_licenses (appid, licenseid)
			SELECT _appid, xlic::INT
			FROM UNNEST(xtmp::TEXT[]) AS xlic
			WHERE (NOT xlic IS NULL) AND (NOT EXISTS (SELECT 1 FROM app_licenses WHERE (appid = _appid) AND (licenseid = xlic::INT)));
		END IF;
	END IF;

	IF EXISTS (SELECT 1 FROM permissions WHERE actor = userguid AND actionid = 12 AND ((object IS NULL) OR object = _appguid)) THEN
		IF xpath_exists('./regional:country[@xsi:nil="true"]', xapp, appdb_xpathns()) THEN
			DELETE FROM appmanualcountries WHERE (appid = _appid);
		ELSIF xpath_exists('./regional:country', xapp, appdb_xpathns()) THEN
			xtmp := xpath('./regional:country/@id', xapp, appdb_xpathns());

			DELETE FROM appmanualcountries
			WHERE (appid = _appid) AND (NOT countryid = ANY((xtmp::TEXT[])::INT[]));

			INSERT INTO appmanualcountries (appid, countryid)
			SELECT _appid, xcountry::INT
			FROM UNNEST(xtmp::TEXT[]) AS xcountry
			WHERE (NOT xcountry IS NULL) AND (NOT EXISTS (SELECT 1 FROM appmanualcountries WHERE (appid = _appid) AND (countryid = xcountry::INT)));
		END IF;
	END IF;

	IF EXISTS (SELECT 1 FROM permissions WHERE actor = userguid AND actionid = 14 AND ((object IS NULL) OR object = _appguid)) THEN
		IF xpath_exists('./application:url[@xsi:nil="true"]', xapp, appdb_xpathns()) THEN
			DELETE FROM app_urls WHERE (appid = _appid);
		ELSIF xpath_exists('./application:url', xapp, appdb_xpathns()) THEN
			xtmp := xpath('./application:url', xapp, appdb_xpathns());

			WITH xurls AS (
				SELECT
					unescapexml(((xpath('./@type', xu, appdb_xpathns()))[1])::TEXT) AS description,
					unescapexml(((xpath('./@title', xu, appdb_xpathns()))[1])::TEXT) AS title,
					unescapexml(((xpath('./text()', xu, appdb_xpathns()))[1])::TEXT) AS url
				FROM UNNEST(xtmp) AS xu
			)
			DELETE FROM app_urls au
			WHERE (au.appid = _appid) AND (NOT EXISTS (SELECT 1 FROM xurls WHERE NOT ((au.title, au.description, au.url) IS DISTINCT FROM (xurls.title, xurls.description, xurls.url))));

			WITH xurls AS (
				SELECT
					unescapexml(((xpath('./@type', xu, appdb_xpathns()))[1])::TEXT) AS description,
					unescapexml(((xpath('./@title', xu, appdb_xpathns()))[1])::TEXT) AS title,
					unescapexml(((xpath('./text()', xu, appdb_xpathns()))[1])::TEXT) AS url
				FROM UNNEST(xtmp) AS xu
			)
			INSERT INTO app_urls (appid, title, description, url)
			SELECT _appid, xurls.title, xurls.description, xurls.url
			FROM xurls
			WHERE (NOT xurls.url IS NULL) AND (NOT EXISTS (SELECT 1 FROM app_urls au WHERE (au.appid = _appid) AND NOT ((au.title, au.description, au.url) IS DISTINCT FROM (xurls.title, xurls. description, xurls.url))));
		END IF;
	END IF;

	-- KEEP IN PHP: logo, relations (?)

	IF EXISTS (SELECT 1 FROM permissions WHERE actor = userguid AND actionid = 13 AND ((object IS NULL) OR object = _appguid)) THEN
		IF xpath_exists('./vo:vo[@xsi:nil="true"]', xapp, appdb_xpathns()) THEN
			DELETE FROM __app_vos WHERE (appid = _appid);
		ELSIF xpath_exists('./vo:vo', xapp, appdb_xpathns()) THEN
			xtmp := xpath('./vo:vo/@id', xapp, appdb_xpathns());

			DELETE FROM __app_vos
			WHERE (appid = _appid) AND (NOT void = ANY((xtmp::TEXT[])::INT[]));

			INSERT INTO __app_vos (appid, void)
			SELECT _appid, xvo::INT
			FROM UNNEST(xtmp::TEXT[]) AS xvo
			WHERE (NOT xvo IS NULL) AND (NOT EXISTS (SELECT 1 FROM __app_vos WHERE (appid = _appid) AND (void = xvo::INT)));
		END IF;
	END IF;

	IF EXISTS (SELECT 1 FROM permissions WHERE actor = userguid AND actionid = 20 AND ((object IS NULL) OR object = _appguid)) THEN
		IF xpath_exists('./middleware:middleware[@xsi:nil="true"]', xapp, appdb_xpathns()) THEN
			DELETE FROM app_middlewares WHERE (appid = _appid);
		ELSIF xpath_exists('./middleware:middleware', xapp, appdb_xpathns()) THEN
			xtmp := xpath('./middleware:middleware/@id', xapp, appdb_xpathns());

			DELETE FROM app_middlewares
			WHERE (appid = _appid) AND (NOT middlewareid = ANY((xtmp::TEXT[])::INT[])) AND (NOT middlewareid = 5);

			INSERT INTO app_middlewares (appid, middlewareid)
			SELECT _appid, xmw::INT
			FROM UNNEST(xtmp::TEXT[]) AS xmw
			WHERE (NOT xmw IS NULL) AND (xmw::INT <> 5) AND (NOT EXISTS (SELECT 1 FROM app_middlewares WHERE (appid = _appid) AND (middlewareid = xmw::INT)));

			-- handle "custom" middlewares (id=5) seperately...
			xtmp := xpath('./middleware:middleware[@id="5"]', xapp, appdb_xpathns());
			IF ARRAY_LENGTH(xtmp, 1) > 0 THEN
				FOR i IN 1..ARRAY_LENGTH(xtmp, 1) LOOP
					DELETE FROM app_middlewares WHERE (appid = _appid) AND (middlewareid = 5) AND
					NOT EXISTS (
						SELECT 1
						FROM app_middlewares
						WHERE 	(appid = _appid) AND
							(middlewareid = 5) AND
							("comment" = unescapexml(((xpath('./text()', xtmp[i], appdb_xpathns()))[1])::TEXT)) AND
							(link = unescapexml(((xpath('./@link', xtmp[i], appdb_xpathns()))[1])::TEXT))
					);
				END LOOP;
				FOR i IN 1..ARRAY_LENGTH(xtmp, 1) LOOP
					INSERT INTO app_middlewares (appid, middlewareid, "comment", link)
					VALUES (
						_appid,
						5,
						unescapexml(((xpath('./@comment', xtmp[i], appdb_xpathns()))[1])::TEXT),
						unescapexml(((xpath('./@link', xtmp[i], appdb_xpathns()))[1])::TEXT)
					);
				END LOOP;
			END IF;
		END IF;
	END IF;

	IF xpath_exists('./application:contact', xapp, appdb_xpathns()) THEN
		xtmp := xpath('./application:contact/@id', xapp, appdb_xpathns());

		-- if can disassociate
		IF EXISTS (SELECT 1 FROM permissions WHERE actor = userguid AND actionid = 17 AND ((object IS NULL) OR object = _appguid)) THEN
			DELETE FROM appcontact_vos WHERE (appid = _appid) AND NOT (researcherid = ANY((xtmp::TEXT[])::INT[]));
			DELETE FROM appcontact_middlewares WHERE (appid = _appid) AND NOT (researcherid = ANY((xtmp::TEXT[])::INT[]));
			DELETE FROM appcontact_otheritems WHERE (appid = _appid) AND NOT (researcherid = ANY((xtmp::TEXT[])::INT[]));

			DELETE FROM researchers_apps WHERE (appid = _appid) AND NOT (researcherid = ANY((xtmp::TEXT[])::INT[])); -- RETURNING researcherid INTO i;
			-- RAISE NOTICE 'del1: %', i;
		END IF;

		-- if can associate
		IF EXISTS (SELECT 1 FROM permissions WHERE actor = userguid AND actionid = 16 AND ((object IS NULL) OR object = _appguid)) THEN
			INSERT INTO researchers_apps (researcherid, appid)
			SELECT
				_appid,
				xres
			FROM UNNEST((xtmp::TEXT[])::INT[]) AS xres
			WHERE (NOT xres IS NULL) AND NOT EXISTS (SELECT 1 FROM researchers_apps WHERE appid = _appid AND researcherid = ANY((xtmp::TEXT[])::INT[]));

			IF xpath_exists('./application:contact/application:contactItem', xapp, appdb_xpathns()) THEN
				IF ARRAY_LENGTH(xtmp, 1) > 0 THEN
					FOR i IN 1..ARRAY_LENGTH(xtmp, 1) LOOP
						IF xpath_exists('./application:contact[@id="' || xtmp[i]::TEXT || '"]/application:contactItem[@xsi:nil="true"]', xapp, appdb_xpathns()) THEN
							DELETE FROM appcontact_vos WHERE (appid = _appid) AND (researcherid = (xtmp[i]::TEXT)::INT); -- RETURNING * INTO rec; RAISE NOTICE '%', rec;
							DELETE FROM appcontact_middlewares WHERE (appid = _appid) AND (researcherid = (xtmp[i]::TEXT)::INT); -- RETURNING * INTO rec; RAISE NOTICE '%', rec;
							DELETE FROM appcontact_otheritems WHERE (appid = _appid) AND (researcherid = (xtmp[i]::TEXT)::INT); -- RETURNING * INTO rec; RAISE NOTICE '%', rec;
						ELSE
							xtmp2 := xpath('./application:contact[@id="' || xtmp[i]::TEXT || '"]/application:contactItem', xapp, appdb_xpathns());

							WITH xcon AS (
								SELECT
									unescapexml((xpath('./@type', xt, appdb_xpathns()))[1]::TEXT) AS "type",
									unescapexml((xpath('./@note', xt, appdb_xpathns()))[1]::TEXT) AS "note",
									unescapexml((xpath('./@id', xt, appdb_xpathns()))[1]::TEXT) AS "itemid",
									unescapexml((xpath('./text()', xt, appdb_xpathns()))[1]::TEXT) AS "item"
								FROM UNNEST(xtmp2) AS xt
							) DELETE FROM appcontact_vos
								WHERE (appid = _appid) AND (researcherid = (xtmp[i]::TEXT)::INT) AND NOT EXISTS (
									SELECT 1 FROM xcon
									WHERE
										(xcon.type = 'vo') AND
										(xcon.itemid = void::TEXT) /*AND
										NOT (xcon.note IS DISTINCT FROM appcontact_vos.note)*/
								);
							-- RETURNING * INTO rec; RAISE NOTICE 'deleted: %', rec;
							WITH xcon AS (
								SELECT
									unescapexml((xpath('./@type', xt, appdb_xpathns()))[1]::TEXT) AS "type",
									unescapexml((xpath('./@note', xt, appdb_xpathns()))[1]::TEXT) AS "note",
									unescapexml((xpath('./@id', xt, appdb_xpathns()))[1]::TEXT) AS "itemid",
									unescapexml((xpath('./text()', xt, appdb_xpathns()))[1]::TEXT) AS "item"
								FROM UNNEST(xtmp2) AS xt
							) DELETE FROM appcontact_middlewares
								WHERE (appid = _appid) AND (researcherid = (xtmp[i]::TEXT)::INT) AND NOT EXISTS (
									SELECT 1 FROM xcon
									INNER JOIN app_middlewares ON app_middlewares.id::TEXT = xcon.itemid
									WHERE
										(xcon.type = 'middleware') AND
										(CASE xcon.itemid
											WHEN '5' THEN
												xcon.item = app_middlewares.comment
											ELSE
												xcon.itemid = app_middlewares.middlewareid::TEXT
										END) AND
										NOT (xcon.note IS DISTINCT FROM appcontact_middlewares.note)
								);
							WITH xcon AS (
								SELECT
									unescapexml((xpath('./@type', xt, appdb_xpathns()))[1]::TEXT) AS "type",
									unescapexml((xpath('./@note', xt, appdb_xpathns()))[1]::TEXT) AS "note",
									unescapexml((xpath('./@id', xt, appdb_xpathns()))[1]::TEXT) AS "itemid",
									unescapexml((xpath('./text()', xt, appdb_xpathns()))[1]::TEXT) AS "item"
								FROM UNNEST(xtmp2) AS xt
							) DELETE FROM appcontact_otheritems
								WHERE (appid = _appid) AND (researcherid = (xtmp[i]::TEXT)::INT) AND NOT EXISTS (
									SELECT 1 FROM xcon
									WHERE
										(xcon.type = 'other') AND
										(xcon.itemid = item) AND
										NOT (xcon.note IS DISTINCT FROM appcontact_otheritems.note)
								);

							WITH xcon AS (
								SELECT
									unescapexml((xpath('./@type', xt, appdb_xpathns()))[1]::TEXT) AS "type",
									unescapexml((xpath('./@note', xt, appdb_xpathns()))[1]::TEXT) AS "note",
									unescapexml((xpath('./@id', xt, appdb_xpathns()))[1]::TEXT) AS "itemid",
									unescapexml((xpath('./text()', xt, appdb_xpathns()))[1]::TEXT) AS "item"
								FROM UNNEST(xtmp2) AS xt
							) INSERT INTO appcontact_vos (appid, researcherid, void, note)
							SELECT
								_appid,
								(xtmp[i]::TEXT)::INT,
								xcon.itemid::INT,
								xcon.note
							FROM xcon
							WHERE (xcon.type = 'vo') AND NOT (xcon.itemid IS NULL) AND NOT EXISTS (
								SELECT 1 FROM appcontact_vos WHERE (appid = _appid) AND (researcherid = (xtmp[i]::TEXT)::INT) AND (void::TEXT = xcon.itemid) AND NOT (xcon.note IS DISTINCT FROM appcontact_vos.note)
							);
							WITH xcon AS (
								SELECT
									unescapexml((xpath('./@type', xt, appdb_xpathns()))[1]::TEXT) AS "type",
									unescapexml((xpath('./@note', xt, appdb_xpathns()))[1]::TEXT) AS "note",
									unescapexml((xpath('./@id', xt, appdb_xpathns()))[1]::TEXT) AS "itemid",
									unescapexml((xpath('./text()', xt, appdb_xpathns()))[1]::TEXT) AS "item"
								FROM UNNEST(xtmp2) AS xt
							) INSERT INTO appcontact_middlewares (appid, researcherid, appmiddlewareid, note)
							SELECT __appid, __researcherid, __appmiddlewareid, __note FROM (
								SELECT
									_appid AS __appid,
									(xtmp[i]::TEXT)::INT AS __researcherid,
									(SELECT id FROM app_middlewares WHERE appid = _appid AND (
										CASE xcon.itemid -- tata
										WHEN '5' THEN
											(app_middlewares.middlewareid = 5) AND (app_middlewares.comment = xcon.item)
										ELSE
											app_middlewares.middlewareid::TEXT = xcon.itemid
										END)
									) AS __appmiddlewareid,
									xcon.note AS __note
								FROM xcon
								WHERE (xcon.type = 'middleware') AND NOT (xcon.itemid IS NULL) AND NOT EXISTS (
									SELECT 1 FROM appcontact_middlewares WHERE (appid = _appid) AND (researcherid = (xtmp[i]::TEXT)::INT) AND (
										CASE xcon.itemid -- lala
										WHEN '5' THEN
											appmiddlewareid = (SELECT id FROM app_middlewares WHERE (middlewareid = 5) AND ("comment" = xcon.item) AND (appid = _appid))
										ELSE
											appmiddlewareid = (SELECT id FROM app_middlewares WHERE (middlewareid::TEXT = xcon.itemid) AND (appid = _appid))
										END
									) AND NOT (xcon.note IS DISTINCT FROM appcontact_middlewares.note)
								)
							) AS t WHERE NOT __appmiddlewareid IS NULL;

							WITH xcon AS (
								SELECT
									unescapexml((xpath('./@type', xt, appdb_xpathns()))[1]::TEXT) AS "type",
									unescapexml((xpath('./@note', xt, appdb_xpathns()))[1]::TEXT) AS "note",
									unescapexml((xpath('./text()', xt, appdb_xpathns()))[1]::TEXT) AS "item"
								FROM UNNEST(xtmp2) AS xt
							) INSERT INTO appcontact_otheritems (appid, researcherid, item, note)
							SELECT
								_appid,
								(xtmp[i]::TEXT)::INT,
								xcon.item,
								xcon.note
							FROM xcon
							WHERE (xcon.type = 'other') AND NOT (xcon.item IS NULL) AND NOT EXISTS (
								SELECT 1 FROM appcontact_otheritems WHERE (appid = _appid) AND (researcherid = (xtmp[i]::TEXT)::INT) AND (item = xcon.item) AND NOT (xcon.note IS DISTINCT FROM appcontact_otheritems.note)
							);
						END IF;

					END LOOP;
				END IF;
			END IF;
		END IF;
	END IF; -- END contacts

	IF EXISTS (SELECT 1 FROM permissions WHERE actor = userguid AND actionid = 15 AND ((object IS NULL) OR object = _appguid)) THEN
		IF xpath_exists('./publication:publication[@xsi:nil="true"]', xapp, appdb_xpathns()) THEN
			DELETE FROM appdocuments WHERE appid = _appid;
		ELSIF xpath_exists('./publication:publication', xapp, appdb_xpathns()) THEN
			DELETE FROM appdocuments
			WHERE (appid = _appid) AND NOT (id = ANY(
				((xpath('./publication:publication/@id', xapp, appdb_xpathns()))::TEXT[])::INT[]
			));

			xtmp := xpath('./publication:publication', xapp, appdb_xpathns());

			PERFORM consume_doc(xdoc, _appid)
			FROM UNNEST(xtmp::TEXT[]) AS xdoc;
		END IF;
	END IF;

	RETURN '{"id": ' || _appid::TEXT || ', "guid": "' || (SELECT guid::TEXT FROM applications WHERE id = _appid) || '"}';
	--RETURN app_to_xml_ext(_appid);
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION public.consume_app(text, integer, integer)
  OWNER TO appdb;

ALTER TABLE pidhandles DROP CONSTRAINT pidhandles_suffix_key;
ALTER TABLE pidhandles DROP CONSTRAINT pidhandles_url_key;
CREATE INDEX idx_pidhandles_url ON pidhandles(url);

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 22, 6, E'Fix regression bug disallowing registration of app entries with same name as an entry which has been marked as deleted'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=22 AND revision=6);

COMMIT;
