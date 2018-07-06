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
Previous version: 8.20.7
New version: 8.21.0
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

CREATE OR REPLACE FUNCTION public.authors_to_xml(mdocid integer)
 RETURNS xml
 LANGUAGE sql
AS $function$
	SELECT
		xmlagg(
			xmlelement(
				name "publication:author",
				xmlattributes(
					authors.main as main,
					CASE WHEN authors.authorid IS NULL THEN 'external' ELSE 'internal' END AS type
				),
			CASE
				WHEN authorid IS NULL THEN
					xmlelement(
						name "publication:extAuthor",
						fullname
					)
				ELSE
					researcher_to_xml(authorid)
			END)
		ORDER BY CASE WHEN NOT authorid IS NULL THEN (SELECT name FROM researchers WHERE id = authorid) ELSE fullname END
		) AS author
	FROM authors
	WHERE docid = $1
	GROUP BY docid;
$function$;

DROP TRIGGER IF EXISTS rtr_app_urls_before ON app_urls;
DROP TRIGGER IF EXISTS rtr_app_urls_after ON app_urls;
DROP FUNCTION IF EXISTS trfn_app_urls();

CREATE OR REPLACE FUNCTION public.trfn_appmanualcountries_cache_delta() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
        IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN
                PERFORM pg_notify('cache_delta', (SELECT DISTINCT (appcountries.*)::record FROM appcountries WHERE appid = NEW.appid AND id = NEW.countryid FETCH FIRST 1 ROWS ONLY) || '|appcountries');
        ELSIF TG_OP = 'DELETE' THEN
                PERFORM pg_notify('cache_delta', zerorec('appcountries', ARRAY['appid'], ARRAY[OLD.appid]) || '|appcountries');
        END IF;
        RETURN NULL;
END;
$$;

DROP VIEW vapps_of_swapps_to_xml;
DROP VIEW apppercountries;
DROP VIEW appviews;
DELETE FROM cache.filtercache;

ALTER TABLE applications DROP COLUMN disciplineid;
ALTER TABLE rankedapps DROP COLUMN disciplineid;
ALTER TABLE applications DROP COLUMN categoryid;
ALTER TABLE rankedapps DROP COLUMN categoryid;
ALTER TABLE applications DROP COLUMN links;
ALTER TABLE rankedapps DROP COLUMN links;
CREATE OR REPLACE FUNCTION disciplineid(applications) RETURNS INT[] AS
$$
	SELECT ARRAY_AGG(disciplineid) FROM appdisciplines WHERE appid = $1.id;
$$
LANGUAGE SQL STABLE;

CREATE OR REPLACE FUNCTION categoryid(applications) RETURNS INT[] AS
$$
	SELECT ARRAY_AGG(categoryid) FROM appcategories WHERE appid = $1.id;
$$
LANGUAGE SQL STABLE;

CREATE OR REPLACE FUNCTION links(applications) RETURNS TEXT[] AS
$$
	SELECT ARRAY_AGG('{"url": "' || COALESCE(url,'') || '", "description": "' || COALESCE(description,'') || '", "title": "' || COALESCE(title,'') || '", "ord": ' || COALESCE(ord,0) || '"}')
	FROM app_urls
	WHERE appid = $1.id;
$$
LANGUAGE SQL STABLE;

CREATE OR REPLACE FUNCTION public.trfn_applications()
  RETURNS trigger AS
$BODY$
DECLARE mFields TEXT[];
BEGIN
    mFields = NULL::TEXT[];
    IF TG_OP = 'INSERT' THEN
        IF TG_LEVEL = 'ROW' THEN
            IF TG_WHEN = 'AFTER' THEN
                IF (NEW.owner IS NULL) AND (NOT NEW.addedby IS NULL) AND (EXISTS (SELECT id FROM researchers WHERE id = NEW.addedby AND accounttype = 0)) THEN
					INSERT INTO researchers_apps (appid, researcherid, iskeycontact) VALUES (NEW.id, NEW.addedby, false);
				ELSIF NOT NEW.owner IS NULL THEN
				    INSERT INTO researchers_apps (appid, researcherid, iskeycontact) VALUES (NEW.id, NEW.owner, false);
				END IF;
                INSERT INTO news ("timestamp", subjectguid, "action") VALUES (NOW(), NEW.guid, 'insert');
				--NOTIFY invalidate_cache, 'permissions';
--				PERFORM pg_notify('cache_delta', 'id = ' || NEW.id::text || ',applications');
				IF (NEW.cname IS NULL) THEN
					INSERT INTO app_cnames (appid, value) VALUES (NEW.id, normalize_cname(NEW.name));
				END IF;
				INSERT INTO cache.appxmlcache (id,"xml") SELECT NEW.id, __app_to_xml(NEW.id);
            END IF;
        END IF;
        RETURN NEW;
    ELSIF TG_OP = 'UPDATE' THEN
        IF TG_LEVEL = 'ROW' THEN
            IF TG_WHEN = 'BEFORE' THEN
                IF (OLD.name != NEW.name) THEN mFields := array_append(mFields,'name'); END IF;
                IF (OLD.description != NEW.description) THEN mFields := array_append(mFields,'description'); END IF;
                IF (OLD.abstract != NEW.abstract) THEN mFields := array_append(mFields,'abstract'); END IF;
                IF (OLD.statusid != NEW.statusid) THEN mFields := array_append(mFields,'status'); END IF;
		IF ((OLD::applications).links != (NEW::applications).links) THEN mFields := array_append(mFields,'urls'); END IF;
                IF (COALESCE((OLD::applications).disciplineid,ARRAY[-1]) != COALESCE((NEW::applications).disciplineid,ARRAY[-1])) THEN mFields := array_append(mFields,'discipline'); END IF;
				IF (COALESCE((OLD::applications).categoryid,ARRAY[-1]) != COALESCE((NEW::applications).categoryid,ARRAY[-1])) THEN mFields := array_append(mFields,'category'); END IF;
                IF NOT mFields IS NULL THEN
                    INSERT INTO news ("timestamp", subjectguid, "action", fields) VALUES (NOW(), NEW.guid, 'update', mFields);
                    DELETE FROM app_validation_log WHERE appid = OLD.id;
                END IF;
                -- NEW.lastupdated = NOW();
		    ELSIF TG_WHEN = 'AFTER' THEN
				IF OLD.moderated <> NEW.moderated OR
					OLD.deleted <> NEW.deleted OR
					OLD.lastupdated <> NEW.lastupdated OR
					OLD.addedby <> NEW.addedby OR
					OLD.owner <> NEW.owner OR
					(OLD::applications).links <> (NEW::applications).links OR
					OLD.rating <> NEW.rating THEN -- the rest of the properties are taken care by the trigger in the "news" table
					-- NOTIFY invalidate_cache, 'filters';
				END IF;
				IF OLD.owner <> NEW.owner OR
					OLD.addedby <> NEW.addedby THEN
					PERFORM request_permissions_refresh();
				END IF;
--				IF  (NEW.name, NEW.description, NEW.abstract, NEW.statusid, NEW.dateadded, NEW.addedby, NEW.lastupdated, NEW.rating, NEW.moderated, NEW.deleted, NEW.disciplineid, NEW.owner, NEW.categoryid, NEW.links) IS DISTINCT FROM (OLD.name, OLD.description, OLD.abstract, OLD.statusid, OLD.dateadded, OLD.addedby, OLD.lastupdated, OLD.rating, OLD.moderated, OLD.deleted, OLD.disciplineid, OLD.owner, OLD.categoryid, OLD.links) THEN
--					PERFORM pg_notify('cache_delta', 'id = ' || NEW.id::text || ',applications');
--				END IF;
				IF (NEW.deleted IS TRUE AND OLD.deleted IS FALSE) OR (NEW.moderated IS TRUE AND OLD.moderated IS FALSE) THEN
					UPDATE app_cnames SET enabled = FALSE WHERE appid = NEW.id;
				END IF;
				UPDATE cache.appxmlcache SET "xml" = __app_to_xml(NEW.id) WHERE id = NEW.id;
            END IF;
        END IF;
        RETURN NEW;
    ELSIF TG_OP = 'DELETE' THEN
        IF TG_LEVEL = 'ROW' THEN
            IF TG_WHEN = 'AFTER' THEN
				INSERT INTO news ("timestamp", subjectguid, "action") VALUES (NOW(), OLD.guid, 'delete');
            END IF;
        END IF;
        RETURN OLD;
    END IF;
END;$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION public.trfn_applications()
  OWNER TO appdb;

-- Function: public.trfn_applications_cache_delta()

-- DROP FUNCTION public.trfn_applications_cache_delta();

CREATE OR REPLACE FUNCTION public.trfn_applications_cache_delta()
  RETURNS trigger AS
$BODY$
DECLARE rec RECORD;
BEGIN
        IF TG_OP = 'INSERT' OR (TG_OP = 'UPDATE' AND
                                (
                                        OLD.moderated <> NEW.moderated OR
                                        OLD.deleted <> NEW.deleted OR
                                        OLD.lastupdated <> NEW.lastupdated OR
                                        OLD.addedby <> NEW.addedby OR
                                        OLD.owner <> NEW.owner OR
                                        array_sort((OLD::applications).links) <> array_sort((NEW::applications).links) OR
                                        OLD.rating <> NEW.rating OR
                                        OLD.name <> NEW.name OR
                                        OLD.description <> NEW.description OR
                                        OLD.abstract <> NEW.abstract OR
                                        OLD.statusid <> NEW.statusid OR
                                        array_sort((OLD::applications).disciplineid) <> array_sort((NEW::applications).disciplineid) OR
                                        array_sort((OLD::applications).categoryid) <> array_sort((NEW::applications).categoryid)
                                )
        ) THEN
                rec := NEW;
                PERFORM pg_notify('cache_delta', rec || '|applications');
        ELSIF TG_OP = 'DELETE' THEN
                PERFORM pg_notify('cache_delta', zerorec('applications') || '|applications');
        END IF;
        RETURN NULL;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION public.trfn_applications_cache_delta()
  OWNER TO appdb;

CREATE VIEW appviews AS
 SELECT DISTINCT applications.id,
    applications.name,
    applications.description,
    applications.abstract,
    applogos.logo,
    applications.statusid,
    app_middlewares.middlewareid,
    applications.dateadded,
    applications.addedby,
    applications.tool,
    applications.respect,
    __appcountries.id AS countryid,
    __appcountries.regionid,
    __app_vos.void,
    (((appteam.lastname || ' '::text) || appteam.firstname) || ' '::text) || appteam.institution AS persondata,
        CASE
            WHEN (EXISTS ( SELECT appdocuments.id
               FROM appdocuments
              WHERE appdocuments.appid = applications.id)) THEN 1
            ELSE 0
        END AS hasdocs,
    applications.guid,
    applications.deleted,
    applications.moderated,
    applications.categoryid,
    applications.metatype
   FROM applications
     LEFT JOIN __appcountries ON __appcountries.appid = applications.id
     LEFT JOIN __app_vos ON __app_vos.appid = applications.id
     LEFT JOIN appteam ON appteam.appid = applications.id
     LEFT JOIN app_middlewares ON app_middlewares.appid = applications.id
     LEFT JOIN applogos ON applogos.appid = applications.id
  ORDER BY applications.id;
ALTER VIEW appviews OWNER TO appdb;

CREATE VIEW apppercountries AS
 SELECT countries.id,
    countries.name,
    countries.isocode,
    ( SELECT count(DISTINCT appviews.id) AS count
           FROM appviews
          WHERE appviews.countryid = countries.id AND appviews.deleted IS FALSE AND appviews.moderated IS FALSE) AS sum
   FROM countries;
ALTER VIEW apppercountries OWNER TO appdb;

CREATE VIEW vapps_of_swapps_to_xml AS
 SELECT vapps.id,
    XMLELEMENT(NAME "application:application", XMLATTRIBUTES(vapps.id AS id, vapps.cname AS cname, vapps.guid AS guid, vapps.vappversionid AS versionid,
        CASE
            WHEN vapps.va_version_expireson < now() THEN true
            ELSE false
        END AS isexpired, vapps.va_version_archived AS archived), XMLELEMENT(NAME "application:name", vapps.name), ( SELECT xmlagg(swapps.xml) AS x
           FROM ( SELECT XMLELEMENT(NAME "application:application", XMLATTRIBUTES(apps.id AS id, apps.cname AS cname, apps.guid AS guid), XMLELEMENT(NAME "application:name", apps.name)) AS xml
                   FROM applications apps
                  WHERE (apps.id IN ( SELECT unnest(vapps.swappids) AS unnest))) swapps)) AS xml
   FROM ( SELECT DISTINCT ON (vapps_1.id) vapps_1.id,
            vapps_1.name,
            vapps_1.description,
            vapps_1.abstract,
            vapps_1.statusid,
            vapps_1.dateadded,
            vapps_1.addedby,
            vapps_1.respect,
            vapps_1.tool,
            vapps_1.guid,
            vapps_1.lastupdated,
            vapps_1.rating,
            vapps_1.ratingcount,
            vapps_1.moderated,
            vapps_1.tagpolicy,
            vapps_1.deleted,
            vapps_1.metatype,
            vapps_1.disciplineid,
            vapps_1.owner,
            vapps_1.categoryid,
            vapps_1.hitcount,
            vapps_1.cname,
            vapps_1.links,
            __vaviews.vappversionid,
            __vaviews.va_version_expireson,
            __vaviews.va_version_archived,
            array_agg(DISTINCT contexts.appid) AS swappids
           FROM contexts
             JOIN context_script_assocs csa ON csa.contextid = contexts.id
             JOIN contextscripts cs ON cs.id = csa.scriptid
             JOIN vmiinstance_contextscripts vcs ON vcs.contextscriptid = cs.id
             JOIN __vaviews ON __vaviews.vmiinstanceid = vcs.vmiinstanceid
             JOIN applications vapps_1 ON vapps_1.id = __vaviews.appid
          WHERE __vaviews.va_version_published = true
          GROUP BY vapps_1.id, __vaviews.vappversionid, __vaviews.va_version_expireson, __vaviews.va_version_archived) vapps;
ALTER VIEW vapps_of_swapps_to_xml OWNER TO appdb;

-- Function: public.export_app(integer, text)

-- DROP FUNCTION public.export_app(integer, text);

CREATE OR REPLACE FUNCTION public.export_app(
    mid integer,
    format text DEFAULT 'csv'::text)
  RETURNS text AS
$BODY$
SELECT CASE WHEN $2 = 'csv' THEN
 '"' || REPLACE(COALESCE(applications.name, ''), '"', E'”') || '",' ||
    '"' || REPLACE(COALESCE(applications.description, ''), '"', E'”') || '",'
    '"' || REPLACE(COALESCE(abstract, ''), '"', E'”') || '",'
    '"' || REPLACE(COALESCE(dateadded::text, ''), '"', E'”') || '",'
    '"' || REPLACE(COALESCE(addedby.name, ''), '"', E'”') || '",'
    '"' || REPLACE(COALESCE(owner.name, ''), '"', E'”') || '",'
    '"' || REPLACE(COALESCE(statuses.name, ''), '"', E'”') || '",'
    '"' || REPLACE(COALESCE(array_to_string(array_agg(DISTINCT categories.name), ','), ''), '"', E'”') || '",'
    '"' || REPLACE(COALESCE(array_to_string(array_agg(DISTINCT middlewares.name), ','), ''), '"', E'”') || '",'
    '"' || REPLACE(COALESCE(array_to_string(array_agg(DISTINCT vos.name), ','), ''), '"', E'”') || '",'
    '"' || REPLACE(COALESCE(array_to_string(array_agg(DISTINCT disciplines.name), ','), ''), '"', E'”') || '",'
    '"' || REPLACE(COALESCE(array_to_string(array_agg(DISTINCT appcountries.name), ','), ''), '"', E'”') || '",'
    '"' || REPLACE(COALESCE(array_to_string(array_agg(DISTINCT app_urls.url), ','), ''), '"', E'”') || '",'
    '"' || REPLACE(COALESCE(array_to_string(array_agg(DISTINCT researchers.name), ','), ''), '"', E'”') || '"'
ELSE
	xmlelement(name "application",
		xmlelement(name "name", applications.name),
		xmlelement(name "description", applications.description),
		xmlelement(name "abstract", applications.abstract),
		xmlelement(name "dateAdded", applications.dateadded),
		xmlelement(name "addedby", addedby.name),
		xmlelement(name "owner", owner.name),
		xmlelement(name "status", statuses.name),
		xmlelement(name "categories",
			xmlconcat(
				array_to_string(
					array_agg(DISTINCT xmlelement(name "category", categories.name)::text)
				,'')::xml
			)
		),
		xmlelement(name "middlewares",
			xmlconcat(
				array_to_string(
					array_agg(DISTINCT xmlelement(name "middleware", middlewares.name)::text)
				,'')::xml
			)
		),
		xmlelement(name "vos",
			xmlconcat(
				array_to_string(
					array_agg(DISTINCT xmlelement(name "vo", vos.name)::text)
				,'')::xml
			)
		),
		xmlelement(name "disciplines",
			xmlconcat(
				array_to_string(
					array_agg(DISTINCT xmlelement(name "discipline", disciplines.name)::text)
				,'')::xml
			)
		),
		xmlelement(name "countries",
			xmlconcat(
				array_to_string(
					array_agg(DISTINCT xmlelement(name "country", appcountries.name)::text)
				,'')::xml
			)
		),
		xmlelement(name "urls",
			xmlconcat(
				array_to_string(
					array_agg(DISTINCT xmlelement(name "url", app_urls.url)::text)
				,'')::xml
			)
		),
		xmlelement(name "researchers",
			xmlconcat(
				array_to_string(
					array_agg(DISTINCT xmlelement(name "researcher", researchers.name)::text)
				,'')::xml
			)
		),
	'')::text
END
AS "application"
FROM applications
LEFT OUTER JOIN app_middlewares ON app_middlewares.appid = applications.id
LEFT OUTER JOIN middlewares ON middlewares.id = app_middlewares.middlewareid
LEFT OUTER JOIN appdisciplines ON appdisciplines.appid = applications.id
LEFT OUTER JOIN disciplines ON disciplines.id = appdisciplines.disciplineid
LEFT OUTER JOIN appcountries ON appcountries.appid = applications.id
LEFT OUTER JOIN app_urls ON app_urls.appid = applications.id
LEFT OUTER JOIN researchers_apps ON researchers_apps.appid = applications.id
LEFT OUTER JOIN researchers ON researchers.id = researchers_apps.researcherid
LEFT OUTER JOIN app_vos ON app_vos.appid = applications.id
LEFT OUTER JOIN vos ON vos.id = app_vos.void
LEFT OUTER JOIN researchers AS addedby ON addedby.id = applications.addedby
LEFT OUTER JOIN researchers AS owner ON owner.id = applications.owner
LEFT OUTER JOIN statuses ON statuses.id = applications.statusid
LEFT OUTER JOIN appcategories ON appcategories.appid = applications.id
LEFT OUTER JOIN categories ON appcategories.categoryid = categories.id
WHERE applications.id = $1
GROUP BY applications.name,
    applications.description,
    applications.abstract,
    applications.dateadded,
    addedby.name,
    owner.name,
    statuses.name

$BODY$
  LANGUAGE sql VOLATILE
  COST 100;
ALTER FUNCTION public.export_app(integer, text)
  OWNER TO appdb;

-- Function: public.__oaidatacite(applications)

-- DROP FUNCTION public.__oaidatacite(applications);

CREATE OR REPLACE FUNCTION public.__oaidatacite(applications)
  RETURNS xml AS
$BODY$
DECLARE t1 double precision;
DECLARE t2 double precision;
DECLARE dt double precision;
DECLARE x TEXT;
BEGIN
	-- RAISE NOTICE '%', $1.id;
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

-- Function: public.__oaidc(applications)

-- DROP FUNCTION public.__oaidc(applications);

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

CREATE OR REPLACE FUNCTION public.trfn_appcategories()
 RETURNS trigger
 LANGUAGE plpgsql
AS $function$
DECLARE REL RECORD;
DECLARE pid INT;
BEGIN
        IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN
                REL = NEW;
        ELSIF TG_OP = 'DELETE' THEN
                REL = OLD;
        END IF;
        IF TG_WHEN = 'AFTER' THEN
                IF TG_OP = 'INSERT' THEN
                        pid := (SELECT parentid FROM categories WHERE id = NEW.categoryid);
                        IF NOT pid IS NULL THEN
                                INSERT INTO appcategories (appid, categoryid, inherited) SELECT NEW.appid, pid, TRUE WHERE NOT EXISTS (SELECT * FROM appcategories WHERE appid = NEW.appid AND categoryid = pid);
                        END IF;
                        IF NEW.categoryid = 34 THEN
                                IF NOT EXISTS (SELECT * FROM vapplications WHERE appid = NEW.appid) THEN
                                        INSERT INTO vapplications(appid) VALUES (NEW.appid);
                                END IF;
                        END IF;
                ELSIF TG_OP = 'UPDATE' THEN
                        --
                ELSIF TG_OP = 'DELETE' THEN
                        pid := (SELECT parentid FROM categories WHERE id = OLD.categoryid);
                        IF NOT pid IS NULL THEN
                                DELETE FROM appcategories WHERE appid = OLD.appid AND categoryid = pid AND inherited IS TRUE;
                        END IF;
                END IF;
        ELSIF TG_WHEN = 'BEFORE' THEN
                IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN
                        --
                ELSIF TG_OP = 'DELETE' THEN
                        IF OLD.inherited IS TRUE AND EXISTS (SELECT * FROM appcategories WHERE appid = OLD.appid AND categoryid IN (SELECT id FROM categories WHERE parentid = OLD.categoryid)) THEN
                                RAISE NOTICE '%','Cannot remove inherited parent category';
                                RETURN NULL;
                        END IF;
                        IF OLD.categoryid = 34 AND EXISTS (SELECT * FROM vapplications WHERE appid = OLD.appid) THEN
                                RAISE NOTICE '%', 'Cannot remove "Virtual Appliances" category from software item bound to a Virtual Appliance entry';
                                RETURN NULL;
                        END IF;
                END IF;
        END IF;

        -- UPDATE applications SET categoryid = (SELECT array_agg(categoryid ORDER BY isprimary DESC) FROM appcategories WHERE appid = REL.appid) WHERE id = REL.appid;
        RETURN REL;
END;
$function$;

CREATE OR REPLACE FUNCTION public.trfn_appdisciplines()
 RETURNS trigger
 LANGUAGE plpgsql
AS $function$
DECLARE pid INT;
DECLARE REL RECORD;
BEGIN
        IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN
                REL = NEW;
        ELSIF TG_OP = 'DELETE' THEN
                REL = OLD;
        END IF;

        IF TG_WHEN = 'AFTER' THEN
                IF TG_OP = 'INSERT' THEN
                        pid := (SELECT parentid FROM disciplines WHERE id = NEW.disciplineid);
                        IF NOT pid IS NULL THEN
                                INSERT INTO appdisciplines (appid, disciplineid, inherited) SELECT NEW.appid, pid, TRUE WHERE NOT EXISTS (SELECT * FROM appdisciplines WHERE appid = NEW.appid AND disciplineid = pid);
                        END IF;
                ELSIF TG_OP = 'UPDATE' THEN
                ELSIF TG_OP = 'DELETE' THEN
                        pid := (SELECT parentid FROM disciplines WHERE id = OLD.disciplineid);
                        IF NOT pid IS NULL THEN
                                DELETE FROM appdisciplines WHERE appid = OLD.appid AND disciplineid = pid AND inherited IS TRUE;
                        END IF;
                END IF;
        ELSIF TG_WHEN = 'BEFORE' THEN
                IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN
                ELSIF TG_OP = 'DELETE' THEN
                        IF OLD.inherited IS TRUE AND EXISTS (SELECT * FROM appdisciplines WHERE appid = OLD.appid AND disciplineid IN (SELECT id FROM disciplines WHERE parentid = OLD.disciplineid)) THEN
                                RAISE NOTICE '%','Cannot remove inherited parent discipline';
                                RETURN NULL;
                        END IF;
                END IF;
        END IF;

        -- UPDATE applications SET disciplineid = (SELECT array_agg(disciplineid) FROM appdisciplines WHERE appid = REL.appid) WHERE id = REL.appid;

        RETURN REL;
END;
$function$;


CREATE OR REPLACE FUNCTION can_mod_app_tags(int, int) RETURNS BOOLEAN AS
$$
SELECT
     CASE tagpolicy
         WHEN 0 THEN
             (owner = $2) OR (addedby = $2)
         WHEN 1 THEN
             EXISTS (SELECT 1 FROM researchers_apps WHERE researcherid = $2)
         WHEN 2 THEN
             TRUE
     END
FROM applications WHERE id = $1;
$$ LANGUAGE SQL STABLE;
ALTER FUNCTION can_mod_app_tags(int, int) OWNER TO appdb;

CREATE OR REPLACE FUNCTION validate_app_cname(text, int DEFAULT NULL) RETURNS TEXT AS
$$
	SELECT value FROM app_cnames WHERE value = normalize_cname($1) AND (($2 IS NULL) OR ((NOT $2 IS NULL) AND ($2 <> appid)))
$$ LANGUAGE SQL STABLE;
ALTER FUNCTION validate_app_cname(text, int) OWNER TO appdb;

CREATE OR REPLACE FUNCTION validate_app_name(text, int DEFAULT NULL) RETURNS TEXT AS
$$
DECLARE
	p TEXT;
	err TEXT;
	reason TEXT;
	exids INT[];
	exnames TEXT[];
BEGIN
	IF (LENGTH($1) < 3) OR (LENGTH($1) > 50) THEN
		err := 'Invalid length';
		reason := 'The length of the name must be from 3 to 50 characters long.The current length is <b>' || LENGTH($1) || '</b>.';
		RETURN '{"valid": false, "error": "' || err || '", "reason": ' || to_json(reason) || '}';
	END IF;

	IF NOT $1 ~ '^[A-Za-z0-9 *.+,&!#@=_^(){}\[\]-]+$' THEN
		err := 'Invalid character';
		reason := 'The name contains invalid characters. Valid characters are alphanumeric characters, spaces, and the following sumbols: +(){}[],*&amp;!#@=^._-';
		RETURN '{"valid": false, "error": "' || err || '", "reason": ' || to_json(reason) || '}';
	END IF;

	SELECT array_agg(id), array_agg(name) FROM app_name_available($1) INTO exids, exnames;
	IF ARRAY_LENGTH(exids, 1) > 0 THEN
		IF ($2 IS NULL) OR (NOT $2 = ANY(exids)) THEN
			err := 'Invalid name';
			reason := 'Name already taken by <a href="http://' || (SELECT data FROM config WHERE var = 'ui-host') || '/?p=' || encode(decode('/apps/details?id=' || (exids[1]::TEXT), 'escape'), 'base64') || '" target="_blank">' || exnames[1] || '</a>.<p></p>';
			reason := reason || '<div>Please have a look at the ' || exnames[1] || ' software entry to understand if it is different from the one you want to register.<br/>If it is <b>not</b> different, please join ' || exnames[1] || ' as a scientific contact (for more information visit the ';
			reason := reason || '<a href="#" onclick="appdb.utils.ToggleFaq(12);" >FAQ</a>).<p></p>If it is different, please modify your applcation name. In order to avoid confusion from similarly named software, you should use a modifier in you software name in order to differentiate it from other related entries. Good examples would be :</div>';
			reason := reason || '<div><span>  </span>' || $1 || '-&lt;Country&gt;</div>';
			reason := reason || '<div><span>  </span>' || $1 || '-&lt;Project&gt;</div>';
			reason := reason || '<div><span>  </span>' || $1 || '-&lt;Virtual Organization&gt;</div>';
			reason := reason || '<div><span>  </span>' || $1 || '-&lt;Consortium&gt;</div>';
			reason := reason || '<div>etc...</div>';
			reason := reason || '<p></p><div>For further information please refer to the <a href="#" onclick="appdb.utils.ToggleFaq(11);" >FAQ</a></div>';
			RETURN '{"valid": false, "error": "' || err || '", "reason": ' || to_json(reason) || '}';
		END IF;
	END IF;

	SELECT validate_app_cname($1, $2) INTO p;
	IF NOT p IS NULL THEN
		err := 'Invalid cname';
		reason := 'Name already taken by <a href="http://' || (SELECT data FROM config WHERE var = 'ui-host') || '/?p=' || encode(decode('/apps/details?id=s:' || p, 'escape'), 'base64') || '" target="_blank">' || p || '</a>.<p></p>';
		reason := reason || 'Please modify your applcation name. In order to avoid confusion from similarly named software, you should use a modifier in you software name in order to differentiate it from other related entries. Good examples would be :</div>';
		reason := reason || '<div><span></span>' || $1 || '-&lt;Country&gt;</div>';
		reason := reason || '<div><span></span>' || $1 || '-&lt;Project&gt;</div>';
		reason := reason || '<div><span></span>' || $1 || '-&lt;Virtual Organization&gt;</div>';
		reason := reason || '<div><span></span>' || $1 || '-&lt;Consortium&gt;</div>';
		reason := reason || '<div>etc...</div>';
		reason := reason || '<p></p><div>For further information please refer to the <a href="#" onclick="appdb.utils.ToggleFaq(11);" >FAQ</a></div>';
		RETURN '{"valid": false, "error": "' || err || '", "reason": ' || to_json(reason) || '}';
	END IF;

	IF EXISTS (SELECT 1 FROM applications WHERE (name ILIKE '%' || $1 || '%') AND (NOT deleted) AND (($2 IS NULL) OR ((NOT $2 IS NULL) AND (id <> $2)))) THEN
		p := encode(DECODE('{"url":"/apps","query":{"flt":"name:' || to_json($1) || '"},"ext":{"mainTitle":"Software","prepend":[],"append":false,"componentType":"appdb.components.Applications","filterDisplay":"Search...","isList":true,"componentArgs":[{"flt":"name:' || to_json($1) || '"}]}}', 'escape'), 'base64');
		reason := 'There are software items containing &#39;<i><b>' || $1 || '</b></i>&#39;. Click <a href="http://' || (SELECT data FROM config WHERE var = 'ui-host') || '?p=' || p || '" target="_blank">here</a> to view them in a new window.<p></p>';
		reason := reason || '<div>In order to avoid confusion from similarly named software, we suggest you use a modifier in your software name in order to differentiate it from other related entries if this applies. <p></p>Good examples would be :</div>';
		reason := reason || '<div  ><span>  </span>' || $1 || '-&lt;Country&gt;</div>';
		reason := reason || '<div ><span>  </span>' || $1 || '-&lt;Project&gt;</div>';
		reason := reason || '<div ><span>  </span>' || $1 || '-&lt;Virtual Organization&gt;</div>';
		reason := reason || '<div ><span>  </span>' || $1 || '-&lt;Consortium&gt;</div>';
		reason := reason || '<div>etc...</div>';
		reason := reason || '<p></p><div>For further information please refer to the <a href="#" onclick="appdb.utils.ToggleFaq(11);" >FAQ</a></div>';
		RETURN '{"valid": true, "reason": ' || to_json(reason) || '}';
	END IF;

	RETURN '{"valid": true}';
END;
$$ LANGUAGE plpgsql STABLE;
ALTER FUNCTION validate_app_name(text, int) OWNER TO appdb;

CREATE OR REPLACE FUNCTION public.rankapp(
    m_id applications,
    m_query text)
  RETURNS integer AS
$BODY$
DECLARE rank INT;
DECLARE lrank INT;
DECLARE args TEXT[];
DECLARE arg TEXT;
DECLARE phrase TEXT;
DECLARE fields TEXT[];
DECLARE field TEXT;
DECLARE vals TEXT[];
DECLARE val TEXT;
DECLARE ops TEXT[];
DECLARE tmp TEXT[];
DECLARE i INT;
DECLARE j INT;
DECLARE kk INT;
DECLARE l INT;
DECLARE k TEXT;
DECLARE r RECORD;
DECLARE m_disciplines TEXT[];
DECLARE m_categories TEXT[];
DECLARE m_phrase_bonus INT;
DECLARE do_phrase_search BOOLEAN;
BEGIN
	do_phrase_search := TRUE;
	IF m_query IS NULL OR TRIM(m_query) = '' THEN RETURN 0; END IF;
	m_query := fltstr_nbs(m_query);
	SELECT array_agg(DISTINCT disciplines.name) FROM disciplines WHERE disciplines.id = ANY((m_id::applications).disciplineid) INTO m_disciplines;
	SELECT array_agg(DISTINCT categories.name) FROM categories WHERE categories.id = ANY((m_id::applications).categoryid) INTO m_categories;
	fields = '{name, description, abstract, tag, disciplinename, subdisciplinename, categoryname}'::TEXT[];
	rank := 0;
	args := string_to_array(m_query, ' ');
	args := array_append(args, '__PHRASE_BONUS__');
	FOR i IN 1..array_length(args, 1) LOOP
		ops := NULL::TEXT[];
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
				do_phrase_search := FALSE;
				ops := array_append(ops, SUBSTRING(arg,1,1));
				arg := SUBSTRING(arg,2);
			ELSE
				EXIT;
			END IF;
		END LOOP;
		IF SUBSTRING(arg,1,13) = 'category.name' THEN arg := 'application.categoryname' || SUBSTRING(arg,14); END IF;
		IF SUBSTRING(arg,1,12) = 'category.any' THEN arg := 'application.categoryname' || SUBSTRING(arg,13); END IF;
		IF SUBSTRING(arg,1,15) = 'discipline.name' THEN arg := 'application.disciplinename' || SUBSTRING(arg,16); END IF;
		IF SUBSTRING(arg,1,14) = 'discipline.any' THEN arg := 'application.disciplinename' || SUBSTRING(arg,15); END IF;
		IF SUBSTRING(arg,1,18) = 'subdiscipline.name' THEN arg := 'application.subdisciplinename' || SUBSTRING(arg,19); END IF;
		IF SUBSTRING(arg,1,17) = 'subdiscipline.any' THEN arg := 'application.subdisciplinename' || SUBSTRING(arg,18); END IF;
		IF NOT (SUBSTRING(arg,1,12) = 'application.' OR SUBSTRING(arg,1,4) = 'any.' OR instr(arg,'.') = 0) THEN CONTINUE; END IF;
		IF SUBSTR(arg,1,12) = 'application.' THEN arg = SUBSTRING(arg,13);
		ELSIF SUBSTR(arg,1,4) = 'any.' THEN arg = SUBSTRING(arg,5); END IF;
		tmp := string_to_array(arg, ':');
		field := NULL;
		IF array_length(tmp, 1) > 1 THEN
			IF tmp[1] <> 'any' THEN
				field := tmp[1];
				do_phrase_search := FALSE;
			END IF;
			val := '';
			FOR j IN 2..array_length(tmp, 1) LOOP
				val := val || tmp[j];
			END LOOP;
		ELSE
			val := tmp[1];
		END IF;
		IF NOT val IS NULL THEN
			IF (val = '__PHRASE_BONUS__') AND (do_phrase_search IS TRUE) THEN
				val := array_to_string(args, ' ');
				val := REPLACE(val, ' __PHRASE_BONUS__', '');
				m_phrase_bonus := 8 * ((LENGTH(REGEXP_REPLACE(val, '[^ ]', '', 'g')))+1);
			ELSIF (val = '__PHRASE_BONUS__') AND (do_phrase_search IS FALSE) THEN
				CONTINUE;
			ELSE
				m_phrase_bonus := 1;
			END IF;
			FOR j IN 1..array_length(fields, 1) LOOP
				-- TODO: re-write this IF statement to properly handle all cases of ops
				IF (ops IS NULL) OR (ops = ARRAY['+']) OR (ops = ARRAY['=']) OR (ops = ARRAY['=','+']) OR (ops = ARRAY['+','=']) THEN
					-- LOOP OVER EXACT AND PARTIAL MATCHES (BONUS FOR EXACT)
					vals = ('{' || val || ', %' || val || '%}')::TEXT[];
					FOR kk IN 1..array_length(vals, 1) LOOP
						k := vals[kk];
						lrank := 0;
						IF fields[j] = 'name' THEN IF m_id.name ILIKE k THEN lrank := lrank + 6; END IF; END IF;
						IF fields[j] = 'description' THEN IF m_id.description ILIKE k THEN lrank := lrank + 4 + (word_count(m_id.description, val)-1); END IF; END IF;
						IF fields[j] = 'tag' THEN IF k ~~~@ ANY(keywords(m_id)) THEN lrank := lrank + 4; END IF; END IF;
						IF fields[j] = 'abstract' THEN IF m_id.abstract ILIKE k THEN lrank := lrank + 1 + (word_count(m_id.abstract, val)-1); END IF; END IF;
						IF fields[j] = 'id' THEN IF m_id.id::TEXT ILIKE k THEN lrank := lrank + 1; END IF; END IF;
						IF fields[j] = 'categoryname' AND NOT m_categories IS NULL THEN
							FOR l IN 1..array_length(m_categories, 1) LOOP
								IF m_categories[l] ILIKE k THEN lrank := lrank + 2; END IF;
							END LOOP;
						END IF;
						IF fields[j] = 'disciplinename' AND NOT m_disciplines IS NULL THEN
							FOR l IN 1..array_length(m_disciplines, 1) LOOP
								IF m_disciplines[l] ILIKE k THEN lrank := lrank + 10; END IF;
							END LOOP;
						END IF;
						-- BONUS FOR SPECIFIC FIELD
						IF fields[j] = field THEN lrank = lrank * 3; END IF;
						lrank := lrank * m_phrase_bonus;
						rank := rank + lrank;
					END LOOP;
				END IF;
			END LOOP;
		END IF;
	END LOOP;
	RETURN rank;
END
$BODY$
  LANGUAGE plpgsql STABLE
  COST 100;
ALTER FUNCTION public.rankapp(applications, text)
  OWNER TO appdb;

CREATE OR REPLACE FUNCTION appdb_ns_array() RETURNS TEXT[] AS
$$
SELECT
ARRAY[
	'appdb',
	'application',
	'category',
	'classification',
	'contextualization',
	'dataset',
	'discipline',
	'dissemination',
	'entity',
	'filter',
	'history',
	'license',
	'logistics',
	'middleware',
	'organization',
	'permission',
	'person',
	'privilege',
	'project',
	'provider',
	'provider_template',
	'publication',
	'rating',
	'ratingreport',
	'regional',
	'resource',
	'site',
	'siteservice',
	'user',
	'virtualization',
	'vo'
];
$$
LANGUAGE SQL IMMUTABLE;
ALTER FUNCTION appdb_ns_array() OWNER TO appdb;

CREATE OR REPLACE FUNCTION public.appdb_xmlns()
 RETURNS text
 LANGUAGE sql
 IMMUTABLE
AS $function$
SELECT
        array_to_string(array_agg('xmlns:' || x || '="http://appdb.egi.eu/api/1.0/' || x ||'"'), ' ') AS xmlns
FROM UNNEST(appdb_ns_array()) AS x
$function$;
ALTER FUNCTION appdb_xmlns() OWNER TO appdb;

CREATE OR REPLACE FUNCTION public.appdb_xpathns()
 RETURNS text[]
 LANGUAGE sql
 IMMUTABLE
AS $function$
SELECT (
	SELECT ARRAY[
	  ARRAY['xs', 'http://www.w3.org/2001/XMLSchema'],
	  ARRAY['xsi', 'http://www.w3.org/2001/XMLSchema-instance']
	]
) || (
	SELECT ARRAY_AGG(ARRAY[x, 'http://appdb.egi.eu/api/1.0/' || x])
	FROM UNNEST(appdb_ns_array()) AS x
);
$function$;
ALTER FUNCTION appdb_xpathns() OWNER TO appdb;

DROP FUNCTION IF EXISTS consume_app(text, int, int);
CREATE OR REPLACE FUNCTION consume_app(xml_in text, http_method int, userid int) RETURNS TEXT AS
$$
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
		xtmp := xpath('//application:application', $1::XML, appdb_xpathns());
	EXCEPTION
		WHEN OTHERS THEN
		RAISE EXCEPTION 'APPDB_REST_API_ERROR %', RE_INVALID_REPRESENTATION;
	END;
	IF ARRAY_LENGTH(xtmp, 1) = 0 THEN
		RAISE NOTICE 'EXCEPTION1';
		RAISE EXCEPTION 'APPDB_REST_API_ERROR %', RE_INVALID_REPRESENTATION;
	END IF;

	xapp := xtmp[1];

	IF xpath_exists('/@metatype', xapp, appdb_xpathns()) THEN
		_metatype := COALESCE((((xpath('/@metatype', xapp, appdb_xpathns()))[1])::TEXT)::INT, 0);
	END IF;

	IF ($2 = RM_PUT AND ((NOT xpath_exists('./application:discipline', xapp, appdb_xpathns())) OR (NOT xpath_exists('application:category', xapp, appdb_xpathns())))) THEN
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
				((xpath('./application:name/text()', xapp, appdb_xpathns()))[1])::TEXT,
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
				COALESCE(((xpath('./application:name/text()', xapp, appdb_xpathns()))[1])::TEXT, a.name)
			ELSE
				a.name
			END ,
			description = CASE WHEN EXISTS (SELECT 1 FROM permissions WHERE actor = userguid AND actionid = 6 AND ((object IS NULL) OR object = a.guid)) THEN
				COALESCE(((xpath('./application:description/text()', xapp, appdb_xpathns()))[1])::TEXT, a.description)
			ELSE
				a.description
			END,
			abstract = CASE WHEN EXISTS (SELECT 1 FROM permissions WHERE actor = userguid AND actionid = 7 AND ((object IS NULL) OR object = a.guid)) THEN
				COALESCE(((xpath('./application:abstract/text()', xapp, appdb_xpathns()))[1])::TEXT, a.abstract)
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
				COALESCE((((xpath('/@tagPolicy', xapp, appdb_xpathns()))[1])::TEXT)::INT, a.tagpolicy)
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
				((xpath('./application:name/text()', xapp, appdb_xpathns()))[1])::TEXT,
				'-DELETED-.{8}-.{4}-.{4}-.{4}-.{12}',
				''
			))::JSON;
			IF NOT (jtmp->>'valid')::BOOLEAN THEN
				RAISE EXCEPTION 'APPDB_REST_API_ERROR %, %', RE_BACKEND_ERROR, jtmp->>'reason';
			END IF;
			INSERT INTO applications (name, description, abstract, statusid, dateadded, addedby, tool, tagpolicy, metatype, "owner", cname)
			VALUES (
				(xpath('./application:name/text()', xapp, appdb_xpathns()))[1],
				(xpath('./application:description/text()', xapp, appdb_xpathns()))[1],
				(xpath('./application:abstract/text()', xapp, appdb_xpathns()))[1],
				COALESCE((((xpath('./application:status/@id', xapp, appdb_xpathns()))[1])::TEXT)::INT, 6),
				NOW(),
				$3,
				COALESCE((((xpath('./@tool', xapp, appdb_xpathns()))[1])::TEXT)::BOOLEAN, FALSE),
				COALESCE((((xpath('/@tagPolicy', xapp, appdb_xpathns()))[1])::TEXT)::INT, 2),
				CASE WHEN _metatype IN (0,1,2) THEN _metatype ELSE 0 END,
				COALESCE((((xpath('./application:owner/@id', xapp, appdb_xpathns()))[1])::TEXT)::INT, $3),
				(xpath('/@cname', xapp, appdb_xpathns()))[1]
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
			WHERE (appid = _appid) AND (NOT tag = ANY(xtmp::TEXT[]));

			INSERT INTO __app_tags (appid, researcherid, tag)
			SELECT _appid, $3, xtag
			FROM UNNEST(xtmp::TEXT[]) AS xtag
			WHERE (NOT xtag IS NULL) AND (NOT EXISTS (SELECT 1 FROM app_tags WHERE (appid = _appid) AND (xtag = tag)));
		END IF;
	END IF;

	IF EXISTS (SELECT 1 FROM permissions WHERE actor = userguid AND actionid = 26 AND ((object IS NULL) OR object = _appguid)) THEN
		IF xpath_exists('./application:category', xapp, appdb_xpathns()) THEN
			xtmp := xpath('./application:category/@id', xapp, appdb_xpathns());

			DELETE FROM appcategories
			WHERE (appid = _appid) AND (NOT categoryid = ANY((xtmp::TEXT[])::INT[]));

			INSERT INTO appcategories (appid, categoryid)
			SELECT _appid, xcat::INT
			FROM UNNEST(xtmp::TEXT[]) AS xcat
			WHERE (NOT xcat IS NULL) AND (NOT EXISTS (SELECT 1 FROM appcategories WHERE (appid = _appid) AND (categoryid = xcat::INT)));

			x := (xpath('./application:category[@primary="true"]/@id', xapp, appdb_xpathns()))[1];
			IF NOT x IS NULL THEN
				UPDATE appcategories SET isprimary = TRUE WHERE appid = _appid AND categoryid = (x::TEXT)::INT;
			END IF;
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
					((xpath('./@type', xu, appdb_xpathns()))[1])::TEXT AS description,
					((xpath('./@title', xu, appdb_xpathns()))[1])::TEXT AS title,
					((xpath('./text()', xu, appdb_xpathns()))[1])::TEXT AS url
				FROM UNNEST(xtmp) AS xu
			)
			DELETE FROM app_urls au
			WHERE (au.appid = _appid) AND (NOT EXISTS (SELECT 1 FROM xurls WHERE NOT ((au.title, au.description, au.url) IS DISTINCT FROM (xurls.title, xurls.description, xurls.url))));

			WITH xurls AS (
				SELECT
					((xpath('./@type', xu, appdb_xpathns()))[1])::TEXT AS description,
					((xpath('./@title', xu, appdb_xpathns()))[1])::TEXT AS title,
					((xpath('./text()', xu, appdb_xpathns()))[1])::TEXT AS url
				FROM UNNEST(xtmp) AS xu
			)
			INSERT INTO app_urls (appid, title, description, url)
			SELECT _appid, xurls.title, xurls.description, xurls.url
			FROM xurls
			WHERE (NOT xurls.url IS NULL) AND (NOT EXISTS (SELECT 1 FROM app_urls au WHERE (au.appid = _appid) AND NOT ((au.title, au.description, au.url) IS DISTINCT FROM (xurls.title, xurls. description, xurls.url))));
		END IF;
	END IF;

	-- KEEP IN PHP: logo, publications, relations (?)

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
							("comment" = ((xpath('./text()', xtmp[i], appdb_xpathns()))[1])::TEXT) AND
							(link = ((xpath('./@link', xtmp[i], appdb_xpathns()))[1])::TEXT)
					);
				END LOOP;
				FOR i IN 1..ARRAY_LENGTH(xtmp, 1) LOOP
					INSERT INTO app_middlewares (appid, middlewareid, "comment", link)
					VALUES (
						_appid,
						5,
						((xpath('./@comment', xtmp[i], appdb_xpathns()))[1])::TEXT,
						((xpath('./@link', xtmp[i], appdb_xpathns()))[1])::TEXT
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
									(xpath('./@type', xt, appdb_xpathns()))[1]::TEXT AS "type",
									(xpath('./@note', xt, appdb_xpathns()))[1]::TEXT AS "note",
									(xpath('./@id', xt, appdb_xpathns()))[1]::TEXT AS "itemid",
									(xpath('./text()', xt, appdb_xpathns()))[1]::TEXT AS "item"
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
									(xpath('./@type', xt, appdb_xpathns()))[1]::TEXT AS "type",
									(xpath('./@note', xt, appdb_xpathns()))[1]::TEXT AS "note",
									(xpath('./@id', xt, appdb_xpathns()))[1]::TEXT AS "itemid",
									(xpath('./text()', xt, appdb_xpathns()))[1]::TEXT AS "item"
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
									(xpath('./@type', xt, appdb_xpathns()))[1]::TEXT AS "type",
									(xpath('./@note', xt, appdb_xpathns()))[1]::TEXT AS "note",
									(xpath('./@id', xt, appdb_xpathns()))[1]::TEXT AS "itemid",
									(xpath('./text()', xt, appdb_xpathns()))[1]::TEXT AS "item"
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
									(xpath('./@type', xt, appdb_xpathns()))[1]::TEXT AS "type",
									(xpath('./@note', xt, appdb_xpathns()))[1]::TEXT AS "note",
									(xpath('./@id', xt, appdb_xpathns()))[1]::TEXT AS "itemid",
									(xpath('./text()', xt, appdb_xpathns()))[1]::TEXT AS "item"
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
									(xpath('./@type', xt, appdb_xpathns()))[1]::TEXT AS "type",
									(xpath('./@note', xt, appdb_xpathns()))[1]::TEXT AS "note",
									(xpath('./@id', xt, appdb_xpathns()))[1]::TEXT AS "itemid",
									(xpath('./text()', xt, appdb_xpathns()))[1]::TEXT AS "item"
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
									(xpath('./@type', xt, appdb_xpathns()))[1]::TEXT AS "type",
									(xpath('./@note', xt, appdb_xpathns()))[1]::TEXT AS "note",
									(xpath('./text()', xt, appdb_xpathns()))[1]::TEXT AS "item"
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
	END IF;

	RETURN '{"id": ' || _appid::TEXT || ', "guid": "' || (SELECT guid::TEXT FROM applications WHERE id = _appid) || '"}';
	--RETURN app_to_xml_ext(_appid);
END;
$$ LANGUAGE plpgsql VOLATILE;
ALTER FUNCTION consume_app(text, int, int) OWNER TO appdb;

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 21, 0, E'Move app XML parsing code and logic from web server to data server'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=21 AND revision=0);

COMMIT;
