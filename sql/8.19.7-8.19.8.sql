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
Previous version: 8.19.7
New version: 8.19.8
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

ALTER TABLE cache.appxmlcache RENAME openairexml TO oaidatacitexml;

CREATE OR REPLACE FUNCTION public.oai_sw_setspecs()
 RETURNS SETOF xml
 LANGUAGE sql
 STABLE
AS $function$
SELECT XMLELEMENT(name "set", XMLELEMENT(name "setName", 'Software'), XMLELEMENT(name "setSpec", 'sw'))
UNION ALL
SELECT 
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
FROM 
        htree_text('categories','',1,':')
WHERE (
        NOT hasparent(id, (SELECT id FROM categories WHERE name = 'Virtual Appliances'), 'categories')
) AND (
        NOT hasparent(id, (SELECT id FROM categories WHERE name = 'Software Appliances'), 'categories')
) AND (name NOT IN ('Virtual Appliances', 'Software Appliances'))
$function$;
ALTER FUNCTION oai_sw_setspecs() OWNER TO appdb;

CREATE OR REPLACE FUNCTION public.oai_va_setspecs()
 RETURNS SETOF xml
 LANGUAGE sql
 STABLE
AS $function$
SELECT x::XML FROM (
	SELECT DISTINCT REPLACE(x::TEXT, 'Virtual Appliances / Virtual Appliances', 'Virtual Appliances') AS x FROM (
		SELECT XMLELEMENT(name "set", XMLELEMENT(name "setName", 'Virtual Appliances'), XMLELEMENT(name "setSpec", 'va')) AS x
		UNION ALL
		SELECT
			XMLELEMENT(
				name "set",
				XMLELEMENT(
					name "setName",
					'Virtual Appliances / ' || REPLACE(name, ':', ' / ')
				), 
				XMLELEMENT(
					name "setSpec",
					REPLACE('va:' || REGEXP_REPLACE(LOWER(name), '[^a-zA-Z0-9:]{1,}', '.', 'g'), 'va:virtual.appliances', 'va')
				)
			)
		FROM 
			htree_text('categories','',1,':')
		WHERE 
			hasparent(id, (SELECT id FROM categories WHERE name = 'Virtual Appliances'), 'categories')
	) AS t
) AS tt
ORDER BY x::TEXT DESC
$function$;

CREATE OR REPLACE FUNCTION public.oai_setspecs(applications) 
RETURNS XML[]
AS
$$
SELECT ARRAY_AGG(DISTINCT REPLACE(x::TEXT, 'va:virtual.appliances', 'va') ORDER BY REPLACE(x::TEXT, 'va:virtual.appliances', 'va') DESC)::XML[] FROM (
	SELECT 
		XMLELEMENT(name "setSpec", CASE $1. metatype WHEN 0 THEN 'sw' WHEN 1 THEN 'va' ELSE NULL::TEXT END)
	AS x
	UNION ALL
	SELECT 
		XMLELEMENT(
			name "setSpec",
			CASE $1.metatype WHEN 0 THEN 'sw:' WHEN 1 THEN 'va:' ELSE NULL::TEXT END || REGEXP_REPLACE(LOWER(name), '[^a-zA-Z0-9:]{1,}', '.', 'g')
		)
	FROM (
	SELECT c.name
	FROM htree_text('categories', '', 1, ':') c
	WHERE c.id = ANY($1.categoryid) AND c.id <> 34
	ORDER BY c.lvl, c.id
	) t
) AS tt
$$ LANGUAGE SQL STABLE;
ALTER FUNCTION oai_setspecs(applications) OWNER TO appdb;

DROP FUNCTION IF EXISTS __openaire2(applications);
DROP FUNCTION IF EXISTS openaire2(applications);

CREATE FUNCTION oai_header(applications) RETURNS XML AS
$$
SELECT (
	E'<header' || CASE WHEN ($1.deleted OR $1.moderated) THEN ' status="deleted"' ELSE '' END || '><identifier>' || E'oai:appdb.egi.eu:' || $1.guid::TEXT || E'</identifier><datestamp>' || 
	REGEXP_REPLACE(REPLACE(
	COALESCE((SELECT tstamp::TIMESTAMPTZ AT TIME ZONE 'UTC' FROM cache.appxmlcache WHERE id = $1.id), (NOW() AT TIME ZONE 'UTC'))::TEXT,
	' ', 'T') || 'Z', '\.[0-9]*Z$', 'Z') || 
	E'</datestamp>' || 
	ARRAY_TO_STRING($1.oai_setspecs, '') ||
	E'</header>'
)::XML
$$ LANGUAGE SQL STABLE;
ALTER FUNCTION oai_header(applications) OWNER TO appdb;

DROP FUNCTION IF EXISTS __openaire(applications);
CREATE OR REPLACE FUNCTION public.__oaidatacite(applications)
  RETURNS xml AS
$BODY$
DECLARE t1 double precision;
DECLARE t2 double precision;
DECLARE dt double precision;
DECLARE x TEXT;
BEGIN
        IF ($1.deleted) OR ($1.moderated) THEN
                RETURN (E'<record>' || $1.oai_header::TEXT || E'</record>')::XML;
        ELSE
		t1 := (SELECT EXTRACT(EPOCH FROM(clock_timestamp())));
		x := (SELECT 
			(
			E'<record>' || $1.oai_header::TEXT || E'<metadata><oai_datacite xmlns="http://schema.datacite.org/oai/oai-1.1/" xsi:schemaLocation="http://schema.datacite.org/oai/oai-1.1/ http://schema.datacite.org/oai/oai-1.1/oai.xsd"><schemaVersion>4</schemaVersion>' || 
			E'<datacentreSymbol>EGI.APPDB</datacentreSymbol><payload>' ||
			REGEXP_REPLACE(
/*                                        xsltproc(
					COALESCE((SELECT data FROM config WHERE var = 'oaidatacite_xslt'), '/var/www/html/appdb/application/configs/api/1.0/xslt/oai_datacite.xsl'),
					('<appdb:appdb xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" ' || 
					E'xmlns:appdb="http://appdb.egi.eu/api/1.0/appdb" xmlns:application="http://appdb.egi.eu/api/1.0/application" xmlns:discipline="http://appdb.egi.eu/api/1.0/discipline" xmlns:category="http://appdb.egi.eu/api/1.0/category" xmlns:dissemination="http://appdb.egi.eu/api/1.0/dissemination" xmlns:filter="http://appdb.egi.eu/api/1.0/filter" xmlns:history="http://appdb.egi.eu/api/1.0/history" xmlns:logistics="http://appdb.egi.eu/api/1.0/logistics" xmlns:resource="http://appdb.egi.eu/api/1.0/resource" xmlns:middleware="http://appdb.egi.eu/api/1.0/middleware" xmlns:person="http://appdb.egi.eu/api/1.0/person" xmlns:permission="http://appdb.egi.eu/api/1.0/permission" xmlns:privilege="http://appdb.egi.eu/api/1.0/privilege" xmlns:publication="http://appdb.egi.eu/api/1.0/publication" xmlns:rating="http://appdb.egi.eu/api/1.0/rating" xmlns:ratingreport="http://appdb.egi.eu/api/1.0/ratingreport" xmlns:regional="http://appdb.egi.eu/api/1.0/regional" xmlns:user="http://appdb.egi.eu/api/1.0/user" xmlns:vo="http://appdb.egi.eu/api/1.0/vo" xmlns:virtualization="http://appdb.egi.eu/api/1.0/virtualization" xmlns:contextualization="http://appdb.egi.eu/api/1.0/contextualization" xmlns:license="http://appdb.egi.eu/api/1.0/license" xmlns:provider="http://appdb.egi.eu/api/1.0/provider" xmlns:provider_template="http://appdb.egi.eu/api/1.0/provider_template" xmlns:classification="http://appdb.egi.eu/api/1.0/classification" xmlns:site="http://appdb.egi.eu/api/1.0/site" xmlns:siteservice="http://appdb.egi.eu/api/1.0/site" xmlns:entity="http://appdb.egi.eu/api/1.0/entity" xmlns:organization="http://appdb.egi.eu/api/1.0/organization" xmlns:project="http://appdb.egi.eu/api/1.0/project" xmlns:dataset="http://appdb.egi.eu/api/1.0/dataset" ' || 
					E'datatype="application" type="entry" host="' || (SELECT data FROM config WHERE var = 'ui-host') || '" apihost="' || (SELECT data FROM config WHERE var = 'api-host') || 
					'" cacheState="0" permsState="' || (SELECT data FROM config WHERE var = 'permissions_cache_dirty') || '" requestedOn="###REQUESTED_ON###" deliveredOn="###DELIVERED_ON###" processingTime="###PROCESSING_TIME###" version="1.0">' ||
					app_to_xml_ext($1.id) || '</appdb:appdb>')::XML
				)::TEXT,*/
				oai_datacite_xslt((E'<appdb:appdb ' || appdb_xmlns() || 
E' datatype="application" type="entry" host="' || (SELECT data FROM config WHERE var = 'ui-host') || '" apihost="' || (SELECT data FROM config WHERE var = 'api-host') || 
E'" cacheState="0" permsState="' || (SELECT data FROM config WHERE var = 'permissions_cache_dirty') || '" requestedOn="###REQUESTED_ON###" deliveredOn="###DELIVERED_ON###" processingTime="###PROCESSING_TIME###" version="1.0"' ||
				'>' || app_to_xml_ext($1.id)::TEXT || '</appdb:appdb>')::XML)::TEXT,
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
                RETURN x;
        END IF;
END;
$BODY$
  LANGUAGE plpgsql STABLE
  COST 100;
ALTER FUNCTION public.__oaidatacite(applications)
  OWNER TO appdb;

DROP FUNCTION IF EXISTS openaire(applications);
CREATE OR REPLACE FUNCTION public.oaidatacite(applications)
 RETURNS xml
 LANGUAGE plpgsql
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
ALTER FUNCTION oaidatacite(applications) OWNER TO appdb;

  CREATE OR REPLACE FUNCTION public.__oaidc(applications)
  RETURNS xml AS
$BODY$
DECLARE t1 double precision;
DECLARE t2 double precision;
DECLARE dt double precision;
DECLARE x TEXT;
BEGIN
        IF ($1.deleted) OR ($1.moderated) THEN
                RETURN (E'<record>' || $1.oai_header::TEXT || E'</record>')::XML;
        ELSE
		t1 := (SELECT EXTRACT(EPOCH FROM(clock_timestamp())));
		x := (SELECT 
			(
			E'<record>' || $1.oai_header || E'<metadata>' ||
			REGEXP_REPLACE(
				oai_dc_xslt((E'<appdb:appdb ' || appdb_xmlns() || 
E' datatype="application" type="entry" host="' || (SELECT data FROM config WHERE var = 'ui-host') || '" apihost="' || (SELECT data FROM config WHERE var = 'api-host') || 
E'" cacheState="0" permsState="' || (SELECT data FROM config WHERE var = 'permissions_cache_dirty') || '" requestedOn="###REQUESTED_ON###" deliveredOn="###DELIVERED_ON###" processingTime="###PROCESSING_TIME###" version="1.0"' ||
				'>' || app_to_xml_ext($1.id)::TEXT || '</appdb:appdb>')::XML)::TEXT,
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
                RETURN x;
        END IF;
END;
$BODY$
  LANGUAGE plpgsql STABLE
  COST 100;
ALTER FUNCTION public.__oaidc(applications)
  OWNER TO appdb;

CREATE OR REPLACE FUNCTION public.oai_app_cursor(
    mfrom timestamp without time zone DEFAULT NULL::timestamp without time zone,
    muntil timestamp without time zone DEFAULT NULL::timestamp without time zone,
    mtoken text DEFAULT NULL::text,
    mabbrev boolean DEFAULT false,
    mtype text DEFAULT NULL::text,
    mformat text DEFAULT 'oai_datacite'::text)
  RETURNS text AS
$BODY$
DECLARE body TEXT;
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
                        ) AND ((NOT a.pidhandle IS NULL) OR (a.deleted OR a.moderated)) -- FIXME: enable this once HANDLE PIDs are available*/
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
                                                        a.oaidatacite::TEXT
                                        END
                                ELSE
                                        a.oai_header::TEXT
                                END AS x
                        FROM applications a
                        INNER JOIN oai_app_cursors AS c ON a.id = ANY(c.appids) AND c.token = mtoken 
                        ORDER BY (deleted OR moderated) DESC, guid::TEXT
                        OFFSET ofs
                        LIMIT 50
                ) AS x
        );

        expdat := REGEXP_REPLACE(REPLACE(((NOW() + INTERVAL '1 hour') AT TIME ZONE 'UTC')::TEXT, ' ', 'T') || 'Z', '\.[0-9]*Z$', 'Z');
        nrec := (SELECT ARRAY_LENGTH(appids, 1) FROM oai_app_cursors WHERE token = mtoken)::TEXT;

        ret = (('{' ||
                                        '"payload": ' || to_json(encode(REPLACE(body, E'\\', E'\\\\')::bytea, 'base64')) ||
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
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION public.oai_app_cursor(timestamp without time zone, timestamp without time zone, text, boolean, text, text)
  OWNER TO appdb;

DROP TRIGGER IF EXISTS rtr_cache_appxmlcache_90_openaire ON cache.appxmlcache;

DROP FUNCTION IF EXISTS trfn_cache_appxmlcache_openaire();
CREATE OR REPLACE FUNCTION public.trfn_cache_appxmlcache_oaipmh() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
        NEW.tstamp := NOW();
        IF (TG_OP = 'INSERT' AND NEW.oaidatacitexml IS NULL) OR (TG_OP = 'UPDATE' AND NEW.xml::TEXT IS DISTINCT FROM OLD.xml::TEXT AND NOT (NEW.oaidatacitexml::TEXT IS DISTINCT FROM OLD.oaidatacitexml::TEXT)) THEN
                -- RAISE NOTICE 'Updating OAI-DATACITE XML cache for %', NEW.id;
                NEW.oaidatacitexml := (SELECT applications.__oaidatacite FROM applications WHERE id = NEW.id);
                -- RAISE NOTICE '%', NEW.oaidatacitexml;
        END IF;
        IF (TG_OP = 'INSERT' AND NEW.oaidcxml IS NULL) OR (TG_OP = 'UPDATE' AND NEW.xml::TEXT IS DISTINCT FROM OLD.xml::TEXT AND NOT (NEW.oaidcxml::TEXT IS DISTINCT FROM OLD.oaidcxml::TEXT)) THEN
                -- RAISE NOTICE 'Updating OAI-DC XML cache for %', NEW.id;
                NEW.oaidcxml := (SELECT applications.__oaidc FROM applications WHERE id = NEW.id);
                -- RAISE NOTICE '%', NEW.oaidcxml;
        END IF;
        RETURN NEW;
END;
$$;
ALTER FUNCTION trfn_cache_appxmlcache_oaipmh() OWNER TO appdb;

CREATE TRIGGER rtr_cache_appxmlcache_90_oaipmh
BEFORE INSERT OR UPDATE ON cache.appxmlcache 
FOR EACH ROW 
EXECUTE PROCEDURE public.trfn_cache_appxmlcache_oaipmh();

UPDATE cache.appxmlcache SET oaidatacitexml = NULL, oaidcxml = NULL;

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 19, 8, E'OAI-PMH code cleanup'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=19 AND revision=8);

COMMIT;
