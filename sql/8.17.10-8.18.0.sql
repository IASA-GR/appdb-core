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
Previous version: 8.17.5
New version: 8.17.6
Author: nakos.al@iasa.gr
*/

START TRANSACTION;

CREATE OR REPLACE FUNCTION hasparent(mid INT, mpid INT, mtbl TEXT) 
RETURNS BOOLEAN AS
$$
DECLARE cur REFCURSOR;
DECLARE p INT;
DECLARE ret BOOLEAN;
BEGIN
	ret := FALSE;
	OPEN cur FOR EXECUTE FORMAT('SELECT DISTINCT parentid FROM %I WHERE id = $1', mtbl) USING mid;
	FETCH NEXT FROM cur INTO p;
	WHILE NOT p IS NULL LOOP
		IF p = mpid THEN 
			ret := TRUE;
			EXIT;
		END IF;
		ret := ret OR hasparent(p, mpid, mtbl);
		IF ret THEN
			EXIT;
		END IF;
		FETCH NEXT FROM cur INTO p;
	END LOOP;
	CLOSE cur;
	RETURN ret;
END;
$$
LANGUAGE plpgsql;
ALTER FUNCTION hasparent(int, int, text) OWNER TO appdb;

DROP FUNCTION IF EXISTS oai_sw_setspecs;
CREATE OR REPLACE FUNCTION oai_sw_setspecs() RETURNS SETOF XML AS
$$
SELECT XMLELEMENT(name "set", XMLELEMENT(name "setName", 'Software'), XMLELEMENT(name "setSpec", 'sw'))
UNION ALL
SELECT
--	XMLAGG(
		XMLELEMENT(
			name "set",
			XMLELEMENT(
				name "setName",
				'Software / ' || REPLACE(name, ':', ' / ')
			), 
			XMLELEMENT(
				name "setSpec",
				'sw:' || REGEXP_REPLACE(LOWER(name), '[^a-zA-Z0-9:]{1,}', '.', 'g')
			)
		)
--	)
FROM 
	htree_text('categories','',1,':')
WHERE (
	NOT hasparent(id, (SELECT id FROM categories WHERE name = 'Virtual Appliances'), 'categories')
) AND (
	NOT hasparent(id, (SELECT id FROM categories WHERE name = 'Software Appliances'), 'categories')
)
$$ LANGUAGE sql STABLE;
ALTER FUNCTION oai_sw_setspecs() OWNER TO appdb;

DROP FUNCTION IF EXISTS oai_va_setspecs;
CREATE OR REPLACE FUNCTION oai_va_setspecs() RETURNS SETOF XML AS
$$
SELECT XMLELEMENT(name "set", XMLELEMENT(name "setName", 'Virtual Appliances'), XMLELEMENT(name "setSpec", 'va'))
UNION ALL
SELECT 
--	XMLAGG(
		XMLELEMENT(
			name "set",
			XMLELEMENT(
				name "setName",
				'Virtual Appliances / ' || REPLACE(name, ':', ' / ')
			), 
			XMLELEMENT(
				name "setSpec",
				'va:' || REGEXP_REPLACE(LOWER(name), '[^a-zA-Z0-9:]{1,}', '.', 'g')
			)
		)
--	)
FROM 
	htree_text('categories','',1,':')
WHERE 
	hasparent(id, (SELECT id FROM categories WHERE name = 'Virtual Appliances'), 'categories')
$$ LANGUAGE sql STABLE;
ALTER FUNCTION oai_va_setspecs() OWNER TO appdb;

DROP FUNCTION oai_setspecs;
CREATE OR REPLACE FUNCTION oai_setspecs() RETURNS SETOF XML AS
$$
SELECT oai_sw_setspecs()
UNION ALL
SELECT oai_va_setspecs()
$$ LANGUAGE SQL STABLE;
ALTER FUNCTION oai_setspecs() OWNER TO appdb;

ALTER TABLE cache.appxmlcache ADD COLUMN tstamp TIMESTAMP NOT NULL DEFAULT NOW();
ALTER TABLE cache.appxmlcache ADD COLUMN openairexml xml;
ALTER TABLE cache.appxmlcache ADD COLUMN oaidcxml xml;

INSERT INTO config (var, data) VALUES ('datacite_xslt', NULL);
INSERT INTO config (var, data) VALUES ('oaidc_xslt', NULL);
INSERT INTO config (var, data) VALUES ('oaidatacite_xslt', NULL);

UPDATE cache.appxmlcache AS c SET openairexml = a.openaire
FROM applications a
WHERE c.id = a.id;

CREATE OR REPLACE FUNCTION public.openaire2(applications)
 RETURNS xml
 LANGUAGE plpgsql
 VOLATILE
AS $function$
BEGIN
        IF ($1.deleted) OR ($1.moderated) THEN
                RETURN (E'<header status="deleted"><identifier>' || E'oai:appdb.egi.eu:' || $1.guid::TEXT || E'</identifier><datestamp>' || 
                        REGEXP_REPLACE(REPLACE(
                                COALESCE((SELECT tstamp::TIMESTAMPTZ AT TIME ZONE 'UTC' FROM cache.appxmlcache WHERE id = $1.id), (NOW() AT TIME ZONE 'UTC'))::TEXT,
                        ' ', 'T') || 'Z', '\.[0-9]*Z$', 'Z') || 
                        E'</datestamp>' || 
                        CASE $1.metatype WHEN 0 THEN '<setSpec>sw</setSpec>' WHEN 1 THEN '<setSpec>va</setSpec>' ELSE '' END ||
                        -- E'<setSpec>SET1</setSpec><setSpec>SET2</setSpec>' || 
                        E'</header>')::XML;
        ELSE
                        RETURN (
                                E'<header><identifier>' || E'oai:appdb.egi.eu:' || $1.guid::TEXT || E'</identifier><datestamp>' || 
                                REGEXP_REPLACE(
                                REPLACE(
                                        COALESCE((SELECT tstamp::TIMESTAMPTZ AT TIME ZONE 'UTC' FROM cache.appxmlcache WHERE id = $1.id), (NOW() AT TIME ZONE 'UTC'))::TEXT,
                                ' ', 'T') || 'Z', '\.[0-9]*Z$', 'Z') 
                                || 
                                E'</datestamp>' || 
                                CASE $1.metatype WHEN 0 THEN '<setSpec>sw</setSpec>' WHEN 1 THEN '<setSpec>va</setSpec>' ELSE '' END ||
                                E'</header>')::XML;
        END IF;
END;
$function$;
ALTER FUNCTION openaire2(applications) OWNER TO appdb;

CREATE OR REPLACE FUNCTION public.openaire(applications)
RETURNS xml
 LANGUAGE plpgsql
 VOLATILE
AS $function$
DECLARE x XML;
BEGIN
        IF EXISTS (SELECT 1 FROM cache.appxmlcache WHERE id = $1.id AND openairexml IS DISTINCT FROM NULL) THEN
                RETURN (SELECT openairexml FROM cache.appxmlcache WHERE id = $1.id);
        ELSE
                x := (($1::applications).__openaire)::XML;
                UPDATE cache.appxmlcache SET openairexml = x WHERE id = $1.id;
                RETURN x;
        END IF;
END;
$function$;
ALTER FUNCTION openaire(applications) OWNER TO appdb;

CREATE OR REPLACE FUNCTION public.__openaire(applications)
 RETURNS xml
 LANGUAGE plpgsql
 VOLATILE
AS $function$
DECLARE t1 double precision;
DECLARE t2 double precision;
DECLARE dt double precision;
DECLARE x TEXT;
BEGIN
        IF ($1.deleted) OR ($1.moderated) THEN
                RETURN (E'<record><header status="deleted"><identifier>' || E'oai:appdb.egi.eu:' || $1.guid::TEXT || E'</identifier><datestamp>' || 
                        REGEXP_REPLACE(REPLACE(
                                COALESCE((SELECT tstamp::TIMESTAMPTZ AT TIME ZONE 'UTC' FROM cache.appxmlcache WHERE id = $1.id), (NOW() AT TIME ZONE 'UTC'))::TEXT,
                        ' ', 'T') || 'Z', '\.[0-9]*Z$', 'Z') || 
                        E'</datestamp>' || 
                        CASE $1.metatype WHEN 0 THEN '<setSpec>sw</setSpec>' WHEN 1 THEN '<setSpec>va</setSpec>' ELSE '' END ||
                        E'</header></record>')::XML;
        ELSE
--                IF EXISTS (SELECT 1 FROM cache.appxmlcache WHERE id = $1.id AND openairexml IS DISTINCT FROM NULL) THEN
--                        -- RAISE NOTICE '% has openaire xml cached', $1.id;
--                        x := (SELECT openairexml::TEXT FROM cache.appxmlcache WHERE id = $1.id);
--                ELSE
                        -- RAISE NOTICE '% has no openaire xml cache', $1.id;
                        t1 := (SELECT EXTRACT(EPOCH FROM(clock_timestamp())));
                        x := (SELECT 
                                (
                                E'<record><header><identifier>' || E'oai:appdb.egi.eu:' || $1.guid::TEXT || E'</identifier><datestamp>' || 
                                REGEXP_REPLACE(
                                REPLACE(
                                        COALESCE((SELECT tstamp::TIMESTAMPTZ AT TIME ZONE 'UTC' FROM cache.appxmlcache WHERE id = $1.id), (NOW() AT TIME ZONE 'UTC'))::TEXT,
                                ' ', 'T') || 'Z', '\.[0-9]*Z$', 'Z') 
                                || 
                                E'</datestamp>' || 
                                CASE $1.metatype WHEN 0 THEN '<setSpec>sw</setSpec>' WHEN 1 THEN '<setSpec>va</setSpec>' ELSE '' END ||
                                E'</header><metadata><oai_datacite xmlns="http://schema.datacite.org/oai/oai-1.1/" xsi:schemaLocation="http://schema.datacite.org/oai/oai-1.1/ http://schema.datacite.org/oai/oai-1.1/oai.xsd"><schemaVersion>4</schemaVersion>' || 
                                E'<datacentreSymbol>EGI.APPDB</datacentreSymbol><payload>' ||
                                REGEXP_REPLACE(
                                        xsltproc(
                                                COALESCE((SELECT data FROM config WHERE var = 'oaidatacite_xslt'), '/var/www/html/appdb/application/configs/api/1.0/xslt/oai_datacite.xsl'),
                                                ('<appdb:appdb xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" ' || 
                                                E'xmlns:appdb="http://appdb.egi.eu/api/1.0/appdb" xmlns:application="http://appdb.egi.eu/api/1.0/application" xmlns:discipline="http://appdb.egi.eu/api/1.0/discipline" xmlns:category="http://appdb.egi.eu/api/1.0/category" xmlns:dissemination="http://appdb.egi.eu/api/1.0/dissemination" xmlns:filter="http://appdb.egi.eu/api/1.0/filter" xmlns:history="http://appdb.egi.eu/api/1.0/history" xmlns:logistics="http://appdb.egi.eu/api/1.0/logistics" xmlns:resource="http://appdb.egi.eu/api/1.0/resource" xmlns:middleware="http://appdb.egi.eu/api/1.0/middleware" xmlns:person="http://appdb.egi.eu/api/1.0/person" xmlns:permission="http://appdb.egi.eu/api/1.0/permission" xmlns:privilege="http://appdb.egi.eu/api/1.0/privilege" xmlns:publication="http://appdb.egi.eu/api/1.0/publication" xmlns:rating="http://appdb.egi.eu/api/1.0/rating" xmlns:ratingreport="http://appdb.egi.eu/api/1.0/ratingreport" xmlns:regional="http://appdb.egi.eu/api/1.0/regional" xmlns:user="http://appdb.egi.eu/api/1.0/user" xmlns:vo="http://appdb.egi.eu/api/1.0/vo" xmlns:virtualization="http://appdb.egi.eu/api/1.0/virtualization" xmlns:contextualization="http://appdb.egi.eu/api/1.0/contextualization" xmlns:license="http://appdb.egi.eu/api/1.0/license" xmlns:provider="http://appdb.egi.eu/api/1.0/provider" xmlns:provider_template="http://appdb.egi.eu/api/1.0/provider_template" xmlns:classification="http://appdb.egi.eu/api/1.0/classification" xmlns:site="http://appdb.egi.eu/api/1.0/site" xmlns:siteservice="http://appdb.egi.eu/api/1.0/site" xmlns:entity="http://appdb.egi.eu/api/1.0/entity" xmlns:organization="http://appdb.egi.eu/api/1.0/organization" xmlns:project="http://appdb.egi.eu/api/1.0/project" xmlns:dataset="http://appdb.egi.eu/api/1.0/dataset" ' || 
                                                E'datatype="application" type="entry" host="' || (SELECT data FROM config WHERE var = 'ui-host') || '" apihost="' || (SELECT data FROM config WHERE var = 'api-host') || 
                                                '" cacheState="0" permsState="' || (SELECT data FROM config WHERE var = 'permissions_cache_dirty') || '" requestedOn="###REQUESTED_ON###" deliveredOn="###DELIVERED_ON###" processingTime="###PROCESSING_TIME###" version="1.0">' ||
                                                app_to_xml_ext($1.id) || '</appdb:appdb>')::XML
                                        )::TEXT,
                                        E'<\\?xml version="[0-9]+\.*[0-9]*"( encoding="\w.+")*\\?>',
                                        '',
                                        'ig'
                                ) ||
                                E'</payload></oai_datacite></metadata></record>'
                                )
                        );
                        t2 := (SELECT EXTRACT(EPOCH FROM(clock_timestamp())));
                        dt := t2 - t1;
                        x := (REPLACE(REPLACE(REPLACE(x, '###REQUESTED_ON###', t1::TEXT), '###DELIVERED_ON###', t2::TEXT), '###PROCESSING_TIME###', dt::TEXT));
                        -- UPDATE cache.appxmlcache SET openairexml = x::XML WHERE id = $1.id;
--                END IF;
                RETURN x;
        END IF;
END;
$function$;
ALTER FUNCTION __openaire(applications) OWNER TO appdb;

CREATE OR REPLACE FUNCTION public.oaidc(applications)
 RETURNS xml
 LANGUAGE plpgsql
 VOLATILE
AS $function$
DECLARE x XML;
BEGIN
        IF EXISTS (SELECT 1 FROM cache.appxmlcache WHERE id = $1.id AND oaidcxml IS DISTINCT FROM NULL) THEN
                RETURN (SELECT oaidcxml FROM cache.appxmlcache WHERE id = $1.id);
        ELSE
                x := (($1::applications).__oaidc)::XML;
                UPDATE cache.appxmlcache SET oaidcxml = x WHERE id = $1.id;
                RETURN x;
        END IF;
END;
$function$;
ALTER FUNCTION oaidc(applications) OWNER TO appdb;

CREATE OR REPLACE FUNCTION public.__oaidc(applications)
 RETURNS xml
 LANGUAGE plpgsql
 VOLATILE
AS $function$
DECLARE t1 double precision;
DECLARE t2 double precision;
DECLARE dt double precision;
DECLARE x TEXT;
BEGIN
        IF ($1.deleted) OR ($1.moderated) THEN
                RETURN (E'<record><header status="deleted"><identifier>' || E'oai:appdb.egi.eu:' || $1.guid::TEXT || E'</identifier><datestamp>' || 
                        REGEXP_REPLACE(REPLACE(
                                COALESCE((SELECT tstamp::TIMESTAMPTZ AT TIME ZONE 'UTC' FROM cache.appxmlcache WHERE id = $1.id), (NOW() AT TIME ZONE 'UTC'))::TEXT,
                        ' ', 'T') || 'Z', '\.[0-9]*Z$', 'Z') || 
                        E'</datestamp>' || 
                        CASE $1.metatype WHEN 0 THEN '<setSpec>sw</setSpec>' WHEN 1 THEN '<setSpec>va</setSpec>' ELSE '' END ||
                        E'</header></record>')::XML;
        ELSE
--                IF EXISTS (SELECT 1 FROM cache.appxmlcache WHERE id = $1.id AND oaidcxml IS DISTINCT FROM NULL) THEN
--                        -- RAISE NOTICE '% has oaidc xml cached', $1.id;
--                        x := (SELECT oaidcxml::TEXT FROM cache.appxmlcache WHERE id = $1.id);
--                ELSE
                        -- RAISE NOTICE '% has no oaidc xml cache', $1.id;
                        t1 := (SELECT EXTRACT(EPOCH FROM(clock_timestamp())));
                        x := (SELECT 
                                (
                                E'<record><header><identifier>' || E'oai:appdb.egi.eu:' || $1.guid::TEXT || E'</identifier><datestamp>' || 
                                REGEXP_REPLACE(
                                REPLACE(
                                        COALESCE((SELECT tstamp::TIMESTAMPTZ AT TIME ZONE 'UTC' FROM cache.appxmlcache WHERE id = $1.id), (NOW() AT TIME ZONE 'UTC'))::TEXT,
                                ' ', 'T') || 'Z', '\.[0-9]*Z$', 'Z') 
                                || 
                                E'</datestamp>' || 
                                CASE $1.metatype WHEN 0 THEN '<setSpec>sw</setSpec>' WHEN 1 THEN '<setSpec>va</setSpec>' ELSE '' END ||
                                E'</header><metadata>' ||
                                REGEXP_REPLACE(
                                        xsltproc(
                                                COALESCE((SELECT data FROM config WHERE var = 'oaidc_xslt'), '/var/www/html/appdb/application/configs/api/1.0/xslt/oai_dc.xsl'),
                                                ('<appdb:appdb xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" ' || 
                                                E'xmlns:appdb="http://appdb.egi.eu/api/1.0/appdb" xmlns:application="http://appdb.egi.eu/api/1.0/application" xmlns:discipline="http://appdb.egi.eu/api/1.0/discipline" xmlns:category="http://appdb.egi.eu/api/1.0/category" xmlns:dissemination="http://appdb.egi.eu/api/1.0/dissemination" xmlns:filter="http://appdb.egi.eu/api/1.0/filter" xmlns:history="http://appdb.egi.eu/api/1.0/history" xmlns:logistics="http://appdb.egi.eu/api/1.0/logistics" xmlns:resource="http://appdb.egi.eu/api/1.0/resource" xmlns:middleware="http://appdb.egi.eu/api/1.0/middleware" xmlns:person="http://appdb.egi.eu/api/1.0/person" xmlns:permission="http://appdb.egi.eu/api/1.0/permission" xmlns:privilege="http://appdb.egi.eu/api/1.0/privilege" xmlns:publication="http://appdb.egi.eu/api/1.0/publication" xmlns:rating="http://appdb.egi.eu/api/1.0/rating" xmlns:ratingreport="http://appdb.egi.eu/api/1.0/ratingreport" xmlns:regional="http://appdb.egi.eu/api/1.0/regional" xmlns:user="http://appdb.egi.eu/api/1.0/user" xmlns:vo="http://appdb.egi.eu/api/1.0/vo" xmlns:virtualization="http://appdb.egi.eu/api/1.0/virtualization" xmlns:contextualization="http://appdb.egi.eu/api/1.0/contextualization" xmlns:license="http://appdb.egi.eu/api/1.0/license" xmlns:provider="http://appdb.egi.eu/api/1.0/provider" xmlns:provider_template="http://appdb.egi.eu/api/1.0/provider_template" xmlns:classification="http://appdb.egi.eu/api/1.0/classification" xmlns:site="http://appdb.egi.eu/api/1.0/site" xmlns:siteservice="http://appdb.egi.eu/api/1.0/site" xmlns:entity="http://appdb.egi.eu/api/1.0/entity" xmlns:organization="http://appdb.egi.eu/api/1.0/organization" xmlns:project="http://appdb.egi.eu/api/1.0/project" xmlns:dataset="http://appdb.egi.eu/api/1.0/dataset" ' || 
                                                E'datatype="application" type="entry" host="' || (SELECT data FROM config WHERE var = 'ui-host') || '" apihost="' || (SELECT data FROM config WHERE var = 'api-host') || 
                                                '" cacheState="0" permsState="' || (SELECT data FROM config WHERE var = 'permissions_cache_dirty') || '" requestedOn="###REQUESTED_ON###" deliveredOn="###DELIVERED_ON###" processingTime="###PROCESSING_TIME###" version="1.0">' ||
                                                app_to_xml_ext($1.id) || '</appdb:appdb>')::XML
                                        )::TEXT,
                                        E'<\\?xml version="[0-9]+\.*[0-9]*"( encoding="\w.+")*\\?>',
                                        '',
                                        'ig'
                                ) ||
                                E'</metadata></record>'
                                )
                        );
                        t2 := (SELECT EXTRACT(EPOCH FROM(clock_timestamp())));
                        dt := t2 - t1;
                        x := (REPLACE(REPLACE(REPLACE(x, '###REQUESTED_ON###', t1::TEXT), '###DELIVERED_ON###', t2::TEXT), '###PROCESSING_TIME###', dt::TEXT));
                        -- UPDATE cache.appxmlcache SET openairexml = x::XML WHERE id = $1.id;
--                END IF;
                RETURN x;
        END IF;
END;
$function$;
ALTER FUNCTION __oaidc(applications) OWNER TO appdb;

DROP TABLE IF EXISTS oai_app_cursors;
CREATE TABLE oai_app_cursors (
        id SERIAL NOT NULL PRIMARY KEY,
        token TEXT NOT NULL UNIQUE,
        createdon TIMESTAMP NOT NULL DEFAULT NOW(),
        lastusedon TIMESTAMP NOT NULL DEFAULT NOW(),
        appids INT[] NOT NULL,
        pos INT NOT NULL DEFAULT 0
);

CREATE INDEX idx_oai_app_cursors_token ON oai_app_cursors(token);
CREATE INDEX idx_oai_app_cursors_lastusedon ON oai_app_cursors(lastusedon);
CREATE INDEX idx_oai_app_cursors_appids ON oai_app_cursors USING GIN(appids);

-- DROP FUNCTION oai_app_cursor(timestamp, timestamp, text, boolean, text)
CREATE OR REPLACE FUNCTION oai_app_cursor(mfrom timestamp DEFAULT NULL, muntil timestamp DEFAULT  NULL, mtoken TEXT DEFAULT NULL, mabbrev BOOLEAN default FALSE, mtype TEXT DEFAULT NULL, mformat TEXT DEFAULT 'oai_datacite') RETURNS TEXT AS
$$
-- DECLARE head TEXT;
DECLARE body TEXT;
-- DECLARE foot TEXT;
DECLARE ofs int;
DECLARE ret TEXT;
DECLARE expdat TEXT;
DECLARE nrec TEXT;
DECLARE tokenarray TEXT[];
DECLARE mt INT; -- parsed metatype from mtype (i.e. setSpec)
DECLARE setspec TEXT[];
BEGIN
	-- parse setSpec
	IF NOT mtype IS NULL THEN
		setspec := STRING_TO_ARRAY(mtype, ':');
		IF setspec[1] = 'sw' THEN 
			mt = 0;
		ELSIF setspec[1] = 'va' THEN
			mt = 1;
		ELSE 
			mt = -1; --invalid
		END IF;
	ELSE
		mt := NULL;
	END IF;
	RAISE NOTICE 'setspec=%', mtype;
	RAISE NOTICE 'mt=%', mt;
	
        -- delete expired tokens
        DELETE FROM oai_app_cursors 
        WHERE NOW() - lastusedon > INTERVAL '1 hour';

        -- check if given token is valid
        IF NOT mtoken IS NULL THEN
                IF NOT EXISTS (SELECT 1 FROM oai_app_cursors WHERE token = mtoken) THEN
                        -- RAISE NOTICE 'token does not exist';
                        RETURN '{"error": "badResumptionToken"}';
                ELSE
                        -- RAISE NOTICE 'resuming token';
                END IF;
        END IF;

        -- create new token if none given
        IF mtoken IS NULL THEN
                -- RAISE NOTICE 'creating new token';
                mtoken := REPLACE(EXTRACT(EPOCH FROM NOW())::TEXT, '.', '') || ',' || 
                        REGEXP_REPLACE(REPLACE(
                                (CASE WHEN mfrom IS NULL THEN (SELECT TIMESTAMP WITH TIME ZONE 'epoch' + 0 * INTERVAL '1 second') AT TIME ZONE 'UTC' ELSE mfrom::TIMESTAMPTZ AT TIME ZONE 'UTC' END)::TEXT
                        , ' ', 'T') || 'Z', '\.[0-9]*Z$', 'Z')
                        || ',' || 
                        REGEXP_REPLACE(REPLACE(
                                (CASE WHEN muntil IS NULL THEN (SELECT TIMESTAMP WITH TIME ZONE 'epoch' + 999999999999 * INTERVAL '1 second') AT TIME ZONE 'UTC' ELSE muntil::TIMESTAMPTZ AT TIME ZONE 'UTC' END)::TEXT
                        , ' ', 'T') || 'Z', '\.[0-9]*Z$', 'Z')
                        || ',' || mabbrev::TEXT
                        || ',' || COALESCE(mtype, 'NULL')
                        || ',50,' || mformat;
                
                /* record app id list for resumption token idempotency */
                IF EXISTS (
                        SELECT 1 
                        FROM applications a
                        INNER JOIN cache.appxmlcache c ON c.id = a.id
                        WHERE (
                                c.tstamp::TIMESTAMPTZ BETWEEN
                                CASE WHEN mfrom IS NULL THEN (SELECT TIMESTAMP WITH TIME ZONE 'epoch' + 0 * INTERVAL '1 second') AT TIME ZONE 'UTC' ELSE mfrom::TIMESTAMPTZ AT TIME ZONE 'UTC' END
                                AND
                                CASE WHEN muntil IS NULL THEN (SELECT TIMESTAMP WITH TIME ZONE 'epoch' + 999999999999 * INTERVAL '1 second') AT TIME ZONE 'UTC' ELSE muntil::TIMESTAMPTZ AT TIME ZONE 'UTC' END
                        ) AND (
                                CASE WHEN mt IS NULL THEN 
					TRUE
				ELSE
					a.metatype = mt
                                END
                        ) AND (
				CASE WHEN mtype IS NULL THEN 
					TRUE
				ELSE (
					CASE WHEN EXISTS (SELECT 1 FROM (SELECT
						hasparent(
							ac.id, (
								SELECT id 
								FROM htree_text('categories','',1,':') 
								WHERE CASE mt WHEN 0 THEN 'sw' WHEN 1 THEN 'va' ELSE '' END || ':' || REGEXP_REPLACE(LOWER(name), '[^a-zA-Z0-9:]{1,}', '.', 'g') = mtype
							), 
							'categories'
						) AS hp
					FROM appcategories ac
					WHERE ac.appid = a.id
					) AS hpq WHERE hpq.hp) OR EXISTS (SELECT 1 FROM appcategories WHERE appid = a.id AND categoryid = (
						SELECT id 
						FROM htree_text('categories','',1,':') 
						WHERE CASE mt WHEN 0 THEN 'sw' WHEN 1 THEN 'va' ELSE '' END || ':' || REGEXP_REPLACE(LOWER(name), '[^a-zA-Z0-9:]{1,}', '.', 'g') = mtype
					)
					) OR (mtype = 'sw') OR (mtype = 'va') THEN TRUE ELSE FALSE END					
				) END
                        )
                ) THEN
                        INSERT INTO oai_app_cursors (token, appids) 
                        SELECT 
                                mtoken, 
                                array_agg(a.id)
                        FROM applications a
                        INNER JOIN cache.appxmlcache c ON c.id = a.id
                        WHERE (
                                c.tstamp::TIMESTAMPTZ BETWEEN
                                CASE WHEN mfrom IS NULL THEN (SELECT TIMESTAMP WITH TIME ZONE 'epoch' + 0 * INTERVAL '1 second') AT TIME ZONE 'UTC' ELSE mfrom::TIMESTAMPTZ AT TIME ZONE 'UTC' END
                                AND
                                CASE WHEN muntil IS NULL THEN (SELECT TIMESTAMP WITH TIME ZONE 'epoch' + 999999999999 * INTERVAL '1 second') AT TIME ZONE 'UTC' ELSE muntil::TIMESTAMPTZ AT TIME ZONE 'UTC' END
                        ) AND (
                                CASE WHEN mt IS NULL THEN 
					TRUE
				ELSE
					a.metatype = mt
                                END
                        ) AND (
				CASE WHEN mtype IS NULL THEN 
					TRUE
				ELSE (
					CASE WHEN EXISTS (SELECT 1 FROM (SELECT
						hasparent(
							ac.id, (
								SELECT id 
								FROM htree_text('categories','',1,':') 
								WHERE CASE mt WHEN 0 THEN 'sw' WHEN 1 THEN 'va' ELSE '' END || ':' || REGEXP_REPLACE(LOWER(name), '[^a-zA-Z0-9:]{1,}', '.', 'g') = mtype
							), 
							'categories'
						) AS hp
					FROM appcategories ac
					WHERE ac.appid = a.id
					) AS hpq WHERE hpq.hp) OR EXISTS (SELECT 1 FROM appcategories WHERE appid = a.id AND categoryid = (
						SELECT id 
						FROM htree_text('categories','',1,':') 
						WHERE CASE mt WHEN 0 THEN 'sw' WHEN 1 THEN 'va' ELSE '' END || ':' || REGEXP_REPLACE(LOWER(name), '[^a-zA-Z0-9:]{1,}', '.', 'g') = mtype
					)
					) OR (mtype = 'sw') OR (mtype = 'va') THEN TRUE ELSE FALSE END					
				) END
                        ) /*AND NOT a.pidhandle IS NULL -- FIXME: enable this once HANDLE PIDs are available*/
                        ;
                ELSE
                        RETURN '{"error": "noRecordsMatch"}';
                END IF;
        END IF;

        /* parse token */
        tokenarray := string_to_array(mtoken, ',');
        -- RAISE NOTICE 'abbrev %', tokenarray[4];
        IF tokenarray[4]::BOOLEAN THEN
                mabbrev = TRUE;
        END IF;
        mformat := tokenarray[7]::TEXT;

        UPDATE oai_app_cursors 
        SET pos = pos + 50,
        lastusedon = NOW()
        WHERE token = mtoken;

        ofs := (SELECT pos - 50 FROM oai_app_cursors WHERE token = mtoken);

        body := (SELECT ARRAY_TO_STRING(ARRAY_AGG(x), '') FROM (
                        SELECT
                                CASE mabbrev WHEN FALSE THEN
                                        CASE mformat 
                                                WHEN 'oai_dc' THEN
                                                        a.oaidc::TEXT
                                                ELSE
                                                        a.openaire::TEXT
                                        END
                                ELSE
                                        a.openaire2::TEXT
                                END AS x
                        FROM applications a
                        INNER JOIN oai_app_cursors AS c ON a.id = ANY(c.appids) AND c.token = mtoken 
                        ORDER BY (deleted OR moderated) DESC, guid::TEXT
                        OFFSET ofs
                        LIMIT 50
                ) AS x
        );

--        head := E'<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/ http://www.openarchive| s.org/OAI/2.0/OAI-PMH.xsd"><responseDate>' ||
--                REGEXP_REPLACE(REPLACE((NOW() AT TIME ZONE 'UTC')::TEXT, ' ', 'T') || 'Z', '\.[0-9]*Z$', 'Z') || 
--                E'</responseDate><request verb="ListRecords" metadataPrefix="' || mformat || '">https://oai.datacite.org/oai</request><ListRecords>';

        expdat := REGEXP_REPLACE(REPLACE(((NOW() + INTERVAL '1 hour') AT TIME ZONE 'UTC')::TEXT, ' ', 'T') || 'Z', '\.[0-9]*Z$', 'Z');
        nrec := (SELECT ARRAY_LENGTH(appids, 1) FROM oai_app_cursors WHERE token = mtoken)::TEXT;

/*        IF ofs + 50 < nrec::int THEN
        foot := E'<resumptionToken expirationDate="'|| expdat || '" completeListSize="' ||
                        nrec || '" cursor="' || ofs::TEXT || '">' || mtoken || '</resumptionToken>' ||
                        E'</ListRecords></OAI-PMH>';
        ELSE
        foot := E'</ListRecords></OAI-PMH>';
        END IF;*/

        ret = (('{' ||
                                        '"payload": ' || to_json(encode(REPLACE(body, E'\\', E'\\\\')::bytea, 'base64')) ||
--                                        ', "header": ' || to_json(encode(REPLACE(head, E'\\', E'\\\\')::bytea, 'base64')) ||
--                                        ', "footer": ' || to_json(encode(REPLACE(foot, E'\\', E'\\\\')::bytea, 'base64')) ||
                CASE WHEN ofs + 50 < nrec::int THEN
                        ', "completeListSize": ' || to_json(nrec) ||
                        ', "cursor": ' || to_json(ofs::TEXT) ||
                        ', "resumptionToken": ' || to_json(mtoken) ||
                        ', "expirationDate": ' || to_json(expdat)
                ELSE
                        ''
                END ||
        '}')::JSONB)::TEXT;

        -- delete exhausted tokens
        DELETE FROM oai_app_cursors
        WHERE pos >= ARRAY_LENGTH(appids, 1);

        RETURN ret;

END;
$$ LANGUAGE plpgsql VOLATILE;
ALTER FUNCTION oai_app_cursor(timestamp, timestamp, TEXT, BOOLEAN, TEXT, TEXT) OWNER TO appdb;

CREATE OR REPLACE FUNCTION trfn_cache_appxmlcache_openaire()
RETURNS TRIGGER
AS
$$
BEGIN
        NEW.tstamp := NOW();
        IF (TG_OP = 'INSERT' AND NEW.openairexml IS NULL) OR (TG_OP = 'UPDATE' AND NEW.xml::TEXT IS DISTINCT FROM OLD.xml::TEXT AND NOT (NEW.openairexml::TEXT IS DISTINCT FROM OLD.openairexml::TEXT)) THEN
                -- RAISE NOTICE 'Updating OpenAIRE XML cache for %', NEW.id;
                NEW.openairexml := (SELECT applications.__openaire FROM applications WHERE id = NEW.id);
                -- RAISE NOTICE '%', NEW.openairexml;
        END IF;
        IF (TG_OP = 'INSERT' AND NEW.oaidcxml IS NULL) OR (TG_OP = 'UPDATE' AND NEW.xml::TEXT IS DISTINCT FROM OLD.xml::TEXT AND NOT (NEW.oaidcxml::TEXT IS DISTINCT FROM OLD.oaidcxml::TEXT)) THEN
                -- RAISE NOTICE 'Updating OAI-DC XML cache for %', NEW.id;
                NEW.oaidcxml := (SELECT applications.__oaidc FROM applications WHERE id = NEW.id);
                -- RAISE NOTICE '%', NEW.oaidcxml;
        END IF;
        RETURN NEW;
END;
$$ LANGUAGE plpgsql;
ALTER FUNCTION trfn_cache_appxmlcache_openaire() OWNER TO appdb;

CREATE TRIGGER rtr_cache_appxmlcache_90_openaire 
BEFORE INSERT OR UPDATE ON cache.appxmlcache
FOR EACH ROW
EXECUTE PROCEDURE trfn_cache_appxmlcache_openaire();

INSERT INTO version (major,minor,revision,notes) 
        SELECT 8, 18, 0, E'Add initial support for OAI-PMH server functionality'
        WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=17 AND revision=6);

COMMIT;
