--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET lock_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

--
-- Name: app_licenses; Type: SCHEMA; Schema: -; Owner: appdb
--

CREATE SCHEMA app_licenses;


ALTER SCHEMA app_licenses OWNER TO appdb;

--
-- Name: app_middlewares; Type: SCHEMA; Schema: -; Owner: appdb
--

CREATE SCHEMA app_middlewares;


ALTER SCHEMA app_middlewares OWNER TO appdb;

--
-- Name: appcountries; Type: SCHEMA; Schema: -; Owner: appdb
--

CREATE SCHEMA appcountries;


ALTER SCHEMA appcountries OWNER TO appdb;

--
-- Name: applications; Type: SCHEMA; Schema: -; Owner: appdb
--

CREATE SCHEMA applications;


ALTER SCHEMA applications OWNER TO appdb;

--
-- Name: archs; Type: SCHEMA; Schema: -; Owner: appdb
--

CREATE SCHEMA archs;


ALTER SCHEMA archs OWNER TO appdb;

--
-- Name: cache; Type: SCHEMA; Schema: -; Owner: appdb
--

CREATE SCHEMA cache;


ALTER SCHEMA cache OWNER TO appdb;

--
-- Name: categories; Type: SCHEMA; Schema: -; Owner: appdb
--

CREATE SCHEMA categories;


ALTER SCHEMA categories OWNER TO appdb;

--
-- Name: contacts; Type: SCHEMA; Schema: -; Owner: appdb
--

CREATE SCHEMA contacts;


ALTER SCHEMA contacts OWNER TO appdb;

--
-- Name: contacttypes; Type: SCHEMA; Schema: -; Owner: appdb
--

CREATE SCHEMA contacttypes;


ALTER SCHEMA contacttypes OWNER TO appdb;

--
-- Name: countries; Type: SCHEMA; Schema: -; Owner: appdb
--

CREATE SCHEMA countries;


ALTER SCHEMA countries OWNER TO appdb;

--
-- Name: disciplines; Type: SCHEMA; Schema: -; Owner: appdb
--

CREATE SCHEMA disciplines;


ALTER SCHEMA disciplines OWNER TO appdb;

--
-- Name: ebiperun; Type: SCHEMA; Schema: -; Owner: appdb
--

CREATE SCHEMA ebiperun;


ALTER SCHEMA ebiperun OWNER TO appdb;

--
-- Name: egiops; Type: SCHEMA; Schema: -; Owner: appdb
--

CREATE SCHEMA egiops;


ALTER SCHEMA egiops OWNER TO appdb;

--
-- Name: elixir; Type: SCHEMA; Schema: -; Owner: appdb
--

CREATE SCHEMA elixir;


ALTER SCHEMA elixir OWNER TO appdb;

--
-- Name: gocdb; Type: SCHEMA; Schema: -; Owner: appdb
--

CREATE SCHEMA gocdb;


ALTER SCHEMA gocdb OWNER TO appdb;

--
-- Name: harvest; Type: SCHEMA; Schema: -; Owner: appdb
--

CREATE SCHEMA harvest;


ALTER SCHEMA harvest OWNER TO appdb;

--
-- Name: harvester; Type: SCHEMA; Schema: -; Owner: appdb
--

CREATE SCHEMA harvester;


ALTER SCHEMA harvester OWNER TO appdb;

--
-- Name: licenses; Type: SCHEMA; Schema: -; Owner: appdb
--

CREATE SCHEMA licenses;


ALTER SCHEMA licenses OWNER TO appdb;

--
-- Name: middlewares; Type: SCHEMA; Schema: -; Owner: appdb
--

CREATE SCHEMA middlewares;


ALTER SCHEMA middlewares OWNER TO appdb;

--
-- Name: oses; Type: SCHEMA; Schema: -; Owner: appdb
--

CREATE SCHEMA oses;


ALTER SCHEMA oses OWNER TO appdb;

--
-- Name: perun; Type: SCHEMA; Schema: -; Owner: appdb
--

CREATE SCHEMA perun;


ALTER SCHEMA perun OWNER TO appdb;

--
-- Name: positiontypes; Type: SCHEMA; Schema: -; Owner: appdb
--

CREATE SCHEMA positiontypes;


ALTER SCHEMA positiontypes OWNER TO appdb;

--
-- Name: proglangs; Type: SCHEMA; Schema: -; Owner: appdb
--

CREATE SCHEMA proglangs;


ALTER SCHEMA proglangs OWNER TO appdb;

--
-- Name: researchers; Type: SCHEMA; Schema: -; Owner: appdb
--

CREATE SCHEMA researchers;


ALTER SCHEMA researchers OWNER TO appdb;

--
-- Name: sci_class; Type: SCHEMA; Schema: -; Owner: appdb
--

CREATE SCHEMA sci_class;


ALTER SCHEMA sci_class OWNER TO appdb;

--
-- Name: sites; Type: SCHEMA; Schema: -; Owner: appdb
--

CREATE SCHEMA sites;


ALTER SCHEMA sites OWNER TO appdb;

--
-- Name: stats; Type: SCHEMA; Schema: -; Owner: appdb
--

CREATE SCHEMA stats;


ALTER SCHEMA stats OWNER TO appdb;

--
-- Name: statuses; Type: SCHEMA; Schema: -; Owner: appdb
--

CREATE SCHEMA statuses;


ALTER SCHEMA statuses OWNER TO appdb;

--
-- Name: vos; Type: SCHEMA; Schema: -; Owner: appdb
--

CREATE SCHEMA vos;


ALTER SCHEMA vos OWNER TO appdb;

--
-- Name: plpgsql; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;


--
-- Name: EXTENSION plpgsql; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';


--
-- Name: pg_trgm; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS pg_trgm WITH SCHEMA public;


--
-- Name: EXTENSION pg_trgm; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION pg_trgm IS 'text similarity measurement and index searching based on trigrams';


SET search_path = public, pg_catalog;

--
-- Name: e_access_token_types; Type: TYPE; Schema: public; Owner: appdb
--

CREATE TYPE e_access_token_types AS ENUM (
    'personal',
    'application'
);


ALTER TYPE e_access_token_types OWNER TO appdb;

--
-- Name: e_account_type; Type: TYPE; Schema: public; Owner: appdb
--

CREATE TYPE e_account_type AS ENUM (
    'egi-sso-ldap',
    'x509',
    'facebook',
    'linkedin',
    'twitter',
    'google',
    'dev-env',
    'edugain',
    'elixir'
);


ALTER TYPE e_account_type OWNER TO appdb;

--
-- Name: e_dataset_category; Type: TYPE; Schema: public; Owner: wvkarag
--

CREATE TYPE e_dataset_category AS ENUM (
    'Life Sciences'
);


ALTER TYPE e_dataset_category OWNER TO wvkarag;

--
-- Name: e_entity; Type: TYPE; Schema: public; Owner: appdb
--

CREATE TYPE e_entity AS ENUM (
    'software',
    'vappliance',
    'swappliance',
    'person',
    'vo',
    'organization',
    'project',
    'publication'
);


ALTER TYPE e_entity OWNER TO appdb;

--
-- Name: e_hashfuncs; Type: TYPE; Schema: public; Owner: appdb
--

CREATE TYPE e_hashfuncs AS ENUM (
    'sha512',
    'sha384',
    'sha256',
    'sha224',
    'sha1',
    'md5'
);


ALTER TYPE e_hashfuncs OWNER TO appdb;

--
-- Name: e_hypervisors; Type: TYPE; Schema: public; Owner: appdb
--

CREATE TYPE e_hypervisors AS ENUM (
    'KVM',
    'QEMU',
    'QEMU-KVM',
    'Xen',
    'VirtualBox',
    'Hyper-V',
    'VMWare',
    'VirtualPC',
    'Chroot',
    'Google'
);


ALTER TYPE e_hypervisors OWNER TO appdb;

--
-- Name: e_vmiformats; Type: TYPE; Schema: public; Owner: appdb
--

CREATE TYPE e_vmiformats AS ENUM (
    'raw',
    'iso',
    'qcow',
    'qcow2',
    'vmdk',
    'vhd',
    'vdi'
);


ALTER TYPE e_vmiformats OWNER TO appdb;

--
-- Name: e_vowide_image_state; Type: TYPE; Schema: public; Owner: appdb
--

CREATE TYPE e_vowide_image_state AS ENUM (
    'draft',
    'published',
    'obsolete',
    'deleted',
    'unknown',
    'up-to-date'
);


ALTER TYPE e_vowide_image_state OWNER TO appdb;

--
-- Name: namedobjecttype; Type: TYPE; Schema: public; Owner: appdb
--

CREATE TYPE namedobjecttype AS ENUM (
    'app',
    'ppl',
    'doc',
    'cmm',
    'req'
);


ALTER TYPE namedobjecttype OWNER TO appdb;

SET search_path = sci_class, pg_catalog;

--
-- Name: e_version_state; Type: TYPE; Schema: sci_class; Owner: appdb
--

CREATE TYPE e_version_state AS ENUM (
    'stable',
    'under-devel',
    'archived'
);


ALTER TYPE e_version_state OWNER TO appdb;

SET search_path = app_licenses, pg_catalog;

--
-- Name: any(integer); Type: FUNCTION; Schema: app_licenses; Owner: appdb
--

CREATE FUNCTION "any"(mid integer) RETURNS text
    LANGUAGE sql STABLE
    AS $_$SELECT public.any('app_licenses', $1);$_$;


ALTER FUNCTION app_licenses."any"(mid integer) OWNER TO appdb;

SET search_path = app_middlewares, pg_catalog;

--
-- Name: any(integer); Type: FUNCTION; Schema: app_middlewares; Owner: appdb
--

CREATE FUNCTION "any"(mid integer) RETURNS text
    LANGUAGE sql STABLE
    AS $_$SELECT public.any('app_middlewares', $1);$_$;


ALTER FUNCTION app_middlewares."any"(mid integer) OWNER TO appdb;

SET search_path = appcountries, pg_catalog;

--
-- Name: any(integer); Type: FUNCTION; Schema: appcountries; Owner: appdb
--

CREATE FUNCTION "any"(mid integer) RETURNS text
    LANGUAGE sql STABLE
    AS $_$SELECT public.any('appcountries', $1);$_$;


ALTER FUNCTION appcountries."any"(mid integer) OWNER TO appdb;

SET search_path = applications, pg_catalog;

--
-- Name: any(integer); Type: FUNCTION; Schema: applications; Owner: appdb
--

CREATE FUNCTION "any"(mid integer) RETURNS text
    LANGUAGE sql STABLE
    AS $_$SELECT public.any('applications', $1);$_$;


ALTER FUNCTION applications."any"(mid integer) OWNER TO appdb;

--
-- Name: substring(text, integer, integer); Type: FUNCTION; Schema: applications; Owner: appdb
--

CREATE FUNCTION "substring"(text, integer, integer) RETURNS text
    LANGUAGE sql IMMUTABLE
    AS $_$SELECT SUBSTRING($1, $2, $3);$_$;


ALTER FUNCTION applications."substring"(text, integer, integer) OWNER TO appdb;

SET search_path = archs, pg_catalog;

--
-- Name: any(integer); Type: FUNCTION; Schema: archs; Owner: appdb
--

CREATE FUNCTION "any"(mid integer) RETURNS text
    LANGUAGE sql STABLE
    AS $_$SELECT public.any('archs', $1);$_$;


ALTER FUNCTION archs."any"(mid integer) OWNER TO appdb;

SET search_path = cache, pg_catalog;

--
-- Name: trfn_filtercache(); Type: FUNCTION; Schema: cache; Owner: appdb
--

CREATE FUNCTION trfn_filtercache() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
	IF TG_OP = 'DELETE' THEN
		IF TG_WHEN = 'AFTER' THEN
			EXECUTE 'DROP TABLE IF EXISTS cache.filtercache_' || OLD.hash;
			RETURN OLD;
		END IF;
	END IF;
END;
$$;


ALTER FUNCTION cache.trfn_filtercache() OWNER TO appdb;

SET search_path = categories, pg_catalog;

--
-- Name: any(integer); Type: FUNCTION; Schema: categories; Owner: appdb
--

CREATE FUNCTION "any"(mid integer) RETURNS text
    LANGUAGE sql STABLE
    AS $_$SELECT public.any('categories', $1);$_$;


ALTER FUNCTION categories."any"(mid integer) OWNER TO appdb;

SET search_path = contacts, pg_catalog;

--
-- Name: any(integer); Type: FUNCTION; Schema: contacts; Owner: appdb
--

CREATE FUNCTION "any"(mid integer) RETURNS text
    LANGUAGE sql STABLE
    AS $_$SELECT public.any('contacts', $1);$_$;


ALTER FUNCTION contacts."any"(mid integer) OWNER TO appdb;

SET search_path = contacttypes, pg_catalog;

--
-- Name: any(integer); Type: FUNCTION; Schema: contacttypes; Owner: appdb
--

CREATE FUNCTION "any"(mid integer) RETURNS text
    LANGUAGE sql STABLE
    AS $_$SELECT public.any('contacttypes', $1);$_$;


ALTER FUNCTION contacttypes."any"(mid integer) OWNER TO appdb;

SET search_path = countries, pg_catalog;

--
-- Name: any(integer); Type: FUNCTION; Schema: countries; Owner: appdb
--

CREATE FUNCTION "any"(mid integer) RETURNS text
    LANGUAGE sql STABLE
    AS $_$SELECT public.any('countries', $1);$_$;


ALTER FUNCTION countries."any"(mid integer) OWNER TO appdb;

SET search_path = disciplines, pg_catalog;

--
-- Name: any(integer); Type: FUNCTION; Schema: disciplines; Owner: appdb
--

CREATE FUNCTION "any"(mid integer) RETURNS text
    LANGUAGE sql STABLE
    AS $_$SELECT public.any('disciplines', $1);$_$;


ALTER FUNCTION disciplines."any"(mid integer) OWNER TO appdb;

SET search_path = elixir, pg_catalog;

--
-- Name: discipline_topics_to_xml(); Type: FUNCTION; Schema: elixir; Owner: appdb
--

CREATE FUNCTION discipline_topics_to_xml() RETURNS SETOF xml
    LANGUAGE sql STABLE
    AS $$
SELECT XMLELEMENT(NAME "map", 
	XMLELEMENT(NAME "topic", XMLATTRIBUTES(topic_id AS id) , XMLELEMENT(NAME "uri", topic_uri), XMLELEMENT(NAME "label", topic_label) ),
	XMLELEMENT(NAME "discipline", XMLATTRIBUTES(disciplines.id AS id, disciplines.parentid as parentid ), XMLELEMENT(NAME "name", disciplines.name))
) FROM elixir.discipline_topics LEFT OUTER JOIN disciplines ON disciplines.id = elixir.discipline_topics.discipline_id 
GROUP BY elixir.discipline_topics.topic_id,elixir.discipline_topics.topic_uri,elixir.discipline_topics.topic_label,disciplines.id,disciplines.name,disciplines.parentid;
$$;


ALTER FUNCTION elixir.discipline_topics_to_xml() OWNER TO appdb;

SET search_path = gocdb, pg_catalog;

--
-- Name: trfn_gocdb_sites_create_uuid(); Type: FUNCTION; Schema: gocdb; Owner: appdb
--

CREATE FUNCTION trfn_gocdb_sites_create_uuid() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
	NEW.guid := uuid_generate_v5(uuid_namespace('ISO OID'::text), 'gocdb:sites:' || NEW.name);
	RETURN NEW;
END;
$$;


ALTER FUNCTION gocdb.trfn_gocdb_sites_create_uuid() OWNER TO appdb;

--
-- Name: trfn_gocdb_sites_update_fields(); Type: FUNCTION; Schema: gocdb; Owner: appdb
--

CREATE FUNCTION trfn_gocdb_sites_update_fields() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
	NEW.updatedon := now();
	RETURN NEW;
END;
$$;


ALTER FUNCTION gocdb.trfn_gocdb_sites_update_fields() OWNER TO appdb;

SET search_path = harvest, pg_catalog;

--
-- Name: search_local_records(text[], text[], integer, integer); Type: FUNCTION; Schema: harvest; Owner: appdb
--

CREATE FUNCTION search_local_records(keytext text[], keyfields text[], archiveid integer, maxitems integer) RETURNS SETOF text
    LANGUAGE plpgsql STABLE COST 1000
    AS $_$
DECLARE k text;
DECLARE f text[];
DECLARE l INT;
BEGIN
IF $1 IS NULL OR ARRAY_LENGTH($1, 1) = 0 THEN
	k := E'%';
ELSE
	k := E'%' || array_to_string($1, E'%') || E'%';
END IF;
IF $2 IS NULL OR ARRAY_LENGTH($2, 1) = 0 THEN
	f := (SELECT array_agg(DISTINCT harvest.raw_fields.name) FROM harvest.raw_fields);
ELSE
	f := keyfields;
END IF;
IF $4 IS NULL OR $4 <0 OR $4 = 0 THEN l := NULL; ELSE l := $4; END IF;
RETURN QUERY WITH rla AS (
SELECT DISTINCT(x.record_id), array_to_string(array_agg(x.value), E' ') AS value, array_to_string(array_agg(x.raw_field_name), E' ') AS field_names FROM
(SELECT l.* FROM harvest.records_local_additional l WHERE l.raw_field_name = ANY(f) ORDER BY l.record_id, l.pos) AS x WHERE x.archive_id = $3 GROUP BY x.record_id )
SELECT DISTINCT(r.record_id)::TEXT FROM harvest.records_local_additional as r INNER JOIN rla ON rla.record_id = r.record_id WHERE rla.value ilike k GROUP BY r.record_id LIMIT l;
END;
$_$;


ALTER FUNCTION harvest.search_local_records(keytext text[], keyfields text[], archiveid integer, maxitems integer) OWNER TO appdb;

--
-- Name: search_local_records_to_xml(text[], text[], integer, integer); Type: FUNCTION; Schema: harvest; Owner: appdb
--

CREATE FUNCTION search_local_records_to_xml(keytext text[], keyfields text[], archiveid integer, maxitems integer) RETURNS SETOF xml
    LANGUAGE plpgsql STABLE COST 1000
    AS $_$
BEGIN
RETURN QUERY
SELECT
	XMLELEMENT(
		name "record",
		XMLATTRIBUTES(ra.record_id AS id, ra.record_id AS guid),
		xmlagg(
			XMLELEMENT(
				name "property",
				XMLATTRIBUTES(ra.name AS name),
				ra.value
			)
		)
	)
FROM harvest.records_local_additional AS ra
WHERE ra.record_id::TEXT IN (SELECT harvest.search_local_records($1::text[], $2::text[],$3::integer,$4::integer))
GROUP BY ra.record_id;
END;
$_$;


ALTER FUNCTION harvest.search_local_records_to_xml(keytext text[], keyfields text[], archiveid integer, maxitems integer) OWNER TO appdb;

--
-- Name: search_records(text[], text[], integer, integer); Type: FUNCTION; Schema: harvest; Owner: appdb
--

CREATE FUNCTION search_records(keytext text[], keyfields text[], archiveid integer, maxitems integer) RETURNS SETOF bigint
    LANGUAGE plpgsql STABLE COST 1000
    AS $_$
DECLARE k text[];
DECLARE f text[];
DECLARE l INT;
BEGIN
IF $1 IS NULL OR ARRAY_LENGTH($1, 1) = 0 THEN
	k := ARRAY['%']::text[];
ELSE
	k := keytext;
END IF;
IF $2 IS NULL OR ARRAY_LENGTH($2, 1) = 0 THEN
	f := (SELECT array_agg(DISTINCT harvest.raw_fields.name) FROM harvest.raw_fields);
ELSE
	f := keyfields;
END IF;
IF $4 IS NULL OR $4 <0 OR $4 = 0 THEN l := NULL; ELSE l := $4; END IF;
RETURN QUERY 
SELECT harvest.records.record_id 
	FROM harvest.records
	INNER JOIN harvest.search_record_ids AS srids ON srids.record_id = harvest.records.record_id 
	INNER JOIN harvest.raw_fields ON srids.raw_field_id = raw_fields.raw_field_id
	WHERE srids.keyword_text LIKE ANY (k) 
		AND srids.archive_id = $3 
		AND raw_fields.name = ANY(f) 
	GROUP BY harvest.records.record_id
	ORDER BY COUNT(harvest.records.record_id) DESC
	LIMIT l;
END;
$_$;


ALTER FUNCTION harvest.search_records(keytext text[], keyfields text[], archiveid integer, maxitems integer) OWNER TO appdb;

--
-- Name: search_records_test(text[], text[], integer, integer); Type: FUNCTION; Schema: harvest; Owner: appdb
--

CREATE FUNCTION search_records_test(keytext text[], keyfields text[], archiveid integer, maxitems integer) RETURNS TABLE(cnt bigint, id bigint, contents text)
    LANGUAGE plpgsql STABLE COST 1000
    AS $_$
DECLARE k text[];
DECLARE f text[];
DECLARE l INT;
BEGIN
IF $1 IS NULL OR ARRAY_LENGTH($1, 1) = 0 THEN
	k := ARRAY['%']::text[];
ELSE
	k := keytext;
END IF;
IF $2 IS NULL OR ARRAY_LENGTH($2, 1) = 0 THEN
	f := (SELECT array_agg(DISTINCT harvest.raw_fields.name) FROM harvest.raw_fields);
ELSE
	f := keyfields;
END IF;
IF $4 IS NULL OR $4 <0 OR $4 = 0 THEN l := NULL; ELSE l := $4; END IF;
RETURN QUERY 
SELECT COUNT(harvest.records.record_id) as cnt, harvest.records.record_id as id, 'content'::text as contents 
	FROM harvest.records
	INNER JOIN harvest.search_record_ids AS srids ON srids.record_id = harvest.records.record_id 
	INNER JOIN harvest.raw_fields ON srids.raw_field_id = raw_fields.raw_field_id
		WHERE srids.keyword_text LIKE ANY (k) 
		AND srids.archive_id = $3 
		AND raw_fields.name = ANY(f) 
	GROUP BY harvest.records.record_id
	ORDER BY COUNT(harvest.records.record_id) DESC
	LIMIT l;
END;
$_$;


ALTER FUNCTION harvest.search_records_test(keytext text[], keyfields text[], archiveid integer, maxitems integer) OWNER TO appdb;

--
-- Name: search_records_to_xml(text[], text[], integer, integer); Type: FUNCTION; Schema: harvest; Owner: appdb
--

CREATE FUNCTION search_records_to_xml(keytext text[], keyfields text[], archiveid integer, maxitems integer) RETURNS SETOF xml
    LANGUAGE plpgsql STABLE COST 1000
    AS $_$
BEGIN
RETURN QUERY 
SELECT 
	XMLELEMENT(
		name "record", 
		XMLATTRIBUTES(ra.record_id AS id, regs.guid AS guid), 
		xmlagg(
			XMLELEMENT(
				name "property", 
				XMLATTRIBUTES(ra.name AS name), 
				ra.value 
			)
		)
	)
FROM harvest.records_additional AS ra
-- INNER JOIN harvest.search_records($1::text[], $2::text[],$3::integer,$4::integer) AS sr ON ra.record_id = sr
LEFT OUTER JOIN harvest.registered_records AS regs ON (regs.record_id = ra.record_id AND regs.archive_id = $3)
WHERE ra.record_id IN (SELECT harvest.search_records($1::text[], $2::text[],$3::integer,$4::integer))
GROUP BY ra.record_id,regs.guid;
END;
$_$;


ALTER FUNCTION harvest.search_records_to_xml(keytext text[], keyfields text[], archiveid integer, maxitems integer) OWNER TO appdb;

SET search_path = licenses, pg_catalog;

--
-- Name: any(integer); Type: FUNCTION; Schema: licenses; Owner: appdb
--

CREATE FUNCTION "any"(mid integer) RETURNS text
    LANGUAGE sql STABLE
    AS $_$SELECT public.any('licenses', $1);$_$;


ALTER FUNCTION licenses."any"(mid integer) OWNER TO appdb;

SET search_path = middlewares, pg_catalog;

--
-- Name: any(integer); Type: FUNCTION; Schema: middlewares; Owner: appdb
--

CREATE FUNCTION "any"(mid integer) RETURNS text
    LANGUAGE sql STABLE
    AS $_$SELECT public.any('middlewares', $1);$_$;


ALTER FUNCTION middlewares."any"(mid integer) OWNER TO appdb;

SET search_path = oses, pg_catalog;

--
-- Name: any(integer); Type: FUNCTION; Schema: oses; Owner: appdb
--

CREATE FUNCTION "any"(mid integer) RETURNS text
    LANGUAGE sql STABLE
    AS $_$SELECT public.any('oses', $1) || ' ' || (SELECT os_families.name FROM os_families WHERE id = (SELECT os_family_id FROM oses WHERE id = $1));$_$;


ALTER FUNCTION oses."any"(mid integer) OWNER TO appdb;

SET search_path = positiontypes, pg_catalog;

--
-- Name: any(integer); Type: FUNCTION; Schema: positiontypes; Owner: appdb
--

CREATE FUNCTION "any"(mid integer) RETURNS text
    LANGUAGE sql STABLE
    AS $_$SELECT public.any('positiontypes', $1);$_$;


ALTER FUNCTION positiontypes."any"(mid integer) OWNER TO appdb;

SET search_path = proglangs, pg_catalog;

--
-- Name: any(integer); Type: FUNCTION; Schema: proglangs; Owner: appdb
--

CREATE FUNCTION "any"(mid integer) RETURNS text
    LANGUAGE sql STABLE
    AS $_$SELECT public.any('proglangs', $1);$_$;


ALTER FUNCTION proglangs."any"(mid integer) OWNER TO appdb;

SET search_path = public, pg_catalog;

--
-- Name: __app_to_xml(integer[]); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION __app_to_xml(m_id integer[]) RETURNS SETOF xml
    LANGUAGE plpgsql
    AS $_$
DECLARE m_categories TEXT;
DECLARE m_disciplines TEXT;
DECLARE m_urls TEXT;
DECLARE m_tags TEXT;
DECLARE m_va TEXT;
DECLARE m_va_hyper TEXT;
DECLARE m_va_os TEXT;
DECLARE m_va_arch TEXT;
DECLARE i INT;
DECLARE j INT;
DECLARE apps RECORD;
DECLARE myxml XML[];
DECLARE isprimarycat BOOL;
BEGIN
myxml := NULL::XML[];
FOR apps IN 
-- PREPARE q1 AS 
SELECT applications.*,
lastupdated BETWEEN NOW() - (SELECT data FROM config WHERE var='app_validation_period' LIMIT 1)::INTERVAL AND NOW() as "validated",
app_popularities.popularity,
array_agg(DISTINCT categories) as categories,
array_agg(DISTINCT appcategories) as appcategories,
array_agg(DISTINCT disciplines) as disciplines,
statuses.name as status_name,
array_agg(DISTINCT app_urls) as urls,
applications.keywords AS tags,
hitcounts.count as hitcount,
(array_agg(applogos.logo))[1] AS logo,
array_agg(DISTINCT vapp_versions) AS vapp_versions,
array_agg(DISTINCT vmiflavours.hypervisors::TEXT) AS vmihyper,
array_agg(DISTINCT vmiflavours.osid::TEXT) AS vmios,
array_agg(DISTINCT vmiflavours.archid::TEXT) AS vmiarch,
vapplications.imglst_private
FROM applications
LEFT OUTER JOIN appcategories ON appcategories.appid = applications.id
LEFT OUTER JOIN categories ON categories.id = appcategories.categoryid
LEFT OUTER JOIN appdisciplines ON appdisciplines.appid = applications.id
LEFT OUTER JOIN disciplines ON disciplines.id = appdisciplines.disciplineid
LEFT OUTER JOIN statuses ON statuses.id = applications.statusid
LEFT OUTER JOIN app_urls ON app_urls.appid = applications.id
LEFT OUTER JOIN hitcounts ON hitcounts.appid = applications.id
LEFT OUTER JOIN app_popularities ON app_popularities.appid = applications.id
LEFT OUTER JOIN applogos ON applogos.appid = applications.id
LEFT OUTER JOIN vapplications ON vapplications.appid = applications.id
LEFT OUTER JOIN vapp_versions ON vapp_versions.vappid = vapplications.id 
	AND published IS TRUE 
	AND enabled IS TRUE
	AND archived IS FALSE
	AND status = 'verified'
LEFT OUTER JOIN vmis ON vmis.vappid = vapplications.id
LEFT OUTER JOIN vmiflavours ON vmiflavours.vmiid = vmis.id
WHERE applications.id = ANY($1)
GROUP BY applications.id,
statuses.name,
hitcounts.count,
app_popularities.popularity,
imglst_private
ORDER BY idx($1, applications.id)
-- EXECUTE q1(ARRAY[787])
LOOP	
	/*IF NOT apps.tags[1] IS NULL THEN
		m_tags = '';
		FOR i IN 1..array_length(apps.tags, 1) LOOP
			m_tags := m_tags || xmlelement(name "application:tag",
				xmlattributes(false AS system), apps.tags[i]
			);
		END LOOP;Added helper function to return the count of sites supporting the images of a given vappliance id
	END IF; */
	IF apps.categories[1] IS NULL THEN
		m_categories = '<application:category xsi:nil="true" id="0" />';
	ELSE
		m_categories = '';
		FOR i IN 1..array_length(apps.categories, 1) LOOP
			isprimarycat := FALSE;
			FOR j IN 1..array_length(apps.appcategories, 1) LOOP
				IF (apps.appcategories[j]).categoryid = (apps.categories[i]).id THEN
					isprimarycat := (apps.appcategories[j]).isprimary;
					EXIT;
				END IF;
			END LOOP;
			m_categories := m_categories || xmlelement(name "application:category",
			xmlattributes(
				(apps.categories[i]).id AS "id",
				isprimarycat AS "primary",
				CASE (apps.categories[i]).parentid WHEN NULL THEN NULL ELSE (apps.categories[i]).parentid END AS "parentid"
			),
			(apps.categories[i]).name)::TEXT;
		END LOOP;
	END IF;
	IF apps.disciplines[1] IS NULL THEN
		m_disciplines = '<discipline:discipline xsi:nil="true" id="0" />';
	ELSE
		m_disciplines = '';
		FOR i IN 1..array_length(apps.disciplines, 1) LOOP
			IF NOT apps.disciplines[i] IS NULL THEN
				m_disciplines := m_disciplines || xmlelement(name "discipline:discipline",
				xmlattributes(
					(apps.disciplines[i]).id AS "id",
					(apps.disciplines[i]).parentid AS "parentid"
				),
				(apps.disciplines[i]).name)::TEXT;
			END IF;
		END LOOP;
	END IF;
	IF apps.urls[1] IS NULL THEN
		m_urls = '<application:url xsi:nil="true" />';
	ELSE
		m_urls = '';
		FOR i IN 1..array_length(apps.urls, 1) LOOP
			m_urls := m_urls || xmlelement(name "application:url",
			xmlattributes((apps.urls[i]).id AS "id",
			(apps.urls[i]).description AS "type",
			(apps.urls[i]).ord AS "ord",
			(apps.urls[i]).title AS "title"),
			(apps.urls[i]).url)::TEXT;
		END LOOP;
	END IF;
	IF NOT apps.vapp_versions[1] IS NULL THEN
		m_va = '';
		FOR i IN 1..array_length(apps.vapp_versions, 1) LOOP
			m_va_hyper = '';			
			FOR j IN 1..array_length(apps.vmihyper, 1) LOOP
				IF NOT apps.vmihyper[j] IS NULL THEN
					m_va_hyper := m_va_hyper || xmlelement(
						name "virtualization:hypervisor",						
						xmlattributes(
							array_to_string((SELECT array_agg(id::TEXT) FROM hypervisors WHERE name = ANY(apps.vmihyper[j]::TEXT[])), ',') AS id
						),						
						array_to_string(apps.vmihyper[j]::TEXT[], ',')
					);
				END IF;
			END LOOP;
			m_va_os = '';			
			FOR j IN 1..array_length(apps.vmios, 1) LOOP
				IF NOT apps.vmios[j] IS NULL THEN
					m_va_os := m_va_os || xmlelement(
						name "virtualization:os",
						xmlattributes(
							apps.vmios[j] AS id
						), (SELECT name FROM oses WHERE id = apps.vmios[j]::int)
					);
				END IF;
			END LOOP;
			m_va_arch = '';			
			FOR j IN 1..array_length(apps.vmiarch, 1) LOOP
				IF NOT apps.vmiarch[j] IS NULL THEN
					m_va_arch := m_va_arch || xmlelement(
						name "virtualization:arch",
						xmlattributes(
							apps.vmiarch[j]::TEXT AS id
						), (SELECT name FROM archs WHERE id = apps.vmiarch[j]::int)
					);
				END IF;
			END LOOP;
			m_va = m_va || xmlelement(name "virtualization:appliance",
				xmlattributes(
					(apps.vapp_versions[i]).vappid AS "id",
					(apps.vapp_versions[i]).id AS "versionid",
					(apps.vapp_versions[i]).version AS "version",
					(apps.vapp_versions[i]).createdon AS "createdOn",
					(apps.vapp_versions[i]).expireson AS "expiresOn",
					NOW() > (apps.vapp_versions[i]).expireson AS "expired",
					apps.imglst_private AS imageListPrivate
				),
				m_va_hyper::XML, m_va_os::XML, m_va_arch::XML
			);
		END LOOP;
	END IF;
	myxml := array_append(myxml, (SELECT xmlelement(name "application:application",
xmlattributes(apps.id,
apps.tool,
apps.rating,
apps.ratingcount as "ratingCount",
apps.popularity,
apps.cname,
apps.metatype,
CASE WHEN apps.metatype = 2 THEN 
(SELECT COUNT(context_script_assocs.scriptid) FROM context_script_assocs INNER JOIN contexts ON contexts.id = context_script_assocs.contextid WHERE contexts.appid = apps.id) 
ELSE (SELECT relcount FROM app_release_count WHERE appid = apps.id) 
END AS relcount,
apps.hitcount,
(SELECT vappliance_site_count(apps.id) ) as "sitecount",
apps.validated,
apps.moderated,
apps.deleted,
apps.guid),
xmlelement(name "application:name", apps.name), E'\n\t',
xmlelement(name "application:description", apps.description),E'\n\t',
xmlelement(name "application:abstract", apps.abstract),E'\n\t',
xmlelement(name "application:addedOn", apps.dateadded),E'\n\t',
xmlelement(name "application:lastUpdated", apps.lastupdated),E'\n\t',
m_categories::XML, E'\n\t',
m_disciplines::XML, E'\n\t',
xmlelement(name "application:status", xmlattributes(apps.statusid AS "id"), apps.status_name),  E'\n\t',
m_urls::XML, E'\n\t',
m_va::XML, E'\n\t',
xmlelement(name "application:permalink",'http://'||(SELECT data FROM config WHERE var='ui-host')||'/?p='||encode(CAST('/apps/details?id='||apps.id::text AS bytea),'base64')), E'\n\t',
CASE WHEN NOT apps.logo IS NULL THEN
xmlelement(name "application:logo",'http://'||(SELECT data FROM config WHERE var='ui-host')||'/apps/getlogo?id='||apps.id::text)
END, E'\n\t'-- ,
-- xmlelement(name "application:tags", array_to_string(apps.tags,','))
-- m_tags::XML, E'\n\t'
)));
END LOOP;
RETURN QUERY SELECT unnest(myxml);
END;
$_$;


ALTER FUNCTION public.__app_to_xml(m_id integer[]) OWNER TO appdb;

--
-- Name: __app_to_xml(integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION __app_to_xml(m_id integer) RETURNS xml
    LANGUAGE sql
    AS $_$
SELECT __app_to_xml(ARRAY[$1]);
$_$;


ALTER FUNCTION public.__app_to_xml(m_id integer) OWNER TO appdb;

--
-- Name: admin_only_actions(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION admin_only_actions() RETURNS integer[]
    LANGUAGE plpgsql IMMUTABLE
    AS $$
BEGIN
	RETURN ARRAY[35];
END
$$;


ALTER FUNCTION public.admin_only_actions() OWNER TO appdb;

--
-- Name: any(text, integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION "any"(tbl text, mid integer) RETURNS text
    LANGUAGE plpgsql STABLE
    AS $$
DECLARE i RECORD;
DECLARE cols text[];
DECLARE col text;
DECLARE result text;
BEGIN
        cols := NULL::text[];
        FOR i IN (SELECT column_name, udt_name FROM INFORMATION_SCHEMA.columns WHERE table_name = tbl AND table_schema = 'public' AND ((udt_name LIKE '%text%') OR (udt_name LIKE '%char'))) LOOP
                IF (SUBSTRING(i.udt_name, 1, 1) = '_') THEN
                        cols := array_append(cols, 'COALESCE(array_to_string("' || (i.column_name)::text || '", '' ''), '''')');
                ELSE
                        cols := array_append(cols, 'COALESCE("' || (i.column_name)::text || '", '''')');
                END IF;
        END LOOP;
        col := array_to_string(cols, '::text || '' '' || ') || '::text';
        EXECUTE 'SELECT ' || col || ' AS "any" FROM public.' || tbl || ' WHERE id = ' || mid INTO result;
        RETURN LOWER(result);
END;
$$;


ALTER FUNCTION public."any"(tbl text, mid integer) OWNER TO appdb;

--
-- Name: any(text, text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION "any"(tbl text, mid text) RETURNS text
    LANGUAGE plpgsql STABLE
    AS $$
  DECLARE i RECORD;
  DECLARE cols text[];
  DECLARE col text;
  DECLARE result text;
  BEGIN
          cols := NULL::text[];
          FOR i IN (SELECT column_name, udt_name FROM INFORMATION_SCHEMA.columns WHERE table_name = tbl AND table_schema = 'public' AND ((udt_name LIKE '%text%') OR (udt_name LIKE '%char'))) LOOP
                  IF (SUBSTRING(i.udt_name, 1, 1) = '_') THEN
                          cols := array_append(cols, 'COALESCE(array_to_string("' || (i.column_name)::text || '", '' ''), '''')');
                  ELSE
                          cols := array_append(cols, 'COALESCE("' || (i.column_name)::text || '", '''')');
                  END IF;
          END LOOP;
          col := array_to_string(cols, '::text || '' '' || ') || '::text';
          EXECUTE 'SELECT ' || col || ' AS "any" FROM public.' || tbl || ' WHERE id::text = ''' || mid::text || '''' INTO result;
          RETURN LOWER(result);
  END;
  $$;


ALTER FUNCTION public."any"(tbl text, mid text) OWNER TO appdb;

--
-- Name: app_actions(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION app_actions() RETURNS integer[]
    LANGUAGE sql IMMUTABLE
    AS $$
  SELECT ARRAY[1,4,5,6,7,8,9,10,11,12,13,14,15,16,17,20,23,26,30,31,32,33,34,40,41,42,43,44];
$$;


ALTER FUNCTION public.app_actions() OWNER TO appdb;

--
-- Name: FUNCTION app_actions(); Type: COMMENT; Schema: public; Owner: appdb
--

COMMENT ON FUNCTION app_actions() IS 'returns action ids that apply to applications';


--
-- Name: app_fc_actions(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION app_fc_actions() RETURNS integer[]
    LANGUAGE sql IMMUTABLE
    AS $$
        SELECT ARRAY[1,5,6,7,8,9,10,11,12,13,14,15,16,17,20,26,31,33,40,41,42,43,44];
$$;


ALTER FUNCTION public.app_fc_actions() OWNER TO appdb;

--
-- Name: FUNCTION app_fc_actions(); Type: COMMENT; Schema: public; Owner: appdb
--

COMMENT ON FUNCTION app_fc_actions() IS 'returns action ids that apply to applications when someone has full-control access';


--
-- Name: app_fld_lst(text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION app_fld_lst(alias text DEFAULT ''::text) RETURNS text
    LANGUAGE plpgsql STABLE
    AS $$
BEGIN
	IF (TRIM(alias) = '') OR (alias IS NULL) THEN
		RETURN (SELECT array_to_string(array_agg(column_name::text ORDER BY ordinal_position::int), ', ') FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = 'applications');
	ELSE
		RETURN alias || '.' || (SELECT array_to_string(array_agg(column_name::text ORDER BY ordinal_position::int), ', ' || alias || '.') FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = 'applications');
	END IF;
END;
$$;


ALTER FUNCTION public.app_fld_lst(alias text) OWNER TO appdb;

--
-- Name: app_licenses_to_xml(integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION app_licenses_to_xml(mid integer) RETURNS xml
    LANGUAGE plpgsql
    AS $$
DECLARE x XML;
BEGIN
	x := (
	SELECT XMLAGG(
		XMLELEMENT(
			name "application:license", 
			xmlattributes(
				app_licenses.licenseid AS id,
				licenses.name AS name,
				licenses.group AS "group"
			), XMLELEMENT(name "license:title", CASE WHEN app_licenses.licenseid = 0 THEN app_licenses.title ELSE licenses.title END),
			XMLELEMENT(name "license:url", CASE WHEN app_licenses.licenseid = 0 THEN app_licenses.link ELSE licenses.link END),
			XMLELEMENT(name "license:comment", 
				XMLATTRIBUTES(
					CASE WHEN app_licenses.comment IS NULL THEN 'true' END AS "xsi:nil"
				), app_licenses.comment
			)
		)
	) FROM app_licenses
	LEFT OUTER JOIN licenses ON licenses.id = app_licenses.licenseid
	WHERE appid = mid
	);
	RETURN x;
END;
$$;


ALTER FUNCTION public.app_licenses_to_xml(mid integer) OWNER TO appdb;

--
-- Name: app_logistics(text, text, text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION app_logistics(m_fltstr text, m_from text, m_where text) RETURNS xml
    LANGUAGE plpgsql
    AS $$
DECLARE h TEXT[];
DECLARE hh TEXT;
DECLARE fl TEXT[];
DECLARE fr TEXT[];
DECLARE w TEXT[];
DECLARE i INT;
DECLARE len INT;
BEGIN  
        IF m_fltstr IS NULL THEN m_fltstr := ''; END IF;
        IF m_from IS NULL THEN m_from := ''; END IF;
        IF m_where IS NULL THEN m_where := ''; END IF;
		m_fltstr := TRIM(m_fltstr);
		m_from := TRIM(m_from);
		m_where := TRIM(m_where);
		IF SUBSTRING(m_fltstr, 1, 1) = '{' THEN
			fl := m_fltstr::text[];
			fr := m_from::text[];
			w := m_where::text[];
		ELSE
			fl := ('{"' || REPLACE(m_fltstr, '"', '\"') || '"}')::text[];
			fr := ('{"' || REPLACE(m_from, '"', '\"') || '"}')::text[];
			w := ('{"' ||  REPLACE(m_where, '"', '\"') || '"}')::text[];
		END IF;
		h := NULL::TEXT[];
		IF m_fltstr = '' THEN
			len := 0;
			hh := MD5(m_from || ' ' || m_where);
			IF NOT EXISTS (SELECT hash FROM cache.filtercache WHERE hash = hh) THEN
				PERFORM filterapps(m_fltstr, m_from, m_where);
			END IF;
			h := ARRAY['cache.filtercache_' || hh];
		ELSE
			len := ARRAY_LENGTH(fl, 1);
		END IF;
		FOR i IN 1..len LOOP
			m_fltstr = TRIM(fl[i]);
			m_from = TRIM(fr[i]);
			m_where = TRIM(w[i]);
			hh := MD5(m_from || ' ' || m_where);
			IF NOT EXISTS (SELECT hash FROM cache.filtercache WHERE hash = hh) THEN
				PERFORM filterapps(m_fltstr, m_from, m_where);
			END IF;
			hh := 'cache.filtercache_' || hh;
			h := array_append(h, hh);
		END LOOP;        
        RETURN xmlelement(name "application:logistics",
                xmlconcat(
			(SELECT xmlagg(xmlelement(name "logistics:license", xmlattributes(t.name as "text", t.count as "count", t.id::text::text as "id"))) FROM count_app_matches('license', h) as t(name TEXT, count bigint, id text)),
                        (SELECT xmlagg(xmlelement(name "logistics:country", xmlattributes(t.name as "text", t.count as "count", t.id::text::text as "id"))) FROM count_app_matches('country', h) as t(name TEXT, count bigint, id text)),
                        (SELECT xmlagg(xmlelement(name "logistics:status", xmlattributes(t.name as "text", t.count as "count", t.id::text::text as "id"))) FROM count_app_matches('status', h) as t(name TEXT, count bigint, id text)),
						(SELECT xmlagg(xmlelement(name "logistics:discipline", xmlattributes(t.name as "text", t.count as "count", t.id::text::text as "id"))) FROM count_app_matches('discipline', h, isPrivateJoin(fl[ARRAY_LENGTH(fl, 1)], 'discipline')) as t(name TEXT, count bigint, id text)),
						(SELECT xmlagg(xmlelement(name "logistics:category", xmlattributes(t.name as "text", t.count as "count", t.id::text::text as "id"))) FROM count_app_matches('category', h) as t(name TEXT, count bigint, id text)),
						(SELECT xmlagg(xmlelement(name "logistics:language", xmlattributes(t.name as "text", t.count as "count", t.id::text::text as "id"))) FROM count_app_matches('proglang', h) as t(name TEXT, count bigint, id text)),
						(SELECT xmlagg(xmlelement(name "logistics:arch", xmlattributes(t.name as "text", t.count as "count", t.id::text::text as "id"))) FROM count_app_matches('arch', h) as t(name TEXT, count bigint, id text)),						
						(SELECT xmlagg(xmlelement(name "logistics:osfamily", xmlattributes(t.name as "text", t.count as "count", t.id::text::text as "id"))) FROM count_app_matches('osfamily', h) as t(name TEXT, count bigint, id text)),
						(SELECT xmlagg(xmlelement(name "logistics:os", xmlattributes(t.name as "text", t.count as "count", t.id::text::text as "id"))) FROM count_app_matches('os', h) as t(name TEXT, count bigint, id text)),
						(SELECT xmlagg(xmlelement(name "logistics:hypervisor", xmlattributes(t.name as "text", t.count as "count", t.id::text::text as "id"))) FROM count_app_matches('hypervisor', h) as t(name TEXT, count bigint, id text)),
						(SELECT xmlagg(xmlelement(name "logistics:vo", xmlattributes(t.name as "text", t.count as "count", t.id::text::text as "id"))) FROM count_app_matches('vo', h) as t(name TEXT, count bigint, id text)),
						(SELECT xmlagg(xmlelement(name "logistics:middleware", xmlattributes(t.name as "text", t.count as "count", t.id::text::text as "id"))) FROM count_app_matches('middleware', h, isPrivateJoin(fl[ARRAY_LENGTH(fl, 1)], 'middleware')) as t(name TEXT, count bigint, id text)),
						(SELECT xmlagg(xmlelement(name "logistics:validated", xmlattributes(t.name as "text", t.count as "count", t.id::text::text as "id")) ORDER BY id) FROM count_app_matches('validated', h, isPrivateJoin(fl[ARRAY_LENGTH(fl, 1)], 'validated')) as t(name TEXT, count bigint, id text) WHERE t.count>0),
						(SELECT xmlagg(xmlelement(name "logistics:phonebook", xmlattributes(t.name as "text", t.count as "count", t.id::text::text as "id"))) FROM 
(
WITH c AS (SELECT * FROM cached_ids(h) AS id)
SELECT l AS "name", COUNT(DISTINCT applications.id) AS count, n AS id FROM 
(
WITH RECURSIVE t(n) AS (
	VALUES (1)
	UNION ALL
	SELECT n+1 FROM t WHERE n < 28
)
SELECT 
CASE 
WHEN n<=26 THEN 
	SUBSTRING('ABCDEFGHIJKLMNOPQRSTUVWXYZ',n,1)
WHEN n=27 THEN 
	'0-9'
ELSE 
	'#'
END AS l,
CASE 
WHEN n<=26 THEN 
	'^' || SUBSTRING('ABCDEFGHIJKLMNOPQRSTUVWXYZ',n,1) || '.+'
WHEN n=27 THEN 
	'^[0-9].+'
ELSE 
	'^[^A-Za-z0-9].+'
END AS p,
n
FROM t) AS q
INNER JOIN applications ON applications.name ~* p
WHERE applications.id::text IN (SELECT id FROM c)
GROUP BY l, n
ORDER BY n
) AS t
)
                )
        );
END;
$$;


ALTER FUNCTION public.app_logistics(m_fltstr text, m_from text, m_where text) OWNER TO appdb;

--
-- Name: app_metadata_actions(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION app_metadata_actions() RETURNS integer[]
    LANGUAGE sql IMMUTABLE
    AS $$
        SELECT ARRAY[5,6,7,8,9,10,11,12,13,14,15,20,24,26,31,33,40,41,42,43];
$$;


ALTER FUNCTION public.app_metadata_actions() OWNER TO appdb;

--
-- Name: FUNCTION app_metadata_actions(); Type: COMMENT; Schema: public; Owner: appdb
--

COMMENT ON FUNCTION app_metadata_actions() IS 'returns action ids that apply to application metadata';


--
-- Name: uuid_generate_v4(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION uuid_generate_v4() RETURNS uuid
    LANGUAGE c STRICT
    AS '$libdir/uuid-ossp', 'uuid_generate_v4';


ALTER FUNCTION public.uuid_generate_v4() OWNER TO appdb;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: applications; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE applications (
    id integer NOT NULL,
    name text NOT NULL,
    description text,
    abstract text,
    statusid integer DEFAULT 6 NOT NULL,
    dateadded timestamp without time zone DEFAULT now() NOT NULL,
    addedby integer,
    respect boolean DEFAULT false NOT NULL,
    tool boolean DEFAULT false NOT NULL,
    guid uuid DEFAULT uuid_generate_v4(),
    lastupdated timestamp without time zone DEFAULT now(),
    rating double precision,
    ratingcount integer DEFAULT 0 NOT NULL,
    moderated boolean DEFAULT false NOT NULL,
    tagpolicy integer DEFAULT 2 NOT NULL,
    deleted boolean DEFAULT false,
    metatype integer DEFAULT 0 NOT NULL,
    disciplineid integer[],
    owner integer,
    categoryid integer[],
    hitcount integer DEFAULT 0,
    cname text,
    links text[]
);


ALTER TABLE applications OWNER TO appdb;

--
-- Name: app_name_available(text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION app_name_available(m_name text) RETURNS SETOF applications
    LANGUAGE plpgsql
    AS $$
BEGIN
IF EXISTS (SELECT * FROM applications WHERE regexp_replace(name,'[^A-Za-z0-9]','','g') ILIKE regexp_replace(m_name,'[^A-Za-z0-9]','','g') AND deleted IS FALSE) THEN
    RETURN QUERY SELECT * FROM applications WHERE regexp_replace(name,'[^A-Za-z0-9]','','g') ILIKE regexp_replace(m_name,'[^A-Za-z0-9]','','g') AND deleted IS FALSE;
ELSE
    RETURN QUERY SELECT * FROM applications WHERE FALSE;
END IF; 
END;
$$;


ALTER FUNCTION public.app_name_available(m_name text) OWNER TO appdb;

--
-- Name: app_popularity(integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION app_popularity(mid integer) RETURNS double precision
    LANGUAGE sql
    AS $_$SELECT CAST(popularity AS NUMERIC(5,2))::float FROM (SELECT CASE WHEN popularity IS NULL THEN 0 ELSE popularity*100 END AS popularity FROM (SELECT (SELECT COUNT(*) FROM app_api_log GROUP BY appid HAVING appid = $1)::float / (SELECT COUNT(*) FROM app_api_log)::float AS popularity) AS T) AS TT;$_$;


ALTER FUNCTION public.app_popularity(mid integer) OWNER TO appdb;

--
-- Name: app_target_privs_to_xml(integer, integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION app_target_privs_to_xml(_appid integer, _userid integer) RETURNS SETOF xml
    LANGUAGE plpgsql
    AS $_$
BEGIN
IF NOT EXISTS (SELECT privs FROM cache.appprivsxmlcache WHERE appid = _appid) THEN
  INSERT INTO cache.appprivsxmlcache (appid, privs)
	  SELECT _appid, target_privs_to_xml(applications.guid, $2, app_actions())
	  FROM applications
	  WHERE applications.id = _appid;
END IF;
RETURN QUERY
SELECT privs FROM cache.appprivsxmlcache WHERE appid = _appid;
END;
$_$;


ALTER FUNCTION public.app_target_privs_to_xml(_appid integer, _userid integer) OWNER TO appdb;

--
-- Name: app_to_json(integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION app_to_json(mid integer) RETURNS text
    LANGUAGE sql
    AS $_$
SELECT '{"application": {"id": "'||applications.id::text||'", "name": "'|| applications.name || '", "cname": "' || applications.cname || '", "description": "'||applications.description||'", "rating": '|| CASE WHEN applications.rating IS NULL THEN 'null' ELSE '"' || applications.rating::text || '"' END ||', "tool": "'|| applications.tool ||'", "discipline": [' || array_to_string(array_agg(DISTINCT '{"id": "'|| disciplines.id || '", "name": "' || disciplines.name || '"}'),',') || '], "category": [' || array_to_string(array_agg(DISTINCT '{"id": "'|| categories.id || '", "name": "' || categories.name || '", "isPrimary": "' || appcategories.isprimary::text || '", "parentid": ' || CASE WHEN categories.parentid IS NULL THEN 'null' ELSE '"' || categories.parentid::text || '"' END || '}'),',') || ']}}'
FROM applications
LEFT OUTER JOIN disciplines ON disciplines.id = ANY(applications.disciplineid)
LEFT OUTER JOIN appcategories ON appcategories.categoryid = ANY(applications.categoryid) AND appcategories.appid = $1
LEFT OUTER JOIN categories ON categories.id = appcategories.categoryid
WHERE applications.id = $1
GROUP BY applications.id, applications.name, applications.description, applications.rating, applications.tool;
$_$;


ALTER FUNCTION public.app_to_json(mid integer) OWNER TO appdb;

--
-- Name: app_to_xml(integer[]); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION app_to_xml(m_id integer[]) RETURNS SETOF xml
    LANGUAGE plpgsql STABLE STRICT
    AS $$
DECLARE i INT;
DECLARE x XML[];
DECLARE xx TEXT;
DECLARE vp INTERVAL;
BEGIN
	vp := (SELECT data FROM config WHERE var = 'app_validation_period')::INTERVAL;
	x := NULL::XML[];
	FOR i IN 1..ARRAY_LENGTH(m_id, 1) LOOP
		xx := (SELECT "xml" FROM cache.appxmlcache WHERE id = m_id[i]);
		xx:= REGEXP_REPLACE(xx, '(validated="true"|validated="false")', 'validated="' || COALESCE((SELECT lastupdated FROM applications WHERE id = m_id[i]) BETWEEN NOW() - vp AND NOW(), FALSE)::TEXT || '"');
		x := array_append(x, xx::XML);
	END LOOP;
	RETURN QUERY SELECT UNNEST(x);
	--RETURN QUERY
	--SELECT "xml" FROM cache.appxmlcache WHERE id = ANY(m_id) ORDER BY idx(m_id, id);
END;
$$;


ALTER FUNCTION public.app_to_xml(m_id integer[]) OWNER TO appdb;

--
-- Name: app_to_xml(integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION app_to_xml(integer) RETURNS xml
    LANGUAGE sql STABLE
    AS $_$SELECT app_to_xml(ARRAY[$1]);$_$;


ALTER FUNCTION public.app_to_xml(integer) OWNER TO appdb;

--
-- Name: app_to_xml_ext(integer, integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION app_to_xml_ext(mid integer, muserid integer DEFAULT NULL::integer) RETURNS xml
    LANGUAGE sql
    AS $_$
WITH target_relations AS(
	SELECT $1 as id, xmlagg(x) as "xml" FROM target_relations_to_xml((SELECT guid FROM applications WHERE id = $1)) as x
),
subject_relations AS (
	SELECT $1 as id, xmlagg(x) as "xml" FROM subject_relations_to_xml((SELECT guid FROM applications WHERE id = $1)) as x
) SELECT xmlelement(name "application:application", xmlattributes(
applications.id as id,
applications.tool as tool,
applications.rating as rating,
applications.ratingcount as "ratingCount",
app_popularity($1) as "popularity",
(SELECT vappliance_site_count(applications.id) ) as "sitecount",
applications.cname as "cname",
applications.metatype,
applications.guid as guid,
CASE WHEN applications.metatype = 2 THEN 
(SELECT COUNT(context_script_assocs.scriptid) FROM context_script_assocs INNER JOIN contexts ON contexts.id = context_script_assocs.contextid WHERE contexts.appid = applications.id) 
ELSE (SELECT relcount FROM app_release_count WHERE appid = applications.id) 
END AS relcount,
hitcounts.count as hitcount,
(SELECT COUNT(DISTINCT(va_provider_images.va_provider_id)) FROM applications
INNER JOIN vaviews ON vaviews.appid = applications.id
INNER JOIN __va_provider_images AS va_provider_images ON va_provider_images.vmiinstanceid = vaviews.vmiinstanceid AND vaviews.vmiinstanceid = get_good_vmiinstanceid(va_provider_images.vmiinstanceid)
WHERE applications.id = $1) AS vaprovidercount,
CASE WHEN applications.metatype = 2 THEN (SELECT COUNT(DISTINCT(va_provider_images.va_provider_id)) FROM contexts
INNER JOIN context_script_assocs ON context_script_assocs.contextid = contexts.id
INNER JOIN contextscripts AS cs ON cs.id = context_script_assocs.scriptid
INNER JOIN vmiinstance_contextscripts AS vcs ON vcs.contextscriptid = cs.id
INNER JOIN vaviews ON vaviews.vmiinstanceid = vcs.vmiinstanceid
INNER JOIN __va_provider_images AS va_provider_images ON va_provider_images.vmiinstanceid = vaviews.vmiinstanceid AND vaviews.vmiinstanceid = get_good_vmiinstanceid(va_provider_images.vmiinstanceid)
INNER JOIN applications AS apps ON apps.id = vaviews.appid
WHERE apps.metatype = 1 AND contexts.appid = $1) ELSE 0 END AS swprovidercount,
applications.tagpolicy as "tagPolicy",
lastupdated BETWEEN NOW() - (SELECT data FROM config WHERE var='app_validation_period' LIMIT 1)::INTERVAL AND NOW() as "validated",
CASE WHEN applications.moderated IS TRUE THEN 'true' END as "moderated",
CASE WHEN applications.deleted IS TRUE THEN 'true' END as "deleted",
CASE WHEN (NOT $2 IS NULL) AND (EXISTS (SELECT * FROM appbookmarks WHERE appid = applications.id AND researcherid = $2)) THEN 'true' END as "bookmarked"), E'\n\t',
xmlelement(name "application:name", applications.name), E'\n\t',
xmlelement(name "application:description", applications.description),E'\n\t',
xmlelement(name "application:abstract", applications.abstract),E'\n\t',
xmlelement(name "application:addedOn", applications.dateadded),E'\n\t',
xmlelement(name "application:lastUpdated", applications.lastupdated),E'\n\t',
owners."owner",E'\n\t',
actors."actor",E'\n\t',
category_to_xml(applications.categoryid,applications.id),E'\n\t',
disciplines.discipline,E'\n\t',
status_to_xml(statuses.id),E'\n\t',
-- CASE WHEN 34 = ANY(applications.categoryid) THEN
-- 	va_vos.vo
-- ELSE
	vos.vo,
-- END, E'\n\t',
countries.country, E'\n\t',
people.person, E'\n\t',
urls.url, E'\n\t',
docs.doc, E'\n\t',
middlewares.mw, E'\n\t',
xmlelement(name "application:permalink",'http://'||(SELECT data FROM config WHERE var='ui-host')||'/?p='||encode(CAST('/apps/details?id='||applications.id::text AS bytea),'base64')), E'\n\t',
CASE WHEN NOT applogos.logo IS NULL THEN
xmlelement(name "application:logo",'http://'||(SELECT data FROM config WHERE var='ui-host')||'/apps/getlogo?id='||applications.id::text)
END,
CASE WHEN applications.moderated AND (NOT $2 IS NULL) /*AND ((SELECT positiontypeid FROM researchers AS moderators WHERE moderators.id = $2) IN (5,7))*/ THEN
(
xmlelement(name "application:moderatedOn",app_mod_infos.moddedon)::text || xmlelement(name "application:moderationReason",app_mod_infos.modreason)::text || researcher_to_xml(app_mod_infos.moddedby, 'moderator')::text
)::xml
END,
CASE WHEN applications.deleted AND (NOT $2 IS NULL) /*AND ((SELECT positiontypeid FROM researchers AS deleters WHERE deleters.id = $2) IN (5,7))*/ THEN
(
xmlelement(name "application:deletedOn",app_del_infos.deletedon)::text || researcher_to_xml(app_del_infos.deletedby, 'deleter')::text
)::xml
END,
CASE WHEN applications.categoryid[1] = 34 THEN
(
	xmlelement(
		name "application:vappliance",
		xmlattributes(
			(SELECT vapplications.id FROM vapplications WHERE vapplications.appid = applications.id) AS "id",
			(SELECT vapplications.appid FROM vapplications WHERE vapplications.appid = applications.id) AS "appid",
			(SELECT vapplications.guid FROM vapplications WHERE vapplications.appid = applications.id) AS "identifier",
			(SELECT vapplications.name FROM vapplications WHERE vapplications.appid = applications.id) AS "name",
			(SELECT vapplications.imglst_private FROM vapplications WHERE vapplications.appid = applications.id) AS "imageListsPrivate"
		)
	)::text
)::xml
END,
app_licenses_to_xml($1),
tags.tag, E'\n\t',
proglangs.proglang, E'\n\t',
archs.arch , E'\n\t',
oses.os, E'\n\t',
-- CASE WHEN NOT $2 IS NULL THEN
-- 	CASE WHEN EXISTS(
-- 		SELECT *
-- 		FROM permissions
-- 		WHERE (object = applications.guid OR object IS NULL) AND (actor = (SELECT guid FROM researchers WHERE id = $2)) AND (actionid IN (1,2))
-- 	) THEN
-- 		targetprivs.privs
-- 	END
-- END, E'\n\t',
target_relations.xml,E'\n\t',
subject_relations.xml,E'\n\t',
CASE WHEN NOT $2 IS NULL THEN
	privs_to_xml(applications.guid, (SELECT guid FROM researchers WHERE id = $2))
END, E'\n\t'
) AS application FROM applications
LEFT OUTER JOIN (SELECT appid, xmlagg(discipline_to_xml(disciplineid)) AS discipline FROM appdisciplines GROUP BY appid) AS disciplines ON disciplines.appid = applications.id
LEFT OUTER JOIN (SELECT id, xmlagg(researcher_to_xml("owner", 'owner')) AS "owner" FROM applications GROUP BY id) AS owners ON owners.id = applications.id
LEFT OUTER JOIN (SELECT id, xmlagg(researcher_to_xml(addedby, 'actor')) AS "actor" FROM applications GROUP BY id) AS actors ON actors.id = applications.id
LEFT OUTER JOIN
	(SELECT appid, xmlagg(vo_to_xml(void)) AS vo FROM app_vos INNER JOIN vos ON vos.id = app_vos.void WHERE vos.deleted IS FALSE GROUP BY appid)
AS vos ON vos.appid = applications.id
/*
LEFT OUTER JOIN
	(
		SELECT
			appid,
			array_to_string(array_agg(DISTINCT vo_to_xml(void)::text), '')::xml AS vo
		FROM vowide_image_lists
		INNER JOIN vowide_image_list_images ON vowide_image_list_images.vowide_image_list_id = vowide_image_lists.id
		INNER JOIN vapplists ON vapplists.id = vowide_image_list_images.vapplistid
		INNER JOIN vapp_versions ON vapp_versions.id = vapplists.vappversionid
		INNER JOIN vos ON vos.id = vowide_image_lists.void
		INNER JOIN vmiinstances ON vmiinstances.id = vapplists.vmiinstanceid
		INNER JOIN vmiflavours ON vmiflavours.id = vmiinstances.vmiflavourid
		INNER JOIN vmis ON vmis.id = vmiflavours.vmiid
		INNER JOIN vapplications ON vapplications.id = vmis.vappid
		WHERE NOT vos.deleted -- AND vapp_versions.published AND vapp_versions.enabled AND NOT vapp_versions.archived
		GROUP BY vapplications.appid
	)
AS va_vos ON va_vos.appid = applications.id
*/
LEFT OUTER JOIN (SELECT appid, xmlagg(country_to_xml(id, appid)) AS country FROM appcountries GROUP BY appid) AS countries ON countries.appid = applications.id
INNER JOIN statuses ON statuses.id = applications.statusid
LEFT OUTER JOIN target_relations ON target_relations.id = applications.id
LEFT OUTER JOIN subject_relations ON subject_relations.id = applications.id
LEFT OUTER JOIN (SELECT appid, xmlagg(appmiddleware_to_xml(id)) AS mw FROM app_middlewares GROUP BY appid) AS middlewares ON middlewares.appid = applications.id
LEFT OUTER JOIN (SELECT appid, xmlagg(researcher_to_xml(researcherid, 'contact',appid)) AS person FROM researchers_apps INNER JOIN researchers ON researchers.id = researchers_apps.researcherid WHERE researchers.deleted IS FALSE GROUP BY appid) AS people ON people.appid = applications.id
LEFT OUTER JOIN (SELECT appid, xmlagg(xmlelement(name "application:url", xmlattributes(id as id, description as type, title as title), url)) AS url FROM app_urls GROUP BY appid) AS urls ON urls.appid = applications.id
LEFT OUTER JOIN (SELECT appid, xmlagg(appdocument_to_xml(id)) AS doc FROM appdocuments GROUP BY appid) AS docs ON docs.appid = applications.id
LEFT OUTER JOIN (SELECT appid, xmlagg(xmlelement(name "application:tag", xmlattributes(CASE WHEN researcherid ISNULL THEN 0 ELSE researcherid END as "submitterID"),tag)) as tag FROM app_tags GROUP BY appid) as tags ON tags.appid = applications.id
LEFT OUTER JOIN app_mod_infos ON app_mod_infos.appid = applications.id
LEFT OUTER JOIN app_del_infos ON app_del_infos.appid = applications.id
LEFT OUTER JOIN hitcounts ON hitcounts.appid = applications.id
LEFT OUTER JOIN (SELECT appid, xmlagg(xmlelement(name "application:language", xmlattributes(proglangid as id),(SELECT proglangs.name FROM proglangs WHERE id = proglangid))) as proglang FROM appproglangs GROUP BY appid) as proglangs ON proglangs.appid = applications.id
LEFT OUTER JOIN (SELECT appid, xmlagg(xmlelement(name "application:arch", xmlattributes(archid as id),(SELECT archs.name FROM archs WHERE id = archid))) as arch FROM app_archs GROUP BY appid) as archs ON archs.appid = applications.id
LEFT OUTER JOIN (SELECT appid, xmlagg(xmlelement(name "application:os", xmlattributes(osid as id),(SELECT oses.name FROM oses WHERE id = osid))) as os FROM app_oses GROUP BY appid) as oses ON oses.appid = applications.id
LEFT OUTER JOIN applogos ON applogos.appid = applications.id
-- LEFT OUTER JOIN (
-- 	SELECT xmlagg(x) AS privs, id FROM (SELECT target_privs_to_xml(applications.guid, $2) AS x, applications.id FROM applications) AS t GROUP BY t.id
-- ) AS targetprivs ON applications.id = targetprivs.id
WHERE applications.id = $1;
$_$;


ALTER FUNCTION public.app_to_xml_ext(mid integer, muserid integer) OWNER TO appdb;

--
-- Name: app_to_xml_list(integer[]); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION app_to_xml_list(ids integer[]) RETURNS SETOF xml
    LANGUAGE sql
    AS $$
SELECT 
	XMLELEMENT(
		name "application:application",
		XMLATTRIBUTES(
			applications.id AS id
		), applications.name
	)
FROM applications
WHERE id = ANY(ids)
$$;


ALTER FUNCTION public.app_to_xml_list(ids integer[]) OWNER TO appdb;

--
-- Name: app_to_xml_list(integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION app_to_xml_list(ids integer) RETURNS SETOF xml
    LANGUAGE sql
    AS $$
SELECT app_to_xml_list(ARRAY[ids]) 
$$;


ALTER FUNCTION public.app_to_xml_list(ids integer) OWNER TO appdb;

--
-- Name: appcontactitems_to_xml(integer, integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION appcontactitems_to_xml(m_appid integer, m_researcherid integer) RETURNS xml
    LANGUAGE sql
    AS $_$
SELECT 
	xmlagg(xmlelement(name "application:contactItem", xmlattributes(
		CASE WHEN itemid IS NULL THEN 0 ELSE itemid END as id,
		itemtype as "type",
		note as "note"
		), item
	))
FROM appcontact_items
WHERE appid = $1 AND researcherid = $2;
$_$;


ALTER FUNCTION public.appcontactitems_to_xml(m_appid integer, m_researcherid integer) OWNER TO appdb;

--
-- Name: appdocument_to_xml(integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION appdocument_to_xml(mid integer) RETURNS xml
    LANGUAGE sql
    AS $_$SELECT xmlelement(name "publication:publication", xmlattributes(appdocuments.id as id), E'\n\t',
xmlelement(name "publication:title", appdocuments.title), E'\n\t',
xmlelement(name "publication:url", appdocuments.url), E'\n\t',
xmlelement(name "publication:conference", appdocuments.conference), E'\n\t',
xmlelement(name "publication:proceedings", appdocuments.proceedings), E'\n\t',
xmlelement(name "publication:isbn", appdocuments.isbn), E'\n\t',
xmlelement(name "publication:startPage", appdocuments.pagestart), E'\n\t',
xmlelement(name "publication:endPage", appdocuments.pageend), E'\n\t',
xmlelement(name "publication:volume", appdocuments.volume), E'\n\t',
xmlelement(name "publication:publisher", appdocuments.publisher), E'\n\t',
xmlelement(name "publication:journal", appdocuments.journal), E'\n\t',
xmlelement(name "publication:year", appdocuments.year), E'\n\t',
xmlelement(name "publication:type", xmlattributes(doctypes.id as id), doctypes.description), E'\n\t',
authors_to_xml(appdocuments.id), E'\n'
) as appdocument 
FROM appdocuments
INNER JOIN doctypes on doctypes.id = appdocuments.doctypeid
WHERE appdocuments.id = $1;
$_$;


ALTER FUNCTION public.appdocument_to_xml(mid integer) OWNER TO appdb;

--
-- Name: appmiddleware_to_xml(integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION appmiddleware_to_xml(mid integer) RETURNS xml
    LANGUAGE sql
    AS $_$SELECT xmlelement(name "middleware:middleware", xmlattributes(middlewareid AS id, comment AS comment, COALESCE(middlewares.link, app_middlewares.link) AS link), middlewares.name) FROM app_middlewares INNER JOIN middlewares ON app_middlewares.middlewareid = middlewares.id WHERE app_middlewares.id = $1;$_$;


ALTER FUNCTION public.appmiddleware_to_xml(mid integer) OWNER TO appdb;

--
-- Name: apprating_report_to_xml(integer, integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION apprating_report_to_xml(mappid integer, mtype integer DEFAULT 0) RETURNS xml
    LANGUAGE sql
    AS $_$SELECT xmlelement(name "application:ratingreport", xmlattributes(
$1 as applicationid, 
CASE $2 WHEN 1 THEN 'internal' WHEN 2 THEN 'external' ELSE 'both' END as "type", 
CAST(AVG(rating) AS NUMERIC(3,2)) as "average", 
COUNT(rating) as "total"), 
xmlelement(name "ratingreport:rating", xmlattributes(1 as "value", (SELECT COUNT(*) FROM appratings WHERE appid = $1 AND rating = 1 AND CASE $2 WHEN 1 THEN NOT submitterid IS NULL WHEN 2 THEN submitterid IS NULL ELSE 1=1 END) as "votes")),
xmlelement(name "ratingreport:rating", xmlattributes(2 as "value", (SELECT COUNT(*) FROM appratings WHERE appid = $1 AND rating = 2 AND CASE $2 WHEN 1 THEN NOT submitterid IS NULL WHEN 2 THEN submitterid IS NULL ELSE 1=1 END) as "votes")),
xmlelement(name "ratingreport:rating", xmlattributes(3 as "value", (SELECT COUNT(*) FROM appratings WHERE appid = $1 AND rating = 3 AND CASE $2 WHEN 1 THEN NOT submitterid IS NULL WHEN 2 THEN submitterid IS NULL ELSE 1=1 END) as "votes")),
xmlelement(name "ratingreport:rating", xmlattributes(4 as "value", (SELECT COUNT(*) FROM appratings WHERE appid = $1 AND rating = 4 AND CASE $2 WHEN 1 THEN NOT submitterid IS NULL WHEN 2 THEN submitterid IS NULL ELSE 1=1 END) as "votes")),
xmlelement(name "ratingreport:rating", xmlattributes(5 as "value", (SELECT COUNT(*) FROM appratings WHERE appid = $1 AND rating = 5 AND CASE $2 WHEN 1 THEN NOT submitterid IS NULL WHEN 2 THEN submitterid IS NULL ELSE 1=1 END) as "votes"))
) FROM appratings
WHERE appid = $1
AND CASE WHEN $2=1 THEN NOT submitterid IS NULL WHEN $2=2 THEN submitterid IS NULL ELSE 1=1 END
$_$;


ALTER FUNCTION public.apprating_report_to_xml(mappid integer, mtype integer) OWNER TO appdb;

--
-- Name: appratings_to_xml(integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION appratings_to_xml(mid integer) RETURNS xml
    LANGUAGE sql
    AS $_$SELECT xmlelement(name "application:rating", xmlattributes(
id as "id",
moderated as "moderated"), E'\n\t',
CASE WHEN rating IS NULL THEN
xmlelement(name "rating:rating", xmlattributes('true' as "xsi:nil"))
ELSE
xmlelement(name "rating:rating", "rating")
END, E'\n\t',
xmlelement(name "rating:comment", CASE WHEN moderated IS TRUE THEN '' ELSE "comment" END), E'\n\t',
xmlelement(name "rating:submittedOn", submittedon), E'\n\t',
CASE WHEN submitterid IS NULL THEN
xmlelement(name "rating:submitter", xmlattributes('external'AS type, submitteremail as email), submittername)
ELSE
xmlelement(name "rating:submitter", xmlattributes('internal' AS type), researcher_to_xml(submitterid))
END)
FROM appratings WHERE id = $1
$_$;


ALTER FUNCTION public.appratings_to_xml(mid integer) OWNER TO appdb;

--
-- Name: array_sort(anyarray); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION array_sort(anyarray) RETURNS anyarray
    LANGUAGE sql
    AS $_$
SELECT ARRAY(
    SELECT $1[s.i] AS "foo"
    FROM
        generate_series(array_lower($1,1), array_upper($1,1)) AS s(i)
    ORDER BY foo
);
$_$;


ALTER FUNCTION public.array_sort(anyarray) OWNER TO appdb;

--
-- Name: array_sort_unique(anyarray); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION array_sort_unique(anyarray) RETURNS anyarray
    LANGUAGE sql
    AS $_$
SELECT ARRAY(
    SELECT DISTINCT $1[s.i] AS "foo"
    FROM
        generate_series(array_lower($1,1), array_upper($1,1)) AS s(i)
    ORDER BY foo
);
$_$;


ALTER FUNCTION public.array_sort_unique(anyarray) OWNER TO appdb;

--
-- Name: authors_to_xml(integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION authors_to_xml(mdocid integer) RETURNS xml
    LANGUAGE sql
    AS $_$
SELECT xmlagg(xmlelement(name "publication:author", xmlattributes(authors.main as main, CASE WHEN authors.authorid IS NULL THEN 'external' ELSE 'internal' END AS type),
CASE WHEN authorid IS NULL THEN xmlelement(name "publication:extAuthor", fullname) ELSE researcher_to_xml(authorid) END)) AS author FROM authors WHERE docid = $1 GROUP BY docid;
$_$;


ALTER FUNCTION public.authors_to_xml(mdocid integer) OWNER TO appdb;

--
-- Name: cache_delta(anyelement, text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION cache_delta(newitem anyelement, itemtype text) RETURNS void
    LANGUAGE plpgsql STRICT
    AS $_$
DECLARE h TEXT;
DECLARE f TEXT;
DECLARE w TEXT;
DECLARE r RECORD; -- used as auto cursor record var, in FOR construct
DECLARE cur REFCURSOR; -- i.e. unbound cursor, will be bounded on OPEN
DECLARE r2 RECORD; -- used as cursor record var for "cur"
DECLARE r3 RECORD; -- used as record var for single-row SELECT query (PK based)
DECLARE n RECORD; 
DECLARE cachetype TEXT;
DECLARE ranker TEXT;
DECLARE filter_cache_limit int;
DECLARE debug_looper bigint;
BEGIN
	filter_cache_limit := COALESCE((SELECT "data" FROM config WHERE var = 'filter_cache_limit'), '20')::int;
	CASE itemtype 
	WHEN 'appbookmarks' THEN
		n := newitem::appbookmarks;
	WHEN 'app_archs' THEN
		n := newitem::app_archs;
	WHEN 'app_middlewares' THEN
		n := newitem::app_middlewares;
	WHEN 'app_oses' THEN
		n := newitem::app_middlewares;
	WHEN 'app_vos' THEN
		n := newitem::app_vos;
	WHEN 'appcountries' THEN
		n := newitem::appcountries;
	WHEN 'appproglangs' THEN
		n := newitem::appproglangs;
	WHEN 'archs' THEN
		n := newitem::archs;
	WHEN 'categories' THEN
		n := newitem::categories;
	WHEN 'contacts' THEN
		n := newitem::contacts;
	WHEN 'countries' THEN
		n := newitem::contacts;
	WHEN 'countries' THEN
		n := newitem::countries;
	WHEN 'disciplines' THEN
		n := newitem::disciplines;
	WHEN 'middlewares' THEN
		n := newitem::middlewares;
	WHEN 'oses' THEN
		n := newitem::oses;
	WHEN 'positiontypes' THEN
		n := newitem::positiontypes;
	WHEN 'proglangs' THEN
		n := newitem::proglangs;
	WHEN 'researchers_apps' THEN
		n := newitem::researchers_apps;
	WHEN 'statuses' THEN
		n := newitem::statuses;
	WHEN 'vo_middlewares' THEN
		n := newitem::vo_middlewares;
	WHEN 'applications' THEN
		n := newitem::applications;
	WHEN 'researchers' THEN
		n := newitem::researchers;
	WHEN 'vos' THEN
		n := newitem::vos;
	ELSE
		RAISE NOTICE 'Unknown itemtype % request for cache delta check, ignoring', itemtype;
		RETURN;
	END CASE;
	FOR r IN SELECT * FROM cache.filtercache /*WHERE invalid IS FALSE*/ ORDER BY (usecount + 1)::float / SUM(usecount+1) OVER () / EXTRACT(EPOCH FROM (NOW() - m_when)) DESC /*LIMIT filter_cache_limit*/ LOOP
		CONTINUE WHEN NOT ' ' || r.m_from || ' ' ILIKE '% ' || itemtype || ' %'; -- ignore irrelevant cache;				
		cachetype := REGEXP_REPLACE(r.m_from, 'FROM (\w+)\s*.*', '\1');
		IF cachetype = 'applications' THEN
			ranker := 'rankapp';
		ELSIF cachetype = 'researchers' THEN
			ranker := 'rankppl';
		ELSIF cachetype = 'vos' THEN
			ranker := 'rankvo';
		ELSE
			RAISE NOTICE 'Unknown cachetype % request for cache delta check, ignoring', cachetype;
			RETURN;
		END IF;
		-- Use a cursor to loop over all qualifying entries, and check whether they should be added or re-ranked
		-- Qualifying entries will be those that match the original query, BUT
		--  1. use a CTE to confine the table to which the altered row belongs to be only that row, AND (we do not need to re-check stuff that have not changed)
		--  2. change LEFT OUTER JOINS to INNER JOINS, in order to constrain the data domain to whatever has changed only (lest the CTE be ineffective)
		-- This should be fast since we are working with the data domain closure of the changes (delta), not the data domain of the entire database
		OPEN cur FOR EXECUTE 'WITH ' || itemtype || ' AS (SELECT $1.*) SELECT DISTINCT ' || cachetype || '.* ' || r.m_from || ' ' || r.m_where USING n;
		FETCH FIRST FROM cur INTO r2;
		debug_looper := 0;
		WHILE FOUND LOOP
			debug_looper := debug_looper + 1;
			IF debug_looper > 1000 THEN 
				RAISE LOG 'Debug_looper in cache_delta() exceeded 1,000. (Hash: %)', r.hash;
			END IF;
			IF NOT r2 IS NULL THEN -- item qualifies, it should be added into the cache table if it is not already there
				EXECUTE 'SELECT * FROM cache.filtercache_' || r.hash || ' WHERE id = ' || r2.id INTO r3; -- single row expected, no need for cursor
				IF r3 IS NULL THEN -- item does not exist, add it
					RAISE NOTICE 'Adding % with id % into %', cachetype, r2.id, r.hash;
					BEGIN
						EXECUTE 'INSERT INTO cache.filtercache_' || r.hash || ' SELECT ($1::' || cachetype || ').*, ' || ranker || '($1::' || cachetype ||', $2)' USING REGEXP_REPLACE(r2::text, ',[0-9]+\)$', ')'), r.fltstr;
					EXCEPTION
						WHEN OTHERS THEN
							FETCH NEXT FROM cur INTO r2;
							CONTINUE;
					END;					
				ELSE -- item already exists, re-rank it
/*					CONTINUE WHEN COALESCE(r.m_where, '') = ''; -- ignore unfiltered cache tables, rank is always zero			
					EXECUTE 'UPDATE cache.filtercache_' || r.hash || ' SET rank = ' || ranker || '($1::' || cachetype ||', $2) WHERE id = $3' USING REGEXP_REPLACE(r3::text, ',[0-9]+\)$', ')'), r.fltstr, r3.id;
					RAISE NOTICE 'Re-ranking % with id % in %', cachetype, r3.id, r.hash;
*/
					-- refresh it instead of just re-ranking it, since computed columns such as
					-- disciplines, categories, etc might have changed
					RAISE NOTICE 'Refreshing % with id % in %', cachetype, r3.id, r.hash;					
					BEGIN
						EXECUTE 'DELETE FROM cache.filtercache_' || r.hash || ' WHERE id = ' || r3.id;
						EXECUTE 'INSERT INTO cache.filtercache_' || r.hash || ' SELECT ($1::' || 'researchers'/*cachetype */|| ').*, ' || ranker || '($1::' || cachetype ||', $2)' USING REGEXP_REPLACE(r2::text, ',[0-9]+\)$', ')'), r.fltstr;
					EXCEPTION
						WHEN OTHERS THEN
							FETCH NEXT FROM cur INTO r2;
							CONTINUE;
					END;
				END IF;
			END IF;
			FETCH NEXT FROM cur INTO r2;
		END LOOP;
		CLOSE cur;
	END LOOP;
END;
$_$;


ALTER FUNCTION public.cache_delta(newitem anyelement, itemtype text) OWNER TO appdb;

--
-- Name: cache_delta_clean(anyelement, text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION cache_delta_clean(newitem anyelement, itemtype text) RETURNS void
    LANGUAGE plpgsql STRICT
    AS $_$
DECLARE r RECORD; -- used as auto cursor record var in FOR construct
DECLARE cachetype TEXT;
DECLARE m_using TEXT;
DECLARE m_join TEXT;
DECLARE n RECORD;
DECLARE cur REFCURSOR;
DECLARE r2 RECORD;
DECLARE r3 RECORD;
DECLARE q1 text;
DECLARE q2 text;
DECLARE filter_cache_limit int;
DECLARE filter_counter int;
BEGIN
	filter_cache_limit := COALESCE((SELECT "data" FROM config WHERE var = 'filter_cache_limit'), '20')::int;
	CASE itemtype 
	WHEN 'appbookmarks' THEN
		n := newitem::appbookmarks;
	WHEN 'app_archs' THEN
		n := newitem::app_archs;
	WHEN 'app_middlewares' THEN
		n := newitem::app_middlewares;
	WHEN 'app_oses' THEN
		n := newitem::app_middlewares;
	WHEN 'app_vos' THEN
		n := newitem::app_vos;
	WHEN 'appcountries' THEN
		n := newitem::appcountries;
	WHEN 'appproglangs' THEN
		n := newitem::appproglangs;
	WHEN 'archs' THEN
		n := newitem::archs;
	WHEN 'categories' THEN
		n := newitem::categories;
	WHEN 'contacts' THEN
		n := newitem::contacts;
	WHEN 'countries' THEN
		n := newitem::contacts;
	WHEN 'countries' THEN
		n := newitem::countries;
	WHEN 'disciplines' THEN
		n := newitem::disciplines;
	WHEN 'middlewares' THEN
		n := newitem::middlewares;
	WHEN 'oses' THEN
		n := newitem::oses;
	WHEN 'positiontypes' THEN
		n := newitem::positiontypes;
	WHEN 'proglangs' THEN
		n := newitem::proglangs;
	WHEN 'researchers_apps' THEN
		n := newitem::researchers_apps;
	WHEN 'statuses' THEN
		n := newitem::statuses;
	WHEN 'vo_middlewares' THEN
		n := newitem::vo_middlewares;
	WHEN 'applications' THEN
		n := newitem::applications;
	WHEN 'researchers' THEN
		n := newitem::researchers;
	WHEN 'vos' THEN
		n := newitem::vos;
	ELSE
		RAISE NOTICE 'Unknown itemtype % request for cache delta check, ignoring', itemtype;
		RETURN;
	END CASE;
	filter_counter := 0;
	RAISE NOTICE 'Cache limit is %', filter_cache_limit;
	FOR r IN SELECT hash, m_from, m_where FROM cache.filtercache /*WHERE invalid IS FALSE*/ ORDER BY (usecount + 1)::float / SUM(usecount+1) OVER () / EXTRACT(EPOCH FROM (NOW() - m_when)) DESC LOOP
		-- Only process first n ( = filter_cache_limit) entries, mark the rest as invalid
		filter_counter := filter_counter + 1;
		
/*
		IF filter_counter > filter_cache_limit THEN
			UPDATE cache.filtercache SET invalid = TRUE WHERE hash = r.hash;
			RAISE NOTICE 'Marking % as invalid', r.hash;
			CONTINUE;		
		END IF;		
*/

		IF filter_counter > filter_cache_limit THEN
			DELETE FROM cache.filtercache WHERE hash = r.hash;
			RAISE NOTICE 'Deleting % from cache pool', r.hash;
			CONTINUE;		
		END IF;		
		
		CONTINUE WHEN (COALESCE(r.m_where, '') = '') OR (NOT ' ' || r.m_from || ' ' ILIKE '% ' || itemtype || ' %'); -- ignore unfiltered and irrelevent cache tables

		RAISE NOTICE 'Acting on %', r.hash;
		cachetype := REGEXP_REPLACE(r.m_from, 'FROM (\w+)\s*.*', '\1');
		-- convert JOIN clauses from original SELECT query INTO a USING clause appropriate for usage with a DELETE statement (m_using)
		-- JOIN conditions will be appended to the WHERE clause of the original SELECT query (m_join)
		m_using := REPLACE(TRIM(REGEXP_REPLACE(REGEXP_REPLACE(REGEXP_REPLACE(REGEXP_REPLACE(r.m_from,'(AND|OR)* \w+\.\w+ IS (FALSE|TRUE)','','g'), '(LEFT|RIGHT)* *(OUTER|INNER|CROSS|NATURAL)* *JOIN (\w+) ON \w+\.\w+ *= *(\w+\.\w+|ANY\(\w+\.\w+\)) *((OR|AND) *\w+\.\w+ *= *(\w+\.\w+|ANY\(\w+\.\w+\)))*', '\3 ', 'g'),'( AND | OR |FROM \w+|(OR|AND)* *IS *(FALSE|TRUE))','','g'),' +',' ','g')),' ',',');
		m_join := REGEXP_REPLACE(REGEXP_REPLACE(REGEXP_REPLACE(TRIM(REGEXP_REPLACE(REGEXP_REPLACE(REGEXP_REPLACE(r.m_from, '(LEFT|RIGHT){0,1} *(OUTER|INNER){0,1} *JOIN (\w+) ON (\w+\.\w+ *= *(\w+\.\w+|ANY\(\w+\.\w+\)) *((OR|AND) *\w+\.\w+ *= *(\w+\.\w+|ANY\(\w+\.\w+\)))*) *', '(\4) ', 'g'),' +',' ','g'),'FROM *\w+','')),' = ','=','g'),' +',' AND ','g'),' AND \)',')','g');
		m_join := REGEXP_REPLACE(m_join, '(AND ){1,}','AND ','g');
		m_join := REGEXP_REPLACE(m_join, 'AND IS AND','IS','g');
		m_join := REGEXP_REPLACE(m_join, 'AND OR AND','OR','g');
		-- use a CTE to constrain the data domain of the modified table to the modified row only
		-- invert the WHERE clause of the original SELECT query to delete rows that do not qualify any more, BUT
		-- append an " AND id NOT IN (SELECT ..." query at the end of the inverted WHERE, in order to keep records
		-- that would be deleted otherwise, but should not since they qualify from other conditions (e.g. keep and application
		-- entry that has an intrinsic discipline of life sciences, but also inherits an extrinsic discipline of life sciences
		-- from an associated vo, when this vo association is been deleted)
		-- this should be fast since the (cheap) inverted WHERE clause gets evaluated on the data domain of the cache, and the (expensive)
		-- SELECT subquery of the WHERE clause only gets evaluated if the record is to be deleted, thanks to partial boolean evaluation
		-- Note the usage of a second CTE in the SELECT subquery of the WHERE clause which replaces the modified table
		-- with a NULL table, in order to reflect the state of the data domain AFTER the modification, since the 1st CTE
		-- has effectively replaced the state of modified table with the one BEFORE the modification (lest nothing be deleted)

		-- Using a CTE (gooditems) for the subquery is ~10% faster and more legible
/*		EXECUTE 
			'WITH ' || itemtype || ' AS (SELECT $1.*),
			gooditems AS (WITH ' || itemtype || ' AS (SELECT (NULL::' || itemtype || ').*)' ||
			'SELECT ' || cachetype || '.id ' || r.m_from || ' ' || r.m_where || ')
			DELETE FROM cache.filtercache_' || r.hash || ' AS ' || cachetype || CASE WHEN COALESCE(m_using, '') <> '' THEN ' USING ' || m_using ELSE '' END || ' ' || REPLACE(r.m_where,'WHERE ','WHERE NOT') || CASE WHEN COALESCE(m_join, '') <> '' THEN ' AND (' || m_join || ')' ELSE '' END ||
			' AND (' || cachetype || '.id NOT IN (SELECT id FROM gooditems))'
		USING n;
*/
		-- Partial boolean eval does not seem to work ... (!)
		-- Fetch deletion candidates into a cursor and eval each row individually to ensure the expensive
		-- part of the query gets evaluated only when needed
		-- This is actually (tested) fast
		-- OPEN cur FOR EXECUTE 'WITH ' || itemtype || ' AS (SELECT $1.*) SELECT ' || cachetype || '.id FROM cache.filtercache_' || r.hash || ' AS ' || cachetype || CASE WHEN COALESCE(m_using, '') <> '' THEN ',' || m_using ELSE '' END || ' ' || REPLACE(r.m_where,'WHERE ','WHERE NOT') || CASE WHEN COALESCE(m_join, '') <> '' THEN ' AND (' || m_join || ')' ELSE '' END USING n;
		
		-- THIS IS MUCH FASTER THAN USING A CURSOR, AND SHOULD BE THE SAME AS THE COMMENTED CODE BELOW
		-- EXECUTE 'SELECT COUNT(*) AS x FROM cache.filtercache_' || r.hash INTO r2;
		-- RAISE NOTICE '% before: %', r.hash, r2;
		q1 := 'DELETE FROM cache.filtercache_' || r.hash || ' AS xx WHERE id IN (/*SELECT ' || cachetype || '.id ' || r.m_from || ' ' || r.m_where || ' AND ' || cachetype || '.id IN */(WITH ' || itemtype || ' AS (SELECT $1.*) SELECT DISTINCT ' || cachetype || '.id FROM cache.filtercache_' || r.hash || ' AS ' || CASE WHEN itemtype <> cachetype THEN cachetype ELSE cachetype || '__ INNER JOIN ' || cachetype || ' ON ' || cachetype || '.id = ' || cachetype || '__.id ' END || ' ' || REGEXP_REPLACE(r.m_from,'FROM \w+','') || ' ' || REGEXP_REPLACE(REGEXP_REPLACE(r.m_where,'^WHERE ','WHERE NOT COALESCE('), '$', ',FALSE) AND xx.id = ' || CASE WHEN itemtype <> cachetype THEN cachetype ELSE cachetype || '__' END || '.id))');
		-- RAISE NOTICE '%', q1;
		EXECUTE q1 USING n;
		-- EXECUTE 'SELECT COUNT(*) AS x FROM cache.filtercache_' || r.hash INTO r2;
		-- RAISE NOTICE '% after: %', r.hash, r2;
/*
		q1 := 'WITH ' || itemtype || ' AS (SELECT $1.*) SELECT DISTINCT ' || cachetype || '.id FROM cache.filtercache_' || r.hash || ' AS ' || CASE WHEN itemtype <> cachetype THEN cachetype ELSE cachetype || '__ INNER JOIN ' || cachetype || ' ON ' || cachetype || '.id = ' || cachetype || '__.id ' END || ' ' || REGEXP_REPLACE(r.m_from,'FROM \w+','') || ' ' || REGEXP_REPLACE(REGEXP_REPLACE(r.m_where,'^WHERE ','WHERE NOT COALESCE('), '$', ',FALSE)');
		RAISE NOTICE '%', q1;
		-- Get deletion candidates from cursor; there might be false positives included
		OPEN cur FOR EXECUTE q1 USING n;
		FETCH FIRST FROM cur INTO r2;
		WHILE FOUND LOOP
			-- Re-run original query on each candidate to avoid deleting false-positives
			EXECUTE 'SELECT ' || cachetype || '.id ' || r.m_from || ' ' || r.m_where || ' AND ' || cachetype || '.id = ' || r2.id INTO r3;
			IF NOT r3 IS DISTINCT FROM ROW(NULL::int) THEN
				-- We got back an empty resultset after executing the original query on the candidate -> not a false positive, remove it
				RAISE NOTICE '%', 'Deleting ' || cachetype || ' with id ' || r2.id || ' from ' || r.hash;
				EXECUTE 'DELETE FROM cache.filtercache_' || r.hash || ' WHERE id = ' || r2.id;
			END IF;
			FETCH NEXT FROM cur INTO r2;
		END LOOP;
		CLOSE cur;
*/
	END LOOP;
END;
$_$;


ALTER FUNCTION public.cache_delta_clean(newitem anyelement, itemtype text) OWNER TO appdb;

--
-- Name: cached_ids(text[]); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION cached_ids(h text[]) RETURNS SETOF text
    LANGUAGE plpgsql
    AS $$
DECLARE ids text[];
DECLARE x_ids text[];
BEGIN
	ids := NULL::text[];
	FOR i IN 1..ARRAY_LENGTH(h, 1) LOOP
		EXECUTE 'SELECT array_agg(id::text) FROM ' || h[i] INTO x_ids;
		IF ids IS NULL THEN
			ids := x_ids;
		ELSE
			ids := (SELECT array_agg(x) FROM (SELECT UNNEST(ids) AS x INTERSECT SELECT UNNEST(x_ids)) AS t);
		END IF;
	END LOOP;
	RETURN QUERY SELECT DISTINCT UNNEST(ids);
END;

$$;


ALTER FUNCTION public.cached_ids(h text[]) OWNER TO appdb;

--
-- Name: cached_ids(text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION cached_ids(h text) RETURNS SETOF text
    LANGUAGE plpgsql
    AS $$
BEGIN
	RETURN QUERY EXECUTE 'SELECT id::text FROM ' || h;
END;
$$;


ALTER FUNCTION public.cached_ids(h text) OWNER TO appdb;

--
-- Name: can_grant_priv(integer, uuid, uuid, integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION can_grant_priv(_actionid integer, _actor uuid, _target uuid, editorid integer) RETURNS boolean
    LANGUAGE plpgsql STRICT
    AS $_$
DECLARE res BOOLEAN;
BEGIN
	BEGIN
		SELECT grant_privilege($1, $2, $3, $4) INTO res;
		RAISE EXCEPTION 'pl/pgsql ROLLBACK';
	EXCEPTION
		WHEN OTHERS THEN 
			RETURN res;
	END;
	RETURN res;
END;
$_$;


ALTER FUNCTION public.can_grant_priv(_actionid integer, _actor uuid, _target uuid, editorid integer) OWNER TO appdb;

--
-- Name: can_revoke_priv(integer, uuid, uuid, integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION can_revoke_priv(_actionid integer, _actor uuid, _target uuid, editorid integer) RETURNS boolean
    LANGUAGE plpgsql STRICT
    AS $_$
DECLARE res BOOLEAN;
BEGIN
	BEGIN
		SELECT revoke_privilege($1, $2, $3, $4) INTO res;
		RAISE EXCEPTION 'pl/pgsql ROLLBACK';
	EXCEPTION
		WHEN OTHERS THEN 
			RETURN res;
	END;
	RETURN res;
END;
$_$;


ALTER FUNCTION public.can_revoke_priv(_actionid integer, _actor uuid, _target uuid, editorid integer) OWNER TO appdb;

--
-- Name: category_level(integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION category_level(mid integer) RETURNS integer
    LANGUAGE plpgsql STABLE
    AS $$
DECLARE lvl INT;
DECLARE pid INT;
BEGIN
	IF mid IS NULL THEN
		RETURN 0;
	ELSE
		lvl := -1;
		pid := mid;	
		WHILE NOT pid IS NULL LOOP
			pid := (SELECT parentid FROM categories WHERE id = pid);
			lvl := lvl + 1;
		END LOOP;
		RETURN lvl;	
	END IF;
END;
$$;


ALTER FUNCTION public.category_level(mid integer) OWNER TO appdb;

--
-- Name: category_to_xml(integer[], integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION category_to_xml(mid integer[], appid integer DEFAULT NULL::integer) RETURNS xml
    LANGUAGE sql
    AS $_$
SELECT CASE WHEN $1 IS NULL THEN (SELECT category_to_xml(NULL::int)) ELSE
(SELECT array_to_string(array_agg(category_to_xml(id, $2) ORDER BY name),'')::xml FROM categories WHERE id = ANY($1)
) END;
$_$;


ALTER FUNCTION public.category_to_xml(mid integer[], appid integer) OWNER TO appdb;

--
-- Name: category_to_xml(integer, integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION category_to_xml(mid integer, appid integer DEFAULT NULL::integer) RETURNS xml
    LANGUAGE sql
    AS $_$SELECT CASE WHEN $1 IS NULL THEN (SELECT xmlelement(name "application:category", xmlattributes(
'true' as "xsi:nil", 0 as id))) ELSE (
SELECT xmlelement(name "application:category", xmlattributes(
	id as id,
	CASE WHEN parentid IS NULL THEN NULL ELSE parentid END as parentid,
	CASE WHEN ord > 0 THEN ord ELSE NULL END as "order",
	CASE WHEN $2 IS NULL THEN NULL ELSE (SELECT isprimary FROM appcategories WHERE appcategories.appid = $2 AND appcategories.categoryid = $1) END as "primary"), name)
FROM categories 
WHERE id = $1) END;$_$;


ALTER FUNCTION public.category_to_xml(mid integer, appid integer) OWNER TO appdb;

--
-- Name: category_to_xml_ext(integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION category_to_xml_ext(mid integer) RETURNS xml
    LANGUAGE plpgsql STRICT
    AS $$
BEGIN
	RETURN (
		SELECT 
			xmlelement(
				name "application:category", 
				xmlattributes(
					categories.id as id,
					categories.parentid as parentid,
					ord as "order"
				), 
				xmlelement(
					name "category:name",
					categories.name
				),
				xmlagg(
					CASE
					WHEN category_help.data IS NULL THEN 
						NULL
					ELSE
						xmlelement(
							name "category:info",
							xmlattributes(
								CASE 
									WHEN category_help."type" IS NULL THEN NULL
									WHEN category_help."type" = 0 THEN 'url'
									WHEN category_help."type" = 1 THEN 'text'									
									ELSE 'other'
								END as "type"
							),
							category_help.data
						)
					END
				)
			)
		FROM 
			categories
		LEFT OUTER JOIN category_help ON categories.id = category_help.categoryid
		WHERE categories.id = mid
		GROUP BY
			categories.id,
			categories.name
	);
END;
$$;


ALTER FUNCTION public.category_to_xml_ext(mid integer) OWNER TO appdb;

--
-- Name: clean_cache(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION clean_cache() RETURNS boolean
    LANGUAGE plpgsql
    AS $$
  DECLARE a text[];                                                                                         
  DECLARE i int;                                                                                            
  BEGIN 
      RAISE NOTICE 'Cleaning cache...';                                                                     
      BEGIN
      a := ARRAY[
        'applications',                                                                                       
        'researchers',                                                                                        
        'categories',                                                                                         
        'countries',                                                                                        
        'disciplines',                                                                                        
        'middlewares',                                                                                        
        'vos',
        'positiontypes',                                                                                      
        'archs',                                                                                              
        'oses',
        'proglangs',                                                                                          
        'contacts',                                                                                         
        'contacttypes',                                                                                       
        'statuses',                                                                                         
        'app_middlewares',                                                                                  
        'licenses',
        'app_licenses',
        'sites'
      ];
  
      FOR i IN 1..array_length(a, 1) LOOP
          RAISE NOTICE 'Rebuilding fulltext index for %', a[i];                                             
          PERFORM rebuild_fulltext_index(a[i]);                                                             
      END LOOP;                                                                                                 
      
      RAISE NOTICE 'Rebuilding XML cache for applications';                                                 
      DELETE FROM cache.filtercache;                                                                        
      DELETE FROM cache.appxmlcache;
      INSERT INTO cache.appxmlcache (id, "xml") SELECT id, __app_to_xml(id) FROM applications;              
      EXCEPTION
          WHEN OTHERS THEN 
              RAISE NOTICE 'Failed to clean cache. Reason: % (%)', SQLERRM, SQLSTATE;                       
              RETURN FALSE;                                                                                 
      END;
      RAISE NOTICE 'Refreshing permissions';
	  PERFORM refresh_permissions();
      RAISE NOTICE 'Cache cleanup complete!';
      UPDATE config SET data = '0' WHERE var = 'cache_build_count' OR var = 'permissions_cache_dirty';
      RETURN TRUE;
  END;
  $$;


ALTER FUNCTION public.clean_cache() OWNER TO appdb;

--
-- Name: contacttype_to_xml(integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION contacttype_to_xml(mid integer) RETURNS xml
    LANGUAGE sql
    AS $_$SELECT xmlelement(name "person:contact", xmlattributes(
id as id, description as "type")) FROM contacttypes WHERE id = $1$_$;


ALTER FUNCTION public.contacttype_to_xml(mid integer) OWNER TO appdb;

--
-- Name: context_scripts_to_xml(integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION context_scripts_to_xml(contexts_id integer) RETURNS SETOF xml
    LANGUAGE sql STABLE
    AS $_$
SELECT contextscript_to_xml(contextscripts.id, context_script_assocs.contextid) AS xml
FROM contextscripts
INNER JOIN context_script_assocs ON context_script_assocs.scriptid = contextscripts.id
INNER JOIN contextformats ON contextformats.id = contextscripts.formatid
WHERE context_script_assocs.contextid = $1
GROUP BY contextscripts.id, context_script_assocs.contextid;
$_$;


ALTER FUNCTION public.context_scripts_to_xml(contexts_id integer) OWNER TO appdb;

--
-- Name: context_to_xml(integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION context_to_xml(appid integer) RETURNS xml
    LANGUAGE sql STABLE
    AS $_$
SELECT XMLELEMENT(NAME "contextualization:context",
	XMLATTRIBUTES(contexts.id AS id, 
		timezone('UTC'::text, contexts.addedon::timestamp with time zone) AS addedon, 
		timezone('UTC'::text, contexts.lastupdatedon::timestamp with time zone) AS lastupdatedon, 
		contexts.guid as guid),
	XMLELEMENT(NAME "contextualization:version", contexts.version),
	XMLELEMENT(NAME "contextualization:description", contexts.description),
	researcher_to_xml(contexts.addedby, 'addedby'::text),
	CASE WHEN contexts.lastupdatedby IS NULL THEN
		NULL
	ELSE
		researcher_to_xml(contexts.lastupdatedby, 'lastupdatedby'::text)
	END,
	(SELECT xmlagg(x.x) FROM context_scripts_to_xml(contexts.id) AS x)
) AS xml
FROM contexts
WHERE contexts.appid = $1
GROUP BY contexts.id;
$_$;


ALTER FUNCTION public.context_to_xml(appid integer) OWNER TO appdb;

--
-- Name: contextscript_images_to_xml(integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION contextscript_images_to_xml(contextscriptid integer) RETURNS xml
    LANGUAGE sql
    AS $_$WITH hypervisors AS (
	 WITH x AS (
		 SELECT vmiflavours_2.id,
		    unnest(vmiflavours_2.hypervisors) AS y
		   FROM vmiflavours vmiflavours_2
		)
	 SELECT vmiflavours_1.id AS vmiflavourid,
	    xmlagg(XMLELEMENT(NAME "virtualization:hypervisor", XMLATTRIBUTES(( SELECT hypervisors_1.id
		   FROM public.hypervisors hypervisors_1
		  WHERE hypervisors_1.name::text = x.y::text) AS id), x.y)) AS hypervisor
	   FROM vmiflavours vmiflavours_1
	JOIN x ON x.id = vmiflavours_1.id
	GROUP BY vmiflavours_1.id
)SELECT xmlagg(contextscriptimages.x) FROM (
SELECT DISTINCT ON (vaviews.appid,vaviews.vmiinstanceid)
XMLELEMENT(NAME "application:application", XMLATTRIBUTES(vaviews.appid AS id, vaviews.appcname AS cname, vaviews.imglst_private as imageListsPrivate),
	XMLELEMENT(NAME "application:name", vaviews.appname ),
	XMLELEMENT(NAME "virtualization:image",
	XMLATTRIBUTES(
		vaviews.vappversionid as versionid,
		vaviews.va_version_archived as archived,
		vaviews.va_version_enabled as enabled,
		vaviews.va_version_published as published,
		vaviews.va_version_expireson as expireson,
		CASE WHEN vaviews.va_version_expireson >= NOW() THEN FALSE ELSE TRUE END AS isexpired,
		vaviews.imglst_private as private,
		vaviews.vmiinstanceid as id,
		vaviews.vmiinstance_guid AS identifier,
		vaviews.vmiinstance_version as version,
		get_good_vmiinstanceid(vaviews.vmiinstanceid) as goodid
	),
	hypervisors.hypervisor::text::xml,
	XMLELEMENT(NAME "virtualization:os", XMLATTRIBUTES(oses.id AS id, vaviews.osversion AS version, oses.os_family_id as family_id), oses.name),
	XMLELEMENT(NAME "virtualization:arch", XMLATTRIBUTES(archs.id AS id), archs.name),
	XMLELEMENT(NAME "virtualization:format", vaviews.format))) as x
FROM vmiinstance_contextscripts AS vmics
INNER JOIN vaviews ON vaviews.vmiinstanceid = vmics.vmiinstanceid
LEFT OUTER JOIN hypervisors ON hypervisors.vmiflavourid = vaviews.vmiflavourid
LEFT OUTER JOIN archs ON archs.id = vaviews.archid
LEFT OUTER JOIN oses ON oses.id = vaviews.osid
LEFT OUTER JOIN vmiformats ON vmiformats.name::text = vaviews.format
WHERE vmics.contextscriptid = $1 AND vaviews.va_version_published
) AS contextscriptimages
$_$;


ALTER FUNCTION public.contextscript_images_to_xml(contextscriptid integer) OWNER TO appdb;

--
-- Name: contextscript_images_to_xml(integer, integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION contextscript_images_to_xml(contextscriptid integer, appid integer) RETURNS xml
    LANGUAGE sql STABLE
    AS $_$WITH hypervisors AS (
	 WITH x AS (
		 SELECT vmiflavours_2.id,
		    unnest(vmiflavours_2.hypervisors) AS y
		   FROM vmiflavours vmiflavours_2
		)
	 SELECT vmiflavours_1.id AS vmiflavourid,
	    xmlagg(XMLELEMENT(NAME "virtualization:hypervisor", XMLATTRIBUTES(( SELECT hypervisors_1.id
		   FROM public.hypervisors hypervisors_1
		  WHERE hypervisors_1.name::text = x.y::text) AS id), x.y)) AS hypervisor
	   FROM vmiflavours vmiflavours_1
	JOIN x ON x.id = vmiflavours_1.id
	GROUP BY vmiflavours_1.id
)SELECT xmlagg(contextscriptimages.x) FROM (
SELECT DISTINCT ON (vaviews.vmiinstanceid)
XMLELEMENT(NAME "virtualization:image",
	XMLATTRIBUTES(
		vaviews.vappversionid as versionid,
		vaviews.va_version_archived as archived,
		vaviews.va_version_enabled as enabled,
		vaviews.va_version_published as published,
		vaviews.va_version_expireson as expireson,
		CASE WHEN vaviews.va_version_expireson >= NOW() THEN FALSE ELSE TRUE END AS isexpired,
		vaviews.imglst_private as private,
		vaviews.vmiinstanceid as id,
		vaviews.vmiinstance_guid AS identifier,
		vaviews.vmiinstance_version as version,
		get_good_vmiinstanceid(vaviews.vmiinstanceid) as goodid
	),
	hypervisors.hypervisor::text::xml,
	XMLELEMENT(NAME "virtualization:os", XMLATTRIBUTES(oses.id AS id, vaviews.osversion AS version, oses.os_family_id as family_id), oses.name),
	XMLELEMENT(NAME "virtualization:arch", XMLATTRIBUTES(archs.id AS id), archs.name),
	XMLELEMENT(NAME "virtualization:format", vaviews.format)) as x
FROM vmiinstance_contextscripts AS vmics
INNER JOIN vaviews ON vaviews.vmiinstanceid = vmics.vmiinstanceid
LEFT OUTER JOIN hypervisors ON hypervisors.vmiflavourid = vaviews.vmiflavourid
LEFT OUTER JOIN archs ON archs.id = vaviews.archid
LEFT OUTER JOIN oses ON oses.id = vaviews.osid
LEFT OUTER JOIN vmiformats ON vmiformats.name::text = vaviews.format
WHERE vmics.contextscriptid = $1 AND vaviews.appid = $2
) AS contextscriptimages
$_$;


ALTER FUNCTION public.contextscript_images_to_xml(contextscriptid integer, appid integer) OWNER TO appdb;

--
-- Name: contextscript_to_xml(integer, integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION contextscript_to_xml(contextscript_id integer, contextid integer DEFAULT 0) RETURNS SETOF xml
    LANGUAGE sql STABLE
    AS $_$
SELECT xmlagg(
	XMLELEMENT(NAME "contextualization:contextscript", XMLATTRIBUTES(contextscripts.id AS id, 
		timezone('UTC'::text, context_script_assocs.addedon::timestamp with time zone) AS addedon, 
		timezone('UTC'::text, contextscripts.lastupdatedon::timestamp with time zone) AS lastupdatedon, 
		context_script_assocs.id AS relationid, 
		contextscripts.guid as guid ),
	XMLELEMENT(NAME "contextualization:url", contextscripts.url ),
	XMLELEMENT(NAME "contextualization:title", contextscripts.title ),
	XMLELEMENT(NAME "contextualization:description", contextscripts.description ),
	XMLELEMENT(NAME "contextualization:name", contextscripts.name ),
	XMLELEMENT(NAME "contextualization:format", XMLATTRIBUTES(contextscripts.formatid AS id, contextformats.name AS name)),
	XMLELEMENT(NAME "contextualization:checksum", XMLATTRIBUTES(contextscripts.checksumfunc::text AS hashtype), contextscripts.checksum ),
	XMLELEMENT(NAME "contextualization:size", contextscripts.size ),
	researcher_to_xml(context_script_assocs.addedby, 'addedby'::text),
	contextscript_vappliances_to_xml(contextscripts.id)
)) AS xml
FROM contextscripts
INNER JOIN context_script_assocs ON context_script_assocs.scriptid = contextscripts.id
INNER JOIN contextformats ON contextformats.id = contextscripts.formatid
WHERE contextscripts.id = $1 AND CASE WHEN $2 > 0 THEN context_script_assocs.contextid = $2 ELSE TRUE END
GROUP BY contextscripts.id;
$_$;


ALTER FUNCTION public.contextscript_to_xml(contextscript_id integer, contextid integer) OWNER TO appdb;

--
-- Name: contextscript_vappliances_to_xml(integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION contextscript_vappliances_to_xml(contextscriptid integer) RETURNS xml
    LANGUAGE sql STABLE
    AS $_$
SELECT DISTINCT ON (vaviews.appid)
XMLELEMENT(NAME "application:application", XMLATTRIBUTES(vaviews.appid AS id, vaviews.appcname AS cname, vaviews.imglst_private as imageListsPrivate, apps.deleted, apps.moderated,CASE WHEN vaviews.va_version_expireson >= NOW() THEN FALSE ELSE TRUE END AS isexpired),
	XMLELEMENT(NAME "application:name", vaviews.appname ),
	(SELECT XMLELEMENT(NAME "virtualization:latestversion", XMLATTRIBUTES(
		ltst.id,
		ltst.version,
		ltst.enabled,
		ltst.isprivate,
		ltst.isexpired,
		ltst.guid
	)) FROM vappliance_latest_version(vaviews.appid) AS ltst),
	contextscript_images_to_xml($1,vaviews.appid)
	)
FROM vmiinstance_contextscripts AS vmics
INNER JOIN vaviews ON vaviews.vmiinstanceid = vmics.vmiinstanceid
INNER JOIN applications AS apps ON apps.id = vaviews.appid
WHERE vmics.contextscriptid = $1;
$_$;


ALTER FUNCTION public.contextscript_vappliances_to_xml(contextscriptid integer) OWNER TO appdb;

--
-- Name: count_app_matches(text, text[], boolean); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION count_app_matches(itemname text, cachetable text[], private boolean DEFAULT false) RETURNS SETOF record
    LANGUAGE plpgsql
    AS $$
BEGIN
	RAISE NOTICE '%', '(SELECT * FROM applications WHERE id IN (SELECT id FROM ' || array_to_string(cachetable, ' INTERSECT SELECT id FROM ') || '))';
	RETURN QUERY SELECT * FROM count_app_matches(itemname, '(SELECT * FROM applications WHERE id IN (SELECT id FROM ' || array_to_string(cachetable, ' INTERSECT SELECT id FROM ') || '))', private) AS count_app_matches(count_text text, count bigint, count_id text);
END;
$$;


ALTER FUNCTION public.count_app_matches(itemname text, cachetable text[], private boolean) OWNER TO appdb;

--
-- Name: count_app_matches(text, text, boolean); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION count_app_matches(itemname text, cachetable text, private boolean DEFAULT false) RETURNS SETOF record
    LANGUAGE plpgsql
    AS $$
DECLARE q TEXT;
DECLARE allitems INT;
BEGIN
	IF itemname = 'country' THEN
		q := 'SELECT countries.name::TEXT AS count_text, COUNT(DISTINCT applications.id) AS count, countries.id AS count_id FROM ' || cachetable || ' AS applications LEFT JOIN app_countries ON app_countries.appid = applications.id LEFT JOIN countries ON countries.id = app_countries.countryid';
	ELSIF itemname = 'license' THEN
		q := 'SELECT CASE WHEN NOT licenses.name IS NULL THEN licenses.name::TEXT ELSE ''Other'' END AS count_text, COUNT(DISTINCT applications.id) AS count, CASE WHEN NOT licenses.id IS NULL THEN licenses.id ELSE 0 END AS count_id FROM ' || cachetable || ' AS applications INNER JOIN app_licenses ON app_licenses.appid = applications.id LEFT JOIN licenses ON licenses.id = app_licenses.licenseid';	
	ELSIF itemname = 'status' THEN
		q := 'SELECT statuses.name::TEXT AS count_text, COUNT(DISTINCT applications.id) AS count, statuses.id AS count_id FROM ' || cachetable || ' AS applications LEFT JOIN statuses ON statuses.id = applications.statusid';
	ELSIF itemname = 'discipline' THEN
		q := 'SELECT disciplines.name::TEXT AS count_text, COUNT(DISTINCT applications.id) AS count, disciplines.id AS count_id FROM ' || cachetable || ' AS applications' || CASE WHEN NOT private THEN ' LEFT JOIN app_vos ON app_vos.appid = applications.id LEFT JOIN vos ON vos.id = app_vos.void AND vos.deleted IS FALSE' ELSE '' END || ' LEFT JOIN appdisciplines ON appdisciplines.appid = applications.id LEFT JOIN disciplines ON disciplines.id = appdisciplines.disciplineid' || CASE WHEN NOT private THEN ' OR disciplines.id = vos.domainid' ELSE '' END;
	ELSIF itemname = 'category' THEN
		q := 'SELECT categories.name::TEXT AS count_text, COUNT(DISTINCT applications.id) AS count, categories.id AS count_id FROM ' || cachetable || ' AS applications LEFT JOIN categories ON categories.id = ANY(applications.categoryid)';
	ELSIF itemname = 'proglang' THEN
		q := 'SELECT proglangs.name::TEXT AS count_text, COUNT(DISTINCT applications.id) AS count, proglangs.id AS count_id FROM ' || cachetable || ' AS applications LEFT JOIN appproglangs ON appproglangs.appid = applications.id LEFT JOIN proglangs ON proglangs.id = appproglangs.proglangid';
	ELSIF itemname = 'arch' THEN
		-- q := 'SELECT archs.name::TEXT AS count_text, COUNT(DISTINCT applications.id) AS count, archs.id AS count_id FROM ' || cachetable || ' AS applications LEFT JOIN app_archs ON app_archs.appid = applications.id LEFT JOIN archs ON archs.id = app_archs.archid';
		q := 'SELECT archs.name::TEXT AS count_text, COUNT(DISTINCT applications.id) AS count, archs.id AS count_id FROM ' || cachetable || ' AS applications 
LEFT JOIN vapplications ON vapplications.appid = applications.id 
LEFT JOIN vapp_versions ON vapp_versions.vappid = vapplications.id AND published AND enabled AND NOT archived AND status = ''verified''
LEFT JOIN vmis ON vmis.vappid = vapplications.id
LEFT JOIN vmiflavours ON vmiflavours.vmiid = vmis.id
LEFT JOIN archs ON archs.id = vmiflavours.archid';
	ELSIF itemname = 'os' THEN
		-- q := 'SELECT oses.name::TEXT AS count_text, COUNT(DISTINCT applications.id) AS count, oses.id AS count_id FROM ' || cachetable || ' AS applications LEFT JOIN app_oses ON app_oses.appid = applications.id LEFT JOIN oses ON oses.id = app_oses.osid';
		q := 'SELECT oses.name::TEXT AS count_text, COUNT(DISTINCT applications.id) AS count, oses.id AS count_id FROM ' || cachetable || ' AS applications 
LEFT JOIN vapplications ON vapplications.appid = applications.id 
LEFT JOIN vapp_versions ON vapp_versions.vappid = vapplications.id AND published AND enabled AND NOT archived AND status = ''verified''
LEFT JOIN vmis ON vmis.vappid = vapplications.id
LEFT JOIN vmiflavours ON vmiflavours.vmiid = vmis.id
LEFT JOIN oses ON oses.id = vmiflavours.osid';
	ELSIF itemname = 'osfamily' THEN
		q := 'SELECT os_families.name::TEXT AS count_text, COUNT(DISTINCT applications.id) AS count, os_families.id AS count_id FROM ' || cachetable || ' AS applications 
LEFT JOIN vapplications ON vapplications.appid = applications.id 
LEFT JOIN vapp_versions ON vapp_versions.vappid = vapplications.id AND published AND enabled AND NOT archived AND status = ''verified''
LEFT JOIN vmis ON vmis.vappid = vapplications.id
LEFT JOIN vmiflavours ON vmiflavours.vmiid = vmis.id
LEFT JOIN oses ON oses.id = vmiflavours.osid
LEFT JOIN os_families ON os_families.id = oses.os_family_id';
	ELSIF itemname = 'hypervisor' THEN
		q := 'SELECT hypervisors.name::TEXT AS count_text, COUNT(DISTINCT applications.id) AS count, hypervisors.id::int AS count_id FROM ' || cachetable || ' AS applications 
LEFT JOIN vapplications ON vapplications.appid = applications.id 
LEFT JOIN vapp_versions ON vapp_versions.vappid = vapplications.id AND published AND enabled AND NOT archived AND status = ''verified''
LEFT JOIN vmis ON vmis.vappid = vapplications.id
LEFT JOIN vmiflavours ON vmiflavours.vmiid = vmis.id
LEFT JOIN hypervisors ON hypervisors.name::text = ANY(vmiflavours.hypervisors::TEXT[])';
	ELSIF itemname = 'vo' THEN
		q := 'SELECT vos.name::TEXT AS count_text, COUNT(DISTINCT applications.id) AS count, vos.id AS count_id FROM ' || cachetable || ' AS applications LEFT JOIN app_vos ON app_vos.appid = applications.id LEFT JOIN vos ON vos.id = app_vos.void AND vos.deleted IS FALSE';
	ELSIF itemname = 'middleware' THEN
		q := 'SELECT middlewares.name::TEXT AS count_text, COUNT(DISTINCT applications.id) AS count, middlewares.id AS count_id FROM ' || cachetable || ' AS applications LEFT JOIN app_middlewares ON app_middlewares.appid = applications.id' || CASE WHEN NOT private THEN ' LEFT JOIN app_vos ON app_vos.appid = applications.id LEFT JOIN vos ON vos.id = app_vos.void AND vos.deleted IS FALSE LEFT JOIN vo_middlewares ON vo_middlewares.void = vos.id' ELSE '' END || ' LEFT JOIN middlewares ON middlewares.id = app_middlewares.middlewareid' || CASE WHEN NOT private THEN ' OR middlewares.id = vo_middlewares.middlewareid' ELSE '' END;
	ELSIF itemname = 'validated' THEN
		EXECUTE 'SELECT COUNT(*) FROM ' || cachetable || ' AS t ' INTO allitems;
		q := 
		'SELECT ''6 months''::TEXT AS count_text, COUNT(DISTINCT applications.id) AS count, 3 AS count_id FROM ' || cachetable || ' AS applications WHERE lastupdated BETWEEN NOW() - INTERVAL ''6 months'' AND NOW() ' ||
		'UNION ' ||
		'SELECT ''1 year''::TEXT AS count_text, COUNT(DISTINCT applications.id) AS count, 4 AS count_id FROM ' || cachetable || ' AS applications WHERE lastupdated BETWEEN NOW() - INTERVAL ''1 year'' AND NOW() ' ||
		'UNION ' ||
		'SELECT ''2 years''::TEXT AS count_text, COUNT(DISTINCT applications.id) AS count, 5 AS count_id FROM ' || cachetable || ' AS applications WHERE lastupdated BETWEEN NOW() - INTERVAL ''2 years'' AND NOW() ' ||
		'UNION ' ||
		'SELECT ''3 years''::TEXT AS count_text, COUNT(DISTINCT applications.id) AS count, 6 AS count_id FROM ' || cachetable || ' AS applications WHERE lastupdated BETWEEN NOW() - INTERVAL ''3 years'' AND NOW() ' ||
		'UNION ' ||
		'SELECT ''false''::TEXT AS count_text, COUNT(DISTINCT applications.id) AS count, 2 AS count_id FROM ' || cachetable || ' AS applications WHERE lastupdated < NOW() - INTERVAL ''3 years'' OR lastupdated IS NULL ' ||
		'UNION ' ||
		'SELECT ''true''::TEXT AS count_text, COUNT(DISTINCT applications.id) AS count, 1 AS count_id FROM ' || cachetable || ' AS applications WHERE lastupdated >= NOW() - INTERVAL ''3 years'' AND NOT lastupdated IS NULL';
	ELSE
		RAISE NOTICE 'Unknown application property requested for logistics counting: %', itemname;
		RETURN;
	END IF;
	RETURN QUERY EXECUTE 'SELECT count_text, count, count_id::text FROM (' || q || ' GROUP BY count_text, count_id) AS t WHERE NOT count_text IS NULL';
END;
$$;


ALTER FUNCTION public.count_app_matches(itemname text, cachetable text, private boolean) OWNER TO appdb;

--
-- Name: FUNCTION count_app_matches(itemname text, cachetable text, private boolean); Type: COMMENT; Schema: public; Owner: appdb
--

COMMENT ON FUNCTION count_app_matches(itemname text, cachetable text, private boolean) IS 'not to be called directly; used by app_logistics function';


--
-- Name: count_ppl_matches(text, text[], boolean); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION count_ppl_matches(itemname text, cachetable text[], private boolean DEFAULT false) RETURNS SETOF record
    LANGUAGE plpgsql
    AS $$
BEGIN
	RAISE NOTICE '%', '(SELECT * FROM researchers WHERE id IN (SELECT id FROM ' || array_to_string(cachetable, ' INTERSECT SELECT id FROM ') || '))';
	RETURN QUERY SELECT * FROM count_ppl_matches(itemname, '(SELECT * FROM researchers WHERE id IN (SELECT id FROM ' || array_to_string(cachetable, ' INTERSECT SELECT id FROM ') || '))', private) AS count_ppl_matches(count_text text, count bigint, count_id text);
END;
$$;


ALTER FUNCTION public.count_ppl_matches(itemname text, cachetable text[], private boolean) OWNER TO appdb;

--
-- Name: count_ppl_matches(text, text, boolean); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION count_ppl_matches(itemname text, cachetable text, private boolean DEFAULT false) RETURNS SETOF record
    LANGUAGE plpgsql
    AS $$
DECLARE q TEXT;
BEGIN
	IF itemname = 'country' THEN
		q := 'SELECT countries.name::TEXT AS count_text, COUNT(DISTINCT researchers.id) AS count, countries.id AS count_id FROM ' || cachetable || ' AS researchers ' || CASE WHEN NOT private THEN ' LEFT JOIN researchers_apps ON researchers_apps.researcherid = researchers.id LEFT JOIN applications ON applications.id = researchers_apps.appid LEFT JOIN app_countries ON app_countries.appid = applications.id LEFT JOIN countries ON countries.id = app_countries.countryid OR countries.id = researchers.countryid' ELSE ' LEFT JOIN countries ON countries.id = researchers.countryid' END;
	ELSIF itemname = 'discipline' THEN
		q := 'SELECT disciplines.name::TEXT AS count_text, COUNT(DISTINCT researchers.id) AS count, disciplines.id AS count_id FROM ' || cachetable || ' AS researchers LEFT JOIN researchers_apps ON researchers_apps.researcherid = researchers.id LEFT JOIN applications ON applications.id = researchers_apps.appid LEFT JOIN appdisciplines ON appdisciplines.appid = applications.id LEFT JOIN app_vos ON app_vos.appid = applications.id LEFT JOIN vos ON vos.id = app_vos.void LEFT JOIN disciplines ON disciplines.id = appdisciplines.disciplineid OR disciplines.id = vos.domainid';
	ELSIF itemname = 'proglang' THEN
		q := 'SELECT proglangs.name::TEXT AS count_text, COUNT(DISTINCT researchers.id) AS count, proglangs.id AS count_id FROM ' || cachetable || ' AS researchers LEFT JOIN researchers_apps ON researchers_apps.researcherid = researchers.id LEFT JOIN applications ON applications.id = researchers_apps.appid LEFT JOIN appproglangs ON appproglangs.appid = applications.id LEFT JOIN proglangs ON proglangs.id = appproglangs.proglangid';
	ELSIF itemname = 'role' THEN
		q := 'SELECT positiontypes.description::TEXT AS count_text, COUNT(DISTINCT researchers.id) AS count, positiontypes.id AS count_id FROM ' || cachetable || ' AS researchers LEFT JOIN positiontypes ON positiontypes.id = researchers.positiontypeid';
	ELSIF itemname = 'group' THEN
		q := 'SELECT actor_groups.name::TEXT as count_text, COUNT(DISTINCT researchers.id) AS count, actor_groups.id AS count_id FROM ' || cachetable || ' AS researchers LEFT JOIN actor_group_members ON actor_group_members.actorid = researchers.guid LEFT JOIN actor_groups ON actor_groups.id = actor_group_members.groupid AND (actor_groups.id > 0 OR actor_groups.id IN (-1, -2, -3))';
	ELSE
		RAISE NOTICE 'Unknown researcher property requested for logistics counting: %', itemname;
		RETURN;
	END IF;
	RETURN QUERY EXECUTE 'SELECT count_text, count, count_id::text FROM (' || q || ' WHERE NOT researchers.deleted GROUP BY count_text, count_id) AS t WHERE NOT count_text IS NULL';
END;
$$;


ALTER FUNCTION public.count_ppl_matches(itemname text, cachetable text, private boolean) OWNER TO appdb;

--
-- Name: FUNCTION count_ppl_matches(itemname text, cachetable text, private boolean); Type: COMMENT; Schema: public; Owner: appdb
--

COMMENT ON FUNCTION count_ppl_matches(itemname text, cachetable text, private boolean) IS 'not to be called directly; used by ppl_logistics function';


--
-- Name: count_site_matches(text, text[], boolean); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION count_site_matches(itemname text, cachetable text[], private boolean DEFAULT false) RETURNS SETOF record
    LANGUAGE plpgsql
    AS $$
BEGIN
	RAISE NOTICE '%', '(SELECT * FROM sites WHERE id IN (SELECT id FROM ' || array_to_string(cachetable, ' INTERSECT SELECT id FROM ') || '))';
	RETURN QUERY SELECT * FROM count_site_matches(itemname, '(SELECT * FROM sites WHERE id IN (SELECT id FROM ' || array_to_string(cachetable, ' INTERSECT SELECT id FROM ') || '))', private) AS count_site_matches(count_text text, count bigint, count_id text);
END;
$$;


ALTER FUNCTION public.count_site_matches(itemname text, cachetable text[], private boolean) OWNER TO appdb;

--
-- Name: count_site_matches(text, text, boolean); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION count_site_matches(itemname text, cachetable text, private boolean DEFAULT false) RETURNS SETOF record
    LANGUAGE plpgsql
    AS $$
DECLARE q TEXT;
DECLARE allitems INT;
BEGIN
	IF itemname = 'country' THEN
		q := 'SELECT countries.name::TEXT AS count_text, COUNT(DISTINCT sites.id) AS count, countries.id AS count_id FROM ' || cachetable || ' AS sites LEFT JOIN countries ON countries.id = sites.countryid';
	ELSIF itemname = 'discipline' THEN
		q := 'SELECT disciplines.name::TEXT AS count_text, COUNT(DISTINCT sites.id) AS count, disciplines.id AS count_id FROM ' || cachetable || ' AS sites LEFT JOIN va_providers ON va_providers.sitename = sites.name
		LEFT JOIN va_provider_images ON va_provider_images.va_provider_id = va_providers.id
		LEFT JOIN vaviews ON vaviews.vmiinstanceid = va_provider_images.vmiinstanceid
		LEFT JOIN applications ON applications.id = vaviews.appid
		LEFT JOIN appdisciplines ON appdisciplines.appid = applications.id
		LEFT JOIN disciplines ON disciplines.id = appdisciplines.disciplineid';
		-- q := 'SELECT disciplines.name::TEXT AS count_text, COUNT(DISTINCT sites.id) AS count, disciplines.id AS count_id FROM ' || cachetable || ' AS sites' || CASE WHEN NOT private THEN ' LEFT JOIN app_vos ON app_vos.appid = applications.id LEFT JOIN vos ON vos.id = app_vos.void AND vos.deleted IS FALSE' ELSE '' END || ' LEFT JOIN appdisciplines ON appdisciplines.appid = applications.id LEFT JOIN disciplines ON disciplines.id = appdisciplines.disciplineid' || CASE WHEN NOT private THEN ' OR disciplines.id = vos.domainid' ELSE '' END;
	ELSIF itemname = 'category' THEN
		q := 'SELECT categories.name::TEXT AS count_text, COUNT(DISTINCT sites.id) AS count, categories.id AS count_id
		FROM ' || cachetable || ' AS sites LEFT JOIN va_providers ON va_providers.sitename = sites.name LEFT JOIN va_provider_images ON va_provider_images.va_provider_id = va_providers.id
		LEFT JOIN vaviews ON vaviews.vmiinstanceid = va_provider_images.vmiinstanceid LEFT JOIN applications ON applications.id = vaviews.appid LEFT JOIN categories ON categories.id = ANY(applications.categoryid)';
		--q := 'SELECT categories.name::TEXT AS count_text, COUNT(DISTINCT applications.id) AS count, categories.id AS count_id FROM ' || cachetable || ' AS applications LEFT JOIN categories ON categories.id = ANY(applications.categoryid)';
	ELSIF itemname = 'arch' THEN
		q := 'SELECT archs.name::TEXT AS count_text, COUNT(DISTINCT sites.id) AS count, archs.id AS count_id FROM ' || cachetable || ' AS sites LEFT JOIN va_providers ON va_providers.sitename = sites.name
		LEFT JOIN va_provider_images ON va_provider_images.va_provider_id = va_providers.id
		LEFT JOIN vaviews ON vaviews.vmiinstanceid = va_provider_images.vmiinstanceid
		LEFT JOIN applications ON applications.id = vaviews.appid
		LEFT JOIN vapplications ON vapplications.appid = applications.id
		LEFT JOIN vapp_versions ON vapp_versions.vappid = vapplications.id AND published AND enabled AND NOT archived AND status = ''verified''
		LEFT JOIN vmis ON vmis.vappid = vapplications.id
		LEFT JOIN vmiflavours ON vmiflavours.vmiid = vmis.id
		LEFT JOIN archs ON archs.id = vmiflavours.archid';
	ELSIF itemname = 'os' THEN
		q := 'SELECT oses.name::TEXT AS count_text, COUNT(DISTINCT sites.id) AS count, oses.id AS count_id FROM  ' || cachetable || ' AS sites
		LEFT JOIN va_providers ON va_providers.sitename = sites.name
		LEFT JOIN va_provider_images ON va_provider_images.va_provider_id = va_providers.id
		LEFT JOIN vaviews ON vaviews.vmiinstanceid = va_provider_images.vmiinstanceid
		LEFT JOIN applications ON applications.id = vaviews.appid
		LEFT JOIN vapplications ON vapplications.appid = applications.id
		LEFT JOIN vapp_versions ON vapp_versions.vappid = vapplications.id AND published AND enabled AND NOT archived AND status = ''verified''
		LEFT JOIN vmis ON vmis.vappid = vapplications.id
		LEFT JOIN vmiflavours ON vmiflavours.vmiid = vmis.id
		LEFT JOIN oses ON oses.id = vmiflavours.osid';
	ELSIF itemname = 'osfamily' THEN
		q := 'SELECT os_families.name::TEXT AS count_text, COUNT(DISTINCT sites.id) AS count, os_families.id AS count_id FROM ' || cachetable || ' AS sites
		LEFT JOIN va_providers ON va_providers.sitename = sites.name
		LEFT JOIN va_provider_images ON va_provider_images.va_provider_id = va_providers.id
		LEFT JOIN vaviews ON vaviews.vmiinstanceid = va_provider_images.vmiinstanceid
		LEFT JOIN applications ON applications.id = vaviews.appid
		LEFT JOIN vapplications ON vapplications.appid = applications.id
		LEFT JOIN vapp_versions ON vapp_versions.vappid = vapplications.id AND published AND enabled AND NOT archived AND status = ''verified''
		LEFT JOIN vmis ON vmis.vappid = vapplications.id
		LEFT JOIN vmiflavours ON vmiflavours.vmiid = vmis.id
		LEFT JOIN oses ON oses.id = vmiflavours.osid
		LEFT JOIN os_families ON os_families.id = oses.os_family_id';
	ELSIF itemname = 'hypervisor' THEN
		q :='SELECT hypervisors.name::TEXT AS count_text, COUNT(DISTINCT sites.id) AS count, hypervisors.id::int AS count_id FROM ' || cachetable || ' AS sites
		LEFT JOIN va_providers ON va_providers.sitename = sites.name
		LEFT JOIN va_provider_images ON va_provider_images.va_provider_id = va_providers.id
		LEFT JOIN vaviews ON vaviews.vmiinstanceid = va_provider_images.vmiinstanceid
		LEFT JOIN applications ON applications.id = vaviews.appid
		LEFT JOIN vapplications ON vapplications.appid = applications.id
		LEFT JOIN vapp_versions ON vapp_versions.vappid = vapplications.id AND published AND enabled AND NOT archived AND status = ''verified''
		LEFT JOIN vmis ON vmis.vappid = vapplications.id
		LEFT JOIN vmiflavours ON vmiflavours.vmiid = vmis.id
		LEFT JOIN hypervisors ON hypervisors.name::text = ANY(vmiflavours.hypervisors::TEXT[])';
	ELSIF itemname = 'vo' THEN
		q := 'SELECT vos.name::TEXT AS count_text, COUNT(DISTINCT sites.id) AS count, vos.id AS count_id FROM ' || cachetable || ' AS sites
		LEFT JOIN va_providers ON va_providers.sitename = sites.name
		LEFT JOIN va_provider_images ON va_provider_images.va_provider_id = va_providers.id AND va_provider_images.vowide_vmiinstanceid IS NOT NULL
		LEFT JOIN vowide_image_list_images ON vowide_image_list_images.ID = va_provider_images.vowide_vmiinstanceid and vowide_image_list_images.state = ''up-to-date''::e_vowide_image_state
		LEFT JOIN vowide_image_lists ON vowide_image_lists.id = vowide_image_list_images.vowide_image_list_id AND vowide_image_list_images.state <> ''draft''::e_vowide_image_state
		LEFT JOIN vos ON vos.id = vowide_image_lists.void AND vos.deleted IS FALSE';
	ELSIF itemname = 'middleware' THEN
		q := 'SELECT middlewares.name::TEXT AS count_text, COUNT(DISTINCT sites.id) AS count, middlewares.id AS count_id FROM ' || cachetable || ' AS sites
		LEFT JOIN va_providers ON va_providers.sitename = sites.name
		LEFT JOIN va_provider_images ON va_provider_images.va_provider_id = va_providers.id
		LEFT JOIN vaviews ON vaviews.vmiinstanceid = va_provider_images.vmiinstanceid
		LEFT JOIN applications ON applications.id = vaviews.appid
		LEFT JOIN app_middlewares ON app_middlewares.appid = applications.id
		LEFT JOIN middlewares ON middlewares.id = app_middlewares.middlewareid';
	ELSIF itemname = 'supports' THEN
		q := 'SELECT CASE WHEN va_providers.sitename IS NULL THEN ''none''
		ELSE ''occi'' END AS count_text, COUNT(DISTINCT sites.id) AS count,
		CASE WHEN va_providers.sitename IS NULL THEN 0 ELSE 1 END AS count_id
		FROM ' || cachetable || ' AS sites
		LEFT JOIN va_providers ON va_providers.sitename = sites.name and va_providers.in_production = true';
	ELSIF itemname = 'hasinstances' THEN
		q := 'SELECT CASE WHEN va_provider_images.vmiinstanceid IS NULL THEN ''none''
		ELSE ''virtual images'' END AS count_text, COUNT(DISTINCT sites.id) AS count,
		CASE WHEN va_provider_images.vmiinstanceid IS NULL THEN 0 ELSE 1 END AS count_id
		FROM ' || cachetable || ' AS sites
		LEFT JOIN va_providers ON va_providers.sitename = sites.name and va_providers.in_production = true
		LEFT JOIN va_provider_images ON va_provider_images.va_provider_id = va_providers.id
		LEFT JOIN vaviews ON vaviews.vmiinstanceid = va_provider_images.vmiinstanceid';
	ELSE
		RAISE NOTICE 'Unknown site property requested for logistics counting: %', itemname;
		RETURN;
	END IF;
	RETURN QUERY EXECUTE 'SELECT count_text, count, count_id::text FROM (' || q || ' GROUP BY count_text, count_id) AS t WHERE NOT count_text IS NULL';
END;
$$;


ALTER FUNCTION public.count_site_matches(itemname text, cachetable text, private boolean) OWNER TO appdb;

--
-- Name: FUNCTION count_site_matches(itemname text, cachetable text, private boolean); Type: COMMENT; Schema: public; Owner: appdb
--

COMMENT ON FUNCTION count_site_matches(itemname text, cachetable text, private boolean) IS 'not to be called directly; used by site_logistics function';


--
-- Name: count_vo_matches(text, text[], boolean); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION count_vo_matches(itemname text, cachetable text[], private boolean DEFAULT false) RETURNS SETOF record
    LANGUAGE plpgsql
    AS $$
BEGIN
	RAISE NOTICE '%', '(SELECT * FROM vos WHERE id IN (SELECT id FROM ' || array_to_string(cachetable, ' INTERSECT SELECT id FROM ') || '))';
	RETURN QUERY SELECT * FROM count_vo_matches(itemname, '(SELECT * FROM vos WHERE id IN (SELECT id FROM ' || array_to_string(cachetable, ' INTERSECT SELECT id FROM ') || '))', private) AS count_vo_matches(count_text text, count bigint, count_id text);
END;
$$;


ALTER FUNCTION public.count_vo_matches(itemname text, cachetable text[], private boolean) OWNER TO appdb;

--
-- Name: count_vo_matches(text, text, boolean); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION count_vo_matches(itemname text, cachetable text, private boolean DEFAULT false) RETURNS SETOF record
    LANGUAGE plpgsql
    AS $$
DECLARE q TEXT;
DECLARE allitems INT;
BEGIN
	IF itemname = 'middleware' THEN
		q := 'SELECT middlewares.name::TEXT AS count_text, COUNT(DISTINCT vos.id) AS count, middlewares.id AS count_id FROM ' || cachetable || ' AS 
vos LEFT JOIN vo_middlewares ON vo_middlewares.void = vos.id ' || CASE WHEN NOT private THEN 'LEFT JOIN app_vos ON app_vos.void = vos.id LEFT JOIN applications ON applications.id = app_vos.appid LEFT JOIN app_middlewares ON app_middlewares.appid = applications.id ' ELSE '' END || 'LEFT JOIN middlewares ON middlewares.id = vo_middlewares.middlewareid ' || CASE WHEN NOT private THEN 'OR middlewares.id = app_middlewares.middlewareid' ELSE '' END;
	ELSIF itemname = 'discipline' THEN
		q := 'SELECT disciplines.name::TEXT AS count_text, COUNT(DISTINCT vos.id) AS count, disciplines.id AS count_id FROM ' || cachetable || ' AS vos' || CASE WHEN NOT private THEN ' LEFT JOIN app_vos ON app_vos.void = vos.id LEFT JOIN applications ON applications.id = app_vos.appid AND NOT (applications.deleted OR applications.moderated)' ELSE '' END || ' LEFT JOIN disciplines ON disciplines.id = ANY(vos.disciplineid)' || CASE WHEN NOT private THEN ' OR disciplines.id = ANY(applications.disciplineid)' ELSE '' END;		
	ELSIF itemname = 'scope' THEN
		q := 'SELECT COALESCE(vos.scope, ''N/A'') AS count_text, COUNT(DISTINCT vos.id) AS count, scopes.id::int AS count_id FROM ' || cachetable || ' AS vos LEFT JOIN (SELECT DISTINCT ON (scope) COALESCE(scope, ''N/A'') AS scope, rank() OVER (ORDER BY scope) AS id FROM vos) AS scopes ON scopes.scope = COALESCE(vos.scope, ''N/A'')';
	ELSIF itemname = 'storetype' THEN
		q := 'SELECT CASE 
			WHEN applications.categoryid IS NULL THEN ''None'' 
			WHEN 34 = ANY(applications.categoryid) THEN ''virtual appliances'' 
			ELSE ''applications'' 
		END AS count_text, COUNT(DISTINCT vos.id) AS count, 
		CASE 
			WHEN applications.categoryid IS NULL THEN 0
			WHEN 34 = ANY(applications.categoryid) THEN 2
			ELSE 1
		END AS count_id 
		FROM ' || cachetable || ' AS vos
		LEFT OUTER JOIN app_vos ON app_vos.void = vos.id
		LEFT OUTER JOIN applications ON applications.id = app_vos.appid 
		WHERE NOT (applications.deleted OR applications.moderated)';
	ELSE
		RAISE NOTICE 'Unknown VO property requested for logistics counting: %', itemname;
		RETURN;
	END IF;
	RETURN QUERY EXECUTE 'SELECT count_text, count, count_id::text FROM (' || q || ' GROUP BY count_text, count_id) AS t WHERE NOT count_text IS NULL';
END;
$$;


ALTER FUNCTION public.count_vo_matches(itemname text, cachetable text, private boolean) OWNER TO appdb;

--
-- Name: FUNCTION count_vo_matches(itemname text, cachetable text, private boolean); Type: COMMENT; Schema: public; Owner: appdb
--

COMMENT ON FUNCTION count_vo_matches(itemname text, cachetable text, private boolean) IS 'not to be called directly; used by vo_logistics function';


--
-- Name: countinstring(text, text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION countinstring(text, text) RETURNS integer
    LANGUAGE sql IMMUTABLE
    AS $_$
 SELECT(Length($1) - Length(REPLACE($1, $2, ''))) / Length($2) ;
$_$;


ALTER FUNCTION public.countinstring(text, text) OWNER TO appdb;

--
-- Name: country_to_xml(integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION country_to_xml(mid integer) RETURNS xml
    LANGUAGE sql
    AS $_$SELECT xmlelement(name "regional:country", xmlattributes(
countries.id as id, countries.isocode as isocode, countries.regionid as regionid), countries.name) FROM countries WHERE id = $1 ORDER BY id$_$;


ALTER FUNCTION public.country_to_xml(mid integer) OWNER TO appdb;

--
-- Name: country_to_xml(integer, integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION country_to_xml(mid integer, mappid integer) RETURNS xml
    LANGUAGE sql
    AS $_$SELECT xmlelement(name "regional:country", xmlattributes(
countries.id as id, countries.isocode as isocode, countries.regionid as regionid,
CASE WHEN NOT $2 IS NULL THEN
CASE WHEN EXISTS (SELECT inherited FROM appcountries WHERE appid = $2 AND appcountries.id = countries.id AND inherited = 1) THEN true ELSE false END 
END as "inherited"), countries.name) FROM countries WHERE id = $1 ORDER BY id$_$;


ALTER FUNCTION public.country_to_xml(mid integer, mappid integer) OWNER TO appdb;

--
-- Name: dataset_location_to_xml(integer[]); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION dataset_location_to_xml(mid integer[]) RETURNS SETOF xml
    LANGUAGE sql STABLE
    AS $$
SELECT
    XMLELEMENT(
        name "dataset:location",
        XMLATTRIBUTES(
            dataset_locations.id,
            dataset_locations.dataset_version_id AS datasetversionid,
            (SELECT datasetid FROM dataset_versions WHERE id = dataset_locations.dataset_version_id) AS datasetid,
            dataset_locations.addedon,
            dataset_locations.is_master AS master,
            dataset_locations.is_public AS public
        ),
        XMLELEMENT(
            name "dataset:addedby",
            XMLATTRIBUTES(
                dataset_locations.addedby AS id,
                (SELECT cname FROM researchers WHERE id = dataset_locations.addedby) AS cname
            ),
            (SELECT name FROM researchers WHERE id = dataset_locations.addedby)
        ),
        XMLELEMENT(
            name "dataset:uri",
            dataset_locations.uri
        ),
        XMLELEMENT(
            name "dataset:interface",
            XMLATTRIBUTES(connection_type AS id),
            dataset_conn_types.name
        ),
        XMLELEMENT(
            name "dataset:exchange_format",
            XMLATTRIBUTES(
                exchange_fmt AS id,
                dataset_exchange_formats.shortname AS alias
            ),
            dataset_exchange_formats.name
        ),
        XMLELEMENT(
            name "dataset:notes",
            dataset_locations.notes
        ),
	CASE WHEN NOT organizationid IS NULL THEN
	ARRAY_TO_STRING(ARRAY_AGG(DISTINCT
		XMLELEMENT(
			name "dataset:organization",
			XMLATTRIBUTES(
				organizations.id AS id,
				organizations.shortname,
				organizations.name,
				organizations.sourceid
			),
			XMLELEMENT(
				name "organization:url",
				XMLATTRIBUTES('website' AS type ),
				organizations.websiteurl
			),
			country_to_xml(organizations.countryid)
		)::text
	),'')::xml END,
	(
	SELECT ARRAY_TO_STRING(ARRAY_AGG(DISTINCT s.x::text),'')::xml FROM (
		SELECT site_to_xml(gocdb.sites.guid::TEXT) as x FROM gocdb.sites
		INNER JOIN dataset_location_sites on dataset_location_sites.siteid = gocdb.sites.pkey
		WHERE dataset_location_sites.dataset_location_id=dataset_locations.id
	) AS s )
    )
FROM
    dataset_locations
INNER JOIN dataset_conn_types ON dataset_conn_types.id = dataset_locations.connection_type
INNER JOIN dataset_exchange_formats ON dataset_exchange_formats.id = exchange_fmt
LEFT OUTER JOIN organizations ON organizations.id = ANY(dataset_locations.organizationid)
WHERE dataset_locations.id = ANY(mid)
GROUP BY
	dataset_locations.id,
	dataset_conn_types.name,
	dataset_exchange_formats.shortname,
	dataset_exchange_formats.name
$$;


ALTER FUNCTION public.dataset_location_to_xml(mid integer[]) OWNER TO appdb;

--
-- Name: dataset_location_to_xml(integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION dataset_location_to_xml(mid integer) RETURNS xml
    LANGUAGE sql STABLE
    AS $_$SELECT dataset_location_to_xml(ARRAY[$1])$_$;


ALTER FUNCTION public.dataset_location_to_xml(mid integer) OWNER TO appdb;

--
-- Name: dataset_parentid_valid(integer, integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION dataset_parentid_valid(mid integer, pid integer) RETURNS boolean
    LANGUAGE sql STABLE
    AS $_$
SELECT (
	-- only allow null parents where there are no derived dataset versions with a parentid
	($2 IS NULL) AND NOT EXISTS (
		SELECT * FROM dataset_versions WHERE datasetid = $1 AND NOT parentid IS NULL
	)
) OR (	
	(NOT $2 IS NULL) AND NOT EXISTS (
		SELECT * FROM datasets WHERE id = $2 AND NOT parentid IS NULL -- parent must be primary dataset
	) AND NOT EXISTS ( -- parent must not invalidate existing dataset versions' parents
		SELECT * FROM dataset_versions WHERE datasetid = $1 AND NOT parentid IS NULL AND parentid NOT IN (
			SELECT id FROM dataset_versions WHERE datasetid = $2
		)
	)
)
$_$;


ALTER FUNCTION public.dataset_parentid_valid(mid integer, pid integer) OWNER TO appdb;

--
-- Name: dataset_to_xml(integer[], boolean); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION dataset_to_xml(mid integer[], flat boolean DEFAULT false) RETURNS SETOF xml
    LANGUAGE sql STABLE
    AS $$
WITH parent_versions AS (
	SELECT 
		dataset_version_to_xml_list(id) AS x,
		datasetid
	FROM dataset_versions
), derived_datasets AS (
	SELECT 
		REPLACE(dataset_to_xml_list(d.id)::text, 'dataset:dataset', 'dataset:derived')::xml AS x,
		d.parentid		
	FROM datasets AS d
)
SELECT 
	XMLELEMENT(
		name "dataset:dataset",
		XMLATTRIBUTES(
			datasets.id,
			datasets.category,
			CASE flat WHEN TRUE THEN dataset_versions.id END AS versionid,
			CASE flat WHEN TRUE THEN dataset_versions.version END AS version,
			datasets.addedon,
			array_to_string(datasets.tags, ' ') AS tags,
			datasets.guid,
			CASE flat
				WHEN FALSE THEN
					(SELECT COUNT(*) FROM dataset_versions WHERE datasetid = datasets.id)
			END AS version_count,
			CASE flat 
				WHEN FALSE THEN
					(SELECT COUNT(*) FROM dataset_locations WHERE dataset_locations.dataset_version_id IN (SELECT id FROM dataset_versions WHERE datasetid = datasets.id))
				ELSE
					(SELECT COUNT(*) FROM dataset_locations WHERE dataset_locations.dataset_version_id = dataset_versions.id)
			END AS location_count,
			CASE WHEN NOT datasets.parentid IS NULL THEN
				'derived'
			ELSE
				'primary'
			END AS ancestry
		),
		CASE WHEN NOT datasets.parentid IS NULL THEN
		XMLELEMENT(
			name "dataset:parent",
			XMLATTRIBUTES(
				datasets.parentid AS id,
				(SELECT guid FROM datasets AS d WHERE id = datasets.parentid) AS guid
			),
			XMLELEMENT(
				name "dataset:name",
				(SELECT d.name FROM datasets AS d WHERE id = datasets.parentid)
			),
			CASE WHEN EXISTS (SELECT * FROM dataset_versions WHERE datasetid = datasets.parentid) THEN
			ARRAY_TO_STRING(ARRAY_AGG(DISTINCT
				parent_versions.x::text
			),'')::xml
			END
		) END,
		XMLELEMENT(
			name "dataset:name",
			datasets.name
		),
		XMLELEMENT(
			name "dataset:description",
			datasets.description
		),
		CASE WHEN NOT datasets.homepage IS NULL THEN
		XMLELEMENT(
			name "dataset:url",
			XMLATTRIBUTES('homepage' AS "type"),
			datasets.homepage
		) END,
		--CASE WHEN NOT datasets.elixir_url IS NULL THEN
		XMLELEMENT(
			name "dataset:url",
			XMLATTRIBUTES('elixir' AS "type"),
			datasets.elixir_url
		),-- END,
		XMLELEMENT(
			name "dataset:addedby",
			XMLATTRIBUTES(
				datasets.addedby AS id,
				(SELECT cname FROM researchers WHERE id = datasets.addedby) AS cname
			),
			(SELECT name FROM researchers WHERE id = datasets.addedby)
		),
		ARRAY_TO_STRING(ARRAY_AGG(DISTINCT
			XMLELEMENT(
				name "discipline:discipline",
				XMLATTRIBUTES(
					disciplines.id,
					disciplines.ord AS order
				),
				disciplines.name
			)::text
		),'')::xml,
		CASE WHEN EXISTS (SELECT * FROM dataset_licenses WHERE datasetid = datasets.id) THEN
		ARRAY_TO_STRING(ARRAY_AGG(DISTINCT			
			XMLELEMENT(
				name "dataset:license",
				XMLATTRIBUTES(
					dataset_licenses.licenseid AS id,
					licenses.name,
					licenses.group					
				),
				XMLELEMENT(
					name "license:title",
					CASE dataset_licenses.licenseid
						WHEN 0 THEN dataset_licenses.title
						ELSE licenses.title
					END
				),
				XMLELEMENT(
					name "license:url",
					CASE dataset_licenses.licenseid
						WHEN 0 THEN dataset_licenses.link
						ELSE licenses.link
					END
				),
				CASE WHEN (NOT dataset_licenses.comment IS NULL) AND (TRIM(dataset_licenses.comment) <> '') THEN
					XMLELEMENT(
						name "license:comment",
						dataset_licenses.comment
					)
				END
			)::text
		),'')::xml
		END,
		CASE WHEN EXISTS (SELECT * FROM datasets AS d WHERE parentid = datasets.id) THEN
		ARRAY_TO_STRING(ARRAY_AGG(DISTINCT
				derived_datasets.x::text
			),'')::xml
		END
	)
FROM
	datasets
LEFT OUTER JOIN disciplines ON disciplines.id = ANY(datasets.disciplineid)
LEFT OUTER JOIN dataset_licenses ON dataset_licenses.datasetid = datasets.id
LEFT OUTER JOIN licenses ON licenses.id = dataset_licenses.licenseid
LEFT OUTER JOIN dataset_versions ON dataset_versions.datasetid = datasets.id AND flat
LEFT OUTER JOIN parent_versions ON parent_versions.datasetid = datasets.parentid
LEFT OUTER JOIN derived_datasets ON derived_datasets.parentid = datasets.id
WHERE datasets.id = ANY(mid)
GROUP BY
	datasets.id,
	dataset_versions.id,
	dataset_versions.version
$$;


ALTER FUNCTION public.dataset_to_xml(mid integer[], flat boolean) OWNER TO appdb;

--
-- Name: dataset_to_xml(integer, boolean); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION dataset_to_xml(mid integer, flat boolean DEFAULT false) RETURNS SETOF xml
    LANGUAGE sql STABLE
    AS $_$SELECT dataset_to_xml(ARRAY[$1], $2)$_$;


ALTER FUNCTION public.dataset_to_xml(mid integer, flat boolean) OWNER TO appdb;

--
-- Name: dataset_to_xml_list(integer[], boolean); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION dataset_to_xml_list(mid integer[], flat boolean DEFAULT false) RETURNS SETOF xml
    LANGUAGE sql STABLE
    AS $$
SELECT 
	XMLELEMENT(
		name "dataset:dataset",
		XMLATTRIBUTES(
			datasets.id,
			CASE flat WHEN TRUE THEN dataset_versions.id END AS versionid,
			CASE flat WHEN TRUE THEN dataset_versions.version END AS version,
			datasets.addedon,
			array_to_string(datasets.tags, ' ') AS tags,
			datasets.guid,
			CASE flat
				WHEN FALSE THEN
					(SELECT COUNT(*) FROM dataset_versions WHERE datasetid = datasets.id)
			END AS version_count,
			CASE flat 
				WHEN FALSE THEN
					(SELECT COUNT(*) FROM dataset_locations WHERE dataset_locations.dataset_version_id IN (SELECT id FROM dataset_versions WHERE datasetid = datasets.id))
				ELSE
					(SELECT COUNT(*) FROM dataset_locations WHERE dataset_locations.dataset_version_id = dataset_versions.id)
			END AS location_count,
			CASE WHEN NOT datasets.parentid IS NULL THEN
				'derived'
			ELSE
				'primary'
			END AS ancestry			
		), datasets.name
	)
FROM
	datasets
LEFT OUTER JOIN disciplines ON disciplines.id = ANY(datasets.disciplineid)
LEFT OUTER JOIN dataset_versions ON dataset_versions.datasetid = datasets.id AND flat
WHERE datasets.id = ANY(mid)
GROUP BY
	datasets.id,
	dataset_versions.id,
	dataset_versions.version
$$;


ALTER FUNCTION public.dataset_to_xml_list(mid integer[], flat boolean) OWNER TO appdb;

--
-- Name: dataset_to_xml_list(integer, boolean); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION dataset_to_xml_list(mid integer, flat boolean DEFAULT false) RETURNS SETOF xml
    LANGUAGE sql STABLE
    AS $_$SELECT dataset_to_xml_list(ARRAY[$1], $2)$_$;


ALTER FUNCTION public.dataset_to_xml_list(mid integer, flat boolean) OWNER TO appdb;

--
-- Name: dataset_version_parentid_valid(integer, integer, integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION dataset_version_parentid_valid(vid integer, pid integer, did integer) RETURNS boolean
    LANGUAGE sql STABLE
    AS $_$ 
SELECT (
-- this will enforce a parent version upon entries belonging to derived datasets
/*  (SELECT $2 IS NULL) AND (
    (
      SELECT parentid FROM datasets WHERE id = (
SELECT datasetid FROM dataset_versions WHERE id = $1
      )
    ) IS NULL
  ) */
-- but this allows null parents for such entries
	$2 IS NULL
) OR (
  SELECT $2 IN (
    SELECT id FROM dataset_versions WHERE datasetid IN (
      SELECT parentid FROM datasets WHERE id = $3
    )
  )
);
$_$;


ALTER FUNCTION public.dataset_version_parentid_valid(vid integer, pid integer, did integer) OWNER TO appdb;

--
-- Name: dataset_version_to_xml(integer[]); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION dataset_version_to_xml(mid integer[]) RETURNS SETOF xml
    LANGUAGE sql STABLE
    AS $$
WITH derived_versions AS (
	SELECT 
		d.parentid,
		dataset_version_to_xml_list(d.id) AS x
		FROM dataset_versions AS d 
)
SELECT 
    XMLELEMENT(
        name "dataset:version",
        XMLATTRIBUTES(
            dataset_versions.id,
            datasets.id AS datasetid,
            dataset_versions.version,
            dataset_versions.addedon,
            dataset_versions.guid
        ),
        CASE WHEN NOT dataset_versions.parentid IS NULL THEN
  REPLACE((SELECT dataset_version_to_xml_list(d.id) FROM dataset_versions AS d WHERE d.id = dataset_versions.parentid)::text, 'dataset:version', 'dataset:parent_version')::xml
END,
        XMLELEMENT(
            name "dataset:addedby",
            XMLATTRIBUTES(
                dataset_versions.addedby AS id,
                (SELECT cname FROM researchers WHERE id = dataset_versions.addedby) AS cname
            ),
            (SELECT name FROM researchers WHERE id = dataset_versions.addedby)
        ),
        XMLELEMENT(
            name "dataset:size",
            dataset_versions.size
        ),
        XMLELEMENT(
            name "dataset:notes",
            dataset_versions.notes
        ),
        CASE WHEN EXISTS (SELECT * FROM dataset_locations WHERE dataset_version_id = dataset_versions.id) THEN (
		SELECT ARRAY_TO_STRING(
			ARRAY_AGG(
				DISTINCT dataset_location_to_xml(id)::text
			),'')::xml 
		FROM dataset_locations WHERE dataset_version_id = dataset_versions.id
        ) END,
	CASE WHEN EXISTS (SELECT * FROM dataset_versions AS d WHERE d.parentid = dataset_versions.id) THEN
		ARRAY_TO_STRING(ARRAY_AGG(DISTINCT
			REPLACE(derived_versions.x::text, 'dataset:version', 'dataset:derived_version')
		),'')::xml
	END
    )
FROM
    dataset_versions
LEFT OUTER JOIN dataset_locations ON dataset_locations.dataset_version_id = dataset_versions.id
INNER JOIN datasets ON datasets.id = dataset_versions.datasetid
LEFT OUTER JOIN dataset_conn_types ON dataset_conn_types.id = dataset_locations.connection_type
LEFT OUTER JOIN dataset_exchange_formats ON dataset_exchange_formats.id = exchange_fmt
LEFT OUTER JOIN derived_versions ON derived_versions.parentid = dataset_versions.id
WHERE dataset_versions.id = ANY(mid)
GROUP BY 
    dataset_versions.id,
    datasets.id
$$;


ALTER FUNCTION public.dataset_version_to_xml(mid integer[]) OWNER TO appdb;

--
-- Name: dataset_version_to_xml(integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION dataset_version_to_xml(mid integer) RETURNS SETOF xml
    LANGUAGE sql STABLE
    AS $_$SELECT dataset_version_to_xml(ARRAY[$1])$_$;


ALTER FUNCTION public.dataset_version_to_xml(mid integer) OWNER TO appdb;

--
-- Name: dataset_version_to_xml_list(integer[]); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION dataset_version_to_xml_list(mid integer[]) RETURNS SETOF xml
    LANGUAGE sql STABLE
    AS $_$
SELECT 
    XMLELEMENT(
        name "dataset:version",
        XMLATTRIBUTES(
            dataset_versions.id,
            datasets.id AS datasetid,
            datasets.name AS dataset_name,
            dataset_versions.version,
            dataset_versions.addedon,
            dataset_versions.guid
        )
    )
FROM dataset_versions
INNER JOIN datasets ON datasets.id = dataset_versions.datasetid
WHERE dataset_versions.id = ANY($1);
$_$;


ALTER FUNCTION public.dataset_version_to_xml_list(mid integer[]) OWNER TO appdb;

--
-- Name: dataset_version_to_xml_list(integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION dataset_version_to_xml_list(mid integer) RETURNS SETOF xml
    LANGUAGE sql STABLE
    AS $_$
SELECT dataset_version_to_xml_list(ARRAY[$1]);
$_$;


ALTER FUNCTION public.dataset_version_to_xml_list(mid integer) OWNER TO appdb;

--
-- Name: uuid_generate_v5(uuid, text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION uuid_generate_v5(namespace uuid, name text) RETURNS uuid
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/uuid-ossp', 'uuid_generate_v5';


ALTER FUNCTION public.uuid_generate_v5(namespace uuid, name text) OWNER TO appdb;

--
-- Name: uuid_namespace(text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION uuid_namespace(_type text) RETURNS uuid
    LANGUAGE sql STRICT
    AS $_$
WITH uuid_namespaces AS
(SELECT '6ba7b810-9dad-11d1-80b4-00c04fd430c8' AS namespace, 'DNS' AS type
UNION
SELECT '6ba7b811-9dad-11d1-80b4-00c04fd430c8', 'URL'
UNION
SELECT '6ba7b812-9dad-11d1-80b4-00c04fd430c8', 'ISO OID'
UNION
SELECT '6ba7b814-9dad-11d1-80b4-00c04fd430c8', 'X.500 DN'
)
SELECT namespace::uuid FROM uuid_namespaces WHERE type = $1;
$_$;


ALTER FUNCTION public.uuid_namespace(_type text) OWNER TO appdb;

SET search_path = egiops, pg_catalog;

--
-- Name: vo_contacts; Type: TABLE; Schema: egiops; Owner: appdb; Tablespace: 
--

CREATE TABLE vo_contacts (
    vo text,
    name text,
    role text,
    email text,
    dn text
);


ALTER TABLE vo_contacts OWNER TO appdb;

--
-- Name: vo_members; Type: TABLE; Schema: egiops; Owner: appdb; Tablespace: 
--

CREATE TABLE vo_members (
    uservo text,
    certdn text,
    ca text,
    vo text,
    last_update timestamp without time zone,
    first_update timestamp without time zone
);


ALTER TABLE vo_members OWNER TO appdb;

SET search_path = gocdb, pg_catalog;

--
-- Name: va_providers; Type: TABLE; Schema: gocdb; Owner: appdb; Tablespace: 
--

CREATE TABLE va_providers (
    pkey text NOT NULL,
    hostname text NOT NULL,
    gocdb_url text NOT NULL,
    host_dn text,
    host_os text,
    host_arch text,
    beta boolean,
    service_type text,
    host_ip text,
    in_production boolean,
    node_monitored boolean,
    sitename text,
    country_name text,
    country_code text,
    roc_name text,
    url text,
    serviceid text
);


ALTER TABLE va_providers OWNER TO appdb;

SET search_path = perun, pg_catalog;

--
-- Name: vo_contacts; Type: TABLE; Schema: perun; Owner: appdb; Tablespace: 
--

CREATE TABLE vo_contacts (
    vo text,
    name text,
    role text,
    email text,
    dn text[],
    ca text[],
    sso text,
    eppn text[]
);


ALTER TABLE vo_contacts OWNER TO appdb;

--
-- Name: vo_members; Type: TABLE; Schema: perun; Owner: appdb; Tablespace: 
--

CREATE TABLE vo_members (
    uservo text,
    certdn text[],
    ca text[],
    sso text,
    eppn text[],
    vo text,
    last_update timestamp without time zone,
    first_update timestamp without time zone
);


ALTER TABLE vo_members OWNER TO appdb;

SET search_path = public, pg_catalog;

--
-- Name: __actor_group_members; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE __actor_group_members (
    id integer NOT NULL,
    groupid integer NOT NULL,
    actorid uuid NOT NULL,
    payload text
);


ALTER TABLE __actor_group_members OWNER TO appdb;

--
-- Name: contacts; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE contacts (
    id integer NOT NULL,
    researcherid integer NOT NULL,
    contacttypeid integer NOT NULL,
    data text NOT NULL,
    isprimary boolean DEFAULT false NOT NULL
);


ALTER TABLE contacts OWNER TO appdb;

--
-- Name: researchers; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE researchers (
    id integer NOT NULL,
    firstname text NOT NULL,
    lastname text NOT NULL,
    dateinclusion date DEFAULT now() NOT NULL,
    institution text NOT NULL,
    countryid integer NOT NULL,
    positiontypeid integer NOT NULL,
    guid uuid DEFAULT uuid_generate_v4(),
    gender character varying(10),
    lastupdated timestamp without time zone DEFAULT now(),
    name text,
    mail_unsubscribe_pwd uuid DEFAULT uuid_generate_v4() NOT NULL,
    lastlogin timestamp without time zone,
    nodissemination boolean DEFAULT false,
    accounttype integer DEFAULT 0 NOT NULL,
    deleted boolean DEFAULT false NOT NULL,
    hitcount integer DEFAULT 0 NOT NULL,
    cname text,
    addedby integer
);


ALTER TABLE researchers OWNER TO appdb;

--
-- Name: user_accounts; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE user_accounts (
    id integer NOT NULL,
    researcherid bigint NOT NULL,
    accountid text NOT NULL,
    account_type e_account_type NOT NULL,
    accountname text,
    stateid bigint DEFAULT 1 NOT NULL
);


ALTER TABLE user_accounts OWNER TO appdb;

--
-- Name: vos; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE vos (
    id integer NOT NULL,
    name text NOT NULL,
    scope text,
    validated timestamp without time zone,
    description text,
    homepage text,
    enrollment text,
    aup text,
    domainid integer,
    deleted boolean DEFAULT false NOT NULL,
    deletedon timestamp without time zone,
    alias text,
    status text,
    guid uuid,
    sourceid integer NOT NULL,
    disciplineid integer[]
);


ALTER TABLE vos OWNER TO appdb;

--
-- Name: ebi_vo_contacts; Type: VIEW; Schema: public; Owner: appdb
--

CREATE VIEW ebi_vo_contacts AS
 WITH emails AS (
         SELECT v.email,
            NULL::integer AS researcherid,
            v.dn
           FROM perun.vo_contacts v
        UNION
         SELECT c.data AS email,
            c.researcherid,
            NULL::text[] AS dn
           FROM contacts c
          WHERE (c.contacttypeid = 7)
        )
 SELECT vos.id AS void,
    user_accounts.researcherid,
    vo_contacts.role,
        CASE
            WHEN (array_agg(DISTINCT emails.email) = '{NULL}'::text[]) THEN NULL::text[]
            ELSE array_agg(DISTINCT emails.email)
        END AS email,
        CASE
            WHEN (user_accounts.researcherid IS NULL) THEN vo_contacts.name
            ELSE researchers.name
        END AS name,
    researchers.cname
   FROM ((((perun.vo_contacts
     LEFT JOIN user_accounts ON (((((user_accounts.accountid = ANY (vo_contacts.dn)) AND (user_accounts.account_type = 'x509'::e_account_type)) OR ((user_accounts.accountid = ANY (vo_contacts.eppn)) AND (user_accounts.account_type = 'edugain'::e_account_type))) OR ((user_accounts.accountid = vo_contacts.sso) AND (user_accounts.account_type = 'egi-sso-ldap'::e_account_type)))))
     LEFT JOIN researchers ON ((researchers.id = user_accounts.researcherid)))
     LEFT JOIN emails ON (((emails.dn = vo_contacts.dn) OR (emails.researcherid = researchers.id))))
     JOIN vos ON ((((lower(vos.name) = lower(vo_contacts.vo)) AND (vos.deleted IS FALSE)) AND (vos.sourceid = 2))))
  GROUP BY vos.id, user_accounts.researcherid, vo_contacts.role, vo_contacts.name, researchers.name, researchers.cname;


ALTER TABLE ebi_vo_contacts OWNER TO appdb;

--
-- Name: egi_vo_contacts; Type: VIEW; Schema: public; Owner: appdb
--

CREATE VIEW egi_vo_contacts AS
 WITH emails AS (
         SELECT v.email,
            NULL::integer AS researcherid,
            v.dn
           FROM egiops.vo_contacts v
        UNION
         SELECT c.data AS email,
            c.researcherid,
            NULL::text AS dn
           FROM contacts c
          WHERE (c.contacttypeid = 7)
        )
 SELECT vos.id AS void,
    user_accounts.researcherid,
    vo_contacts.role,
    array_agg(DISTINCT emails.email) AS email,
        CASE
            WHEN (user_accounts.researcherid IS NULL) THEN vo_contacts.name
            ELSE researchers.name
        END AS name,
    researchers.cname
   FROM ((((egiops.vo_contacts
     LEFT JOIN user_accounts ON (((user_accounts.accountid = vo_contacts.dn) AND (user_accounts.account_type = 'x509'::e_account_type))))
     LEFT JOIN researchers ON ((researchers.id = user_accounts.researcherid)))
     JOIN emails ON (((emails.dn = vo_contacts.dn) OR (emails.researcherid = researchers.id))))
     JOIN vos ON ((((lower(vos.name) = lower(vo_contacts.vo)) AND (vos.deleted IS FALSE)) AND (vos.sourceid = 1))))
  GROUP BY vos.id, user_accounts.researcherid, vo_contacts.role, vo_contacts.name, researchers.name, researchers.cname;


ALTER TABLE egi_vo_contacts OWNER TO appdb;

--
-- Name: privileges; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE privileges (
    actionid integer NOT NULL,
    object uuid,
    id integer NOT NULL,
    actor uuid DEFAULT uuid_generate_v4() NOT NULL,
    revoked boolean DEFAULT false,
    addedby integer
);


ALTER TABLE privileges OWNER TO appdb;

--
-- Name: researchers_apps; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE researchers_apps (
    appid integer NOT NULL,
    researcherid integer NOT NULL,
    iskeycontact boolean DEFAULT false NOT NULL,
    guid uuid DEFAULT uuid_generate_v4() NOT NULL
);


ALTER TABLE researchers_apps OWNER TO appdb;

--
-- Name: vo_contacts; Type: VIEW; Schema: public; Owner: appdb
--

CREATE VIEW vo_contacts AS
 SELECT egi_vo_contacts.void,
    egi_vo_contacts.researcherid,
    egi_vo_contacts.role,
    egi_vo_contacts.email,
    egi_vo_contacts.name,
    egi_vo_contacts.cname
   FROM egi_vo_contacts
UNION
 SELECT ebi_vo_contacts.void,
    ebi_vo_contacts.researcherid,
    ebi_vo_contacts.role,
    ebi_vo_contacts.email,
    ebi_vo_contacts.name,
    ebi_vo_contacts.cname
   FROM ebi_vo_contacts;


ALTER TABLE vo_contacts OWNER TO appdb;

--
-- Name: vo_members; Type: VIEW; Schema: public; Owner: appdb
--

CREATE VIEW vo_members AS
 SELECT vos.id AS void,
    researchers.id AS researcherid,
    vo_members.first_update AS member_since
   FROM (((egiops.vo_members
     JOIN vos ON ((((lower(vos.name) = lower(vo_members.vo)) AND (NOT vos.deleted)) AND (vos.sourceid = 1))))
     JOIN user_accounts ON (((user_accounts.accountid = vo_members.certdn) AND (user_accounts.account_type = 'x509'::e_account_type))))
     JOIN researchers ON ((researchers.id = user_accounts.researcherid)))
UNION ALL
 SELECT vos.id AS void,
    researchers.id AS researcherid,
    vo_members.first_update AS member_since
   FROM (((perun.vo_members
     JOIN vos ON ((((lower(vos.name) = lower(vo_members.vo)) AND (NOT vos.deleted)) AND (vos.sourceid = 2))))
     JOIN user_accounts ON (((user_accounts.accountid = ANY (vo_members.certdn)) AND (user_accounts.account_type = 'x509'::e_account_type))))
     JOIN researchers ON ((researchers.id = user_accounts.researcherid)));


ALTER TABLE vo_members OWNER TO appdb;

--
-- Name: _actor_group_members; Type: MATERIALIZED VIEW; Schema: public; Owner: appdb; Tablespace: 
--

CREATE MATERIALIZED VIEW _actor_group_members AS
 SELECT __actor_group_members.id,
    __actor_group_members.groupid,
    __actor_group_members.actorid,
    __actor_group_members.payload
   FROM __actor_group_members
UNION
 SELECT NULL::integer AS id,
    (-5) AS groupid,
    researchers.guid AS actorid,
    NULL::text AS payload
   FROM researchers
UNION
 SELECT NULL::integer AS id,
    (-4) AS groupid,
    researchers.guid AS actorid,
    (vo_contacts.void)::text AS payload
   FROM (vo_contacts
     JOIN researchers ON ((researchers.id = vo_contacts.researcherid)))
  WHERE (upper(vo_contacts.role) = 'VO MANAGER'::text)
UNION
 SELECT NULL::integer AS id,
    (-6) AS groupid,
    researchers.guid AS actorid,
    (researchers_apps.appid)::text AS payload
   FROM (researchers_apps
     JOIN researchers ON ((researchers.id = researchers_apps.researcherid)))
UNION
 SELECT NULL::integer AS id,
    (-7) AS groupid,
    researchers.guid AS actorid,
    (vo_members.void)::text AS payload
   FROM (vo_members
     JOIN researchers ON ((researchers.id = vo_members.researcherid)))
UNION
 SELECT DISTINCT ON (privileges.actor) NULL::integer AS id,
    (-8) AS groupid,
    privileges.actor AS actorid,
    NULL::text AS payload
   FROM privileges
  WHERE ((privileges.object IS NULL) AND (NOT privileges.revoked))
UNION
 SELECT NULL::integer AS id,
    (-11) AS groupid,
    researchers.guid AS actorid,
    (vo_contacts.void)::text AS payload
   FROM (vo_contacts
     JOIN researchers ON ((researchers.id = vo_contacts.researcherid)))
  WHERE (upper(vo_contacts.role) = 'VO DEPUTY'::text)
UNION
 SELECT NULL::integer AS id,
    (-12) AS groupid,
    researchers.guid AS actorid,
    (vo_contacts.void)::text AS payload
   FROM (vo_contacts
     JOIN researchers ON ((researchers.id = vo_contacts.researcherid)))
  WHERE (upper(vo_contacts.role) = 'VO EXPERT'::text)
UNION
 SELECT NULL::integer AS id,
    (-13) AS groupid,
    researchers.guid AS actorid,
    (vo_contacts.void)::text AS payload
   FROM (vo_contacts
     JOIN researchers ON ((researchers.id = vo_contacts.researcherid)))
  WHERE (upper(vo_contacts.role) = 'VO SHIFTER'::text)
  WITH NO DATA;


ALTER TABLE _actor_group_members OWNER TO appdb;

--
-- Name: actions; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE actions (
    id integer NOT NULL,
    description text NOT NULL
);


ALTER TABLE actions OWNER TO appdb;

--
-- Name: actor_groups; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE actor_groups (
    id integer NOT NULL,
    guid uuid DEFAULT uuid_generate_v4() NOT NULL,
    name text NOT NULL
);


ALTER TABLE actor_groups OWNER TO appdb;

--
-- Name: archs; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE archs (
    id integer NOT NULL,
    name text NOT NULL,
    aliases text[]
);


ALTER TABLE archs OWNER TO appdb;

--
-- Name: countries; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE countries (
    id integer NOT NULL,
    name text NOT NULL,
    continent boolean NOT NULL,
    isocode text NOT NULL,
    regionid integer NOT NULL
);


ALTER TABLE countries OWNER TO appdb;

--
-- Name: oses; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE oses (
    id integer NOT NULL,
    name text NOT NULL,
    os_family_id integer,
    aliases text[]
);


ALTER TABLE oses OWNER TO appdb;

--
-- Name: va_providers; Type: VIEW; Schema: public; Owner: appdb
--

CREATE VIEW va_providers AS
 SELECT va_providers.pkey AS id,
    va_providers.sitename,
    va_providers.url,
    va_providers.gocdb_url,
    va_providers.hostname,
    va_providers.host_dn,
    va_providers.host_ip,
    oses.id AS host_os_id,
    archs.id AS host_arch_id,
    va_providers.beta,
    va_providers.in_production,
    va_providers.node_monitored,
    countries.id AS country_id,
    va_providers.roc_name AS ngi,
    uuid_generate_v5(uuid_namespace('ISO OID'::text), va_providers.pkey) AS guid,
    va_providers.serviceid
   FROM (((gocdb.va_providers
     LEFT JOIN oses ON ((oses.name = va_providers.host_os)))
     LEFT JOIN archs ON (((archs.name = va_providers.host_arch) OR (va_providers.host_arch = ANY (archs.aliases)))))
     LEFT JOIN countries ON ((countries.isocode = va_providers.country_code)));


ALTER TABLE va_providers OWNER TO appdb;

--
-- Name: VIEW va_providers; Type: COMMENT; Schema: public; Owner: appdb
--

COMMENT ON VIEW va_providers IS '
  6ba7b812-9dad-11d1-80b4-00c04fd430c8 is the ISO OID namespace uuid seed for SHA1-based uuid generator (v5)
  ';


--
-- Name: actors; Type: VIEW; Schema: public; Owner: appdb
--

CREATE VIEW actors AS
 SELECT researchers.guid,
    'ppl'::text AS type,
    researchers.name,
    (researchers.id)::text AS id,
    researchers.deleted AS hidden,
    researchers.cname
   FROM researchers
UNION ALL
 SELECT va_providers.guid,
    'vap'::text AS type,
    va_providers.sitename AS name,
    va_providers.id,
    false AS hidden,
    NULL::text AS cname
   FROM va_providers
UNION ALL
 SELECT actor_groups.guid,
    'grp'::text AS type,
    actor_groups.name,
    (actor_groups.id)::text AS id,
    false AS hidden,
    NULL::text AS cname
   FROM actor_groups;


ALTER TABLE actors OWNER TO appdb;

--
-- Name: appmanualcountries; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE appmanualcountries (
    appid integer NOT NULL,
    countryid integer NOT NULL
);


ALTER TABLE appmanualcountries OWNER TO appdb;

--
-- Name: app_countries; Type: VIEW; Schema: public; Owner: appdb
--

CREATE VIEW app_countries AS
 SELECT DISTINCT t.appid,
    t.countryid
   FROM ( SELECT applications.id AS appid,
            researchers.countryid
           FROM ((applications
             JOIN researchers_apps ON ((researchers_apps.appid = applications.id)))
             JOIN researchers ON ((researchers.id = researchers_apps.researcherid)))
        UNION
         SELECT appmanualcountries.appid,
            appmanualcountries.countryid
           FROM appmanualcountries) t;


ALTER TABLE app_countries OWNER TO appdb;

--
-- Name: targets; Type: VIEW; Schema: public; Owner: appdb
--

CREATE VIEW targets AS
 SELECT applications.guid,
    'app'::text AS type,
    applications.name,
    (applications.id)::text AS id,
    (applications.deleted OR applications.moderated) AS hidden
   FROM applications
UNION ALL
 SELECT researchers.guid,
    'ppl'::text AS type,
    researchers.name,
    (researchers.id)::text AS id,
    researchers.deleted AS hidden
   FROM researchers
UNION ALL
 SELECT actor_groups.guid,
    'grp'::text AS type,
    actor_groups.name,
    (actor_groups.id)::text AS id,
    false AS hidden
   FROM actor_groups
UNION ALL
 SELECT va_providers.guid,
    'vap'::text AS type,
    va_providers.sitename AS name,
    va_providers.id,
    false AS hidden
   FROM va_providers
  ORDER BY 2, 3;


ALTER TABLE targets OWNER TO appdb;

--
-- Name: userrequests; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE userrequests (
    id integer NOT NULL,
    typeid integer NOT NULL,
    userguid uuid NOT NULL,
    userdata text,
    targetguid uuid,
    actorguid uuid,
    actordata text,
    stateid integer NOT NULL,
    created timestamp without time zone DEFAULT now() NOT NULL,
    lastupdated timestamp without time zone,
    guid uuid DEFAULT uuid_generate_v4() NOT NULL
);


ALTER TABLE userrequests OWNER TO appdb;

--
-- Name: permissions; Type: MATERIALIZED VIEW; Schema: public; Owner: appdb; Tablespace: 
--

CREATE MATERIALIZED VIEW permissions AS
 WITH actor_group_members AS (
         SELECT _actor_group_members.id,
            _actor_group_members.groupid,
            _actor_group_members.actorid,
            _actor_group_members.payload
           FROM _actor_group_members
        )
 SELECT DISTINCT ON (_permissions.actor, _permissions.actionid, _permissions.object) array_agg(_permissions.id) AS ids,
    (EXISTS ( SELECT u.u
           FROM unnest(array_agg(_permissions.id)) u(u)
          WHERE (u.u < 0))) AS system,
    _permissions.actor,
    _permissions.actionid,
    _permissions.object
   FROM ( SELECT __permissions.id,
            __permissions.actor,
            __permissions.actionid,
            __permissions.object
           FROM ( SELECT privileges.id,
                    privileges.actor,
                    privileges.actionid,
                    privileges.object
                   FROM privileges
                  WHERE (NOT privileges.revoked)
                UNION
                 SELECT privileges.id,
                    actors.guid AS actor,
                    privileges.actionid,
                    privileges.object
                   FROM (((privileges
                     CROSS JOIN actors)
                     JOIN actor_group_members ON ((actor_group_members.actorid = actors.guid)))
                     JOIN actor_groups ON ((actor_groups.id = actor_group_members.groupid)))
                  WHERE ((actor_groups.guid = privileges.actor) AND (NOT privileges.revoked))
                UNION
                 SELECT (-1) AS id,
                    actors.guid AS actor,
                    actions.id AS actionid,
                    applications.guid AS object
                   FROM ((((actions
                     CROSS JOIN applications)
                     CROSS JOIN actor_group_members)
                     JOIN actors ON ((actors.guid = actor_group_members.actorid)))
                     JOIN app_countries ON (((app_countries.appid = applications.id) AND ((app_countries.countryid)::text = actor_group_members.payload))))
                  WHERE ((actor_group_members.groupid = (-3)) AND (actions.id = ANY (app_actions())))
                UNION
                 SELECT (-2) AS id,
                    actors.guid AS actor,
                    3 AS actionid,
                    NULL::uuid AS object
                   FROM (actors
                     JOIN actor_group_members ON ((actor_group_members.actorid = actors.guid)))
                  WHERE (actor_group_members.groupid = (-5))
                UNION
                 SELECT
                        CASE actor_group_members.groupid
                            WHEN (-1) THEN (-3)
                            WHEN (-2) THEN (-9)
                            ELSE NULL::integer
                        END AS id,
                    actors.guid AS actor,
                    actions.id AS actionid,
                    NULL::uuid AS object
                   FROM ((actors
                     CROSS JOIN actions)
                     JOIN actor_group_members ON ((actor_group_members.actorid = actors.guid)))
                  WHERE ((actor_group_members.groupid = ANY (ARRAY[(-1), (-2)])) AND
                        CASE actor_group_members.groupid
                            WHEN (-2) THEN (NOT (actions.id = ANY (admin_only_actions())))
                            ELSE true
                        END)
                UNION
                 SELECT (-4) AS id,
                    researchers.guid AS actor,
                    actions.id AS actionid,
                    applications.guid AS object
                   FROM ((applications
                     CROSS JOIN actions)
                     JOIN researchers ON (((researchers.id = applications.addedby) OR (researchers.id = applications.owner))))
                  WHERE ((NOT (applications.addedby IS NULL)) AND (actions.id = ANY (app_actions())))
                UNION
                 SELECT (-5) AS id,
                    r1.guid AS actor,
                    act.act AS actionid,
                    r2.guid AS object
                   FROM (((actors r1
                     CROSS JOIN researchers r2)
                     CROSS JOIN unnest(ARRAY[21, 40, 41]) act(act))
                     JOIN actor_group_members agm1 ON ((agm1.actorid = r1.guid)))
                  WHERE (((agm1.groupid = (-3)) AND ((r2.countryid)::text = agm1.payload)) AND (NOT (r2.guid IN ( SELECT agm2.actorid
                           FROM actor_group_members agm2
                          WHERE (agm2.groupid = ANY (ARRAY[(-1), (-2)]))))))
                UNION
                 SELECT (-7) AS id,
                    researchers.guid AS actor,
                    act.act AS actionid,
                    researchers.guid AS object
                   FROM (researchers
                     CROSS JOIN unnest(ARRAY[21, 40, 41]) act(act))
                UNION
                 SELECT (-8) AS id,
                    actors.guid AS actor,
                    actions.id AS actionid,
                    applications.guid AS object
                   FROM (((applications
                     CROSS JOIN actions)
                     JOIN actor_group_members ON ((actor_group_members.payload = (applications.id)::text)))
                     JOIN actors ON ((actor_group_members.actorid = actors.guid)))
                  WHERE ((actor_group_members.groupid = (-6)) AND (actions.id = ANY (app_metadata_actions())))
                UNION
                 SELECT (-14) AS id,
                    researchers.guid AS actor,
                    25 AS actionid,
                    userrequests.guid AS object
                   FROM ((userrequests
                     JOIN applications ON ((applications.guid = userrequests.targetguid)))
                     JOIN researchers ON (((researchers.id = applications.addedby) OR (researchers.id = applications.owner))))
                  WHERE ((userrequests.typeid = ANY (ARRAY[1, 2])) AND (NOT ((applications.addedby IS NULL) AND (applications.owner IS NULL))))
                UNION
                 SELECT (-20) AS id,
                    researchers.guid AS actor,
                    privileges.actionid,
                    privileges.object
                   FROM ((privileges
                     JOIN actor_group_members agm ON ((agm.payload = ( SELECT va_providers.id
                           FROM va_providers
                          WHERE (va_providers.guid = privileges.actor)))))
                     JOIN researchers ON ((agm.actorid = researchers.guid)))
                  WHERE (((agm.groupid = (-10)) AND (privileges.actionid = ANY (ARRAY[36, 37]))) AND (NOT privileges.revoked))
                UNION
                 SELECT (-21) AS id,
                    actor_group_members.actorid AS actor,
                    37 AS actionid,
                    vos.guid AS object
                   FROM (actor_group_members
                     JOIN vos ON ((((vos.id)::text = actor_group_members.payload) AND (NOT vos.deleted))))
                  WHERE (actor_group_members.groupid = ANY (ARRAY[(-4), (-11), (-12), (-13)]))
                UNION
                 SELECT (-22) AS id,
                    actor_group_members.actorid AS actor,
                    45 AS actionid,
                    NULL::uuid AS object
                   FROM actor_group_members
                  WHERE (actor_group_members.groupid = (-19))
                UNION
                 SELECT (-15) AS id,
                    privileges.actor,
                    34 AS actionid,
                    privileges.object
                   FROM privileges
                  WHERE (privileges.actionid = 32)) __permissions
          WHERE (NOT ((__permissions.actor, __permissions.actionid, __permissions.object) IN ( SELECT privileges.actor,
                    privileges.actionid,
                    targets.guid
                   FROM (privileges
                     JOIN targets ON ((targets.guid = COALESCE(privileges.object, targets.guid))))
                  WHERE (privileges.revoked = true)
                UNION
                 SELECT privileges.actor,
                    privileges.actionid,
                    privileges.object
                   FROM privileges
                  WHERE (privileges.revoked = true))))) _permissions
  GROUP BY _permissions.actor, _permissions.actionid, _permissions.object
  WITH NO DATA;


ALTER TABLE permissions OWNER TO appdb;

--
-- Name: __permissions; Type: VIEW; Schema: public; Owner: appdb
--

CREATE VIEW __permissions AS
 SELECT permissions.ids,
    permissions.system,
    permissions.actor,
    permissions.actionid,
    permissions.object
   FROM permissions;


ALTER TABLE __permissions OWNER TO appdb;

--
-- Name: _actor_group_members2; Type: MATERIALIZED VIEW; Schema: public; Owner: appdb; Tablespace: 
--

CREATE MATERIALIZED VIEW _actor_group_members2 AS
 SELECT _actor_group_members.id,
    _actor_group_members.groupid,
    _actor_group_members.actorid,
    _actor_group_members.payload
   FROM _actor_group_members
UNION
 SELECT DISTINCT ON (privileges.actor) NULL::integer AS id,
    (-9) AS groupid,
    privileges.actor AS actorid,
    (( SELECT applications.id
           FROM applications
          WHERE (applications.guid = privileges.object)))::text AS payload
   FROM (( SELECT __permissions.actionid,
            __permissions.actor,
            __permissions.object,
            false AS revoked
           FROM __permissions
        UNION
         SELECT privileges_1.actionid,
            privileges_1.actor,
            privileges_1.object,
            privileges_1.revoked
           FROM privileges privileges_1) privileges
     JOIN targets ON ((targets.guid = privileges.object)))
  WHERE ((NOT privileges.revoked) AND (targets.type = 'app'::text))
  GROUP BY privileges.actor, privileges.object
 HAVING (array_agg(privileges.actionid) @> app_fc_actions())
  WITH NO DATA;


ALTER TABLE _actor_group_members2 OWNER TO appdb;

--
-- Name: actor_group_members; Type: VIEW; Schema: public; Owner: appdb
--

CREATE VIEW actor_group_members AS
 SELECT _actor_group_members2.id,
    _actor_group_members2.groupid,
    _actor_group_members2.actorid,
    _actor_group_members2.payload
   FROM _actor_group_members2;


ALTER TABLE actor_group_members OWNER TO appdb;

--
-- Name: delete_agm(integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION delete_agm(_id integer) RETURNS SETOF actor_group_members
    LANGUAGE plpgsql STRICT
    AS $$
BEGIN
        DELETE FROM __actor_group_members
        WHERE __actor_group_members.id = _id;
        RETURN QUERY SELECT * FROM __actor_group_members WHERE FALSE;
END;
$$;


ALTER FUNCTION public.delete_agm(_id integer) OWNER TO appdb;

--
-- Name: delete_app(integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION delete_app(_appid integer) RETURNS boolean
    LANGUAGE plpgsql
    AS $_$
  BEGIN
      BEGIN
          DELETE FROM applications.any WHERE id = $1;
          DELETE FROM vapplications WHERE appid = $1;
          DELETE FROM app_cnames WHERE appid = $1;
          DELETE FROM app_validation_log WHERE appid = $1;
          DELETE FROM "privileges" WHERE object = (SELECT guid FROM applications WHERE id = $1);
          DELETE FROM app_vos WHERE appid = $1;
          DELETE FROM appdisciplines WHERE appid = $1;
          DELETE FROM appcategories WHERE appid = $1;
          DELETE FROM app_middlewares WHERE appid = $1;
          DELETE FROM appmodhistories WHERE appid = $1;
          DELETE FROM appmanualcountries WHERE appid = $1;
          DELETE FROM app_data WHERE appid = $1;
          DELETE FROM appdocuments WHERE appid = $1;
          DELETE FROM app_urls WHERE appid = $1;
          DELETE FROM appcontact_otheritems WHERE appid = $1;
          DELETE FROM researchers_apps WHERE appid = $1;
          DELETE FROM appbookmarks WHERE appid = $1;
          DELETE FROM appratings WHERE appid = $1;
          DELETE FROM app_tags WHERE appid = $1;
          DELETE FROM app_mod_infos WHERE appid = $1;
          DELETE FROM app_del_infos WHERE appid = $1;
          DELETE FROM app_api_log WHERE appid = $1;
          DELETE FROM appcontact_vos WHERE appid = $1;
          DELETE FROM appcontact_middlewares WHERE appid = $1;
          DELETE FROM app_licenses WHERE appid = $1;
          DELETE FROM cache.appxmlcache WHERE id = $1;
		  DELETE FROM cache.appprivsxmlcache WHERE appid = $1;
		  DELETE FROM applications WHERE id = $1;		  
      EXCEPTION
          WHEN OTHERS THEN
              RAISE NOTICE 'Deleting application with id % failed. Reason: % (%)', $1, SQLERRM, SQLSTATE;
              RETURN FALSE;
      END;
      RAISE NOTICE 'Do not forget to clean up the cache';
      RETURN TRUE;
  END;                                                                                                      
  $_$;


ALTER FUNCTION public.delete_app(_appid integer) OWNER TO appdb;

--
-- Name: delete_dataset(integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION delete_dataset(mid integer) RETURNS void
    LANGUAGE plpgsql
    AS $$
BEGIN
    DELETE FROM dataset_licenses WHERE datasetid = mid;
    DELETE FROM dataset_disciplines WHERE datasetid = mid;
    PERFORM delete_dataset_version(id) FROM dataset_versions WHERE datasetid = mid;
    DELETE FROM datasets WHERE id = mid;
END;
$$;


ALTER FUNCTION public.delete_dataset(mid integer) OWNER TO appdb;

--
-- Name: delete_dataset_version(integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION delete_dataset_version(mid integer) RETURNS void
    LANGUAGE plpgsql
    AS $$
BEGIN
    DELETE FROM dataset_location_organizations WHERE dataset_location_organizations.dataset_location_id IN (SELECT dataset_locations.id FROM dataset_locations WHERE dataset_locations.dataset_version_id = mid);
    DELETE FROM dataset_location_sites WHERE dataset_location_sites.dataset_location_id IN (SELECT dataset_locations.id FROM dataset_locations WHERE dataset_locations.dataset_version_id = mid);
    DELETE FROM dataset_locations WHERE dataset_version_id = mid;
    DELETE FROM dataset_versions WHERE id = mid;
END;
$$;


ALTER FUNCTION public.delete_dataset_version(mid integer) OWNER TO appdb;

--
-- Name: delete_researcher(integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION delete_researcher(_pplid integer) RETURNS boolean
    LANGUAGE plpgsql
    AS $_$
  BEGIN
      BEGIN
      DELETE FROM researcher_cnames WHERE researcherid = $1;
      DELETE FROM mail_subscriptions WHERE researcherid = $1;
      DELETE FROM privileges WHERE (object = (SELECT guid FROM researchers WHERE id = $1)) OR (actor = (SELECT guid FROM researchers WHERE id = $1));
      DELETE FROM messages WHERE receiverid = $1;
      UPDATE messages SET senderID = NULL WHERE senderid = $1;
      DELETE FROM researchers_apps WHERE researcherid = $1;
      DELETE FROM intAuthors WHERE authorid = $1;
      UPDATE appratings SET
          submitterid = NULL,
          submittername = (SELECT name FROM researchers WHERE id = $1),
          submitteremail = (SELECT data FROM contacts WHERE contacttypeid = 7 AND researcherid = $1 LIMIT 1)
      WHERE submitterid = $1;
      DELETE FROM contacts.any WHERE id IN (SELECT id FROM contacts WHERE researcherid = $1);
      ALTER TABLE contacts DISABLE TRIGGER rtr_contacts_primary_entry;
          DELETE FROM contacts WHERE researcherid = $1;
      ALTER TABLE contacts ENABLE TRIGGER rtr_contacts_primary_entry;
      DELETE FROM ppl_api_log WHERE pplid = $1;
      DELETE FROM researchers.any WHERE id = $1;
      DELETE FROM user_credentials WHERE researcherid = $1;
      DELETE FROM user_accounts WHERE researcherid = $1;
      UPDATE organizations SET deletedby = null WHERE deletedby = $1;
      UPDATE organizations SET addedby = null WHERE addedby = $1;
      UPDATE projects SET deletedby = null WHERE deletedby = $1;
      UPDATE projects SET addedby = null WHERE addedby = $1;
      DELETE FROM relations WHERE target_guid = (SELECT guid FROM researchers WHERE id=$1);
      DELETE FROM relations WHERE subject_guid = (SELECT guid FROM researchers WHERE id=$1);
      DELETE FROM researchers WHERE id = $1;
      EXCEPTION
          WHEN OTHERS THEN
              ALTER TABLE contacts ENABLE TRIGGER rtr_contacts_primary_entry;
              RAISE NOTICE 'Deleting researcher with id % failed. Reason: % (%)', $1, SQLERRM, SQLSTATE;
              RETURN FALSE;
      END;
      RAISE NOTICE 'Do not forget to clean up the cache';
      RETURN TRUE;
  END;
  $_$;


ALTER FUNCTION public.delete_researcher(_pplid integer) OWNER TO appdb;

--
-- Name: derived_dataset_discipline_valid(integer, integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION derived_dataset_discipline_valid(mid integer, did integer) RETURNS boolean
    LANGUAGE sql STABLE STRICT
    AS $_$
	SELECT (
			(SELECT parentid FROM datasets WHERE id = $1) IS NULL
	) OR (
		$2 IN (
			SELECT dataset_disciplines.disciplineid 
			FROM datasets
			INNER JOIN dataset_disciplines ON dataset_disciplines.datasetid = datasets.parentid
			WHERE datasets.id = $1
		)
	)
$_$;


ALTER FUNCTION public.derived_dataset_discipline_valid(mid integer, did integer) OWNER TO appdb;

--
-- Name: difference(text, text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION difference(text, text) RETURNS integer
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/fuzzystrmatch', 'difference';


ALTER FUNCTION public.difference(text, text) OWNER TO appdb;

--
-- Name: discard_vowide_image_list(integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION discard_vowide_image_list(_void integer) RETURNS boolean
    LANGUAGE plpgsql STRICT
    AS $_$
BEGIN
IF EXISTS (SELECT id FROM vowide_image_lists WHERE void = $1 AND state = 'draft') THEN
	--DELETE FROM vowide_image_list_images WHERE vowide_image_list_id IN (SELECT id FROM vowide_image_lists WHERE void = $1 AND state = 'draft');
	DELETE FROM vowide_image_lists WHERE void = $1 AND state = 'draft';
	RETURN TRUE;
ELSE
	RETURN FALSE;
END IF;
END;
$_$;


ALTER FUNCTION public.discard_vowide_image_list(_void integer) OWNER TO appdb;

--
-- Name: discipline_to_xml(integer[]); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION discipline_to_xml(mid integer[]) RETURNS xml
    LANGUAGE sql
    AS $_$
  SELECT CASE WHEN $1 IS NULL THEN (SELECT discipline_to_xml(NULL::int)) ELSE
  (SELECT array_to_string(array_agg(discipline_to_xml(id) ORDER BY idx(mid,id)),'')::xml FROM disciplines WHERE id = ANY($1)
  ) END;
  $_$;


ALTER FUNCTION public.discipline_to_xml(mid integer[]) OWNER TO appdb;

--
-- Name: discipline_to_xml(integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION discipline_to_xml(mid integer) RETURNS xml
    LANGUAGE sql
    AS $_$
SELECT CASE WHEN $1 IS NULL THEN (SELECT xmlelement(name "discipline:discipline", xmlattributes(
'true' as "xsi:nil", 0 as id))) ELSE (
SELECT xmlelement(name "discipline:discipline", xmlattributes(
	id as id, 
	parentid as parentid,
	CASE WHEN ord > 0 THEN ord ELSE NULL END AS "order"
), name) FROM disciplines WHERE id = $1) END;
$_$;


ALTER FUNCTION public.discipline_to_xml(mid integer) OWNER TO appdb;

--
-- Name: discipline_to_xml_ext(integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION discipline_to_xml_ext(mid integer) RETURNS xml
    LANGUAGE plpgsql STRICT
    AS $$
DECLARE elname TEXT;
BEGIN
    RETURN (SELECT
        xmlelement(
          name "discipline",
          xmlattributes(
              disciplines.id as id /*,
              disciplines.parentid as parentid*/
          ),
          xmlelement(
              name "discipline:name",
              disciplines.name
          ),
          xmlagg(
              CASE
              WHEN discipline_help.data IS NULL THEN
                  NULL
              ELSE
                  xmlelement(
                      name "discipline:info",
                      xmlattributes(
                          CASE
                              WHEN discipline_help."type" IS NULL THEN NULL
                              WHEN discipline_help."type" = 0 THEN 'url'
                              WHEN discipline_help."type" = 1 THEN 'text'
                              ELSE 'other'
                          END as "type"
                      ),
                      discipline_help.data
                  )
              END
          )
        )
    FROM
        disciplines
    LEFT OUTER JOIN discipline_help ON disciplines.id = discipline_help.disciplineid
    WHERE disciplines.id = mid
    GROUP BY
          disciplines.id,
          disciplines.name
	);
END;
$$;


ALTER FUNCTION public.discipline_to_xml_ext(mid integer) OWNER TO appdb;

--
-- Name: dissemination_to_xml(integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION dissemination_to_xml(mid integer) RETURNS xml
    LANGUAGE sql
    AS $_$SELECT xmlelement(name "dissemination:dissemination", xmlattributes(
dissemination.id as id),
xmlelement(name "dissemination:message", dissemination.message),
xmlelement(name "dissemination:subject", dissemination.subject),
xmlelement(name "dissemination:filter", dissemination.filter),
xmlelement(name "dissemination:sentOn", dissemination.senton),
researcher_to_xml(composerid, 'composer')
) FROM dissemination
WHERE dissemination.id = $1 
GROUP BY dissemination.message, 
dissemination.id, 
dissemination.filter, 
dissemination.senton, 
dissemination.subject, 
dissemination.composerid
ORDER BY dissemination.id
$_$;


ALTER FUNCTION public.dissemination_to_xml(mid integer) OWNER TO appdb;

--
-- Name: dissemination_to_xml_ext(integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION dissemination_to_xml_ext(mid integer) RETURNS xml
    LANGUAGE sql
    AS $_$SELECT xmlelement(name "dissemination:dissemination", xmlattributes(
dissemination.id as id),
xmlelement(name "dissemination:message", dissemination.message),
xmlelement(name "dissemination:subject", dissemination.subject),
xmlelement(name "dissemination:filter", dissemination.filter),
xmlelement(name "dissemination:sentOn", dissemination.senton),
researcher_to_xml(composerid, 'composer'),
xmlagg(researcher_to_xml(researchers.id, 'recipient'))
) FROM dissemination
INNER JOIN researchers ON researchers.id = ANY(dissemination.recipients)
WHERE dissemination.id = $1 
GROUP BY dissemination.message, 
dissemination.id, 
dissemination.filter, 
dissemination.senton, 
dissemination.subject, 
dissemination.composerid
ORDER BY dissemination.id
$_$;


ALTER FUNCTION public.dissemination_to_xml_ext(mid integer) OWNER TO appdb;

--
-- Name: dmetaphone(text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION dmetaphone(text) RETURNS text
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/fuzzystrmatch', 'dmetaphone';


ALTER FUNCTION public.dmetaphone(text) OWNER TO appdb;

--
-- Name: dmetaphone_alt(text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION dmetaphone_alt(text) RETURNS text
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/fuzzystrmatch', 'dmetaphone_alt';


ALTER FUNCTION public.dmetaphone_alt(text) OWNER TO appdb;

--
-- Name: edit_vowide_image_list(integer, integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION edit_vowide_image_list(_void integer, _userid integer) RETURNS integer
    LANGUAGE plpgsql
    AS $_$
DECLARE listid INT;
BEGIN
	BEGIN
		listid := (SELECT id FROM vowide_image_lists WHERE void = $1 AND state = 'draft');
		IF listid IS NULL THEN
			listid := (SELECT id FROM vowide_image_lists WHERE void = $1 AND state = 'published');
			IF listid IS NULL THEN
				INSERT INTO vowide_image_lists (void, alteredby) VALUES ($1, $2) RETURNING id INTO listid;
			ELSE
				INSERT INTO vowide_image_lists (void, guid, expires_on, notes, title, alteredby) SELECT void, guid, expires_on, notes, title, $2 FROM vowide_image_lists WHERE id = listid RETURNING id INTO listid;
			END IF;
		END IF;
		RETURN listid;
	EXCEPTION
		WHEN OTHERS THEN 
			RAISE NOTICE '[edit_vowide_image_list] %', MESSAGE_TEXT;
			RETURN NULL;
	END;
END;
$_$;


ALTER FUNCTION public.edit_vowide_image_list(_void integer, _userid integer) OWNER TO appdb;

--
-- Name: FUNCTION edit_vowide_image_list(_void integer, _userid integer); Type: COMMENT; Schema: public; Owner: appdb
--

COMMENT ON FUNCTION edit_vowide_image_list(_void integer, _userid integer) IS 'Returns the id of the draft image list for the specified VO. If one does not exists, it will be cloned from the published one, or created from scratch';


--
-- Name: export_app(integer[], text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION export_app(m_id integer[], format text DEFAULT 'csv'::text) RETURNS SETOF text
    LANGUAGE plpgsql STABLE STRICT
    AS $$
BEGIN
	RETURN QUERY SELECT export_app(id, format) FROM UNNEST(m_id) AS id;
END;
$$;


ALTER FUNCTION public.export_app(m_id integer[], format text) OWNER TO appdb;

--
-- Name: export_app(integer, text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION export_app(mid integer, format text DEFAULT 'csv'::text) RETURNS text
    LANGUAGE sql
    AS $_$
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
LEFT OUTER JOIN disciplines ON disciplines.id = ANY(disciplineid)
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

$_$;


ALTER FUNCTION public.export_app(mid integer, format text) OWNER TO appdb;

--
-- Name: export_researcher(integer, text, integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION export_researcher(mid integer, format text DEFAULT 'csv'::text, muserid integer DEFAULT NULL::integer) RETURNS text
    LANGUAGE sql
    AS $_$
SELECT CASE WHEN $2 = 'csv' THEN
'"' || REPLACE(COALESCE(researchers.firstname, ''), '"', E'”') || '",' ||
    '"' || REPLACE(COALESCE(researchers.lastname, ''), '"', E'”') || '",'
    '"' || REPLACE(COALESCE(researchers.gender, ''), '"', E'”') || '",'
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
		xmlelement(name "gender", researchers.gender),
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
    researchers.gender,
    researchers.dateinclusion,
    researchers.institution,
    countries.name,
    positiontypes.description,
    researchers.id
$_$;


ALTER FUNCTION public.export_researcher(mid integer, format text, muserid integer) OWNER TO appdb;

--
-- Name: rankedapps; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE rankedapps (
    id integer NOT NULL,
    name text NOT NULL,
    description text,
    abstract text,
    statusid integer NOT NULL,
    dateadded timestamp without time zone NOT NULL,
    addedby integer,
    respect boolean NOT NULL,
    tool boolean NOT NULL,
    guid uuid,
    lastupdated timestamp without time zone,
    rating double precision,
    ratingcount integer NOT NULL,
    moderated boolean NOT NULL,
    tagpolicy integer NOT NULL,
    deleted boolean,
    metatype integer NOT NULL,
    disciplineid integer[],
    owner integer,
    categoryid integer[],
    hitcount integer DEFAULT 0,
    cname text DEFAULT ''::text,
    links text[],
    rank integer DEFAULT 0 NOT NULL
);


ALTER TABLE rankedapps OWNER TO appdb;

--
-- Name: filterapps(text[], text[], text[]); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION filterapps(fltstr text[], m_from text[], m_where text[]) RETURNS SETOF rankedapps
    LANGUAGE plpgsql
    AS $$
DECLARE h TEXT;
DECLARE i INT;
DECLARE j INT;
DECLARE ids rankedids[];
BEGIN
	ids := NULL::rankedids[];
	FOR i IN 1..ARRAY_LENGTH(fltstr, 1) LOOP
		IF i = ARRAY_LENGTH(fltstr ,1) THEN
			IF ids IS NULL AND i = 1 THEN
				RETURN QUERY SELECT * FROM filterapps(fltstr[i], m_from[i], m_where[i]);
			ELSE
				RETURN QUERY SELECT
					applications.*,
					f.rank + rids.rank
				FROM filterapps(fltstr[i], m_from[i], m_where[i]) AS f 
				INNER JOIN UNNEST(ids) AS rids ON rids.id = f.id
				INNER JOIN applications ON applications.id = rids.id;
			END IF;
		ELSE
			IF ids IS NULL THEN
				SELECT array_agg((f.id, f.rank)::rankedids ORDER BY f.rank) FROM filterapps(fltstr[i]::text, m_from[i]::text, m_where[i]::text) AS f INTO ids;
			ELSE
				SELECT array_agg((f.id, f.rank + rids.rank)::rankedids ORDER BY f.rank) FROM filterapps(fltstr[i]::text, m_from[i]::text, m_where[i]::text) AS f INNER JOIN UNNEST(ids) AS rids ON rids.id = f.id INTO ids;		
			END IF;
		END IF;
	END LOOP;
END;
$$;


ALTER FUNCTION public.filterapps(fltstr text[], m_from text[], m_where text[]) OWNER TO appdb;

--
-- Name: filterapps(text, text, text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION filterapps(fltstr text, m_from text, m_where text) RETURNS SETOF rankedapps
    LANGUAGE plpgsql
    AS $$
DECLARE h TEXT;
BEGIN
	SELECT filteritems(fltstr, m_from, m_where, 'rankedapps') INTO h;
	IF (TRIM(fltstr) = '') OR (fltstr IS NULL) OR (TRIM(fltstr) = '""') THEN
		RETURN QUERY EXECUTE 'SELECT * FROM cache.filtercache_' || h || ' ORDER BY rank DESC, name ASC';
	ELSE
		RETURN QUERY SELECT * FROM rankapp_post(h); --ORDER BY rank DESC, socialrank DESC, name ASC;
	END IF;
END;
$$;


ALTER FUNCTION public.filterapps(fltstr text, m_from text, m_where text) OWNER TO appdb;

--
-- Name: filteritems(text, text, text, text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION filteritems(fltstr text, m_from text, m_where text, itemtype text) RETURNS text
    LANGUAGE plpgsql
    AS $$
DECLARE h TEXT;
DECLARE t TEXT;
DECLARE rank TEXT;
DECLARE _rank TEXT;
DECLARE cols TEXT[];
DECLARE cachecount BIGINT;
BEGIN
	IF fltstr IS NULL THEN fltstr = ''; END IF;
	IF m_from IS NULL THEN m_from = ''; END IF;
	IF m_where IS NULL THEN m_where = ''; END IF;
	fltstr := TRIM(fltstr);
	m_from := TRIM(m_from);
	m_where := TRIM(m_where);
	IF itemtype IS NULL THEN itemtype = ''; END IF;
	IF itemtype = 'rankedapps' THEN 
		t := 'applications';
		-- rank := 'rankapp(' || t || '.*, ''' || fltstr || ''') as rank';
		rank := 'rankapp';
		cols := (SELECT array_agg(column_name::text ORDER BY ordinal_position) FROM INFORMATION_SCHEMA.columns WHERE table_name = 'applications' AND table_schema = 'public');
	ELSIF itemtype = 'rankedppl' THEN
		t := 'researchers';
		-- rank := 'rankppl(' || t || '.*, ''' || fltstr || ''') as rank';
		rank := 'rankppl';
		cols := (SELECT array_agg(column_name::text ORDER BY ordinal_position) FROM INFORMATION_SCHEMA.columns WHERE table_name = 'researchers' AND table_schema = 'public');
	ELSIF itemtype = 'rankedvos' THEN
		t := 'vos';
		-- rank := 'rankvo(' || t || '.*, ''' || fltstr || ''') as rank';
		rank := 'rankvo';
		cols := (SELECT array_agg(column_name::text ORDER BY ordinal_position) FROM INFORMATION_SCHEMA.columns WHERE table_name = 'vos' AND table_schema = 'public');
	ELSIF itemtype = 'rankedsites' THEN
		t := 'sites';
		-- rank := 'ranksite(' || t || '.*, ''' || fltstr || ''') as rank';
		rank := 'ranksite';
		cols := (SELECT array_agg(column_name::text ORDER BY ordinal_position) FROM INFORMATION_SCHEMA.columns WHERE table_name = 'sites' AND table_schema = 'public');
	END IF;
	_rank := '0 as rank';
	h := MD5(m_from || ' ' || m_where);
	IF m_where = 'WHERE ()' THEN m_where = ''; END IF;
	IF EXISTS (SELECT * FROM config WHERE var = 'disable_filtercache' AND data::BOOLEAN IS TRUE) THEN
		DELETE FROM cache.filtercache WHERE hash = h;
	END IF;
	cachecount := 0;
	BEGIN
		EXECUTE 'SELECT COUNT(*) FROM cache.filtercache_' || h INTO cachecount;
	EXCEPTION
		WHEN OTHERS THEN
	END;
	IF (NOT EXISTS (SELECT hash FROM cache.filtercache WHERE hash = h AND invalid IS FALSE)) OR (cachecount = 0) THEN
		EXECUTE 'DROP TABLE IF EXISTS cache.filtercache_' || h;
		EXECUTE 'CREATE TABLE cache.filtercache_' || h || ' AS SELECT DISTINCT ON (' || t || '.id) ' || t || '.*, ' || _rank || ' ' || m_from || ' ' || m_where;			
		EXECUTE 'UPDATE cache.filtercache_' || h || ' SET rank = ' || rank || '((' || array_to_string(cols,' ,') || '), ''' || REPLACE(fltstr,'''','''''') || ''')';
		IF NOT EXISTS (SELECT hash FROM cache.filtercache WHERE hash = h) THEN
			INSERT INTO cache.filtercache (hash, m_from, m_where, fltstr) SELECT h, m_from, m_where, fltstr WHERE NOT EXISTS (SELECT hash FROM cache.filtercache AS c WHERE c.hash = h);
		ELSE
			UPDATE cache.filtercache SET usecount = usecount+1, invalid = FALSE WHERE hash = h;
		END IF;
	ELSE
		UPDATE cache.filtercache SET usecount = usecount+1 WHERE hash = h;
	END IF;
	RETURN h;
END;
$$;


ALTER FUNCTION public.filteritems(fltstr text, m_from text, m_where text, itemtype text) OWNER TO appdb;

--
-- Name: rankedppl; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE rankedppl (
    id integer NOT NULL,
    firstname text NOT NULL,
    lastname text NOT NULL,
    dateinclusion date NOT NULL,
    institution text NOT NULL,
    countryid integer NOT NULL,
    positiontypeid integer NOT NULL,
    guid uuid,
    gender character varying(10),
    lastupdated timestamp without time zone,
    name text,
    mail_unsubscribe_pwd uuid NOT NULL,
    lastlogin timestamp without time zone,
    nodissemination boolean,
    accounttype integer NOT NULL,
    deleted boolean NOT NULL,
    hitcount integer DEFAULT 0,
    cname text DEFAULT ''::text,
    addedby integer,
    rank integer DEFAULT 0 NOT NULL
);


ALTER TABLE rankedppl OWNER TO appdb;

--
-- Name: filterppl(text[], text[], text[]); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION filterppl(fltstr text[], m_from text[], m_where text[]) RETURNS SETOF rankedppl
    LANGUAGE plpgsql
    AS $$
DECLARE h TEXT;
DECLARE i INT;
DECLARE j INT;
DECLARE ids rankedids[];
BEGIN
	ids := NULL::rankedids[];
	FOR i IN 1..ARRAY_LENGTH(fltstr, 1) LOOP
		IF i = ARRAY_LENGTH(fltstr ,1) THEN
			IF ids IS NULL AND i = 1 THEN
				RETURN QUERY SELECT * FROM filterppl(fltstr[i], m_from[i], m_where[i]);
			ELSE
				RETURN QUERY SELECT
					researchers.*,
					f.rank + rids.rank
				FROM filterppl(fltstr[i], m_from[i], m_where[i]) AS f 
				INNER JOIN UNNEST(ids) AS rids ON rids.id = f.id
				INNER JOIN researchers ON researchers.id = rids.id;
			END IF;
		ELSE
			IF ids IS NULL THEN
				SELECT array_agg((f.id, f.rank)::rankedids ORDER BY f.rank) FROM filterppl(fltstr[i]::text, m_from[i]::text, m_where[i]::text) AS f INTO ids;
			ELSE
				SELECT array_agg((f.id, f.rank + rids.rank)::rankedids ORDER BY f.rank) FROM filterppl(fltstr[i]::text, m_from[i]::text, m_where[i]::text) AS f INNER JOIN UNNEST(ids) AS rids ON rids.id = f.id INTO ids;		
			END IF;
		END IF;
	END LOOP;
END;
$$;


ALTER FUNCTION public.filterppl(fltstr text[], m_from text[], m_where text[]) OWNER TO appdb;

--
-- Name: filterppl(text, text, text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION filterppl(fltstr text, m_from text, m_where text) RETURNS SETOF rankedppl
    LANGUAGE plpgsql
    AS $$
DECLARE h TEXT;
BEGIN
	SELECT filteritems(fltstr, m_from, m_where, 'rankedppl') INTO h;
	RETURN QUERY EXECUTE 'SELECT * FROM cache.filtercache_' || h || ' ORDER BY rank DESC, name ASC';
END;
$$;


ALTER FUNCTION public.filterppl(fltstr text, m_from text, m_where text) OWNER TO appdb;

--
-- Name: rankedsites; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE rankedsites (
    id text,
    name text,
    shortname text,
    officialname text,
    description text,
    portalurl text,
    homeurl text,
    contactemail text,
    contacttel text,
    alarmemail text,
    csirtemail text,
    giisurl text,
    countryid integer,
    countrycode text,
    countryname text,
    regionid integer,
    regionname text,
    tier text,
    subgrid text,
    roc text,
    productioninfrastructure text,
    certificationstatus text,
    timezone text,
    latitude text,
    longitude text,
    domainname text,
    ip text,
    guid uuid,
    datasource text,
    createdon timestamp without time zone,
    createdby text,
    updatedon timestamp without time zone,
    updatedby text,
    deleted boolean,
    deletedon timestamp without time zone,
    deletedby text,
    rank integer DEFAULT 0 NOT NULL
);


ALTER TABLE rankedsites OWNER TO appdb;

--
-- Name: filtersites(text[], text[], text[]); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION filtersites(fltstr text[], m_from text[], m_where text[]) RETURNS SETOF rankedsites
    LANGUAGE plpgsql
    AS $$
DECLARE h TEXT;
DECLARE i INT;
DECLARE j INT;
DECLARE ids rankedidstxt[];
BEGIN
	ids := NULL::rankedidstxt[];
	FOR i IN 1..ARRAY_LENGTH(fltstr, 1) LOOP
		IF i = ARRAY_LENGTH(fltstr ,1) THEN
			IF ids IS NULL AND i = 1 THEN
				RETURN QUERY SELECT * FROM filtersites(fltstr[i], m_from[i], m_where[i]);
			ELSE
				RETURN QUERY SELECT
					sites.*,
					f.rank + rids.rank
				FROM filtersites(fltstr[i], m_from[i], m_where[i]) AS f 
				INNER JOIN UNNEST(ids) AS rids ON rids.id = f.id
				INNER JOIN sites ON sites.id = rids.id;
			END IF;
		ELSE
			IF ids IS NULL THEN
				SELECT array_agg((f.id, f.rank)::rankedidstxt ORDER BY f.rank) FROM filtersites(fltstr[i]::text, m_from[i]::text, m_where[i]::text) AS f INTO ids;
			ELSE
				SELECT array_agg((f.id, f.rank + rids.rank)::rankedidstxt ORDER BY f.rank) FROM filtersites(fltstr[i]::text, m_from[i]::text, m_where[i]::text) AS f INNER JOIN UNNEST(ids) AS rids ON rids.id = f.id INTO ids;		
			END IF;
		END IF;
	END LOOP;
END;
$$;


ALTER FUNCTION public.filtersites(fltstr text[], m_from text[], m_where text[]) OWNER TO appdb;

--
-- Name: filtersites(text, text, text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION filtersites(fltstr text, m_from text, m_where text) RETURNS SETOF rankedsites
    LANGUAGE plpgsql
    AS $$
DECLARE h TEXT;
BEGIN
	SELECT filteritems(fltstr, m_from, m_where, 'rankedsites') INTO h;
	RETURN QUERY EXECUTE 'SELECT * FROM cache.filtercache_' || h || ' ORDER BY rank DESC, name ASC';
END;
$$;


ALTER FUNCTION public.filtersites(fltstr text, m_from text, m_where text) OWNER TO appdb;

--
-- Name: rankedvos; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE rankedvos (
    id integer NOT NULL,
    name text NOT NULL,
    scope text,
    validated timestamp without time zone,
    description text,
    homepage text,
    enrollment text,
    aup text,
    domainid integer,
    deleted boolean NOT NULL,
    deletedon timestamp without time zone,
    alias text,
    status text,
    guid uuid,
    sourceid integer,
    disciplineid integer[],
    rank integer DEFAULT 0 NOT NULL
);


ALTER TABLE rankedvos OWNER TO appdb;

--
-- Name: filtervos(text[], text[], text[]); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION filtervos(fltstr text[], m_from text[], m_where text[]) RETURNS SETOF rankedvos
    LANGUAGE plpgsql
    AS $$
DECLARE h TEXT;
DECLARE i INT;
DECLARE j INT;
DECLARE ids rankedids[];
BEGIN
	ids := NULL::rankedids[];
	FOR i IN 1..ARRAY_LENGTH(fltstr, 1) LOOP
		IF i = ARRAY_LENGTH(fltstr ,1) THEN
			IF ids IS NULL AND i = 1 THEN
				RETURN QUERY SELECT * FROM filtervos(fltstr[i], m_from[i], m_where[i]);
			ELSE
				RETURN QUERY SELECT
					vos.*,
					f.rank + rids.rank
				FROM filtervos(fltstr[i], m_from[i], m_where[i]) AS f 
				INNER JOIN UNNEST(ids) AS rids ON rids.id = f.id
				INNER JOIN vos ON vos.id = rids.id;
			END IF;
		ELSE
			IF ids IS NULL THEN
				SELECT array_agg((f.id, f.rank)::rankedids ORDER BY f.rank) FROM filtervos(fltstr[i]::text, m_from[i]::text, m_where[i]::text) AS f INTO ids;
			ELSE
				SELECT array_agg((f.id, f.rank + rids.rank)::rankedids ORDER BY f.rank) FROM filtervos(fltstr[i]::text, m_from[i]::text, m_where[i]::text) AS f INNER JOIN UNNEST(ids) AS rids ON rids.id = f.id INTO ids;
			END IF;
		END IF;
	END LOOP;
END;
$$;


ALTER FUNCTION public.filtervos(fltstr text[], m_from text[], m_where text[]) OWNER TO appdb;

--
-- Name: filtervos(text, text, text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION filtervos(fltstr text, m_from text, m_where text) RETURNS SETOF rankedvos
    LANGUAGE plpgsql
    AS $$
DECLARE h TEXT;
BEGIN
	SELECT filteritems(fltstr, m_from, m_where, 'rankedvos') INTO h;
	RETURN QUERY EXECUTE 'SELECT * FROM cache.filtercache_' || h || ' ORDER BY rank DESC, name ASC';
END;
$$;


ALTER FUNCTION public.filtervos(fltstr text, m_from text, m_where text) OWNER TO appdb;

--
-- Name: find_os(text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION find_os(_os text) RETURNS SETOF oses
    LANGUAGE sql
    AS $_$
	SELECT (oses.*)::oses FROM oses WHERE (REPLACE($1, ' ', '') ILIKE '%' || REPLACE(name, ' ', '') || '%') OR ($1 ~* ANY(aliases));
$_$;


ALTER FUNCTION public.find_os(_os text) OWNER TO appdb;

--
-- Name: relationtypes; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE relationtypes (
    id integer NOT NULL,
    target_type e_entity NOT NULL,
    verbid integer NOT NULL,
    subject_type e_entity NOT NULL,
    description text,
    actionid integer,
    guid uuid DEFAULT uuid_generate_v4()
);


ALTER TABLE relationtypes OWNER TO appdb;

--
-- Name: find_relationtype(uuid, integer, uuid); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION find_relationtype(subject_guid uuid, verbid integer, target_guid uuid) RETURNS SETOF relationtypes
    LANGUAGE sql STABLE
    AS $_$
SELECT relationtypes.* FROM relationtypes 
INNER JOIN entityguids AS e1 ON e1.entitytype = relationtypes.subject_type
INNER JOIN entityguids AS e2 ON e2.entitytype = relationtypes.target_type
WHERE relationtypes.verbid = $2
AND e1.guid = $1
AND e2.guid = $3;
$_$;


ALTER FUNCTION public.find_relationtype(subject_guid uuid, verbid integer, target_guid uuid) OWNER TO appdb;

--
-- Name: find_user(text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION find_user(dat text) RETURNS SETOF researchers
    LANGUAGE sql
    AS $_$
SELECT DISTINCT (researchers.*)::researchers
FROM researchers
LEFT OUTER JOIN contacts ON contacts.researcherid = researchers.id
LEFT OUTER JOIN user_accounts ON user_accounts.researcherid = researchers.id
WHERE 
	contacts.contacttypeid = 7 AND (
		user_accounts.accountid ILIKE '%' || $1 || '%' OR
		contacts.data ILIKE '%' || $1 || '%' OR
		researchers.name ILIKE '%' || $1 || '%' OR
		researchers.cname ILIKE '%' || $1 || '%'
	);
$_$;


ALTER FUNCTION public.find_user(dat text) OWNER TO appdb;

--
-- Name: fix_linkdb_url_bug(text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION fix_linkdb_url_bug(url text) RETURNS text
    LANGUAGE sql IMMUTABLE STRICT
    AS $$
SELECT REGEXP_REPLACE(url, '%(25){1,}([0-9A-Fa-f]{2,2})', '%\2', 'g');
$$;


ALTER FUNCTION public.fix_linkdb_url_bug(url text) OWNER TO appdb;

--
-- Name: fixmails(text, text, text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION fixmails(fname text, lname text, mail text) RETURNS void
    LANGUAGE plpgsql
    AS $$BEGIN
IF EXISTS (SELECT data FROM contacts WHERE contacttypeid=7 AND researcherid = (SELECT id FROM researchers WHERE firstname = fname AND lastname = lname)) THEN
	UPDATE contacts SET data = mail WHERE contacttypeid=7 AND researcherid = (SELECT id FROM researchers WHERE firstname = fname AND lastname = lname);
ELSE
	INSERT INTO contacts (contacttypeid, data, researcherid) VALUES (7, mail, (SELECT id FROM researchers WHERE firstname = fname AND lastname = lname));
END IF;
END;$$;


ALTER FUNCTION public.fixmails(fname text, lname text, mail text) OWNER TO appdb;

--
-- Name: flthash(text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION flthash(flt text) RETURNS numeric
    LANGUAGE plpgsql
    AS $$
DECLARE x TEXT[];
DECLARE i INT;
DECLARE n NUMERIC;
BEGIN
	IF flt IS NULL OR TRIM(flt) = '' THEN 
		RETURN 0;
	ELSE
		n := 0;
		x := fltstr_to_array(flt);
		FOR i IN 1..ARRAY_LENGTH(x,1) LOOP
			n := n + hex2dec(MD5(x[i]));
		END LOOP;
		RETURN n;
	END IF;
END;
$$;


ALTER FUNCTION public.flthash(flt text) OWNER TO appdb;

--
-- Name: fltstr_nbs(text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION fltstr_nbs(t text) RETURNS text
    LANGUAGE plpgsql
    AS $$
DECLARE i INT;
DECLARE c CHAR(1);
DECLARE inq BOOLEAN;
BEGIN
	inq := FALSE;
	FOR i IN 1..LENGTH(t) LOOP
		c := SUBSTR(t,i,1);
		IF c = '"' THEN inq := NOT inq; END IF;
		IF c = ' ' THEN
			IF inq THEN
				t := SUBSTRING(t,1,i-1) || '_' || SUBSTRING(t,i+1);
			END IF;
		END IF;
	END LOOP;
	RETURN REPLACE(t, '"', '');
END;
$$;


ALTER FUNCTION public.fltstr_nbs(t text) OWNER TO appdb;

--
-- Name: fltstr_to_array(text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION fltstr_to_array(t text) RETURNS text[]
    LANGUAGE plpgsql
    AS $$
DECLARE i INT;
DECLARE c CHAR(1);
DECLARE inq BOOLEAN;
DECLARE x TEXT[];
BEGIN
	inq := FALSE;
	FOR i IN 1..LENGTH(t) LOOP
		c := SUBSTR(t,i,1);
		IF c = '"' THEN inq := NOT inq; END IF;
		IF c = ' ' THEN
			IF inq THEN
				t := SUBSTRING(t,1,i-1) || E'\357\273\277' || SUBSTRING(t,i+1);
			END IF;
		END IF;
	END LOOP;
	x := string_to_array(t, ' ');
	FOR i IN 1..ARRAY_LENGTH(x,1) LOOP
		x[i]:=REPLACE(x[i],E'\357\273\277',' ');
	END LOOP;
	RETURN x;
END;
$$;


ALTER FUNCTION public.fltstr_to_array(t text) OWNER TO appdb;

--
-- Name: fn_rilike(text, text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION fn_rilike(text, text) RETURNS boolean
    LANGUAGE sql IMMUTABLE STRICT
    AS $_$SELECT $2 ILIKE $1$_$;


ALTER FUNCTION public.fn_rilike(text, text) OWNER TO appdb;

--
-- Name: fn_rlike(text, text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION fn_rlike(text, text) RETURNS boolean
    LANGUAGE sql IMMUTABLE STRICT
    AS $_$SELECT $2 LIKE $1$_$;


ALTER FUNCTION public.fn_rlike(text, text) OWNER TO appdb;

--
-- Name: fn_texttouuid(text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION fn_texttouuid(v text) RETURNS uuid
    LANGUAGE sql
    AS $_$ SELECT $1::text::uuid$_$;


ALTER FUNCTION public.fn_texttouuid(v text) OWNER TO appdb;

--
-- Name: get_good_vmiinstanceid(integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION get_good_vmiinstanceid(_vmiinstanceid integer) RETURNS integer
    LANGUAGE sql
    AS $_$
SELECT CASE WHEN goodid IS NULL THEN $1 ELSE goodid END FROM (
	SELECT max(t1.id) as goodid FROM vmiinstances AS t1
	INNER JOIN vmiinstances AS t2 ON t1.checksum = t2.checksum AND t1.guid = t2.guid AND t2.id = $1
	INNER JOIN vapplists ON t1.id = vapplists.vmiinstanceid
	INNER JOIN vapp_versions ON vapplists.vappversionid = vapp_versions.id 
	WHERE vapp_versions.published
) AS t
$_$;


ALTER FUNCTION public.get_good_vmiinstanceid(_vmiinstanceid integer) OWNER TO appdb;

--
-- Name: get_vowide_image_state(integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION get_vowide_image_state(_vapplistid integer) RETURNS e_vowide_image_state
    LANGUAGE sql
    AS $_$
	SELECT CASE WHEN EXISTS (
		SELECT *
		FROM vapplists
		INNER JOIN vapp_versions ON vapp_versions.id = vapplists.vappversionid
		WHERE vapplists.id = $1
		AND vapp_versions.published AND vapp_versions.enabled AND NOT vapp_versions.archived
	) THEN
		'up-to-date'::e_vowide_image_state
	WHEN EXISTS (
		SELECT *
		FROM vapplists
		INNER JOIN vapp_versions ON vapp_versions.id = vapplists.vappversionid
		WHERE vapplists.id = $1
		AND vapp_versions.archived
	) AND NOT EXISTS (
		SELECT * FROM vapplists 
		INNER JOIN vmiinstances ON vmiinstances.id = vapplists.vmiinstanceid
		INNER JOIN vapp_versions ON vapp_versions.id = vapplists.vappversionid
		WHERE vmiinstances.guid = (SELECT guid FROM vmiinstances WHERE id = (SELECT vmiinstanceid FROM vapplists WHERE id = $1))
		AND vapp_versions.published AND vapp_versions.enabled AND NOT vapp_versions.archived
	)THEN 
		'deleted'::e_vowide_image_state
	WHEN EXISTS (
		SELECT *
		FROM vapplists
		INNER JOIN vapp_versions ON vapp_versions.id = vapplists.vappversionid
		WHERE vapplists.id = $1
		AND vapp_versions.archived
	) THEN 
		'obsolete'::e_vowide_image_state
	WHEN EXISTS (
		SELECT *
		FROM vapplists
		INNER JOIN vapp_versions ON vapp_versions.id = vapplists.vappversionid
		WHERE vapplists.id = $1
		AND NOT vapp_versions.archived AND NOT vapp_versions.published
	) THEN
		'draft'::e_vowide_image_state
	ELSE
		'unknown'::e_vowide_image_state
	END 
	--FROM vowide_image_list_images
	--WHERE id = _id;
$_$;


ALTER FUNCTION public.get_vowide_image_state(_vapplistid integer) OWNER TO appdb;

--
-- Name: grant_privilege(integer, uuid, uuid, integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION grant_privilege(_actionid integer, _actor uuid, _target uuid, editorid integer) RETURNS boolean
    LANGUAGE plpgsql
    AS $$
DECLARE g1 INT[];
DECLARE g2 INT[];
DECLARE g3 INT[];
BEGIN
	IF EXISTS (SELECT * FROM permissions WHERE actionid = 1 AND (actor = (SELECT guid FROM researchers WHERE id = editorid)) AND ((object = _target) OR (object IS NULL))) THEN
		-- editor has grant/revoke access
		-- g2: groups of the person who is editing the privilege
		g2 := (
			SELECT array_agg(groupid)
			FROM actor_group_members
			WHERE actorid = (SELECT guid FROM researchers WHERE id = editorid)
			AND CASE
				WHEN groupid IN (-3, -6, -9) THEN
					CASE WHEN (SELECT type FROM targets WHERE guid = _target) = 'app' THEN
						payload IN (SELECT id::text FROM appcountries WHERE appid = (SELECT id FROM applications WHERE guid = _target))
					ELSE
						FALSE
					END
				ELSE
					TRUE
			END
		);
		-- g3: groups of the actor who will receive the privilege
		g3 := (
			SELECT array_agg(groupid)
			FROM actor_group_members
			WHERE actorid = _actor
			AND CASE
				WHEN groupid IN (-3, -6, -9) THEN
					CASE WHEN (SELECT type FROM targets WHERE guid = _target) = 'app' THEN
						payload IN (SELECT id::text FROM appcountries WHERE appid = (SELECT id FROM applications WHERE guid = _target))
					ELSE
						FALSE
					END
				ELSE
					TRUE
			END
		);
		IF NOT EXISTS (SELECT * FROM permissions WHERE actionid = _actionid AND actor = _actor AND ((object = _target) OR (object IS NULL))) THEN
			IF NOT EXISTS (SELECT * FROM privileges WHERE actionid = _actionid AND actor = _actor AND ((object = _target) OR (object IS NULL)) AND revoked) THEN
				IF (-1 = ANY(g3) OR -2 = ANY(g3)) AND NOT (-1 = ANY(g2)) THEN
					-- only admins may alter privileges of other admins and of managers
					RETURN FALSE;
				ELSIF (-3 = ANY(g3)) AND NOT (-1 = ANY(g2) OR -2 = ANY(g2)) THEN
					-- only admins and managers may alter privileges of NILs
					RETURN FALSE;
				ELSE
					INSERT INTO privileges (actionid, object, actor, revoked, addedby) SELECT _actionid, _target, _actor, FALSE, editorid WHERE NOT EXISTS (SELECT * FROM permissions WHERE actionid = _actionid AND actor = _actor AND ((object = _target) OR (object IS NULL)));
					RETURN TRUE;
				END IF;
			ELSE
				-- privilege has been revoked. See if it can be un-revoked
				-- g1: groups of the person that added the privilege
				g1 := (
					SELECT array_agg(groupid) 
					FROM privileges 
					INNER JOIN actor_group_members AS agm ON agm.actorid = (SELECT guid FROM researchers WHERE id = privileges.addedby) 
					WHERE 
						revoked AND actionid = _actionid AND actor = _actor AND ((object = _target) OR (object IS NULL))
					AND CASE
						WHEN groupid IN (-3, -6, -9) THEN
							CASE WHEN (SELECT type FROM targets WHERE guid = _target) = 'app' THEN
								payload IN (SELECT id::text FROM appcountries WHERE appid = (SELECT id FROM applications WHERE guid = _target))
							ELSE
								FALSE
							END
						ELSE
							TRUE
					END
				);
				CASE
					WHEN (-1 = ANY(g1)) AND NOT (-1 = ANY(g2) OR -9 = ANY(g2)) THEN
						RETURN FALSE;
					WHEN (-2 = ANY(g1)) AND NOT (-1 = ANY(g2) OR -9 = ANY(g2)) THEN
						RETURN FALSE;
					WHEN -3 = ANY(g1) AND NOT (-2 = ANY(g2) OR -1 = ANY(g2) OR -9 = ANY(g2)) THEN
						RETURN FALSE;
					ELSE
						IF 
							_actionid = ANY(app_actions()) AND 
							_actor IN (
								SELECT guid FROM researchers WHERE id IN (
									SELECT addedby FROM applications WHERE guid = _target 
									UNION 
									SELECT owner FROM applications WHERE guid = _target
								)
							) AND NOT (-3 = ANY(g2) OR -2 = ANY(g2) OR -1 = ANY(g2))					
						THEN
							-- only admins, managers, and NILs can alter s/w owner/submitter privileges 
							RETURN FALSE;
						ELSIF (-1 = ANY(g3) OR -2 = ANY(g3)) AND NOT (-1 = ANY(g2)) THEN
							-- only admins may alter privileges of other admins and of managers
							RETURN FALSE;
						ELSIF (-3 = ANY(g3)) AND NOT (-1 = ANY(g2) OR -2 = ANY(g2)) THEN
							-- only admins and managers may alter privileges of NILs
							RETURN FALSE;
						ELSE
							IF (NOT _target IS NULL) AND (EXISTS (SELECT * FROM privileges WHERE actionid = _actionid AND actor = _actor AND object IS NULL)) AND (NOT EXISTS (SELECT * FROM privileges WHERE actionid = _actionid AND actor = _actor AND object = _target)) THEN
								-- cannot grant target-specific priv for when priv has been revoked for all targets
								RETURN FALSE;
							ELSE
								DELETE FROM privileges WHERE revoked AND actionid = _actionid AND actor = _actor AND ((object = _target) OR (object IS NULL));
								RETURN TRUE;
							END IF;
						END IF;
				END CASE;
			END IF;
		ELSE
			RETURN TRUE;
		END IF;
	ELSE
		-- editor does not have grant/revoke access 
		RETURN FALSE;
	END IF;
END;
$$;


ALTER FUNCTION public.grant_privilege(_actionid integer, _actor uuid, _target uuid, editorid integer) OWNER TO appdb;

--
-- Name: va_provider_templates; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE va_provider_templates (
    id bigint NOT NULL,
    va_provider_id text NOT NULL,
    resource_name text,
    memsize text,
    logical_cpus text,
    physical_cpus text,
    cpu_multiplicity text,
    resource_manager text,
    computing_manager text,
    os_family text,
    connectivity_in text,
    connectivity_out text,
    cpu_model text,
    resource_id text
);


ALTER TABLE va_provider_templates OWNER TO appdb;

--
-- Name: group_hash(va_provider_templates); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION group_hash(v va_provider_templates) RETURNS text
    LANGUAGE sql STABLE
    AS $$
SELECT md5(v.memsize || '_' || v.logical_cpus || '_' || v.physical_cpus || '_' || v.cpu_multiplicity || '_' || v.os_family || '_' || v.connectivity_in || '_' || v.connectivity_out || '_' || v.cpu_model);
$$;


ALTER FUNCTION public.group_hash(v va_provider_templates) OWNER TO appdb;

--
-- Name: has_34_in_array(integer[]); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION has_34_in_array(a integer[]) RETURNS boolean
    LANGUAGE plpgsql IMMUTABLE
    AS $_$BEGIN RETURN 34 = ANY($1);END;$_$;


ALTER FUNCTION public.has_34_in_array(a integer[]) OWNER TO appdb;

--
-- Name: FUNCTION has_34_in_array(a integer[]); Type: COMMENT; Schema: public; Owner: appdb
--

COMMENT ON FUNCTION has_34_in_array(a integer[]) IS 'used to index VA applications';


--
-- Name: hex2dec(text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION hex2dec(hex text) RETURNS numeric
    LANGUAGE plpgsql
    AS $$
DECLARE r NUMERIC;
DECLARE c CHAR(1);
DECLARE v NUMERIC;
DECLARE i NUMERIC;
DECLARE j NUMERIC;
DECLARE p NUMERIC;
BEGIN
	hex := UPPER(hex);
	r:=0;
	FOR i IN 0..LENGTH(hex)-1 LOOP
		c := SUBSTRING(hex,i+1,1);
		CASE c 
		WHEN '0' THEN
			v:=1;
		WHEN '1' THEN
			v:=1;
		WHEN '2' THEN
			v:=2;
		WHEN '3' THEN
			v:=3;
		WHEN '4' THEN
			v:=4;
		WHEN '5' THEN
			v:=5;
		WHEN '6' THEN
			v:=6;
		WHEN '7' THEN
			v:=7;
		WHEN '8' THEN
			v:=8;
		WHEN '9' THEN
			v:=9;
		WHEN 'A' THEN
			v:=10;
		WHEN 'B' THEN
			v:=11;
		WHEN 'C' THEN
			v:=12;
		WHEN 'D' THEN
			v:=13;
		WHEN 'E' THEN
			v:=14;
		WHEN 'F' THEN
			v:=15;
		ELSE 
			RAISE 'Invalid HEX representation %', c;
		END CASE;
		p:=1;
		FOR j IN 0..i-1 LOOP
			p:=p*16;
		END LOOP;
		r := r + v*p;
	END LOOP;
	RETURN r;
END;
$$;


ALTER FUNCTION public.hex2dec(hex text) OWNER TO appdb;

--
-- Name: htree(text, character, integer, text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION htree(tbl text, padding character DEFAULT ' '::bpchar, padding_count integer DEFAULT 1, indicator text DEFAULT '⤷'::text) RETURNS TABLE(id integer, name text, parentid integer, lvl integer)
    LANGUAGE plpgsql
    AS $$
BEGIN
/**
* this function assumes the requested table has the following columns:
*   - id: the entry's numeric representation
*   - name: the entry's text repesentation
*   - parentid: the numeric representation of the entry's parent entry
* 
* the recursive CTE used computes "l" as the entry's nesting level (1-based),
* and "o" as the inheritance chain, which serves as an ordering field
*/
RETURN QUERY
EXECUTE
'WITH RECURSIVE lvl(cid,l,cname,pid,o) AS (
	VALUES (NULL::int,0,'''',0,NULL::text)
	UNION ALL
	SELECT 
		id, 
		l+1,
		LPAD(''' || indicator || ''', ' || padding_count::text || ' * l, ''' || padding || ''') || name,
		parentid,
		CASE WHEN o IS NULL THEN CASE COALESCE(ord, 0) WHEN 0 THEN ''Z'' ELSE ord::text END || '' '' || name ELSE o || ''_'' || name END
	FROM lvl, ' || tbl || ' WHERE NOT ' || tbl || '.parentid IS DISTINCT FROM cid
)
SELECT cid,cname,pid,l FROM lvl
WHERE l>0
ORDER BY o';
END;
$$;


ALTER FUNCTION public.htree(tbl text, padding character, padding_count integer, indicator text) OWNER TO appdb;

--
-- Name: htree_html(text, integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION htree_html(tbl text, padding integer DEFAULT 10) RETURNS SETOF text
    LANGUAGE plpgsql
    AS $$
BEGIN
RETURN QUERY
SELECT CASE WHEN STRPOS(n,'@') = 0 THEN
	REPLACE(n, ' style="padding-left: ','>')
ELSE
	REPLACE(REGEXP_REPLACE(n,'@+','#">'), '#', (CountInString(n,'@') * padding)::text || 'px')
END AS name
FROM (SELECT '<li style="padding-left: ' || name || '</li>' AS n FROM htree(tbl,'@',1,'')) AS t;
END;
$$;


ALTER FUNCTION public.htree_html(tbl text, padding integer) OWNER TO appdb;

--
-- Name: idx(anyarray, anyelement); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION idx(anyarray, anyelement) RETURNS integer
    LANGUAGE sql IMMUTABLE
    AS $_$
  SELECT i FROM (
     SELECT generate_series(array_lower($1,1),array_upper($1,1))
  ) g(i)
  WHERE $1[i] = $2
  LIMIT 1;
$_$;


ALTER FUNCTION public.idx(anyarray, anyelement) OWNER TO appdb;

--
-- Name: instr(character varying, character varying); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION instr(character varying, character varying) RETURNS integer
    LANGUAGE plpgsql
    AS $_$
DECLARE
    pos integer;
BEGIN
    pos:= instr($1, $2, 1);
    RETURN pos;
END;
$_$;


ALTER FUNCTION public.instr(character varying, character varying) OWNER TO appdb;

--
-- Name: instr(character varying, character varying, character varying); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION instr(character varying, character varying, character varying) RETURNS integer
    LANGUAGE plpgsql
    AS $_$
DECLARE
    string ALIAS FOR $1;
    string_to_search ALIAS FOR $2;
    beg_index ALIAS FOR $3;
    pos integer NOT NULL DEFAULT 0;
    temp_str varchar;
    beg integer;
    length integer;
    ss_length integer;
BEGIN
    IF beg_index > 0 THEN
        temp_str := substring(string FROM beg_index);
        pos := position(string_to_search IN temp_str);

        IF pos = 0 THEN
            RETURN 0;
        ELSE
            RETURN pos + beg_index - 1;
        END IF;
    ELSE
        ss_length := char_length(string_to_search);
        length := char_length(string);
        beg := length + beg_index - ss_length + 2;

        WHILE beg > 0 LOOP
            temp_str := substring(string FROM beg FOR ss_length);
            pos := position(string_to_search IN temp_str);

            IF pos > 0 THEN
                RETURN beg;
            END IF;

            beg := beg - 1;
        END LOOP;

        RETURN 0;
    END IF;
END;
$_$;


ALTER FUNCTION public.instr(character varying, character varying, character varying) OWNER TO appdb;

--
-- Name: instr(character varying, character varying, integer, integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION instr(character varying, character varying, integer, integer DEFAULT 1) RETURNS integer
    LANGUAGE plpgsql
    AS $_$
DECLARE
    string ALIAS FOR $1;
    string_to_search ALIAS FOR $2;
    beg_index ALIAS FOR $3;
    occur_index ALIAS FOR $4;
    pos integer NOT NULL DEFAULT 0;
    occur_number integer NOT NULL DEFAULT 0;
    temp_str varchar;
    beg integer;
    i integer;
    length integer;
    ss_length integer;
BEGIN
    IF beg_index > 0 THEN
        beg := beg_index;
        temp_str := substring(string FROM beg_index);

        FOR i IN 1..occur_index LOOP
            pos := position(string_to_search IN temp_str);

            IF i = 1 THEN
                beg := beg + pos - 1;
            ELSE
                beg := beg + pos;
            END IF;

            temp_str := substring(string FROM beg + 1);
        END LOOP;

        IF pos = 0 THEN
            RETURN 0;
        ELSE
            RETURN beg;
        END IF;
    ELSE
        ss_length := char_length(string_to_search);
        length := char_length(string);
        beg := length + beg_index - ss_length + 2;

        WHILE beg > 0 LOOP
            temp_str := substring(string FROM beg FOR ss_length);
            pos := position(string_to_search IN temp_str);

            IF pos > 0 THEN
                occur_number := occur_number + 1;

                IF occur_number = occur_index THEN
                    RETURN beg;
                END IF;
            END IF;

            beg := beg - 1;
        END LOOP;

        RETURN 0;
    END IF;
END;
$_$;


ALTER FUNCTION public.instr(character varying, character varying, integer, integer) OWNER TO appdb;

--
-- Name: invalidate_filtercache(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION invalidate_filtercache() RETURNS integer
    LANGUAGE plpgsql
    AS $$
DECLARE r RECORD;
BEGIN
	FOR r IN SELECT * FROM cache.filtercache LOOP
		EXECUTE 'DROP TABLE IF EXISTS cache.filtercache_' || r.hash;
	END LOOP;
	TRUNCATE TABLE cache.filtercache;
	RETURN 0;
END;
$$;


ALTER FUNCTION public.invalidate_filtercache() OWNER TO appdb;

--
-- Name: is_subscribed_to_notification(integer, integer, integer, text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION is_subscribed_to_notification(m_researcherid integer, m_notificationtype integer, m_delivery integer DEFAULT NULL::integer, m_payload text DEFAULT NULL::text) RETURNS boolean
    LANGUAGE plpgsql
    AS $$
BEGIN
	RETURN manage_notifications('QUERY', m_researcherid, m_notificationtype, m_delivery, m_payload)::BOOLEAN;
END;
$$;


ALTER FUNCTION public.is_subscribed_to_notification(m_researcherid integer, m_notificationtype integer, m_delivery integer, m_payload text) OWNER TO appdb;

--
-- Name: is_valid_actor_guid(uuid); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION is_valid_actor_guid(_guid uuid) RETURNS boolean
    LANGUAGE sql
    AS $_$SELECT EXISTS (SELECT * FROM actors WHERE guid = $1)$_$;


ALTER FUNCTION public.is_valid_actor_guid(_guid uuid) OWNER TO appdb;

--
-- Name: is_valid_url(text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION is_valid_url(url text) RETURNS boolean
    LANGUAGE plpgsql
    AS $$BEGIN
IF SUBSTRING(url,1,7) = 'http://' THEN
	RETURN TRUE;
ELSIF SUBSTRING(url,1,8) = 'https://' THEN
	RETURN TRUE;
ELSIF SUBSTRING(url,1,6) = 'ftp://' THEN
	RETURN TRUE;
ELSIF SUBSTRING(url,1,7) = 'sftp://' THEN 
	RETURN TRUE;
ELSIF SUBSTRING(url,1,7) = 'ftps://' THEN
	RETURN TRUE;
ELSE
	RETURN FALSE;
END IF;
END;$$;


ALTER FUNCTION public.is_valid_url(url text) OWNER TO appdb;

--
-- Name: isprivatejoin(text, text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION isprivatejoin(fl text, itemname text) RETURNS boolean
    LANGUAGE plpgsql IMMUTABLE
    AS $_$
BEGIN
	RETURN (fl ~* ('[+=<>&~$-]*&[+=<>&~$-]*' || itemname)) OR (fl = '&') OR (fl = '&%') OR (SUBSTRING(fl, 1, 2) = '& ');
END;
$_$;


ALTER FUNCTION public.isprivatejoin(fl text, itemname text) OWNER TO appdb;

--
-- Name: keywords(applications); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION keywords(applications) RETURNS text[]
    LANGUAGE sql STABLE
    AS $_$
	SELECT array_agg(tag) FROM app_tags WHERE appid = $1.id;
$_$;


ALTER FUNCTION public.keywords(applications) OWNER TO appdb;

--
-- Name: levenshtein(text, text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION levenshtein(text, text) RETURNS integer
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/fuzzystrmatch', 'levenshtein';


ALTER FUNCTION public.levenshtein(text, text) OWNER TO appdb;

--
-- Name: levenshtein(text, text, integer, integer, integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION levenshtein(text, text, integer, integer, integer) RETURNS integer
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/fuzzystrmatch', 'levenshtein_with_costs';


ALTER FUNCTION public.levenshtein(text, text, integer, integer, integer) OWNER TO appdb;

--
-- Name: disciplines; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE disciplines (
    id integer NOT NULL,
    parentid integer,
    name text NOT NULL,
    ord integer DEFAULT 0 NOT NULL
);


ALTER TABLE disciplines OWNER TO appdb;

--
-- Name: logoid(disciplines); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION logoid(d disciplines) RETURNS integer
    LANGUAGE plpgsql STABLE
    AS $$
DECLARE i INT;
BEGIN
	i := d.id;
	WHILE i NOT IN (1001, 1002, 1007, 1024, 1032, 1046, 1077, 1105, 1117, 1185, 1252, 1285, 1351, 1378, 998, 1092, 1082, 1102) LOOP
		SELECT (disciplines.*) FROM disciplines WHERE id = d.parentid INTO d;
		IF d IS NULL THEN
			EXIT;
		END IF;
		i := d.id;
	END LOOP;
	IF i NOT IN (1001, 1002, 1007, 1024, 1032, 1046, 1077, 1105, 1117, 1185, 1252, 1285, 1351, 1378, 998, 1092, 1082, 1102) THEN
		i := 998; -- OTHER
	END IF;
	RETURN i;
END;
$$;


ALTER FUNCTION public.logoid(d disciplines) OWNER TO appdb;

--
-- Name: logoid(vos); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION logoid(v vos) RETURNS text
    LANGUAGE plpgsql STABLE
    AS $$
DECLARE i INT[];
BEGIN
	SELECT ARRAY_AGG(DISTINCT disciplines.logoid ORDER BY disciplines.logoid) 
	FROM disciplines 
	INNER JOIN (SELECT UNNEST(disciplineid) AS did FROM vos WHERE id = v.id) AS x ON disciplines.id = x.did	AND 
	(ARRAY_LENGTH(v.disciplineid, 1) = 1 OR voDiscIsLeaf(x.did, v.id))
	INTO i;
	IF ARRAY_LENGTH(i, 1) > 1 THEN
		RETURN ARRAY_TO_STRING(i, '_');
	ELSIF ARRAY_LENGTH(i, 1) = 1 THEN
		RETURN i[1]::text;
	ELSE
		RETURN 998::text; -- OTHER
	END IF;
END;
$$;


ALTER FUNCTION public.logoid(v vos) OWNER TO appdb;

--
-- Name: manage_notifications(text, integer, integer, integer, text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION manage_notifications(m_action text, m_researcherid integer, m_notificationtype integer, m_delivery integer DEFAULT NULL::integer, m_payload text DEFAULT NULL::text) RETURNS integer
    LANGUAGE plpgsql
    AS $$
DECLARE newid INT;
DECLARE roleid INT[];
DECLARE m_subjecttype TEXT;
DECLARE m_events INT;
DECLARE m_name TEXT;
DECLARE m_flt TEXT;
BEGIN
	CASE m_notificationtype 
	WHEN 0 THEN
		m_subjecttype := 'inbox';
		m_events := 31;
		m_name := 'New inbox message';
		m_delivery := 1;
		m_flt := 'id:SYSTAG_INBOX';
	WHEN 1 THEN
		m_subjecttype := 'app';
		m_events := 31;
		m_name := 'Related software subscription';
		m_delivery := 4;
		m_flt := '=person.id:' || m_researcherid::text || ' id:SYSTAG_RELATED';
	WHEN 2 THEN
		IF m_payload IS NULL THEN 
			RETURN 0;
		ELSE
			IF EXISTS (SELECT id FROM applications WHERE id::TEXT = m_payload) THEN
				m_subjecttype := 'app-entry';
				m_events := 27;
				m_name := COALESCE((SELECT name FROM applications WHERE id::TEXT = m_payload LIMIT 1)::TEXT, '') || ' subscription';
				m_delivery := 2;
				m_flt := '=application.id:' || m_payload || ' application.id:SYSTAG_FOLLOW';
			ELSE
				RETURN 0;
			END IF;
		END IF;
		
	WHEN 3 THEN
		m_subjecttype := 'app';
		m_events := 31;
		m_name := 'Owned software subscription';
		m_delivery := 4;
		m_flt := '=application.owner:' || m_researcherid || ' id:SYSTAG_OWNER';
	WHEN 4 THEN
		RAISE NOTICE 'Obsolete notification type requested from notification management module %', m_action;
		RETURN 0;
/*		
		roleid := (SELECT CASE WHEN roleverified THEN positiontypeid ELSE 4 END FROM researchers WHERE id = m_researcherid);
		CASE roleid
		WHEN 5 THEN			
			m_flt := '';
		WHEN 7 THEN
			m_flt := '-=role.id:5';
		WHEN 6 THEN
			m_flt = '+=country.id:' || COALESCE((SELECT countryid FROM researchers WHERE id = m_researcherid LIMIT 1)::TEXT, '0') || ' -=role.id:7 -=role.id:5';
		ELSE
			RETURN 0;
		END CASE;
		m_name := 'Role verification mailing list';
		m_subjecttype := 'ppl';
		m_events := 96;
		m_delivery := 1;
*/
	WHEN 5 THEN
		roleid = (SELECT array_agg(groupid) FROM actor_group_members WHERE actorid = (SELECT guid FROM researchers WHERE id = m_researcherid));
		IF -1 = ANY(roleid) OR -2 = ANY(roleid) THEN -- admin, manager
			m_flt := '~application.name:. id:SYSTAG1';
		ELSIF -3 = ANY(roleid) THEN -- NGI rep.
			m_flt = '+=country.id:' || COALESCE((SELECT countryid FROM researchers WHERE id = m_researcherid LIMIT 1)::TEXT, '0') || ' id:SYSTAG1';
		ELSE
			RETURN 0;
		END IF;
		m_name := 'Software activities';
		m_subjecttype := 'app';
		m_events := 31;
		m_delivery := 4;
		
	ELSE
		RETURN 0;
	END CASE;
	CASE m_action
	WHEN 'SUBSCRIBE' THEN
		newid := (SELECT id FROM mail_subscriptions WHERE name = m_name AND subjecttype = m_subjecttype AND events = m_events AND researcherid = m_researcherid AND CASE WHEN NOT m_delivery IS NULL THEN delivery = m_delivery ELSE TRUE END AND flt = m_flt LIMIT 1);
		IF newid IS NULL THEN
			IF EXISTS (SELECT id FROM mail_subscriptions WHERE name = m_name AND researcherid = m_researcherid) THEN
				RAISE NOTICE 'Overwriting existing notification subscription ''%'' for user %', m_name, m_researcherid;
				DELETE FROM mail_subscriptions WHERE name = m_name AND researcherid = m_researcherid;
			END IF;
			INSERT INTO mail_subscriptions(name, subjecttype, events, researcherid, delivery, flt)
				VALUES (m_name, m_subjecttype, m_events, m_researcherid, m_delivery, m_flt) RETURNING id INTO newid;
		END IF;
		RETURN newid;
	WHEN 'UNSUBSCRIBE' THEN
		DELETE FROM mail_subscriptions WHERE name = m_name AND subjecttype = m_subjecttype AND events = m_events AND researcherid = m_researcherid AND delivery = m_delivery AND flt = m_flt;
		RETURN 1;
	WHEN 'QUERY' THEN
		IF EXISTS (SELECT * FROM mail_subscriptions WHERE name = m_name AND subjecttype = m_subjecttype AND events = m_events AND researcherid = m_researcherid AND delivery = m_delivery AND flt = m_flt) THEN
			RETURN 1;
		ELSE
			RETURN 0;
		END IF;
	ELSE
		RAISE NOTICE 'Invalid operation requested from notification management module %', m_action;
		RETURN 0;
	END CASE;
END;
$$;


ALTER FUNCTION public.manage_notifications(m_action text, m_researcherid integer, m_notificationtype integer, m_delivery integer, m_payload text) OWNER TO appdb;

--
-- Name: FUNCTION manage_notifications(m_action text, m_researcherid integer, m_notificationtype integer, m_delivery integer, m_payload text); Type: COMMENT; Schema: public; Owner: appdb
--

COMMENT ON FUNCTION manage_notifications(m_action text, m_researcherid integer, m_notificationtype integer, m_delivery integer, m_payload text) IS '
m_notificationtype ENUM:
-------------------------
0   inbox (SYSTAG_INBOX)
1   related software subscription (SYSTAG_RELATED)
2   followed apps (SYSTAG_FOLLOW)
3   owned apps (SYSTAG_OWNER)
4   role verification
5   software activities
';


--
-- Name: metaphone(text, integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION metaphone(text, integer) RETURNS text
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/fuzzystrmatch', 'metaphone';


ALTER FUNCTION public.metaphone(text, integer) OWNER TO appdb;

--
-- Name: middleware_to_xml(integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION middleware_to_xml(mid integer) RETURNS xml
    LANGUAGE sql
    AS $_$SELECT xmlelement(name "middleware:middleware", xmlattributes(
id as id, link as link), name) FROM middlewares WHERE id = $1;$_$;


ALTER FUNCTION public.middleware_to_xml(mid integer) OWNER TO appdb;

--
-- Name: ngi_to_xml(integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION ngi_to_xml(mid integer) RETURNS xml
    LANGUAGE sql
    AS $_$SELECT xmlelement(name "regional:provider", xmlattributes(
id as "id",
CASE WHEN countryID IS NULL THEN 'EIRO' ELSE 'NGI' END as "type",
european as "european",
countryid as "countryid"
), 
xmlelement(name "regional:name", name),
xmlelement(name "regional:description", description),
xmlelement(name "regional:url", url),
CASE WHEN NOT logo IS NULL THEN xmlelement(name "regional:logo", 'http://'||(SELECT data FROM config WHERE var='ui-host')||'/ngi/getlogo?id='||ngis.id::text) END)
FROM ngis WHERE id = $1 ORDER BY id$_$;


ALTER FUNCTION public.ngi_to_xml(mid integer) OWNER TO appdb;

--
-- Name: normalize_cname(text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION normalize_cname(s text) RETURNS text
    LANGUAGE plpgsql IMMUTABLE
    AS $_$
BEGIN
	s := LOWER(s);
	s := REPLACE(trim(regexp_replace(translate(
    lower($1),
    'áàâãäåāăąèééêëēĕėęěìíîïìĩīĭḩóôõöōŏőùúûüũūŭůäàáâãåæçćĉčöòóôõøüùúûßéèêëýñîìíïş',
    'aaaaaaaaaeeeeeeeeeeiiiiiiiihooooooouuuuuuuuaaaaaaeccccoooooouuuuseeeeyniiiis'
), '[^a-z0-9\-]+', ' ', 'g')),' ', '-');
	s := REGEXP_REPLACE(s, '[^A-Za-z0-9]',' ','g');
	-- s := REPLACE(s, ',', ' ');
	s := REGEXP_REPLACE(s, ' +', ' ', 'g');	
	s := REPLACE(s, ' ', '.');	
	s := REGEXP_REPLACE(s, '^\.', '');
	s := REGEXP_REPLACE(s, '\.$', '');	
	RETURN s;
END;
$_$;


ALTER FUNCTION public.normalize_cname(s text) OWNER TO appdb;

--
-- Name: appdocuments; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE appdocuments (
    id integer NOT NULL,
    appid integer NOT NULL,
    title text NOT NULL,
    url text,
    conference text,
    proceedings text,
    isbn text,
    pagestart integer,
    pageend integer,
    volume text,
    publisher text,
    year integer,
    mainauthor text,
    doctypeid integer NOT NULL,
    journal text,
    guid uuid DEFAULT uuid_generate_v4()
);


ALTER TABLE appdocuments OWNER TO appdb;

--
-- Name: appratings; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE appratings (
    id integer NOT NULL,
    appid integer NOT NULL,
    rating integer,
    comment text,
    submittedon timestamp without time zone DEFAULT now(),
    submitterid integer,
    submittername text,
    submitteremail text,
    guid uuid DEFAULT uuid_generate_v4() NOT NULL,
    moderated boolean DEFAULT false NOT NULL
);


ALTER TABLE appratings OWNER TO appdb;

--
-- Name: userrequesttypes; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE userrequesttypes (
    id integer NOT NULL,
    name text NOT NULL,
    description text
);


ALTER TABLE userrequesttypes OWNER TO appdb;

--
-- Name: namedobjects; Type: VIEW; Schema: public; Owner: appdb
--

CREATE VIEW namedobjects AS
 SELECT applications.id AS objectid,
    applications.name AS objectname,
    'app'::namedobjecttype AS objecttype
   FROM applications
UNION
 SELECT researchers.id AS objectid,
    ((researchers.firstname || ' '::text) || researchers.lastname) AS objectname,
    'ppl'::namedobjecttype AS objecttype
   FROM researchers
UNION
 SELECT appdocuments.id AS objectid,
    appdocuments.title AS objectname,
    'doc'::namedobjecttype AS objecttype
   FROM appdocuments
UNION
 SELECT appratings.id AS objectid,
    appratings.comment AS objectname,
    'cmm'::namedobjecttype AS objecttype
   FROM appratings
  WHERE ((NOT (appratings.comment IS NULL)) AND (btrim(appratings.comment) <> ''::text))
UNION
 SELECT userrequests.id AS objectid,
    userrequesttypes.name AS objectname,
    'req'::namedobjecttype AS objecttype
   FROM (userrequests
     JOIN userrequesttypes ON ((userrequesttypes.id = userrequests.typeid)));


ALTER TABLE namedobjects OWNER TO appdb;

--
-- Name: object_from_guid(uuid); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION object_from_guid(m_guid uuid) RETURNS namedobjects
    LANGUAGE plpgsql STABLE
    AS $$
DECLARE r namedobjects;
DECLARE mid integer;
DECLARE mname text;
BEGIN
	IF EXISTS (SELECT id FROM applications WHERE guid::text::uuid = m_guid) THEN
		SELECT id,name FROM applications WHERE guid = m_guid INTO mid, mname;
		r=(mid, mname,'app'::namedobjecttype);		
	ELSIF EXISTS (SELECT id FROM researchers WHERE guid::text::uuid = m_guid) THEN
		SELECT id, firstname||' '||lastname::text FROM researchers WHERE guid = m_guid INTO mid, mname;
		r=(mid, mname, 'ppl'::namedobjecttype);
	ELSIF EXISTS (SELECT id FROM appdocuments WHERE guid::text::uuid = m_guid) THEN
		SELECT id, title FROM appdocuments WHERE guid = m_guid INTO mid, mname;		
		r=(mid, mname, 'doc'::namedobjecttype);
	ELSIF EXISTS (SELECT id FROM appratings WHERE guid::text::uuid = m_guid) THEN
		SELECT id, "comment" FROM appratings WHERE guid = m_guid INTO mid, mname;
		r=(mid,mname,'cmm'::namedobjecttype);
	ELSIF EXISTS (SELECT id FROM userrequests WHERE guid::text::uuid = m_guid) THEN
		SELECT userrequesttypes.id, userrequesttypes.name FROM userrequests INNER JOIN userrequesttypes ON userrequesttypes.id = userrequests.typeid WHERE guid = m_guid INTO mid, mname;
		r=(mid,mname,'req'::namedobjecttype);
	END IF;
	RETURN r;
END;
$$;


ALTER FUNCTION public.object_from_guid(m_guid uuid) OWNER TO appdb;

--
-- Name: perms_to_xml(uuid, integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION perms_to_xml(object uuid, userid integer) RETURNS xml
    LANGUAGE sql
    AS $_$
SELECT 
	xmlelement(
		name "user:permissions", 
		xmlattributes(
			$2 as "userid"
		),
		xmlagg(
			xmlelement(
				name "privilege:action", 
				xmlattributes(
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
	AND CASE WHEN objecttype(object_from_guid($1)) = 'app' THEN
		actions.id = ANY(app_actions())
	WHEN objecttype(object_from_guid($1)) = 'ppl' THEN
		actions.id = ANY(ppl_actions())
	ELSE
		NOT (actions.id = ANY(ppl_actions()) OR actions.id = ANY(app_actions()))
	END
) AS t;
$_$;


ALTER FUNCTION public.perms_to_xml(object uuid, userid integer) OWNER TO appdb;

--
-- Name: ppl_actions(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION ppl_actions() RETURNS integer[]
    LANGUAGE sql IMMUTABLE
    AS $$
SELECT ARRAY[1, 2, 18, 21, 40, 41];
$$;


ALTER FUNCTION public.ppl_actions() OWNER TO appdb;

--
-- Name: FUNCTION ppl_actions(); Type: COMMENT; Schema: public; Owner: appdb
--

COMMENT ON FUNCTION ppl_actions() IS 'returns action ids that apply to people';


--
-- Name: ppl_logistics(text, text, text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION ppl_logistics(m_fltstr text, m_from text, m_where text) RETURNS xml
    LANGUAGE plpgsql
    AS $$
DECLARE h TEXT[];
DECLARE hh TEXT;
DECLARE fl TEXT[];
DECLARE fr TEXT[];
DECLARE w TEXT[];
DECLARE i INT;
DECLARE len INT;
BEGIN
        IF m_fltstr IS NULL THEN m_fltstr = ''; END IF;
        IF m_from IS NULL THEN m_from = ''; END IF;
        IF m_where IS NULL THEN m_where = ''; END IF;
		m_fltstr := TRIM(m_fltstr);
		m_from := TRIM(m_from);
		m_where := TRIM(m_where);
		IF SUBSTRING(m_fltstr, 1, 1) = '{' THEN
			fl := m_fltstr::text[];
			fr := m_from::text[];
			w := m_where::text[];
		ELSE
			fl := ('{"' || REPLACE(m_fltstr, '"', '\"') || '"}')::text[];
			fr := ('{"' || REPLACE(m_from, '"', '\"') || '"}')::text[];
			w := ('{"' ||  REPLACE(m_where, '"', '\"') || '"}')::text[];
		END IF;
		h := NULL::TEXT[];
		IF m_fltstr = '' THEN
			len := 0;
			hh := MD5(m_from || ' ' || m_where);
			IF NOT EXISTS (SELECT hash FROM cache.filtercache WHERE hash = hh) THEN
				PERFORM filterppl(m_fltstr, m_from, m_where);
			END IF;
			h := ARRAY['cache.filtercache_' || hh];
		ELSE
			len := ARRAY_LENGTH(fl, 1);
		END IF;
		FOR i IN 1..len LOOP
			m_fltstr = TRIM(fl[i]);
			m_from = TRIM(fr[i]);
			m_where = TRIM(w[i]);
			hh := MD5(m_from || ' ' || m_where);
			IF NOT EXISTS (SELECT hash FROM cache.filtercache WHERE hash = hh) THEN
                PERFORM filterppl(m_fltstr, m_from, m_where);
			END IF;
			hh := 'cache.filtercache_' || hh;
			h := array_append(h, hh);
		END LOOP;  
        RETURN xmlelement(name "person:logistics",
                xmlconcat(
                        (SELECT xmlagg(xmlelement(name "logistics:country", xmlattributes(t.name as "text", t.count as "count", t.id::text as "id"))) FROM count_ppl_matches('country', h, isPrivateJoin(fl[ARRAY_LENGTH(fl, 1)], 'country')) as t(name TEXT, count bigint, id text)),
						(SELECT xmlagg(xmlelement(name "logistics:discipline", xmlattributes(t.name as "text", t.count as "count", t.id::text as "id"))) FROM count_ppl_matches('discipline', h) as t(name TEXT, count bigint, id text)),
						(SELECT xmlagg(xmlelement(name "logistics:language", xmlattributes(t.name as "text", t.count as "count", t.id::text as "id"))) FROM count_ppl_matches('proglang', h) as t(name TEXT, count bigint, id text)),
						(SELECT xmlagg(xmlelement(name "logistics:role", xmlattributes(t.name as "text", t.count as "count", t.id::text as "id"))) FROM count_ppl_matches('role', h) as t(name TEXT, count bigint, id text)),
						(SELECT xmlagg(xmlelement(name "logistics:group", xmlattributes(t.name as "text", t.count as "count", t.id::text as "id"))) FROM count_ppl_matches('group', h) as t(name TEXT, count bigint, id text)),
						
						(SELECT xmlagg(xmlelement(name "logistics:phonebook", xmlattributes(t.name as "text", t.count as "count", t.id::text as "id"))) FROM 
			(
				WITH c AS (SELECT * FROM cached_ids(h) AS id)
				SELECT l AS "name", COUNT(DISTINCT researchers.id) AS count, n AS id FROM 
				(
				WITH RECURSIVE t(n) AS (
					VALUES (1)
					UNION ALL
					SELECT n+1 FROM t WHERE n < 28
				)
				SELECT 
				CASE 
				WHEN n<=26 THEN 
					SUBSTRING('ABCDEFGHIJKLMNOPQRSTUVWXYZ',n,1)
				WHEN n=27 THEN 
					'0-9'
				ELSE 
					'#'
				END AS l,
				CASE 
				WHEN n<=26 THEN 
					'^' || SUBSTRING('ABCDEFGHIJKLMNOPQRSTUVWXYZ',n,1) || '.+'
				WHEN n=27 THEN 
					'^[0-9].+'
				ELSE 
					'^[^A-Za-z0-9].+'
				END AS p,
				n
				FROM t) AS q
				INNER JOIN researchers ON researchers.name ~* p AND researchers.deleted IS FALSE
				WHERE researchers.id::text IN (SELECT id FROM c)
				GROUP BY l, n
				ORDER BY n
				) AS t
			)
                )
        );
END;
$$;


ALTER FUNCTION public.ppl_logistics(m_fltstr text, m_from text, m_where text) OWNER TO appdb;

--
-- Name: priv_is_editable(integer, uuid, uuid, integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION priv_is_editable(_actionid integer, _actor uuid, _target uuid, editorid integer) RETURNS boolean
    LANGUAGE plpgsql STRICT
    AS $$
DECLARE res BOOLEAN;
BEGIN
	IF EXISTS (SELECT * FROM permissions WHERE actionid = _actionid AND actor = _actor AND (object = _target OR object IS NULL)) THEN
		res := can_revoke_priv(_actionid, _actor, _target, editorid);
	ELSE
		res := can_grant_priv(_actionid, _actor, _target, editorid); 
	END IF;
	RETURN res;
END;
$$;


ALTER FUNCTION public.priv_is_editable(_actionid integer, _actor uuid, _target uuid, editorid integer) OWNER TO appdb;

--
-- Name: privgroup_to_xml(integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION privgroup_to_xml(groupid integer) RETURNS SETOF xml
    LANGUAGE sql
    AS $_$
SELECT 
	xmlelement(
		name "privilege:group",
		xmlattributes(
			actor_groups.id,
			actor_groups.guid AS suid,
			actor_groups.name AS name,
			actor_group_members.payload AS payload
		),
		xmlagg(
			xmlelement(
				name "privilege:actor",
				xmlattributes(
					actors.guid AS suid,
					actors.id,					
					actors.type
				),
				actors.name
			)
		)
	)
FROM actor_group_members 
INNER JOIN actor_groups ON actor_groups.id = actor_group_members.groupid
INNER JOIN actors ON actors.guid = actor_group_members.actorid
WHERE groupid = $1
GROUP BY 
	actor_groups.id,
	actor_group_members.payload;
$_$;


ALTER FUNCTION public.privgroup_to_xml(groupid integer) OWNER TO appdb;

--
-- Name: privgroups_to_xml(uuid); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION privgroups_to_xml(actor_guid uuid) RETURNS xml
    LANGUAGE sql
    AS $_$
SELECT 
	xmlagg(
	xmlelement(
		name "privilege:group",
		xmlattributes(
			actor_groups.id,
			actor_groups.guid AS suid,
			actor_group_members.payload AS payload
		),
		actor_groups.name
	))
FROM actor_group_members 
INNER JOIN actor_groups ON actor_groups.id = actor_group_members.groupid
WHERE actorid = $1;
$_$;


ALTER FUNCTION public.privgroups_to_xml(actor_guid uuid) OWNER TO appdb;

--
-- Name: privs_to_xml(uuid); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION privs_to_xml(user_guid uuid) RETURNS SETOF xml
    LANGUAGE sql
    AS $$
SELECT
	xmlelement(
		name "privilege:action", 
		xmlattributes(
			"system" as "system",
			actionid as "id",
			description as "description"
		),
		xmlagg(
			xmlelement(
				name "privilege:target",
				xmlattributes(
					CASE WHEN object IS NULL AND NOT actionid IS NULL THEN true END AS "xsi:nil",
					CASE WHEN NOT object IS NULL THEN object END AS suid,
					CASE WHEN NOT object IS NULL THEN 
						CASE targets.type 
							WHEN 'app' THEN 'application'
							WHEN 'ppl' THEN 'person'
							WHEN 'grp' THEN 'group'
						END
					END AS type,
					CASE WHEN NOT object IS NULL THEN targets.id END AS id
				),
				CASE WHEN NOT object IS NULL THEN targets.name END
			) ORDER BY object NULLS FIRST
		)
	)
FROM (
	SELECT DISTINCT "system", actor, actionid, object
	FROM permissions
) AS permissions
INNER JOIN actions ON actions.id = permissions.actionid
LEFT OUTER JOIN targets ON targets.guid = object
WHERE 
	actor = user_guid
	AND NOT COALESCE(targets.hidden, FALSE)
GROUP BY 
	"system",
	actionid,
	description
ORDER BY actionid
;
$$;


ALTER FUNCTION public.privs_to_xml(user_guid uuid) OWNER TO appdb;

--
-- Name: privs_to_xml(uuid, uuid); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION privs_to_xml(object uuid, user_guid uuid) RETURNS xml
    LANGUAGE sql
    AS $_$
	SELECT 
		xmlelement(
			name "user:permissions", 
			xmlattributes(
				(SELECT id FROM researchers WHERE guid = $2) as "userid"
			),
			xmlagg(
				xmlelement(
					name "privilege:action", 
					xmlattributes(
						"system",
						--priv_is_editable(actionid, $2, $1, (SELECT id FROM researchers WHERE guid = $2)) AS "editable",
						actionid as "id"
					), 
					description
				) ORDER by actionid
			)
		)
	FROM (
		SELECT DISTINCT 
			"system",
			actionid, 
			description 
		FROM /*(
			   SELECT DISTINCT ON (_permissions.actor, _permissions.actionid, _permissions.object) array_agg(_permissions.id) AS ids,
			    (EXISTS ( SELECT u.u
				   FROM unnest(array_agg(_permissions.id)) u(u)
				  WHERE u.u < 0)) AS system,
			    _permissions.actor,
			    _permissions.actionid,
			    _permissions.object
			   FROM _permissions
			  GROUP BY _permissions.actor, _permissions.actionid, _permissions.object
		)*/ permissions AS permissions
		INNER JOIN actions ON actions.id = permissions.actionid
		WHERE 
			actor = $2 
			AND (object = $1 OR object IS NULL)
			AND CASE 
				WHEN objecttype(object_from_guid($1)) = 'app' THEN
					actions.id = ANY(app_actions())
				WHEN objecttype(object_from_guid($1)) = 'ppl' THEN
					actions.id IN (18,21)
				ELSE
					TRUE
			END
	) AS T;
$_$;


ALTER FUNCTION public.privs_to_xml(object uuid, user_guid uuid) OWNER TO appdb;

--
-- Name: privs_to_xml_nocache(uuid, uuid); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION privs_to_xml_nocache(object uuid, user_guid uuid) RETURNS xml
    LANGUAGE sql
    AS $_$
	SELECT 
		xmlelement(
			name "user:permissions", 
			xmlattributes(
				(SELECT id FROM researchers WHERE guid = $2) as "userid"
			),
			xmlagg(
				xmlelement(
					name "privilege:action", 
					xmlattributes(
						"system",
						--priv_is_editable(actionid, $2, $1, (SELECT id FROM researchers WHERE guid = $2)) AS "editable",
						actionid as "id"
					), 
					description
				) ORDER by actionid
			)
		)
	FROM (
		SELECT DISTINCT 
			"system",
			actionid, 
			description 
		FROM (
			   SELECT DISTINCT ON (_permissions.actor, _permissions.actionid, _permissions.object) array_agg(_permissions.id) AS ids,
			    (EXISTS ( SELECT u.u
				   FROM unnest(array_agg(_permissions.id)) u(u)
				  WHERE u.u < 0)) AS system,
			    _permissions.actor,
			    _permissions.actionid,
			    _permissions.object
			   FROM _permissions
			  GROUP BY _permissions.actor, _permissions.actionid, _permissions.object
		) AS permissions
		INNER JOIN actions ON actions.id = permissions.actionid
		WHERE 
			actor = $2 
			AND (object = $1 OR object IS NULL)
			AND CASE 
				WHEN objecttype(object_from_guid($1)) = 'app' THEN
					actions.id = ANY(app_actions())
				WHEN objecttype(object_from_guid($1)) = 'ppl' THEN
					actions.id IN (18,21)
				ELSE
					TRUE
			END
	) AS T;
$_$;


ALTER FUNCTION public.privs_to_xml_nocache(object uuid, user_guid uuid) OWNER TO appdb;

--
-- Name: publish_vowide_image_list(integer, integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION publish_vowide_image_list(_void integer, _userid integer) RETURNS integer
    LANGUAGE plpgsql
    AS $_$
DECLARE listid INT;
BEGIN
	listid := (SELECT id FROM vowide_image_lists WHERE void = $1 AND state = 'draft');
	IF NOT listid IS NULL THEN
		UPDATE vowide_image_lists SET state = 'obsolete' WHERE void = $1 AND state = 'published';
		DELETE FROM vowide_image_list_images WHERE vowide_image_list_id = listid AND vapplistid IN (
			SELECT vapplistid 
			FROM vaviews 
			INNER JOIN applications ON applications.id = vaviews.appid
			WHERE applications.deleted -- OR applications.moderated
		);
		UPDATE vowide_image_lists SET state = 'published', published_on = NOW(), publishedby = $2 WHERE id = listid;
		NOTIFY clean_cache;
		RETURN listid;
	ELSE 
		RETURN NULL;
	END IF;
END;
$_$;


ALTER FUNCTION public.publish_vowide_image_list(_void integer, _userid integer) OWNER TO appdb;

--
-- Name: FUNCTION publish_vowide_image_list(_void integer, _userid integer); Type: COMMENT; Schema: public; Owner: appdb
--

COMMENT ON FUNCTION publish_vowide_image_list(_void integer, _userid integer) IS 'Sets currently published list to "obsolete" state, and promotes the draft version to "published". Returns the id of the published image list or NULL if no draft version exists';


--
-- Name: rankapp(applications, text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION rankapp(m_id applications, m_query text) RETURNS integer
    LANGUAGE plpgsql STABLE
    AS $_$
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
	SELECT array_agg(DISTINCT disciplines.name) FROM disciplines WHERE disciplines.id = ANY(m_id.disciplineid) INTO m_disciplines;
	SELECT array_agg(DISTINCT categories.name) FROM categories WHERE categories.id = ANY(m_id.categoryid) INTO m_categories;
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
$_$;


ALTER FUNCTION public.rankapp(m_id applications, m_query text) OWNER TO appdb;

--
-- Name: rankapp_post(text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION rankapp_post(h text) RETURNS SETOF rankedapps
    LANGUAGE plpgsql STABLE
    AS $$
DECLARE f TEXT;
DECLARE f2 TEXT;
BEGIN
f := app_fld_lst();
f2 := 'ranked.' || REGEXP_REPLACE(f, ', ', ', ranked.', 'g');
RETURN QUERY
EXECUTE '
WITH ranked2 AS (
WITH ranked AS (
SELECT 
	*
FROM 
	cache.filtercache_' || h || ' AS applications 
WHERE 
	(deleted OR moderated) IS FALSE 
),
allvisits AS (
SELECT 
	COUNT(*) AS count 
FROM 
app_api_log
),
appvisits AS (
SELECT 
	COUNT(*) AS count, 
	appid 
FROM 
	app_api_log 
GROUP BY 
	appid
)
SELECT ' || f2 || ',
	ranked.rank, 
	CASE WHEN MAX(rank) OVER () = 0 THEN
		0
	ELSE
		((COALESCE(rating, 0) + 1) * (COALESCE(appvisits.count::float * 100 / (SELECT allvisits.count FROM allvisits), 0) + 1) * (ranked.rank) * 100 / MAX(rank) OVER ())::int END 
		AS socialrank
FROM 
	ranked 
LEFT OUTER JOIN appvisits ON appvisits.appid = ranked.id 
)
SELECT ' || f || ',
	CASE WHEN (SELECT MAX(rank) FROM ranked2) = 0 THEN
		0
	ELSE
		rank * 100 / (SELECT MAX(rank) FROM ranked2)
	END
FROM
	ranked2
ORDER BY 
	rank DESC,
	socialrank DESC,
	name ASC ';

END;
$$;


ALTER FUNCTION public.rankapp_post(h text) OWNER TO appdb;

--
-- Name: rankppl(researchers, text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION rankppl(m_id researchers, m_query text) RETURNS integer
    LANGUAGE plpgsql
    AS $_$
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
	fields = '{id, name, institution, gender, countryname}'::TEXT[];
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
						IF fields[j] = 'gender' THEN IF m_id.gender ILIKE k THEN lrank := lrank + 1; END IF; END IF;
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
$_$;


ALTER FUNCTION public.rankppl(m_id researchers, m_query text) OWNER TO appdb;

SET search_path = gocdb, pg_catalog;

--
-- Name: sites; Type: TABLE; Schema: gocdb; Owner: appdb; Tablespace: 
--

CREATE TABLE sites (
    id integer NOT NULL,
    pkey text NOT NULL,
    name text NOT NULL,
    shortname text NOT NULL,
    officialname text,
    description text,
    portalurl text,
    homeurl text,
    contactemail text,
    contacttel text,
    alarmemail text,
    csirtemail text,
    giisurl text,
    countrycode text,
    country text,
    tier text,
    subgrid text,
    roc text,
    prodinfrastructure text,
    certstatus text,
    timezone text,
    latitude text,
    longitude text,
    domainname text,
    siteip text,
    guid uuid,
    createdon timestamp without time zone DEFAULT now() NOT NULL,
    updatedon timestamp without time zone,
    deleted boolean DEFAULT false,
    deletedon timestamp without time zone,
    deletedby text
);


ALTER TABLE sites OWNER TO appdb;

SET search_path = public, pg_catalog;

--
-- Name: regions; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE regions (
    id integer NOT NULL,
    name text NOT NULL
);


ALTER TABLE regions OWNER TO appdb;

--
-- Name: sites; Type: VIEW; Schema: public; Owner: appdb
--

CREATE VIEW sites AS
 SELECT sites.pkey AS id,
    sites.name,
    sites.shortname,
    sites.officialname,
    sites.description,
    sites.portalurl,
    sites.homeurl,
    sites.contactemail,
    sites.contacttel,
    sites.alarmemail,
    sites.csirtemail,
    sites.giisurl,
    countries.id AS countryid,
    sites.countrycode,
    sites.country AS countryname,
    regions.id AS regionid,
    regions.name AS regionname,
    sites.tier,
    sites.subgrid,
    sites.roc,
    sites.prodinfrastructure AS productioninfrastructure,
    sites.certstatus AS certificationstatus,
    sites.timezone,
    sites.latitude,
    sites.longitude,
    sites.domainname,
    sites.siteip AS ip,
    sites.guid,
    'gocdb'::text AS datasource,
    sites.createdon,
    'gocdb'::text AS createdby,
    sites.updatedon,
    'gocdb'::text AS updatedby,
    sites.deleted,
    sites.deletedon,
    sites.deletedby
   FROM ((gocdb.sites
     LEFT JOIN countries ON ((countries.isocode = sites.countrycode)))
     LEFT JOIN regions ON ((regions.id = countries.regionid)))
  WHERE ((sites.prodinfrastructure = 'Production'::text) AND (sites.certstatus = 'Certified'::text));


ALTER TABLE sites OWNER TO appdb;

--
-- Name: ranksite(sites, text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION ranksite(m_id sites, m_query text) RETURNS integer
    LANGUAGE plpgsql
    AS $_$
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
	RAISE NOTICE 'in ranksite function';
	IF m_query IS NULL OR TRIM(m_query) = '' THEN RETURN 0; END IF;
	m_query := fltstr_nbs(m_query);
	SELECT countries.name FROM countries WHERE countries.id = m_id.countryid INTO m_country;
	fields = '{id, name, shortname, officialname, description, roc, subgrid, countryname}'::TEXT[];
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
		IF SUBSTRING(arg,1,12) = 'country.name' THEN arg := 'site.countryname' || SUBSTRING(arg,13); END IF;
		IF SUBSTRING(arg,1,11) = 'country.any' THEN arg := 'site.countryname' || SUBSTRING(arg,12); END IF;		
		IF NOT (SUBSTRING(arg,1,5) = 'site.' OR SUBSTRING(arg,1,4) = 'any.' OR instr(arg,'.') = 0) THEN CONTINUE; END IF;
		IF SUBSTR(arg,1,5) = 'site.' THEN arg = SUBSTRING(arg,6);
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
						RAISE NOTICE '%', k;
						lrank := 0;					
						IF fields[j] = 'name' THEN IF m_id.name ILIKE k THEN lrank := lrank + 4; END IF; END IF;
						IF fields[j] = 'shortname' THEN IF m_id.shortname ILIKE k THEN lrank := lrank + 4; END IF; END IF;
						IF fields[j] = 'officialname' THEN IF m_id.officialname ILIKE k THEN lrank := lrank + 4; END IF; END IF;
						IF fields[j] = 'description' THEN IF m_id.description ILIKE k THEN lrank := lrank + 3; END IF; END IF;
						IF fields[j] = 'roc' THEN IF m_id.roc ILIKE k THEN lrank := lrank + 1; END IF; END IF;
						IF fields[j] = 'subgrid' THEN IF m_id.subgrid ILIKE k THEN lrank := lrank + 1; END IF; END IF;
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
$_$;


ALTER FUNCTION public.ranksite(m_id sites, m_query text) OWNER TO appdb;

--
-- Name: rankvo(vos, text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION rankvo(m_id vos, m_query text) RETURNS integer
    LANGUAGE plpgsql
    AS $_$
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
DECLARE m_disciplines TEXT[];
BEGIN
	RAISE NOTICE 'in rankvo function';
	IF m_query IS NULL OR TRIM(m_query) = '' THEN RETURN 0; END IF;
	m_query := fltstr_nbs(m_query);
	SELECT array_agg(DISTINCT disciplines.name) FROM disciplines WHERE disciplines.id = ANY(m_id.disciplineid) INTO m_disciplines;
	fields = '{id, name, scope, description, disciplinename}'::TEXT[];
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
		IF SUBSTRING(arg,1,15) = 'discipline.name' THEN arg := 'vo.disciplinename' || SUBSTRING(arg,16); END IF;
		IF SUBSTRING(arg,1,14) = 'discipline.any' THEN arg := 'vo.disciplinename' || SUBSTRING(arg,15); END IF;		
		IF NOT (SUBSTRING(arg,1,3) = 'vo.' OR SUBSTRING(arg,1,4) = 'any.' OR instr(arg,'.') = 0) THEN CONTINUE; END IF;
		IF SUBSTR(arg,1,3) = 'vo.' THEN arg = SUBSTRING(arg,4);
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
						RAISE NOTICE '%', k;
						lrank := 0;					
						IF fields[j] = 'name' THEN IF m_id.name ILIKE k THEN lrank := lrank + 6; END IF; END IF;
						IF fields[j] = 'description' THEN IF m_id.description ILIKE k THEN lrank := lrank + 4; END IF; END IF;
						IF fields[j] = 'scope' THEN IF m_id.scope ILIKE k THEN lrank := lrank + 1; END IF; END IF;
						IF fields[j] = 'id' THEN IF m_id.id::TEXT ILIKE k THEN lrank := lrank + 1; END IF; END IF;
						IF fields[j] = 'disciplinename' AND NOT m_disciplines IS NULL THEN 
							FOR l IN 1..array_length(m_disciplines, 1) LOOP
								IF m_disciplines[l] ILIKE k THEN lrank := lrank + 10; END IF;
							END LOOP;
						END IF;
						-- BONUS FOR SPECIFIC FIELD
						IF fields[j] = field THEN lrank = lrank * 3; END IF;
						rank := rank + lrank;
					END LOOP;
				END IF;
			END LOOP;
		END IF;
	END LOOP;
	RETURN rank;
END
$_$;


ALTER FUNCTION public.rankvo(m_id vos, m_query text) OWNER TO appdb;

--
-- Name: rebuild_fulltext_index(text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION rebuild_fulltext_index(itemtype text) RETURNS void
    LANGUAGE plpgsql STRICT
    AS $$
BEGIN
	BEGIN
		EXECUTE 'TRUNCATE TABLE ' || itemtype || '.any';
        EXECUTE 'INSERT INTO ' || itemtype || '.any (id, "any") SELECT id, ' || itemtype || '.any(id) FROM ' || itemtype;
	EXCEPTION
		WHEN OTHERS THEN -- DO NOTHING
	END;
END;
$$;


ALTER FUNCTION public.rebuild_fulltext_index(itemtype text) OWNER TO appdb;

--
-- Name: rebuildfiltercache(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION rebuildfiltercache() RETURNS void
    LANGUAGE plpgsql
    AS $$
DECLARE cachetype TEXT;
DECLARE ranktype TEXT;
DECLARE i RECORD;
DECLARE cur CURSOR FOR SELECT * FROM cache.filtercache;
DECLARE _usecount INT;
BEGIN
	FOR i IN cur LOOP
		cachetype := REGEXP_REPLACE(TRIM(i.m_from), 'FROM (\w+)\s*.*', '\1');
		CASE cachetype 
		WHEN'applications' THEN
			ranktype := 'rankedapps';
		WHEN 'researchers' THEN
			ranktype := 'rankedppl';
		WHEN 'vos' THEN
			ranktype := 'rankedvos';
		WHEN 'sites' THEN
			ranktype := 'rankedsites';
		ELSE
			RAISE NOTICE 'Unknown cachetype % request for cache delta check, ignoring', cachetype;
			CONTINUE;
		END CASE;		
		_usecount := i.usecount;
		DELETE FROM cache.filtercache WHERE CURRENT OF cur;
		PERFORM filteritems(i.fltstr, i.m_from, i.m_where, ranktype);
		UPDATE cache.filtercache SET usecount = _usecount WHERE hash = i.hash;
	END LOOP;
END;
$$;


ALTER FUNCTION public.rebuildfiltercache() OWNER TO appdb;

--
-- Name: refresh_permissions(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION refresh_permissions() RETURNS void
    LANGUAGE plpgsql
    AS $$
BEGIN
        REFRESH MATERIALIZED VIEW CONCURRENTLY _actor_group_members;
        REFRESH MATERIALIZED VIEW CONCURRENTLY _actor_group_members2;
        REFRESH MATERIALIZED VIEW CONCURRENTLY permissions;
        TRUNCATE TABLE cache.appprivsxmlcache;
        UPDATE config SET data = '0' WHERE var = 'permissions_cache_dirty';
END; 
$$;


ALTER FUNCTION public.refresh_permissions() OWNER TO appdb;

--
-- Name: region_to_xml(integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION region_to_xml(mid integer) RETURNS xml
    LANGUAGE sql
    AS $_$SELECT xmlelement(name "regional:region", xmlattributes(
regions.id as id), regions.name) FROM regions WHERE id = $1 ORDER BY name$_$;


ALTER FUNCTION public.region_to_xml(mid integer) OWNER TO appdb;

--
-- Name: related_apps(integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION related_apps(m_appid integer) RETURNS TABLE(app applications, rank integer)
    LANGUAGE plpgsql STABLE
    AS $$
BEGIN
	RETURN QUERY
	SELECT ROW(apps1.*)::applications AS app, 1 AS rank FROM applications as apps1, applications as apps2 WHERE (apps1.name ILIKE '%'||apps2.name||'%' OR apps2.name ILIKE '%'||apps1.name||'%') AND apps1.id <> apps2.id AND apps2.id = m_appid AND (apps1.moderated=false AND apps1.deleted=false AND apps2.moderated=false AND apps2.deleted=false)
	UNION
	SELECT ROW(applications.*)::applications, 2 FROM applications WHERE applications.id IN (SELECT r1.appid FROM researchers_apps AS r1 WHERE r1.researcherid IN (SELECT r2.researcherid FROM researchers_apps AS r2 WHERE r2.appid = m_appid)) AND applications.id <> m_appid AND applications.moderated=false AND applications.deleted=false
	UNION
	SELECT ROW(apps1.*)::applications, 3 FROM applications AS apps1, applications AS apps2 WHERE soundex(apps1.name) = ANY(soundexx(string_to_array(apps2.name,' '))) AND apps1.id <> apps2.id AND apps2.id = m_appid AND (apps1.moderated=false and apps1.deleted=false AND apps2.moderated=false AND apps2.deleted=false);
END
$$;


ALTER FUNCTION public.related_apps(m_appid integer) OWNER TO appdb;

--
-- Name: relation_item_to_xml(uuid); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION relation_item_to_xml(guid uuid) RETURNS xml
    LANGUAGE sql STABLE
    AS $_$
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
			XMLELEMENT( name "person:lastname", researchers.lastname ),
			XMLELEMENT( name "person:gender", researchers.gender )
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
$_$;


ALTER FUNCTION public.relation_item_to_xml(guid uuid) OWNER TO appdb;

--
-- Name: relation_to_xml(integer, uuid); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION relation_to_xml(relid integer, guid uuid DEFAULT NULL::uuid) RETURNS SETOF xml
    LANGUAGE sql STABLE
    AS $_$SELECT XMLELEMENT(
	name "entity:relation",
	XMLATTRIBUTES(
		er.id AS id,
		er.reltypeid AS relationtypeid,
		er.verb AS verbname,
		er.guid AS guid,
		er.addedon AS addedon,
		er.denyon AS denyon,
		CASE WHEN $2 IS NOT NULL AND $2 = er.target_guid THEN TRUE ELSE NULL END AS reversed,
		CASE WHEN $2 IS NOT NULL AND $2 = er.target_guid AND er.hiddenby IS NOT NULL THEN TRUE ELSE NULL END AS hidden,
		CASE WHEN $2 IS NOT NULL AND $2 = er.target_guid AND er.hiddenby IS NOT NULL THEN er.hiddenby ELSE NULL END AS hiddenby,
		CASE WHEN $2 IS NOT NULL AND $2 = er.target_guid AND er.hiddenby IS NOT NULL THEN er.hiddenon ELSE NULL END AS hiddenon
	),
	CASE WHEN $2 IS NOT NULL AND $2 = er.target_guid THEN
	XMLELEMENT(
		name "entity:entity" ,
		XMLATTRIBUTES(
			er.subject_type AS type,
			er.subject_guid AS guid
		),
		relation_item_to_xml(er.subject_guid)
	)
	ELSE
	XMLELEMENT(
		name "entity:entity" ,
		XMLATTRIBUTES(
			er.target_type AS type,
			er.target_guid AS guid
		),
		relation_item_to_xml(er.target_guid)
	)
	END,
	CASE WHEN er.addedby IS NOT NULL THEN
	XMLELEMENT(
		name "entity:addedby",
		XMLATTRIBUTES(ar.id, ar.cname, CASE WHEN ar.deleted THEN 'true' END as deleted), 
		XMLELEMENT(name "person:firstname", TRIM(ar.firstname)), 
		XMLELEMENT(name "person:lastname", TRIM(ar.lastname))
	) ELSE NULL END,
	CASE WHEN er.denyby IS NOT NULL THEN
	XMLELEMENT(
		name "entity:denyby",
		XMLATTRIBUTES(dr.id, dr.cname, CASE WHEN dr.deleted THEN 'true' END as deleted), 
		XMLELEMENT(name "person:firstname", TRIM(dr.firstname)), 
		XMLELEMENT(name "person:lastname", TRIM(dr.lastname))
	) ELSE NULL END
) FROM entityrelations AS er
LEFT OUTER JOIN researchers AS ar ON ar.id = er.addedby
LEFT OUTER JOIN researchers AS dr ON dr.id = er.denyby
 WHERE er.id = $1 ORDER BY er.subject_type, er.verb, er.target_type;
$_$;


ALTER FUNCTION public.relation_to_xml(relid integer, guid uuid) OWNER TO appdb;

--
-- Name: relation_types_to_xml(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION relation_types_to_xml() RETURNS SETOF xml
    LANGUAGE sql
    AS $$SELECT XMLELEMENT(NAME "entity:relationtype" , XMLATTRIBUTES(
	rt.id AS id, 
	rv.name as verb,
	rv.dname as directverb,
	rv.rname as reverseverb,
	rt.subject_type as subjecttype,
	rt.target_type AS targettype,
	rt.actionid as actionid
), rt.description) FROM relationTypes AS rt INNER JOIN relationVerbs AS rv ON rv.id = rt.verbid ORDER BY rt.subject_type, rv.name, rt.target_type;
$$;


ALTER FUNCTION public.relation_types_to_xml() OWNER TO appdb;

--
-- Name: remove_va_from_vowide_image_list(integer, integer, integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION remove_va_from_vowide_image_list(_void integer, _appid integer DEFAULT NULL::integer, _userid integer DEFAULT NULL::integer) RETURNS void
    LANGUAGE plpgsql
    AS $_$
BEGIN
	DELETE FROM vowide_image_list_images 
	WHERE vowide_image_list_id = (SELECT id FROM vowide_image_lists WHERE void = $1 AND state = 'draft')
	AND CASE WHEN NOT $2 IS NULL THEN
		vapplistid IN (SELECT vapplistid FROM vaviews WHERE appid = $2)
	ELSE
		TRUE
	END;

	IF NOT $3 IS NULL THEN
		UPDATE vowide_image_lists SET alteredby = $3 WHERE id = (SELECT id FROM vowide_image_lists WHERE void = $1 AND state = 'draft');
	END IF;
END;
$_$;


ALTER FUNCTION public.remove_va_from_vowide_image_list(_void integer, _appid integer, _userid integer) OWNER TO appdb;

--
-- Name: replace_vo_dupe(vos); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION replace_vo_dupe(vos) RETURNS SETOF vos
    LANGUAGE sql
    AS $_$
SELECT (t.x).* FROM (
	SELECT CASE 
	WHEN EXISTS (SELECT * FROM vo_dupes WHERE egiid = $1.id) THEN
		(SELECT (vos.*)::vos FROM vos WHERE id = (SELECT ebiid FROM vo_dupes WHERE egiid = $1.id))
	ELSE
		(SELECT (vos.*)::vos FROM vos WHERE id = $1.id)
	END AS x
) AS t
$_$;


ALTER FUNCTION public.replace_vo_dupe(vos) OWNER TO appdb;

--
-- Name: request_permissions_refresh(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION request_permissions_refresh() RETURNS void
    LANGUAGE plpgsql
    AS $$
BEGIN
	UPDATE config SET data = '1' WHERE var = 'permissions_cache_dirty';
	NOTIFY invalidate_cache, 'permissions';
END;
$$;


ALTER FUNCTION public.request_permissions_refresh() OWNER TO appdb;

--
-- Name: researcher_from_sso(text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION researcher_from_sso(sso text) RETURNS researchers
    LANGUAGE sql
    AS $_$
SELECT researchers.* 
FROM researchers 
INNER JOIN user_accounts ON user_accounts.researcherid = researchers.id
WHERE user_accounts.account_type = 'egi-sso-ldap' AND accountid = $1
$_$;


ALTER FUNCTION public.researcher_from_sso(sso text) OWNER TO appdb;

--
-- Name: researcher_privs_to_xml(integer, integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION researcher_privs_to_xml(_researcherid integer, _userid integer) RETURNS SETOF xml
    LANGUAGE sql
    AS $_$
SELECT 
--	CASE -- only privileges about self or if viewing user has edit permissions upon the person's profile
--	WHEN ($1 = $2) OR EXISTS(
--		SELECT * 
--		FROM permissions 
--		WHERE (object = (SELECT guid FROM researchers WHERE id = $1) OR object IS NULL) AND (actor = (SELECT guid FROM researchers WHERE id = $2)) AND (actionid = 1)
--	) THEN
		privs.priv
--	ELSE 
--		NULL::XML
--	END
FROM researchers
CROSS JOIN (
	SELECT 
		xmlagg(x) AS priv 
	FROM (
		SELECT privs_to_xml((SELECT guid FROM researchers WHERE id = $1)) AS x 
		UNION ALL 
		SELECT privgroups_to_xml((SELECT guid FROM researchers WHERE id = $1))
	) AS privs1
) AS privs
WHERE researchers.id = $1
$_$;


ALTER FUNCTION public.researcher_privs_to_xml(_researcherid integer, _userid integer) OWNER TO appdb;

--
-- Name: researcher_to_xml(integer[], text, integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION researcher_to_xml(mid integer[], mname text DEFAULT ''::text, mappid integer DEFAULT NULL::integer) RETURNS SETOF xml
    LANGUAGE plpgsql
    AS $$
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
			researchers.gender,
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
				xmlelement(name "person:gender", ppl.gender), E'\n\t',
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
$$;


ALTER FUNCTION public.researcher_to_xml(mid integer[], mname text, mappid integer) OWNER TO appdb;

--
-- Name: researcher_to_xml(integer, text, integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION researcher_to_xml(mid integer, mname text DEFAULT ''::text, mappid integer DEFAULT NULL::integer) RETURNS xml
    LANGUAGE sql
    AS $_$SELECT researcher_to_xml(ARRAY[$1], $2, $3);$_$;


ALTER FUNCTION public.researcher_to_xml(mid integer, mname text, mappid integer) OWNER TO appdb;

--
-- Name: researcher_to_xml_ext(integer, text, integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION researcher_to_xml_ext(mid integer, mname text DEFAULT ''::text, muserid integer DEFAULT NULL::integer) RETURNS SETOF xml
    LANGUAGE plpgsql
    AS $_$
BEGIN
RETURN QUERY
WITH relations AS(
	SELECT $1 as id, xmlagg(x) as "xml" FROM subject_relations_to_xml((SELECT guid FROM researchers WHERE id = $1)) as x 
)
SELECT xmlelement(name "person:person", xmlattributes(
researchers.id as id, researchers.guid, researchers.nodissemination as nodissemination, $2 as metatype, cname, hitcount as hitcount, CASE WHEN deleted IS TRUE THEN 'true' END as deleted), E'\n\t',
xmlelement(name "person:firstname", researchers.firstname), E'\n\t',
xmlelement(name "person:lastname", researchers.lastname), E'\n\t',
xmlelement(name "person:gender", researchers.gender), E'\n\t',
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
LEFT OUTER JOIN (SELECT researcherid, xmlagg(xmlelement(name "person:contact", xmlattributes(contacttypes.description as type, contacts.id as id, contacts.isprimary as primary), contacts.data)) AS contact FROM contacts INNER JOIN contacttypes ON contacttypes.id = contacts.contacttypeid GROUP BY researcherid) AS conts ON conts.researcherid = researchers.id
LEFT OUTER JOIN (SELECT DISTINCT ON (researcherid, array_agg(appid)) researcherid, xmlagg(app_to_xml(appid)) AS app FROM (SELECT researcherid, appid FROM researchers_apps INNER JOIN applications ON applications.id = researchers_apps.appid AND (applications.deleted OR applications.moderated) IS FALSE UNION SELECT owner, id FROM applications WHERE (deleted OR moderated) IS FALSE) AS t GROUP BY researcherid) AS apps ON apps.researcherid = researchers.id
LEFT OUTER JOIN (SELECT authorid, xmlagg(appdocument_to_xml(docid)) AS pub FROM (SELECT DISTINCT authorid, docid FROM intauthors INNER JOIN appdocuments ON appdocuments.id = docid INNER JOIN applications ON applications.id = appdocuments.appid AND NOT (applications.deleted OR applications.moderated)) AS T GROUP BY authorid) AS pubs ON pubs.authorid = researchers.id
LEFT OUTER JOIN (SELECT countryid, xmlagg(ngi_to_xml(id)) AS ngi FROM ngis GROUP BY countryid) AS ngis ON ngis.countryid = researchers.countryid
LEFT OUTER JOIN ppl_del_infos ON ppl_del_infos.researcherid = researchers.id
LEFT OUTER JOIN researcherimages ON researcherimages.researcherid = researchers.id
LEFT OUTER JOIN (
	SELECT 
		researcherid, 
		xmlagg(
			xmlelement(
				name "vo:vo",
				xmlattributes(
					void AS id, 
					(SELECT name FROM vos WHERE id = void) AS name, 
					(SELECT domains.name FROM domains WHERE id = (SELECT domainid FROM vos WHERE id = void)) AS discipline,
					'member' AS relation,
					member_since
				)
			)
		) AS vo 
	FROM vo_members 
	GROUP BY researcherid
) AS vos ON vos.researcherid = researchers.id
LEFT OUTER JOIN (
	SELECT 
		researcherid, 
		xmlagg(
			xmlelement(
				name "vo:vo",
				xmlattributes(
					void AS id, 
					(SELECT name FROM vos WHERE id = void) AS name, 
					(SELECT domains.name FROM domains WHERE id = (SELECT domainid FROM vos WHERE id = void)) AS discipline,
					'contact' AS relation,
					"role"
				)
			)
		) AS vocontact 
	FROM vo_contacts
	GROUP BY researcherid
) AS vocontacts ON vocontacts.researcherid = researchers.id
CROSS JOIN (
	SELECT /*xmlelement(name "privilege:actor", xmlattributes(), */xmlagg(x)/*)*/ AS priv FROM (SELECT privs_to_xml((SELECT guid FROM researchers WHERE id = $1)) AS x UNION ALL SELECT privgroups_to_xml((SELECT guid FROM researchers WHERE id = $1))) AS privs1
) AS privs
WHERE researchers.id=mid;
END;
$_$;


ALTER FUNCTION public.researcher_to_xml_ext(mid integer, mname text, muserid integer) OWNER TO appdb;

--
-- Name: revoke_privilege(integer, uuid, uuid, integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION revoke_privilege(_actionid integer, _actor uuid, _target uuid, editorid integer) RETURNS boolean
    LANGUAGE plpgsql
    AS $$
DECLARE g1 INT[];
DECLARE g2 INT[];
DECLARE g3 INT[];
DECLARE sysonly BOOLEAN;
BEGIN
	IF editorid::text = (SELECT id FROM actors WHERE type = 'ppl' AND guid = _actor)::text THEN 
		-- do not alter own privs
		RETURN FALSE;
	END IF;
	IF EXISTS (SELECT * FROM permissions WHERE actionid = 1 AND (actor = (SELECT guid FROM researchers WHERE id = editorid)) AND ((object = _target) OR (object IS NULL))) THEN
		-- editor has grant/revoke access 	
		g2 := (
			SELECT array_agg(groupid)
			FROM actor_group_members
			WHERE actorid = (SELECT guid FROM researchers WHERE id = editorid)
			AND CASE
				WHEN ((SELECT type FROM targets WHERE guid = _target) = 'app') AND (groupid = -3) THEN
					payload IN (SELECT id::text FROM appcountries WHERE appid = (SELECT id FROM applications WHERE guid = _target))
				ELSE
					TRUE
			END
		);
		-- g3: groups of the actor who will receive the privilege
		g3 := (
			SELECT array_agg(groupid)
			FROM actor_group_members
			WHERE actorid = _actor
			AND CASE
				WHEN groupid IN (-3, -6, -9) THEN
					CASE WHEN (SELECT type FROM targets WHERE guid = _target) = 'app' THEN
						payload IN (SELECT id::text FROM appcountries WHERE appid = (SELECT id FROM applications WHERE guid = _target))
					ELSE
						FALSE
					END
				ELSE
					TRUE
			END
		);		
		IF EXISTS (SELECT * FROM privileges WHERE actionid = _actionid AND actor = _actor AND ((object = _target) OR (object IS NULL)) AND (NOT revoked)) THEN
			-- there exists an explicit privilege. See if it can be overridden
			sysonly := FALSE;
			-- g1: groups of the person that added the privilege
			g1 := (
				SELECT array_agg(groupid) 
				FROM privileges 
				INNER JOIN actor_group_members AS agm ON agm.actorid = (SELECT guid FROM researchers WHERE id = privileges.addedby) 
				WHERE 
					(NOT revoked) AND actionid = _actionid AND actor = _actor AND ((object = _target) OR (object IS NULL))
				AND CASE
					WHEN groupid IN (-3, -6, -9) THEN
						CASE WHEN (SELECT type FROM targets WHERE guid = _target) = 'app' THEN
							payload IN (SELECT id::text FROM appcountries WHERE appid = (SELECT id FROM applications WHERE guid = _target))
						ELSE
							FALSE
						END
					ELSE
						TRUE
				END
			);
		ELSIF EXISTS (SELECT * FROM permissions WHERE actionid = _actionid AND actor = _actor AND ((object = _target) OR (object IS NULL))) THEN
			sysonly := TRUE;
			-- there exist only system-generated privileges. See if it they can be overridden
			g1:= g3;
		ELSE 
			-- priv not granted or already revoked. do nothing
			RETURN true;
		END IF;		
		CASE 
			WHEN (-1 = ANY(g1)) AND NOT (-1 = ANY(g2) OR -9 = ANY(g2)) THEN
				RETURN FALSE;
			WHEN -2 = ANY(g1) AND NOT (-1 = ANY(g2) OR -9 = ANY(g2)) THEN
				RETURN FALSE;
			WHEN -3 = ANY(g1) AND NOT (-2 = ANY(g2) OR -1 = ANY(g2) OR -9 = ANY(g2)) THEN
				RETURN FALSE;
			ELSE
				IF 
					_actionid = ANY(app_actions()) AND 
					_actor IN (
						SELECT guid FROM researchers WHERE id IN (
							SELECT addedby FROM applications WHERE guid = _target 
							UNION 
							SELECT owner FROM applications WHERE guid = _target
						)
					) AND NOT (-3 = ANY(g2) OR -2 = ANY(g2) OR -1 = ANY(g2))					
				THEN
					-- only admins, managers, and NILs can alter s/w owner/submitter privileges 
					RETURN FALSE;
				ELSE
					IF (-1 = ANY(g3) OR -2 = ANY(g3)) AND NOT (-1 = ANY(g2)) THEN
						-- only admins may alter privileges of other admins and of managers
						RETURN FALSE;
					ELSIF (-3 = ANY(g3)) AND NOT (-1 = ANY(g2) OR -2 = ANY(g2)) THEN
						-- only admins and managers may alter privileges of NILs
						RETURN FALSE;
					ELSE
						IF sysonly THEN
							INSERT INTO privileges (actionid, object, actor, revoked, addedby) VALUES (_actionid, _target, _actor, TRUE, editorid);
						ELSE
							IF (NOT _target IS NULL) AND EXISTS (SELECT * FROM privileges WHERE actionid = _actionid AND actor = _actor AND object IS NULL) THEN
								INSERT INTO privileges (actionid, object, actor, revoked, addedby) VALUES (_actionid, _target, _actor, TRUE, editorid);
							ELSE
								DELETE FROM privileges WHERE actionid = _actionid AND object = _target AND actor = _actor AND (NOT revoked);
							END IF;
						END IF;
						RETURN TRUE;
					END IF;
				END IF;
		END CASE;
	ELSE
		-- editor does not have grant/revoke access 
		RETURN FALSE;
	END IF;
END;
$$;


ALTER FUNCTION public.revoke_privilege(_actionid integer, _actor uuid, _target uuid, editorid integer) OWNER TO appdb;

--
-- Name: role_to_xml(integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION role_to_xml(mid integer) RETURNS xml
    LANGUAGE sql
    AS $_$SELECT xmlelement(name "person:role", xmlattributes(
positiontypes.id as id, positiontypes.description as "type")) FROM positiontypes WHERE id = $1$_$;


ALTER FUNCTION public.role_to_xml(mid integer) OWNER TO appdb;

--
-- Name: sa_categories(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION sa_categories() RETURNS SETOF integer
    LANGUAGE sql STRICT
    AS $$
WITH RECURSIVE lvl(cid, l, cname, pid, o) AS (
        SELECT id, 0, name, parentid, '' FROM categories WHERE name = 'Software Appliances'
        UNION ALL
        SELECT 
                id, 
                l+1,
                name,
                parentid,
                CASE WHEN o IS NULL THEN CASE COALESCE(ord, 0) WHEN 0 THEN 'Z' ELSE ord::text END || ' ' || name ELSE o || '_' || name END
        FROM lvl, categories WHERE NOT categories.parentid IS DISTINCT FROM cid
)
SELECT cid FROM lvl 
WHERE l>=0
ORDER BY o;
$$;


ALTER FUNCTION public.sa_categories() OWNER TO appdb;

--
-- Name: set_config_var(text, text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION set_config_var(_var text, _val text) RETURNS text
    LANGUAGE plpgsql
    AS $_$
DECLARE oldval TEXT;
BEGIN
	IF EXISTS (SELECT * FROM config WHERE var = $1) THEN
		oldval := (SELECT data FROM config WHERE var = $1);
		UPDATE config SET data = $2 WHERE var = $1;
	ELSE
		oldval := NULL;
		INSERT INTO config (var, data) VALUES ($1, $2);
	END IF;
	RETURN oldval;
END;
$_$;


ALTER FUNCTION public.set_config_var(_var text, _val text) OWNER TO appdb;

--
-- Name: set_vowide_image_state(integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION set_vowide_image_state(_id integer) RETURNS void
    LANGUAGE sql
    AS $_$
	UPDATE vowide_image_list_images SET state = CASE WHEN EXISTS (
		SELECT *
		FROM vapplists
		INNER JOIN vapp_versions ON vapp_versions.id = vapplists.vappversionid
		WHERE vapplists.id = (SELECT vapplistid FROM vowide_image_list_images WHERE id = $1)
		AND vapp_versions.published AND vapp_versions.enabled AND NOT vapp_versions.archived
	) THEN
		'up-to-date'::e_vowide_image_state
	WHEN EXISTS (
		SELECT *
		FROM vapplists
		INNER JOIN vapp_versions ON vapp_versions.id = vapplists.vappversionid
		WHERE vapplists.id = (SELECT vapplistid FROM vowide_image_list_images WHERE id = $1)
		AND vapp_versions.archived
	) AND NOT EXISTS (
		SELECT * FROM vapplists 
		INNER JOIN vmiinstances ON vmiinstances.id = vapplists.vmiinstanceid
		INNER JOIN vapp_versions ON vapp_versions.id = vapplists.vappversionid
		WHERE vmiinstances.guid = (SELECT guid FROM vmiinstances WHERE id = (SELECT vmiinstanceid FROM vapplists WHERE id = (SELECT vapplistid FROM vowide_image_list_images WHERE id = $1)))
		AND vapp_versions.published AND vapp_versions.enabled AND NOT vapp_versions.archived
	)THEN 
		'deleted'::e_vowide_image_state
	WHEN EXISTS (
		SELECT *
		FROM vapplists
		INNER JOIN vapp_versions ON vapp_versions.id = vapplists.vappversionid
		WHERE vapplists.id = (SELECT vapplistid FROM vowide_image_list_images WHERE id = $1)
		AND vapp_versions.archived
	) THEN 
		'obsolete'::e_vowide_image_state
	WHEN EXISTS (
		SELECT *
		FROM vapplists
		INNER JOIN vapp_versions ON vapp_versions.id = vapplists.vappversionid
		WHERE vapplists.id = (SELECT vapplistid FROM vowide_image_list_images WHERE id = $1)
		AND NOT vapp_versions.archived AND NOT vapp_versions.published
	) THEN
		'draft'::e_vowide_image_state
	ELSE
		'unknown'::e_vowide_image_state
	END WHERE id = _id;
$_$;


ALTER FUNCTION public.set_vowide_image_state(_id integer) OWNER TO appdb;

--
-- Name: site_instances(text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION site_instances(servname text) RETURNS SETOF text
    LANGUAGE plpgsql
    AS $_$
BEGIN
	IF $1 = 'occi' THEN
		RETURN QUERY SELECT DISTINCT(sites.id) FROM sites INNER JOIN va_providers ON va_providers.sitename = sites.name AND va_providers.in_production = true 
			INNER JOIN va_provider_images ON va_provider_images.va_provider_id = va_providers.id
			INNER JOIN vaviews ON vaviews.vmiinstanceid = va_provider_images.vmiinstanceid;
	ELSE
		RETURN QUERY SELECT DISTINCT(sites.id) FROM sites WHERE sites.name NOT IN (SELECT va_providers.sitename FROM va_providers 
			INNER JOIN va_provider_images ON va_provider_images.va_provider_id = va_providers.id
			INNER JOIN vaviews ON vaviews.vmiinstanceid = va_provider_images.vmiinstanceid
			WHERE va_providers.in_production = true);
	END IF;
END;
$_$;


ALTER FUNCTION public.site_instances(servname text) OWNER TO appdb;

--
-- Name: site_logistics(text, text, text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION site_logistics(m_fltstr text, m_from text, m_where text) RETURNS xml
    LANGUAGE plpgsql
    AS $$
DECLARE h TEXT[];
DECLARE hh TEXT;
DECLARE fl TEXT[];
DECLARE fr TEXT[];
DECLARE w TEXT[];
DECLARE i INT;
DECLARE len INT;
BEGIN
        IF m_fltstr IS NULL THEN m_fltstr := ''; END IF;
        IF m_from IS NULL THEN m_from := ''; END IF;
        IF m_where IS NULL THEN m_where := ''; END IF;
		m_fltstr := TRIM(m_fltstr);
		m_from := TRIM(m_from);
		m_where := TRIM(m_where);
		IF SUBSTRING(m_fltstr, 1, 1) = '{' THEN
			fl := m_fltstr::text[];
			fr := m_from::text[];
			w := m_where::text[];
		ELSE
			fl := ('{"' || REPLACE(m_fltstr, '"', '\"') || '"}')::text[];
			fr := ('{"' || REPLACE(m_from, '"', '\"') || '"}')::text[];
			w := ('{"' ||  REPLACE(m_where, '"', '\"') || '"}')::text[];
		END IF;
		h := NULL::TEXT[];
		IF m_fltstr = '' THEN
			len := 0;
			hh := MD5(m_from || ' ' || m_where);
			IF NOT EXISTS (SELECT hash FROM cache.filtercache WHERE hash = hh) THEN
				PERFORM filtersites(m_fltstr, m_from, m_where);
			END IF;
			h := ARRAY['cache.filtercache_' || hh];
		ELSE
			len := ARRAY_LENGTH(fl, 1);
		END IF;
		FOR i IN 1..len LOOP
			m_fltstr = TRIM(fl[i]);
			m_from = TRIM(fr[i]);
			m_where = TRIM(w[i]);
			hh := MD5(m_from || ' ' || m_where);
			IF NOT EXISTS (SELECT hash FROM cache.filtercache WHERE hash = hh) THEN
				PERFORM filtersites(m_fltstr, m_from, m_where);
			END IF;
			hh := 'cache.filtercache_' || hh;
			h := array_append(h, hh);
		END LOOP;
        RETURN xmlelement(name "site:logistics",
                xmlconcat(
			(SELECT xmlagg(xmlelement(name "logistics:supports", xmlattributes(t.name as "text", t.count as "count", t.id::text::text::text as "id"))) FROM count_site_matches('supports', h) as t(name TEXT, count bigint, id text)),
			(SELECT xmlagg(xmlelement(name "logistics:hasinstances", xmlattributes(t.name as "text", t.count as "count", t.id::text::text::text as "id"))) FROM count_site_matches('hasinstances', h) as t(name TEXT, count bigint, id text)),
			(SELECT xmlagg(xmlelement(name "logistics:country", xmlattributes(t.name as "text", t.count as "count", t.id::text::text::text as "id"))) FROM count_site_matches('country', h) as t(name TEXT, count bigint, id text)),
                        (SELECT xmlagg(xmlelement(name "logistics:discipline", xmlattributes(t.name as "text", t.count as "count", t.id::text::text::text as "id"))) FROM count_site_matches('discipline', h, isPrivateJoin(fl[ARRAY_LENGTH(fl, 1)], 'discipline')) as t(name TEXT, count bigint, id text)),
			(SELECT xmlagg(xmlelement(name "logistics:category", xmlattributes(t.name as "text", t.count as "count", t.id::text::text::text as "id"))) FROM count_site_matches('category', h) as t(name TEXT, count bigint, id text)),
			(SELECT xmlagg(xmlelement(name "logistics:arch", xmlattributes(t.name as "text", t.count as "count", t.id::text::text::text as "id"))) FROM count_site_matches('arch', h) as t(name TEXT, count bigint, id text)),
			(SELECT xmlagg(xmlelement(name "logistics:osfamily", xmlattributes(t.name as "text", t.count as "count", t.id::text::text::text as "id"))) FROM count_site_matches('osfamily', h) as t(name TEXT, count bigint, id text)),
			(SELECT xmlagg(xmlelement(name "logistics:os", xmlattributes(t.name as "text", t.count as "count", t.id::text::text::text as "id"))) FROM count_site_matches('os', h) as t(name TEXT, count bigint, id text)),
			(SELECT xmlagg(xmlelement(name "logistics:hypervisor", xmlattributes(t.name as "text", t.count as "count", t.id::text::text::text as "id"))) FROM count_site_matches('hypervisor', h) as t(name TEXT, count bigint, id text)),
			(SELECT xmlagg(xmlelement(name "logistics:vo", xmlattributes(t.name as "text", t.count as "count", t.id::text::text::text as "id"))) FROM count_site_matches('vo', h) as t(name TEXT, count bigint, id text)),
			(SELECT xmlagg(xmlelement(name "logistics:middleware", xmlattributes(t.name as "text", t.count as "count", t.id::text::text::text as "id"))) FROM count_site_matches('middleware', h, isPrivateJoin(fl[ARRAY_LENGTH(fl, 1)], 'middleware')) as t(name TEXT, count bigint, id text)),
			(SELECT xmlagg(xmlelement(name "logistics:phonebook", xmlattributes(t.name as "text", t.count as "count", t.id::text::text::text as "id"))) FROM 
(
WITH c AS (SELECT * FROM cached_ids(h) AS id)
SELECT l AS "name", COUNT(DISTINCT sites.id) AS count, n::text AS id FROM 
(
WITH RECURSIVE t(n) AS (
	VALUES (1)
	UNION ALL
	SELECT n+1 FROM t WHERE n < 28
)
SELECT 
CASE 
WHEN n<=26 THEN 
	SUBSTRING('ABCDEFGHIJKLMNOPQRSTUVWXYZ',n,1)
WHEN n=27 THEN 
	'0-9'
ELSE 
	'#'
END AS l,
CASE 
WHEN n<=26 THEN 
	'^' || SUBSTRING('ABCDEFGHIJKLMNOPQRSTUVWXYZ',n,1) || '.+'
WHEN n=27 THEN 
	'^[0-9].+'
ELSE 
	'^[^A-Za-z0-9].+'
END AS p,
n
FROM t) AS q
INNER JOIN sites ON sites.name ~* p
WHERE sites.id IN (SELECT id::text FROM c)
GROUP BY l, n
ORDER BY n
) AS t
)
));
END;
$$;


ALTER FUNCTION public.site_logistics(m_fltstr text, m_from text, m_where text) OWNER TO appdb;

--
-- Name: site_service_imageocciids_to_xml(text, integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION site_service_imageocciids_to_xml(providerid text, vmiinstanceid integer) RETURNS xml
    LANGUAGE sql
    AS $_$SELECT xmlagg(siteimageoccids.x) FROM (
SELECT XMLELEMENT(NAME "siteservice:occi",
	XMLATTRIBUTES(va_provider_images.va_provider_image_id AS id, va_provider_images.id AS providerimageid, vowide_image_list_images.id AS voimageid , vowide_image_lists.state AS voimagestate),
	vo_to_xml(vowide_image_lists.void)
) as x 
FROM va_providers
INNER JOIN va_provider_images ON va_provider_images.va_provider_id = va_providers.id
LEFT OUTER JOIN vowide_image_list_images ON vowide_image_list_images.id = va_provider_images.vowide_vmiinstanceid
LEFT OUTER JOIN vowide_image_lists ON vowide_image_lists.id = vowide_image_list_images.vowide_image_list_id -- AND vowide_image_lists.state::text = 'published'
WHERE va_providers.id = $1 AND va_provider_images.vmiinstanceid = $2
) as siteimageoccids
$_$;


ALTER FUNCTION public.site_service_imageocciids_to_xml(providerid text, vmiinstanceid integer) OWNER TO appdb;

--
-- Name: site_service_images_to_xml(text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION site_service_images_to_xml(providerid text) RETURNS xml
    LANGUAGE sql
    AS $_$WITH hypervisors AS (
	 WITH x AS (
		 SELECT vmiflavours_2.id,
		    unnest(vmiflavours_2.hypervisors) AS y
		   FROM vmiflavours vmiflavours_2
		)
	 SELECT vmiflavours_1.id AS vmiflavourid,
	    xmlagg(XMLELEMENT(NAME "virtualization:hypervisor", XMLATTRIBUTES(( SELECT hypervisors_1.id
		   FROM public.hypervisors hypervisors_1
		  WHERE hypervisors_1.name::text = x.y::text) AS id), x.y)) AS hypervisor
	   FROM vmiflavours vmiflavours_1
	JOIN x ON x.id = vmiflavours_1.id
	GROUP BY vmiflavours_1.id
)SELECT xmlagg(siteimages.x) FROM (
SELECT DISTINCT ON (vaviews.vmiinstanceid,va_provider_images.good_vmiinstanceid) XMLELEMENT(NAME "siteservice:image", 
	XMLATTRIBUTES(
		vaviews.vappversionid as versionid,
		vaviews.va_version_archived as archived,
		vaviews.va_version_enabled as enabled,
		vaviews.va_version_expireson as expireson,
		CASE WHEN vaviews.va_version_expireson <= NOW() THEN TRUE ELSE FALSE END AS isexpired,
		vaviews.imglst_private as private,
		vaviews.vmiinstanceid as id,
		vaviews.vmiinstance_guid AS identifier,
		vaviews.vmiinstance_version as version,
		va_provider_images.good_vmiinstanceid as goodid
	),
	hypervisors.hypervisor::text::xml,
	XMLELEMENT(NAME "virtualization:os", XMLATTRIBUTES(oses.id AS id, vaviews.osversion AS version, oses.os_family_id as family_id), oses.name), 
	XMLELEMENT(NAME "virtualization:arch", XMLATTRIBUTES(archs.id AS id), archs.name),
	XMLELEMENT(NAME "virtualization:format", vaviews.format),
	XMLELEMENT(NAME "virtualization:url", XMLATTRIBUTES(CASE WHEN vaviews.imglst_private = TRUE THEN 'true' ELSE NULL END AS protected), 
		CASE WHEN vaviews.imglst_private = FALSE THEN vaviews.uri END),
	XMLELEMENT(NAME "virtualization:size",XMLATTRIBUTES(CASE WHEN vaviews.imglst_private = TRUE THEN 'true' ELSE NULL END AS protected), 
		CASE WHEN vaviews.imglst_private = FALSE THEN vaviews.size END),
	XMLELEMENT(NAME "siteservice:mpuri", va_provider_images.mp_uri),
	site_service_imageocciids_to_xml($1::TEXT,vaviews.vmiinstanceid::INTEGER),
	XMLELEMENT(NAME "application:application", XMLATTRIBUTES(vaviews.appid AS id, vaviews.appcname AS cname, vaviews.imglst_private as imageListsPrivate, applications.deleted, applications.moderated), 
		XMLELEMENT(NAME "application:name", vaviews.appname )),
	vmiinst_cntxscripts_to_xml(vaviews.vmiinstanceid)) as x
FROM va_provider_images
INNER JOIN vaviews ON vaviews.vmiinstanceid = va_provider_images.vmiinstanceid
INNER JOIN applications ON applications.id = vaviews.appid
LEFT OUTER JOIN hypervisors ON hypervisors.vmiflavourid = vaviews.vmiflavourid
LEFT OUTER JOIN archs ON archs.id = vaviews.archid
LEFT OUTER JOIN oses ON oses.id = vaviews.osid
LEFT OUTER JOIN vmiformats ON vmiformats.name::text = vaviews.format
WHERE va_provider_images.va_provider_id::TEXT = $1 AND vaviews.va_version_published
) AS siteimages
$_$;


ALTER FUNCTION public.site_service_images_to_xml(providerid text) OWNER TO appdb;

--
-- Name: site_service_to_xml(text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION site_service_to_xml(sitename text) RETURNS xml
    LANGUAGE sql STABLE
    AS $_$
SELECT 
	xmlagg(services.x)
FROM (
	SELECT 
		XMLELEMENT(
			name "site:service", 
			XMLATTRIBUTES(
				'occi' AS type, 
				va_providers.id AS id , 
				hostname AS host, 
				COUNT(DISTINCT va_provider_images.good_vmiinstanceid) AS instances, 
				va_providers.beta AS beta, 
				va_providers.in_production AS in_production 
			),
			xmlagg(
				XMLELEMENT(NAME "siteservice:image", XMLATTRIBUTES(
					va_provider_images.vmiinstanceid AS id,
					va_provider_images.good_vmiinstanceid AS goodid
				))
			)
		)AS x
	FROM va_providers 
	LEFT JOIN va_provider_images ON va_provider_images.va_provider_id = va_providers.id
	LEFT JOIN vaviews ON vaviews.vmiinstanceid = va_provider_images.vmiinstanceid
	WHERE va_providers.sitename = $1::TEXT 
	AND	vaviews.appid NOT IN (SELECT appid FROM app_order_hack)
	GROUP BY va_providers.id, hostname, va_providers.id, hostname, beta, in_production
) AS services 
$_$;


ALTER FUNCTION public.site_service_to_xml(sitename text) OWNER TO appdb;

--
-- Name: site_service_to_xml_ext(text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION site_service_to_xml_ext(sitename text) RETURNS xml
    LANGUAGE sql STABLE
    AS $_$
SELECT xmlagg(services.x) FROM (SELECT XMLELEMENT(NAME "site:service", 
    XMLATTRIBUTES( 'occi' as type, va_providers.id as id , hostname as host, va_providers.beta as beta, va_providers.in_production as in_production, va_providers.node_monitored as monitored, va_providers.ngi as ngi),
    XMLELEMENT( NAME "siteservice:host", XMLATTRIBUTES( hostname as name , host_dn as dn, host_ip as ip)),
    XMLELEMENT( NAME "siteservice:url", XMLATTRIBUTES( 'default' as type ) , va_providers.url),
    XMLELEMENT( NAME "siteservice:url", XMLATTRIBUTES( 'gocdb' as type ) , va_providers.gocdb_url),
   CASE WHEN EXISTS (SELECT * FROM va_provider_endpoints WHERE va_provider_endpoints.va_provider_id = va_providers.id) THEN array_to_string(array_agg( 
	DISTINCT xmlelement(name "siteservice:occi_endpoint_url", endpoint_url)::text
    ),'')::xml END,
    array_to_string(array_agg(DISTINCT
	xmlelement(name "provider:template",
		xmlattributes(
			va_provider_templates.group_hash AS group_hash
		),
	xmlelement(name "provider_template:resource_name", resource_name),
	xmlelement(name "provider_template:main_memory_size", memsize),
	xmlelement(name "provider_template:logical_cpus", logical_cpus),
	xmlelement(name "provider_template:physical_cpus", physical_cpus),
	xmlelement(name "provider_template:cpu_multiplicity", cpu_multiplicity),
	xmlelement(name "provider_template:resource_manager", resource_manager),
	xmlelement(name "provider_template:computing_manager", computing_manager),
	xmlelement(name "provider_template:os_family", os_family),
	xmlelement(name "provider_template:connectivity_in", connectivity_in),
	xmlelement(name "provider_template:connectivity_out", connectivity_out),
	xmlelement(name "provider_template:cpu_model", cpu_model),
	xmlelement(name "provider_template:resource_id", resource_id)
	)::text
    ), '')::xml,
    site_service_images_to_xml(va_providers.id::TEXT)
    ) as x 
   FROM va_providers 
   LEFT JOIN va_provider_endpoints ON va_provider_endpoints.va_provider_id = va_providers.id
   LEFT JOIN va_provider_templates ON va_provider_templates.va_provider_id = va_providers.id
   WHERE  va_providers.sitename = $1::TEXT 
   GROUP BY va_providers.id, hostname,va_providers.id, va_providers.hostname, 
   va_providers.beta, va_providers.in_production, va_providers.node_monitored, 
   va_providers.ngi, va_providers.host_dn, va_providers.host_ip,va_providers.url,va_providers.gocdb_url) as services
$_$;


ALTER FUNCTION public.site_service_to_xml_ext(sitename text) OWNER TO appdb;

--
-- Name: site_supports(text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION site_supports(servname text) RETURNS SETOF text
    LANGUAGE plpgsql
    AS $_$
BEGIN
	IF $1 = 'occi' THEN
		RETURN QUERY SELECT DISTINCT(sites.id) FROM sites INNER JOIN va_providers ON va_providers.sitename = sites.name AND va_providers.in_production = true;
	ELSE
		RETURN QUERY SELECT DISTINCT(sites.id) FROM sites WHERE sites.name NOT IN (SELECT va_providers.sitename FROM va_providers WHERE va_providers.in_production = true);
	END IF;
END;
$_$;


ALTER FUNCTION public.site_supports(servname text) OWNER TO appdb;

--
-- Name: site_to_xml(text[]); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION site_to_xml(suid text[]) RETURNS SETOF xml
    LANGUAGE sql STABLE
    AS $$
	SELECT site_to_xml(guid::text) FROM UNNEST(suid) AS guid;
$$;


ALTER FUNCTION public.site_to_xml(suid text[]) OWNER TO appdb;

--
-- Name: site_to_xml(text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION site_to_xml(suid text) RETURNS SETOF xml
    LANGUAGE sql STABLE
    AS $_$
SELECT 
	XMLELEMENT(
		name "appdb:site", 
		XMLATTRIBUTES(
			sites.id as id,
			sites.name as name,
			sites.productioninfrastructure as infrastructure, 
			sites.certificationstatus as status,
			sites.deleted as deleted,
			sites.datasource as source
		),
		XMLELEMENT(
			name "site:officialname", 
			sites.officialname
		), 
		XMLELEMENT(
			name "site:url", 
			XMLATTRIBUTES('portal' as type),
			sites.portalurl
		), 
		XMLELEMENT(
			name "site:url", 
			XMLATTRIBUTES('home' as type), 
			sites.homeurl
		), 
		(SELECT xmlagg(ssx.x) FROM site_services_xml AS ssx WHERE ssx.sitename = sites.name),
		CASE WHEN sites.countryid IS NOT NULL THEN
			country_to_xml(sites.countryid) 
		ELSE 
			XMLELEMENT(
				name "regional:country", 
				XMLATTRIBUTES(sites.countrycode as isocode),
				sites.countryname
			) 
		END 
	) 
FROM sites 
WHERE sites.guid::text = $1::text
$_$;


ALTER FUNCTION public.site_to_xml(suid text) OWNER TO appdb;

--
-- Name: site_to_xml2(text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION site_to_xml2(suid text) RETURNS SETOF xml
    LANGUAGE sql STABLE
    AS $_$
SELECT 
	XMLELEMENT(
		name "appdb:site", 
		XMLATTRIBUTES(
			sites.id as id,
			sites.name as name,
			sites.productioninfrastructure as infrastructure, 
			sites.certificationstatus as status,
			sites.deleted as deleted,
			sites.datasource as source
		),
		XMLELEMENT(
			name "site:officialname", 
			sites.officialname
		), 
		XMLELEMENT(
			name "site:url", 
			XMLATTRIBUTES('portal' as type),
			sites.portalurl
		), 
		XMLELEMENT(
			name "site:url", 
			XMLATTRIBUTES('home' as type), 
			sites.homeurl
		), 
--		(SELECT xmlagg(ssx.x) FROM site_services_xml AS ssx WHERE ssx.sitename = sites.name),
--		xmlagg(ssx.x),
		CASE WHEN sites.countryid IS NOT NULL THEN
			country_to_xml(sites.countryid) 
		ELSE 
			XMLELEMENT(
				name "regional:country", 
				XMLATTRIBUTES(sites.countrycode as isocode),
				sites.countryname
			) 
		END 
	) 
FROM sites 
LEFT OUTER JOIN site_services_xml as ssx ON ssx.sitename = sites.name
WHERE sites.guid::text = $1::text
/*GROUP BY 
	sites.id, 
	sites.name, 
	sites.productioninfrastructure, 
	sites.certificationstatus, 
	sites.deleted,
	sites.datasource,
	sites.officialname,
	sites.portalurl,
	sites.homeurl,
	sites.countryid,
	sites.countrycode,
	sites.countryname*/
$_$;


ALTER FUNCTION public.site_to_xml2(suid text) OWNER TO appdb;

--
-- Name: site_to_xml_ext(text[]); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION site_to_xml_ext(suid text[]) RETURNS SETOF xml
    LANGUAGE plpgsql
    AS $_$
BEGIN
	RETURN QUERY SELECT site_to_xml_ext(guid::text) FROM sites WHERE guid::text = ANY($1);
END;
$_$;


ALTER FUNCTION public.site_to_xml_ext(suid text[]) OWNER TO appdb;

--
-- Name: site_to_xml_ext(text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION site_to_xml_ext(suid text) RETURNS SETOF xml
    LANGUAGE sql
    AS $_$SELECT 
	xmlelement(
		name "appdb:site", 
		xmlattributes(
			sites.id as id,
			sites.name as name,
			sites.productioninfrastructure as infrastructure,
			sites.certificationstatus as status,
			sites.deleted as deleted,
			sites.datasource as source
		),
		XMLELEMENT(name "site:officialname", sites.officialname ),
		XMLELEMENT(name "site:description", sites.description ),
		XMLELEMENT(name "site:url", XMLATTRIBUTES('portal' as type), sites.portalurl),
		XMLELEMENT(name "site:url", XMLATTRIBUTES('home' as type), sites.homeurl),
		XMLELEMENT(name "site:url", XMLATTRIBUTES('giis' as type), sites.giisurl),
		XMLELEMENT(name "site:contact", XMLATTRIBUTES('email' as contacttype, 'contact' as type), sites.contactemail),
		XMLELEMENT(name "site:contact", XMLATTRIBUTES('tel' as contacttype, 'contact' as type), sites.contacttel),
		XMLELEMENT(name "site:contact", XMLATTRIBUTES('email' as contacttype, 'alarm' as type), sites.alarmemail),
		XMLELEMENT(name "site:contact", XMLATTRIBUTES('email' as contacttype, 'csirt' as type), sites.csirtemail),
		XMLELEMENT(name "site:tier", sites.tier),
		XMLELEMENT(name "site:subgrid", sites.subgrid),
		XMLELEMENT(name "site:roc", sites.roc),
		CASE WHEN sites.countryid IS NOT NULL THEN country_to_xml(sites.countryid) ELSE xmlelement(name "regional:country", xmlattributes( sites.countrycode as isocode), sites.countryname) END,
		site_service_to_xml_ext(sites.name)
	) FROM sites WHERE sites.guid::TEXT = $1::TEXT
$_$;


ALTER FUNCTION public.site_to_xml_ext(suid text) OWNER TO appdb;

--
-- Name: soundex(text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION soundex(text) RETURNS text
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/fuzzystrmatch', 'soundex';


ALTER FUNCTION public.soundex(text) OWNER TO appdb;

--
-- Name: soundexx(text[]); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION soundexx(s text[]) RETURNS text[]
    LANGUAGE plpgsql
    AS $$
DECLARE a text[]; 
BEGIN
IF s IS NULL THEN
	RETURN NULL;
END IF;
IF array_upper(s,1) IS NULL THEN
	RETURN NULL;
END IF;
FOR i IN 1 .. array_upper(s,1)
LOOP
	a:= a || soundex(s[i]);
END LOOP;
RETURN a;
END;
$$;


ALTER FUNCTION public.soundexx(s text[]) OWNER TO appdb;

--
-- Name: status_to_xml(integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION status_to_xml(mid integer) RETURNS xml
    LANGUAGE sql
    AS $_$SELECT xmlelement(name "application:status", xmlattributes(
id as id), name) FROM statuses WHERE id = $1$_$;


ALTER FUNCTION public.status_to_xml(mid integer) OWNER TO appdb;

--
-- Name: subdiscipline_to_xml(integer[]); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION subdiscipline_to_xml(mid integer[]) RETURNS xml
    LANGUAGE sql
    AS $_$
SELECT CASE WHEN $1 IS NULL THEN (SELECT subdiscipline_to_xml(NULL::int)) ELSE
(SELECT array_to_string(array_agg(subdiscipline_to_xml(id) ORDER BY name),'')::xml FROM subdomains WHERE id = ANY($1))
END;
$_$;


ALTER FUNCTION public.subdiscipline_to_xml(mid integer[]) OWNER TO appdb;

--
-- Name: subdiscipline_to_xml(integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION subdiscipline_to_xml(mid integer) RETURNS xml
    LANGUAGE sql
    AS $_$SELECT CASE WHEN $1 IS NULL THEN (SELECT xmlelement(name "discipline:subdiscipline", xmlattributes(
'true' as "xsi:nil", 0 as id))) ELSE (SELECT xmlelement(name "discipline:subdiscipline", xmlattributes(
id as id), name) FROM subdomains WHERE id = $1) END $_$;


ALTER FUNCTION public.subdiscipline_to_xml(mid integer) OWNER TO appdb;

--
-- Name: subject_relations_to_xml(uuid); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION subject_relations_to_xml(guid uuid) RETURNS SETOF xml
    LANGUAGE sql STABLE
    AS $_$SELECT relation_to_xml(r.id) FROM relations r
     JOIN relationtypes rt ON rt.id = r.reltypeid
     JOIN relationverbs rv ON rv.id = rt.verbid WHERE r.subject_guid = $1 AND r.denyby IS NULL ORDER BY rt.subject_type, rv.name, rt.target_type;
$_$;


ALTER FUNCTION public.subject_relations_to_xml(guid uuid) OWNER TO appdb;

--
-- Name: subscribe_to_notification(integer, integer, integer, text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION subscribe_to_notification(m_researcherid integer, m_notificationtype integer, m_delivery integer DEFAULT NULL::integer, m_payload text DEFAULT NULL::text) RETURNS integer
    LANGUAGE plpgsql
    AS $$
BEGIN
	RETURN manage_notifications('SUBSCRIBE', m_researcherid, m_notificationtype, m_delivery, m_payload);
END;
$$;


ALTER FUNCTION public.subscribe_to_notification(m_researcherid integer, m_notificationtype integer, m_delivery integer, m_payload text) OWNER TO appdb;

--
-- Name: FUNCTION subscribe_to_notification(m_researcherid integer, m_notificationtype integer, m_delivery integer, m_payload text); Type: COMMENT; Schema: public; Owner: appdb
--

COMMENT ON FUNCTION subscribe_to_notification(m_researcherid integer, m_notificationtype integer, m_delivery integer, m_payload text) IS 'See manager_notifications function for usage';


--
-- Name: swapp_image_providers_to_xml(integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION swapp_image_providers_to_xml(_appid integer) RETURNS SETOF xml
    LANGUAGE sql
    AS $_$
 WITH hypervisors AS (
	 WITH x AS (
		 SELECT vmiflavours_2.id,
		    unnest(vmiflavours_2.hypervisors) AS y
		   FROM vmiflavours vmiflavours_2
		)
	 SELECT vmiflavours_1.id AS vmiflavourid,
	    xmlagg(XMLELEMENT(NAME "virtualization:hypervisor", XMLATTRIBUTES(( SELECT hypervisors_1.id
		   FROM public.hypervisors hypervisors_1
		  WHERE hypervisors_1.name::text = x.y::text) AS id), x.y)) AS hypervisor
	   FROM vmiflavours vmiflavours_1
	JOIN x ON x.id = vmiflavours_1.id
	GROUP BY vmiflavours_1.id
)
SELECT
	xmlelement(
		name "virtualization:image",
		xmlattributes(
			vaviews.vmiinstanceid,
			vaviews.vmiinstance_guid AS identifier,
			vaviews.vmiinstance_version,
			vaviews.va_version_archived AS archived,
			vaviews.va_version_enabled AS enabled,
			CASE WHEN vaviews.va_version_expireson >= NOW() THEN FALSE ELSE TRUE END AS isexpired
		),
		XMLELEMENT(NAME "application:application",
			XMLATTRIBUTES(applications.id AS id, applications.cname AS cname, applications.guid AS guid, applications.deleted, applications.moderated),
			XMLELEMENT(NAME "application:name", applications.name)
		),
		hypervisors.hypervisor::text::xml,
		XMLELEMENT(NAME "virtualization:os", XMLATTRIBUTES(oses.id AS id, vaviews.osversion AS version, oses.os_family_id as family_id), oses.name), 
		XMLELEMENT(NAME "virtualization:arch", XMLATTRIBUTES(archs.id AS id), archs.name),
		vmiinst_cntxscripts_to_xml(vaviews.vmiinstanceid),
		array_to_string(array_agg(DISTINCT 
			xmlelement(name "virtualization:provider",
				xmlattributes(
					va_provider_images.va_provider_id as provider_id,
					va_provider_images.va_provider_image_id as occi_id,
					vowide_image_lists.void,
					va_provider_images.vmiinstanceid as vmiinstanceid
				)
			)::text
		),'')::xml
)
FROM contexts
	INNER JOIN context_script_assocs ON context_script_assocs.contextid = contexts.id
	INNER JOIN contextscripts AS cs ON cs.id = context_script_assocs.scriptid
	INNER JOIN vmiinstance_contextscripts AS vcs ON vcs.contextscriptid = cs.id
	INNER JOIN vaviews ON vaviews.vmiinstanceid = vcs.vmiinstanceid
	INNER JOIN applications ON applications.id = vaviews.appid
	INNER JOIN va_provider_images ON va_provider_images.good_vmiinstanceid = vaviews.vmiinstanceid
	LEFT OUTER JOIN hypervisors ON hypervisors.vmiflavourid = vaviews.vmiflavourid
	LEFT OUTER JOIN archs ON archs.id = vaviews.archid
	LEFT OUTER JOIN oses ON oses.id = vaviews.osid
	LEFT OUTER JOIN vmiformats ON vmiformats.name::text = vaviews.format
	LEFT OUTER JOIN app_vos ON app_vos.appid = applications.id
	LEFT OUTER JOIN vowide_image_list_images ON vowide_image_list_images.id = va_provider_images.vowide_vmiinstanceid
	LEFT OUTER JOIN vowide_image_lists ON vowide_image_lists.id = vowide_image_list_images.vowide_image_list_id AND (vowide_image_lists.state::text = 'published' OR vowide_image_lists.state::text = 'obsolete')
WHERE  
	vaviews.va_version_published AND
	contexts.appid = $1
GROUP BY 
	applications.id,
	vaviews.osversion,
	hypervisors.hypervisor::text,
	vaviews.vmiinstanceid, 
	vaviews.vmiflavourid, 
	vaviews.vmiinstance_guid,
	vaviews.vmiinstance_version,
	vaviews.va_version_archived,
	vaviews.va_version_enabled,
	vaviews.va_version_expireson,
	archs.id, 
	oses.id,
	vmiformats.id,
	app_vos.appid;
$_$;


ALTER FUNCTION public.swapp_image_providers_to_xml(_appid integer) OWNER TO appdb;

--
-- Name: tags_to_xml(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION tags_to_xml() RETURNS SETOF xml
    LANGUAGE plpgsql
    AS $$
BEGIN
RETURN QUERY
SELECT xmlelement(name "application:tag", xmlattributes(
    COUNT(tag) AS "count"
),
tag
)
FROM app_tags 
INNER JOIN applications ON applications.id = app_tags.appid
WHERE applications.moderated IS FALSE
AND applications.deleted IS FALSE
GROUP BY tag ORDER BY COUNT(tag) DESC;
END;
$$;


ALTER FUNCTION public.tags_to_xml() OWNER TO appdb;

--
-- Name: target_privs_to_xml(uuid, integer, integer[]); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION target_privs_to_xml(target_guid uuid, userid integer, actionlist integer[] DEFAULT NULL::integer[]) RETURNS SETOF xml
    LANGUAGE plpgsql
    AS $_$
DECLARE materialize BOOLEAN;
BEGIN

	BEGIN -- start a transaction
/*
		materialize := (SELECT NOT EXISTS (
			SELECT 1 
			FROM   pg_catalog.pg_class c
			JOIN   pg_catalog.pg_namespace n ON n.oid = c.relnamespace
			WHERE  n.nspname = 'public'
			AND    c.relname = 'permissions_static'
			AND    c.relkind = 'r'    -- only tables(?)
		));

		IF materialize THEN
			RAISE NOTICE 'MATERIALIZING PERMISSIONS...';
			CREATE TEMPORARY TABLE permissions_static AS SELECT * FROM permissions;
			ALTER TABLE permissions_static OWNER TO appdb;
			CREATE INDEX idx_permissions_static_actionid ON permissions_static(actionid);
			CREATE INDEX idx_permissions_static_actor ON permissions_static(actor);
			CREATE INDEX idx_permissions_static_object ON permissions_static(object);
			ALTER VIEW permissions RENAME TO permissions_dynamic;
			ALTER TABLE permissions_static RENAME TO permissions;
			RAISE NOTICE 'PERMISSIONS MATERIALIZED';
		ELSE
			RAISE NOTICE 'PERMISSIONS ALREADY MATERIALIZED!';
		END IF;
*/
		RETURN QUERY
		WITH permissions AS (
			SELECT 
				-- DISTINCT ON ("system", actor, actionid)
				array_to_string(array_agg(array_to_string(ids,',')), ',') AS rules,
				MAX("system"::int)::boolean AS "system", 
				MAX((object IS NULL)::int)::boolean AS "global",
				actor, 
				actionid, 
				'granted' AS state,
				NULL::int AS revoked_by
			FROM permissions
			WHERE 
				(object = target_guid OR object IS NULL)
				AND CASE 
					WHEN NOT $3 IS NULL THEN
						actionid = ANY(actionlist)  
					ELSE
						TRUE
				END                 
			GROUP BY actor, actionid
			UNION
			SELECT 
				NULL,
				NULL,
				NULL,
				actors.guid,
				actions.id,
				'notgranted',
				NULL
			FROM actions
			CROSS JOIN actors
			WHERE NOT EXISTS (
				SELECT * FROM permissions WHERE actor = actors.guid AND (object = target_guid OR object IS NULL) AND actionid = actions.id
			) AND actors.guid IN (SELECT actor FROM __permissions WHERE actionid = ANY(actionlist) AND (object = target_guid OR object IS NULL))
			AND actions.id = ANY(actionlist)
			AND NOT EXISTS (
				SELECT * FROM privileges WHERE actor = actors.guid AND (object = target_guid OR object IS NULL) AND actionid = actions.id AND revoked
			)
			UNION
			SELECT
				NULL,
				FALSE,
				MAX((object IS NULL)::int)::boolean,
				actor,
				actionid,
				'revoked' AS state,
				(array_agg(addedby ORDER BY object NULLS FIRST))[1] AS addedby
			FROM privileges
			WHERE 
				revoked 
				AND (object = target_guid OR object IS NULL)
				AND actionid = ANY(actionlist)
			GROUP BY actor, actionid
		)
		SELECT
			xmlelement(
				name "privilege:actor",
				xmlattributes(
					actors.guid AS "suid",
					actors.type AS "type",
					actors.name AS "name",
					actors.cname as "cname",
					actors.id AS "id"
				),
				xmlagg(
					xmlelement(
						name "privilege:action",
						xmlattributes(
							rules,
							"system",
							"global",
							CASE 
								WHEN NOT $2 IS NULL AND state = 'granted' THEN 
									can_revoke_priv(actions.id, actors.guid, $1, $2)
								WHEN NOT $2 IS NULL AND (state = 'notgranted' OR state = 'revoked') THEN 
									can_grant_priv(actions.id, actors.guid, $1, $2) 
							END AS "canModify",
							actions.id AS "id",
							state,
							revoked_by AS "revokedBy"
							
						), actions.description
					) ORDER BY actions.id
				)
			)
		FROM permissions
		INNER JOIN actions ON actions.id = actionid
		INNER JOIN actors ON actors.guid = actor
		WHERE 
			NOT COALESCE(actors.hidden, FALSE)
		AND
			CASE WHEN (SELECT COUNT(*) FROM permissions AS pp WHERE pp.actor = permissions.actor AND pp.actionid = permissions.actionid GROUP BY actionid, actor) > 1 THEN
				NOT EXISTS (SELECT * FROM permissions AS pp WHERE pp.actionid = permissions.actionid AND pp.actor = permissions.actor AND pp.global IS TRUE)
			ELSE
				TRUE
			END 
		GROUP BY 
			actors.guid,
			actors.type,
			actors.name,
			actors.cname,
			actors.id
		ORDER BY actors.id
		;
/*
		IF materialize THEN
			RAISE NOTICE 'DE-MATERIALIZING PERMISSIONS';
			DROP INDEX idx_permissions_static_actionid;
			DROP INDEX idx_permissions_static_actor;
			DROP INDEX idx_permissions_static_object;
			DROP TABLE permissions;
			ALTER VIEW permissions_dynamic RENAME TO permissions;
		END IF;
*/
	END; -- end transaction
END;
$_$;


ALTER FUNCTION public.target_privs_to_xml(target_guid uuid, userid integer, actionlist integer[]) OWNER TO appdb;

--
-- Name: target_relations_to_xml(uuid); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION target_relations_to_xml(guid uuid) RETURNS SETOF xml
    LANGUAGE sql STABLE
    AS $_$SELECT relation_to_xml(r.id,$1) FROM relations r
     JOIN relationtypes rt ON rt.id = r.reltypeid
     JOIN relationverbs rv ON rv.id = rt.verbid WHERE r.target_guid = $1 AND r.denyby IS NULL ORDER BY rt.subject_type, rv.name, rt.target_type;
$_$;


ALTER FUNCTION public.target_relations_to_xml(guid uuid) OWNER TO appdb;

--
-- Name: text_soundex(text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION text_soundex(text) RETURNS text
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/fuzzystrmatch', 'soundex';


ALTER FUNCTION public.text_soundex(text) OWNER TO appdb;

--
-- Name: trfn_10_app_licenses(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_10_app_licenses() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE mFields TEXT[];
BEGIN
	IF TG_OP = 'INSERT' THEN
		IF TG_WHEN = 'AFTER' THEN
			mFields := array_append(mFields,'licenses');
			INSERT INTO news ("timestamp", subjectguid, "action", fields) 
			VALUES (NOW(), (SELECT guid FROM applications WHERE id = NEW.appid LIMIT 1), 'update', mFields);
			UPDATE cache.appxmlcache SET "xml" = __app_to_xml(NEW.appid) WHERE id = NEW.appid;
			RETURN NEW;
		END IF;
		RETURN NEW;
	ELSIF TG_OP = 'UPDATE' THEN
		IF TG_LEVEL = 'ROW' THEN
			IF TG_WHEN = 'AFTER' THEN
				IF NEW.licenseid <> OLD.licenseid THEN 
					mFields := array_append(mFields,'licenses');
					INSERT INTO news ("timestamp", subjectguid, "action", fields) 
					VALUES (NOW(), (SELECT guid FROM applications WHERE id = NEW.appid LIMIT 1), 'update', mFields);
					UPDATE cache.appxmlcache SET "xml" = __app_to_xml(NEW.appid) WHERE id = NEW.appid;
				END IF;
			END IF;
		END IF;
		RETURN NEW;
	ELSIF TG_OP = 'DELETE' THEN
		IF TG_LEVEL = 'ROW' THEN
			IF TG_WHEN = 'AFTER' THEN
				mFields := array_append(mFields,'licenses');
				INSERT INTO news ("timestamp", subjectguid, "action", fields) VALUES (NOW(), (SELECT guid FROM applications WHERE id = OLD.appid LIMIT 1), 'update', mFields);
				UPDATE cache.appxmlcache SET "xml" = __app_to_xml(OLD.appid) WHERE id = OLD.appid;
			END IF;
		END IF;
		RETURN OLD;
	END IF;
END;$$;


ALTER FUNCTION public.trfn_10_app_licenses() OWNER TO appdb;

--
-- Name: trfn_app_api_log(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_app_api_log() RETURNS trigger
    LANGUAGE plpgsql
    AS $$BEGIN
IF TG_OP = 'INSERT' THEN
	IF TG_WHEN = 'BEFORE' THEN
		IF EXISTS (SELECT * FROM app_api_log
			WHERE appid = NEW.appid
			AND COALESCE(researcherid,0) = COALESCE(NEW.researcherid,0)
			AND COALESCE(source,-1) = COALESCE(NEW.source,-1)
			AND COALESCE(ip,'') = COALESCE(NEW.ip,'')
			AND NEW.timestamp - "timestamp" < INTERVAL '1 minute')
		THEN
			RETURN NULL;
		ELSE 
			RETURN NEW;
		END IF;
	ELSIF TG_WHEN = 'AFTER' THEN
		UPDATE applications SET hitcount = CASE 
			WHEN (SELECT count FROM hitcounts WHERE appid = NEW.appid) IS NULL THEN 0 
			ELSE (SELECT count FROM hitcounts WHERE appid = NEW.appid) 
		END WHERE applications.id = NEW.appid;
		RETURN NEW;
	END IF;
ELSIF TG_OP = 'UPDATE' THEN
	RETURN NEW;
ELSIF TG_OP = 'DELETE' THEN
	RETURN OLD;
END IF;
END;
$$;


ALTER FUNCTION public.trfn_app_api_log() OWNER TO appdb;

--
-- Name: trfn_app_archs_cache_delta(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_app_archs_cache_delta() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
	IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN
		PERFORM pg_notify('cache_delta', NEW || '|app_archs');
	ELSIF TG_OP = 'DELETE' THEN
		PERFORM pg_notify('cache_delta', zerorec('app_archs', ARRAY['appid'], ARRAY[OLD.appid]) || '|app_archs');
	END IF;
	RETURN NULL;
END;
$$;


ALTER FUNCTION public.trfn_app_archs_cache_delta() OWNER TO appdb;

--
-- Name: trfn_app_cnames(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_app_cnames() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE REL RECORD;
BEGIN
	IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN
		REL = NEW;
	ELSIF TG_OP = 'DELETE' THEN
		REL = OLD;
	END IF;	
	UPDATE applications SET cname = (SELECT value FROM app_cnames WHERE isprimary IS TRUE AND appid = REL.appid LIMIT 1) WHERE id = REL.appid;
	RETURN REL;
END;
$$;


ALTER FUNCTION public.trfn_app_cnames() OWNER TO appdb;

--
-- Name: trfn_app_licenses_cache_delta(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_app_licenses_cache_delta() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
	IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN
		PERFORM pg_notify('cache_delta', NEW || '|app_licenses');
	ELSIF TG_OP = 'DELETE' THEN
		PERFORM pg_notify('cache_delta', zerorec('app_licenses', ARRAY['appid'], ARRAY[OLD.appid]) || '|app_licenses');
	END IF;
	RETURN NULL;
END;
$$;


ALTER FUNCTION public.trfn_app_licenses_cache_delta() OWNER TO appdb;

--
-- Name: trfn_app_middlewares_cache_delta(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_app_middlewares_cache_delta() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
	IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN
		PERFORM pg_notify('cache_delta', NEW || '|app_middlewares');
	ELSIF TG_OP = 'DELETE' THEN
		PERFORM pg_notify('cache_delta', zerorec('app_middlewares', ARRAY['appid'], ARRAY[OLD.appid]) || '|app_middlewares');
	END IF;
	RETURN NULL;
END;
$$;


ALTER FUNCTION public.trfn_app_middlewares_cache_delta() OWNER TO appdb;

--
-- Name: trfn_app_mws(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_app_mws() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE mFields TEXT[];
BEGIN
	IF TG_OP = 'INSERT' THEN
		mFields := array_append(mFields,'middlewares');
		INSERT INTO news ("timestamp", subjectguid, "action", fields) VALUES (NOW(), (SELECT guid FROM applications WHERE id = NEW.appid LIMIT 1), 'update', mFields);
		RETURN NEW;
	ELSIF TG_OP = 'UPDATE' THEN
		IF TG_LEVEL = 'ROW' THEN
			IF TG_WHEN = 'BEFORE' THEN
				IF NEW.middlewareid <> OLD.middlewareid THEN 
					mFields := array_append(mFields,'middlewares');
					INSERT INTO news ("timestamp", subjectguid, "action", fields) VALUES (NOW(), (SELECT guid FROM applications WHERE id = NEW.appid LIMIT 1), 'update', mFields);
				END IF;
			END IF;
		END IF;
		RETURN NEW;
	ELSIF TG_OP = 'DELETE' THEN
		IF TG_LEVEL = 'ROW' THEN
			IF TG_WHEN = 'BEFORE' THEN
				mFields := array_append(mFields,'middlewares');
				INSERT INTO news ("timestamp", subjectguid, "action", fields) VALUES (NOW(), (SELECT guid FROM applications WHERE id = OLD.appid LIMIT 1), 'update', mFields);
			END IF;
		END IF;
		RETURN OLD;
	END IF;
END;$$;


ALTER FUNCTION public.trfn_app_mws() OWNER TO appdb;

--
-- Name: trfn_app_oses_cache_delta(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_app_oses_cache_delta() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
	IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN
		PERFORM pg_notify('cache_delta', NEW || '|app_oses');
	ELSIF TG_OP = 'DELETE' THEN
		PERFORM pg_notify('cache_delta', zerorec('app_oses',ARRAY['appid'], ARRAY[OLD.appid]) || '|app_oses');
	END IF;
	RETURN NULL;
END;
$$;


ALTER FUNCTION public.trfn_app_oses_cache_delta() OWNER TO appdb;

--
-- Name: trfn_app_releases(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_app_releases() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
/*
states: 1 = unverified, 2 = production, 3 = candidate
*/
DECLARE news_meta TEXT[];
BEGIN
	IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN
		IF TG_WHEN = 'AFTER' THEN
			IF NEW.state IN (2,3) THEN
				news_meta = NULL::TEXT[];
				IF TG_OP = 'UPDATE' THEN
					IF NEW.release <> OLD.release THEN news_meta := array_append(news_meta, 'release:' || NEW.releaseid::TEXT); END IF;
					IF NEW.series <> OLD.series THEN news_meta := array_append(news_meta, 'series:' || NEW.releaseid::TEXT); END IF;
					IF NEW.state <> OLD.state THEN news_meta := array_append(news_meta, 'state:' || NEW.releaseid::TEXT); END IF;
					IF NEW.publishedon <> OLD.publishedon THEN news_meta := array_append(news_meta, 'publishedon:' || NEW.releaseid::TEXT); END IF;
				END IF;
				IF (TG_OP = 'INSERT') OR ((TG_OP = 'UPDATE') AND (NOT news_meta IS NULL)) THEN
					IF TG_OP = 'INSERT' THEN news_meta := array_append(news_meta, 'releaseid:' || (NEW.releaseid)::TEXT); END IF;
					INSERT INTO news("timestamp", "action", subjectguid, fields) VALUES (NOW(), LOWER(TG_OP) || 'rel', (SELECT guid FROM applications WHERE id = NEW.appid), news_meta);
				END IF;
			END IF;
			RETURN NEW;
		ELSIF TG_WHEN = 'BEFORE' THEN
			IF TG_OP = 'INSERT' AND EXISTS (SELECT * FROM app_releases WHERE (releaseid = NEW.releaseid)) THEN
				UPDATE 
					app_releases 
				SET 
					state = NEW.state,
					lastupdated = NOW(),
					manager = NEW.manager,
					"release" = NEW.release
				WHERE
					releaseid = NEW.releaseid;
				RETURN NULL;
			ELSE
				RETURN NEW;
			END IF;
		END IF;
	END IF;
END;
$$;


ALTER FUNCTION public.trfn_app_releases() OWNER TO appdb;

--
-- Name: trfn_app_urls(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_app_urls() RETURNS trigger
    LANGUAGE plpgsql
    AS $$BEGIN
	IF TG_OP = 'INSERT' THEN
		IF TG_LEVEL = 'ROW' THEN
			IF TG_WHEN = 'AFTER' THEN
				UPDATE applications SET links = (SELECT array_agg('{"url": "' || COALESCE(url,'') || '", "description": "' || COALESCE(description,'') || '", "title": "' || COALESCE(title,'') || '", "ord": ' || COALESCE(ord,0) || '"}') FROM app_urls WHERE appid = NEW.appid) WHERE id = NEW.appid;
				RETURN NEW;
			ELSE				
				RETURN NEW;
			END IF;
		END IF;
	ELSIF TG_OP = 'UPDATE' THEN
		IF TG_LEVEL = 'ROW' THEN
			IF TG_WHEN = 'AFTER' THEN
				UPDATE applications SET links = (SELECT array_agg('{"url": "' || COALESCE(url,'') || '", "description": "' || COALESCE(description,'') || '", "title": "' || COALESCE(title,'') || '", "ord": ' || COALESCE(ord,0) || '"}') FROM app_urls WHERE appid = NEW.appid) WHERE id = NEW.appid;
				RETURN NEW;
			ELSE
				RETURN NEW;
			END IF;
		END IF;
	ELSIF TG_OP = 'DELETE' THEN
		IF TG_LEVEL = 'ROW' THEN
			IF TG_WHEN = 'BEFORE' THEN				
				RETURN OLD;
			ELSE
				UPDATE applications SET links = (SELECT array_agg('{"url": "' || COALESCE(url,'') || '", "description": "' || COALESCE(description,'') || '", "title": "' || COALESCE(title,'') || '", "ord": ' || COALESCE(ord,0) || '"}') FROM app_urls WHERE appid = OLD.appid) WHERE id = OLD.appid;
				RETURN OLD;
			END IF;
		END IF;
	END IF;
	RETURN NULL;
END;$$;


ALTER FUNCTION public.trfn_app_urls() OWNER TO appdb;

--
-- Name: trfn_app_vos(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_app_vos() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE mFields TEXT[];
BEGIN
	IF TG_OP = 'INSERT' THEN
		mFields := array_append(mFields,'VOs');
		INSERT INTO news ("timestamp", subjectguid, "action", fields) VALUES (NOW(), (SELECT guid FROM applications WHERE id = NEW.appid LIMIT 1), 'update', mFields);
		RETURN NEW;
	ELSIF TG_OP = 'UPDATE' THEN
		IF TG_LEVEL = 'ROW' THEN
			IF TG_WHEN = 'BEFORE' THEN
				IF NEW.void <> OLD.void THEN 
					mFields := array_append(mFields,'VOs');
					INSERT INTO news ("timestamp", subjectguid, "action", fields) VALUES (NOW(), (SELECT guid FROM applications WHERE id = NEW.appid LIMIT 1), 'update', mFields);
				END IF;
			END IF;
		END IF;
		RETURN NEW;
	ELSIF TG_OP = 'DELETE' THEN
		IF TG_LEVEL = 'ROW' THEN
			IF TG_WHEN = 'BEFORE' THEN
				mFields := array_append(mFields,'VOs');
				INSERT INTO news ("timestamp", subjectguid, "action", fields) VALUES (NOW(), (SELECT guid FROM applications WHERE id = OLD.appid LIMIT 1), 'update', mFields);
			END IF;
		END IF;
		RETURN OLD;
	END IF;
END;$$;


ALTER FUNCTION public.trfn_app_vos() OWNER TO appdb;

--
-- Name: trfn_app_vos_cache_delta(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_app_vos_cache_delta() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
	IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN
		PERFORM pg_notify('cache_delta', NEW || '|app_vos');
	ELSIF TG_OP = 'DELETE' THEN
		DELETE FROM cache.filtercache WHERE m_from LIKE '%app_vos%';
--		PERFORM pg_notify('cache_delta', zerorec('app_vos',ARRAY['appid'], ARRAY[OLD.appid]) || '|app_vos');
--		PERFORM pg_notify('cache_delta', zerorec('app_vos',ARRAY['void'], ARRAY[OLD.void]) || '|app_vos');
	END IF;
	RETURN NULL;
END;
$$;


ALTER FUNCTION public.trfn_app_vos_cache_delta() OWNER TO appdb;

--
-- Name: trfn_appbookmarks_cache_delta(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_appbookmarks_cache_delta() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
	IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN
		PERFORM pg_notify('cache_delta', NEW || '|appbookmarks');
	ELSIF TG_OP = 'DELETE' THEN
		PERFORM pg_notify('cache_delta', zerorec('appbookmarks', ARRAY['appid'], ARRAY[OLD.appid]) || '|appbookmarks');
	END IF;
	RETURN NULL;
END;
$$;


ALTER FUNCTION public.trfn_appbookmarks_cache_delta() OWNER TO appdb;

--
-- Name: trfn_appcategories(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_appcategories() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
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

	UPDATE applications SET categoryid = (SELECT array_agg(categoryid ORDER BY isprimary DESC) FROM appcategories WHERE appid = REL.appid) WHERE id = REL.appid;
	RETURN REL;
END;
$$;


ALTER FUNCTION public.trfn_appcategories() OWNER TO appdb;

--
-- Name: trfn_appcategories_primary_entry(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_appcategories_primary_entry() RETURNS trigger
    LANGUAGE plpgsql
    AS $$DECLARE REL RECORD;
BEGIN
IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN
    REL = NEW;
ELSIF TG_OP = 'DELETE' THEN
    REL = OLD;
END IF;

IF (SELECT COUNT(*) FROM appcategories WHERE appcategories.appid = REL.appid AND isprimary IS TRUE) = 0 THEN
    UPDATE appcategories SET isprimary = TRUE WHERE id = (SELECT id FROM appcategories WHERE appcategories.appid = REL.appid LIMIT 1);
ELSIF (SELECT COUNT(*) FROM appcategories WHERE appcategories.appid = REL.appid AND isprimary IS TRUE) > 1 THEN
    UPDATE appcategories SET isprimary = FALSE WHERE appid = REL.appid AND id <> REL.id;
END IF;
RETURN REL;
END;
$$;


ALTER FUNCTION public.trfn_appcategories_primary_entry() OWNER TO appdb;

--
-- Name: trfn_appdisciplines(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_appdisciplines() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
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

	UPDATE applications SET disciplineid = (SELECT array_agg(disciplineid) FROM appdisciplines WHERE appid = REL.appid) WHERE id = REL.appid;

	RETURN REL;
END;
$$;


ALTER FUNCTION public.trfn_appdisciplines() OWNER TO appdb;

--
-- Name: trfn_appdocuments(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_appdocuments() RETURNS trigger
    LANGUAGE plpgsql
    AS $$BEGIN
    IF TG_OP = 'INSERT' THEN
        IF TG_LEVEL = 'ROW' THEN
            IF TG_WHEN = 'AFTER' THEN
                INSERT INTO news (timestamp, subjectguid, action) VALUES (NOW(), NEW.guid, 'insert');
            END IF;
        END IF;
        RETURN NEW;
    ELSIF TG_OP = 'UPDATE' THEN
        IF TG_LEVEL = 'ROW' THEN
            IF TG_WHEN = 'AFTER' THEN
                INSERT INTO news (timestamp, subjectguid, action) VALUES (NOW(), NEW.guid, 'update');
            ELSIF TG_WHEN = 'BEFORE' THEN
                IF NEW.url <> OLD.url THEN 
                    DELETE FROM linksdb WHERE name = 'DOC' || NEW.id::text;
                END IF;
            END IF;
        END IF;
        RETURN NEW;
    ELSIF TG_OP = 'DELETE' THEN
        IF TG_LEVEL = 'ROW' THEN
            IF TG_WHEN = 'BEFORE' THEN
                INSERT INTO news (timestamp, subjectguid, action) VALUES (NOW(), OLD.guid, 'delete');
                DELETE FROM intauthors WHERE docid = OLD.id;
                DELETE FROM extauthors WHERE docid = OLD.id;
                DELETE FROM linksdb WHERE name = 'DOC' || OLD.id::text;
            END IF;
        END IF;
        RETURN OLD;
    END IF;
END;$$;


ALTER FUNCTION public.trfn_appdocuments() OWNER TO appdb;

--
-- Name: trfn_applications(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_applications() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
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
				IF (OLD.links != NEW.links) THEN mFields := array_append(mFields,'urls'); END IF;
                IF (COALESCE(OLD.disciplineid,ARRAY[-1]) != COALESCE(NEW.disciplineid,ARRAY[-1])) THEN mFields := array_append(mFields,'discipline'); END IF;
				IF (COALESCE(OLD.categoryid,ARRAY[-1]) != COALESCE(NEW.categoryid,ARRAY[-1])) THEN mFields := array_append(mFields,'category'); END IF;
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
					OLD.links <> NEW.links OR
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
END;$$;


ALTER FUNCTION public.trfn_applications() OWNER TO appdb;

--
-- Name: trfn_applications_cache_delta(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_applications_cache_delta() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE rec RECORD;
BEGIN
        IF TG_OP = 'INSERT' OR (TG_OP = 'UPDATE' AND
                                (
                                        OLD.moderated <> NEW.moderated OR
                                        OLD.deleted <> NEW.deleted OR
                                        OLD.lastupdated <> NEW.lastupdated OR
                                        OLD.addedby <> NEW.addedby OR
                                        OLD.owner <> NEW.owner OR
                                        array_sort(OLD.links) <> array_sort(NEW.links) OR
                                        OLD.rating <> NEW.rating OR
                                        OLD.name <> NEW.name OR
                                        OLD.description <> NEW.description OR
                                        OLD.abstract <> NEW.abstract OR
                                        OLD.statusid <> NEW.statusid OR
                                        array_sort(OLD.disciplineid) <> array_sort(NEW.disciplineid) OR
                                        array_sort(OLD.categoryid) <> array_sort(NEW.categoryid)
                                )
        ) THEN
                rec := NEW;
                PERFORM pg_notify('cache_delta', rec || '|applications');
        ELSIF TG_OP = 'DELETE' THEN
                PERFORM pg_notify('cache_delta', zerorec('applications') || '|applications');
        END IF;
        RETURN NULL;
END;
$$;


ALTER FUNCTION public.trfn_applications_cache_delta() OWNER TO appdb;

--
-- Name: trfn_appmanualcountries_cache_delta(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_appmanualcountries_cache_delta() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
	IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN
		RAISE NOTICE 'this is new';
		PERFORM pg_notify('cache_delta', (SELECT DISTINCT (appcountries.*)::record FROM appcountries WHERE appid = NEW.appid AND id = NEW.countryid FETCH FIRST 1 ROWS ONLY) || '|appcountries');
	ELSIF TG_OP = 'DELETE' THEN
		PERFORM pg_notify('cache_delta', zerorec('appcountries', ARRAY['appid'], ARRAY[OLD.appid]) || '|appcountries');
	END IF;
	RETURN NULL;
END;
$$;


ALTER FUNCTION public.trfn_appmanualcountries_cache_delta() OWNER TO appdb;

--
-- Name: trfn_appproglangs_cache_delta(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_appproglangs_cache_delta() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
	IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN
		PERFORM pg_notify('cache_delta', NEW || '|appproglangs');
	ELSIF TG_OP = 'DELETE' THEN
		PERFORM pg_notify('cache_delta', zerorec('appproglangs', ARRAY['appid'], ARRAY[OLD.appid]) || '|appproglangs');
	END IF;
	RETURN NULL;
END;
$$;


ALTER FUNCTION public.trfn_appproglangs_cache_delta() OWNER TO appdb;

--
-- Name: trfn_appratings(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_appratings() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE mFields TEXT[];
BEGIN
	IF TG_OP = 'INSERT' THEN
		IF TG_LEVEL = 'ROW' THEN
			IF TG_WHEN = 'AFTER' THEN
				UPDATE applications SET 
					rating = (SELECT CAST(AVG(rating) AS NUMERIC(3,2)) FROM appratings WHERE appid = NEW.appid),
					ratingcount = (SELECT COUNT(*) FROM appratings WHERE appid = NEW.appid AND NOT rating IS NULL)
				WHERE id = NEW.appid;
				IF NOT NEW.comment IS NULL THEN
					IF trim(NEW.comment) <> '' THEN
						IF NOT NEW.submitterid IS NULL THEN
							mFields = array_append(mFields, NEW.submitterid||':'||(SELECT name FROM researchers WHERE id = NEW.submitterid));
						ELSE
							mFields = array_append(mFields, ':'||NEW.submittername);
						END IF;
						INSERT INTO news ("timestamp", subjectguid, "action", fields) 
						VALUES (NOW(), (SELECT guid FROM applications WHERE id = NEW.appid LIMIT 1), 'insertcmm', mFields);
					END IF;
				END IF;				
			END IF;
		END IF;
		RETURN NEW;
	ELSIF TG_OP = 'UPDATE' THEN
		IF TG_LEVEL = 'ROW' THEN
			IF TG_WHEN = 'AFTER' THEN
				UPDATE applications SET 
					rating = (SELECT CAST(AVG(rating) AS NUMERIC(3,2)) FROM appratings WHERE appid = NEW.appid),
					ratingcount = (SELECT COUNT(*) FROM appratings WHERE appid = NEW.appid AND NOT rating IS NULL)
				WHERE id = NEW.appid;				
				IF NOT NEW.comment IS NULL THEN					
					IF trim(NEW.comment) <> '' THEN
						IF NOT NEW.submitterid IS NULL THEN
							mFields = array_append(mFields, NEW.submitterid||':'||(SELECT name FROM researchers WHERE id = NEW.submitterid));
						ELSE
							mFields = array_append(mFields, ':'||NEW.submittername);
						END IF;
						INSERT INTO news ("timestamp", subjectguid, "action", fields) 
						VALUES (NOW(), (SELECT guid FROM applications WHERE id = NEW.appid LIMIT 1), 'insertcmm', mFields);	
					END IF;
				END IF;				
			END IF;
		END IF;
		RETURN NEW;
	ELSIF TG_OP = 'DELETE' THEN
		IF TG_LEVEL = 'ROW' THEN
			IF TG_WHEN = 'AFTER' THEN				
				UPDATE applications SET 
					rating = (SELECT CAST(AVG(rating) AS NUMERIC(3,2)) FROM appratings WHERE appid = OLD.appid),
					ratingcount = (SELECT COUNT(*) FROM appratings WHERE appid = OLD.appid AND NOT rating IS NULL)
				WHERE id = OLD.appid;
			END IF;
		END IF;
		RETURN OLD;
	END IF;
END;$$;


ALTER FUNCTION public.trfn_appratings() OWNER TO appdb;

--
-- Name: trfn_archs_cache_delta(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_archs_cache_delta() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
	IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN
		PERFORM pg_notify('cache_delta', NEW || '|archs');
	ELSIF TG_OP = 'DELETE' THEN
		PERFORM pg_notify('cache_delta', zerorec('archs') || '|archs');
	END IF;
	RETURN NULL;
END;
$$;


ALTER FUNCTION public.trfn_archs_cache_delta() OWNER TO appdb;

--
-- Name: trfn_auto_create_app(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_auto_create_app() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
	IF NEW.appid IS NULL THEN
		INSERT INTO applications (name, statusid) VALUES (NEW.name, 6) RETURNING id INTO NEW.appid;
	END IF;
	RETURN NEW;
END;
$$;


ALTER FUNCTION public.trfn_auto_create_app() OWNER TO appdb;

--
-- Name: trfn_categories_cache_delta(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_categories_cache_delta() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
	IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN
		PERFORM pg_notify('cache_delta', NEW || '|categories');
	ELSIF TG_OP = 'DELETE' THEN
		PERFORM pg_notify('cache_delta', (0,'',0,0) || '|categories');
	END IF;
	RETURN NULL;
END;
$$;


ALTER FUNCTION public.trfn_categories_cache_delta() OWNER TO appdb;

--
-- Name: trfn_config_before(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_config_before() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
	IF TG_WHEN = 'BEFORE' THEN
		IF TG_OP = 'UPDATE' OR TG_OP = 'INSERT' THEN
			IF NEW.var = 'cache_build_count' THEN
				BEGIN
					IF NEW.data::INT < 0 THEN
						NEW.data = '0';
					END IF;
				EXCEPTION
					WHEN OTHERS THEN NEW.data = '0';
				END;
			END IF;
			RETURN NEW;
		ELSE
			RETURN OLD;
		END IF;
	ELSE
		RETURN NULL;
	END IF;
END;
$$;


ALTER FUNCTION public.trfn_config_before() OWNER TO appdb;

--
-- Name: trfn_contacts_cache_delta(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_contacts_cache_delta() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
	IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN
		PERFORM pg_notify('cache_delta', NEW || '|contacts');
	ELSIF TG_OP = 'DELETE' THEN
		PERFORM pg_notify('cache_delta', zerorec('contacts') || '|contacts');
	END IF;
	RETURN NULL;
END;
$$;


ALTER FUNCTION public.trfn_contacts_cache_delta() OWNER TO appdb;

--
-- Name: trfn_contacts_primary_entry(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_contacts_primary_entry() RETURNS trigger
    LANGUAGE plpgsql
    AS $$DECLARE REL RECORD;
BEGIN   
        IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN
                REL = NEW;
        ELSIF TG_OP = 'DELETE' THEN
                REL = OLD;
        END IF;

        IF (SELECT COUNT(*) FROM contacts WHERE contacts.researcherid = REL.researcherid AND contacts.contacttypeid = 7 AND isprimary IS TRUE) = 0 THEN
                UPDATE contacts SET isprimary = TRUE WHERE id = (SELECT id FROM contacts WHERE contacts.researcherid = REL.researcherid AND contacts.contacttypeid = 7 LIMIT 1);
        ELSIF (SELECT COUNT(*) FROM contacts WHERE contacts.researcherid = REL.researcherid AND contacts.contacttypeid = 7 AND isprimary IS TRUE) > 1 THEN
                UPDATE contacts SET isprimary = FALSE WHERE researcherid = REL.researcherid AND id <> REL.id;
        END IF;
        RETURN REL;
END;
$$;


ALTER FUNCTION public.trfn_contacts_primary_entry() OWNER TO appdb;

--
-- Name: trfn_context_script_assocs_appxmlcache(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_context_script_assocs_appxmlcache() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE REL RECORD;
DECLARE appid INT;
BEGIN
	IF TG_WHEN = 'AFTER' THEN 
		IF TG_OP = 'UPDATE' OR TG_OP = 'INSERT' THEN
			REL = NEW;
		ELSE
			REL = OLD;
		END IF;
	END IF;
	appid := (SELECT contexts.appid FROM contexts WHERE contexts.id = REL.contextid);
	UPDATE cache.appxmlcache 
		SET "xml" = __app_to_xml(appid)
		WHERE id = appid;
	RETURN REL;
END;
$$;


ALTER FUNCTION public.trfn_context_script_assocs_appxmlcache() OWNER TO appdb;

--
-- Name: trfn_countries_cache_delta(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_countries_cache_delta() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
	IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN
		PERFORM pg_notify('cache_delta', NEW || '|countries');
	ELSIF TG_OP = 'DELETE' THEN
		PERFORM pg_notify('cache_delta', zerorec('countries') || '|countries');
	END IF;
	RETURN NULL;
END;
$$;


ALTER FUNCTION public.trfn_countries_cache_delta() OWNER TO appdb;

--
-- Name: trfn_dataset_disciplines(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_dataset_disciplines() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
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
				INSERT INTO dataset_disciplines (datasetid, disciplineid, inherited) SELECT NEW.datasetid, pid, TRUE WHERE NOT EXISTS (SELECT * FROM dataset_disciplines WHERE datasetid = NEW.datasetid AND disciplineid = pid);
			END IF;
		ELSIF TG_OP = 'UPDATE' THEN
		ELSIF TG_OP = 'DELETE' THEN
			pid := (SELECT parentid FROM disciplines WHERE id = OLD.disciplineid);
			IF NOT pid IS NULL THEN
				DELETE FROM dataset_disciplines WHERE datasetid = OLD.datasetid AND disciplineid = pid AND inherited IS TRUE;
			END IF;
		END IF;
	ELSIF TG_WHEN = 'BEFORE' THEN
		IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN
		ELSIF TG_OP = 'DELETE' THEN
			IF OLD.inherited IS TRUE AND EXISTS (SELECT * FROM dataset_disciplines WHERE datasetid = OLD.datasetid AND disciplineid IN (SELECT id FROM disciplines WHERE parentid = OLD.disciplineid)) THEN
				RAISE NOTICE '%','Cannot remove inherited parent discipline';
				RETURN NULL;
			END IF;
		END IF;
	END IF;

	UPDATE datasets SET disciplineid = (SELECT array_agg(disciplineid) FROM dataset_disciplines WHERE datasetid = REL.datasetid) WHERE id = REL.datasetid;

	RETURN REL;
END;
$$;


ALTER FUNCTION public.trfn_dataset_disciplines() OWNER TO appdb;

--
-- Name: trfn_dataset_location_organizations(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_dataset_location_organizations() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE REC RECORD;
BEGIN
	IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN
		REC = NEW;
	ELSE
		REC = OLD;
	END IF;
	UPDATE dataset_locations SET organizationid = (SELECT array_agg(organizationid) FROM dataset_location_organizations WHERE dataset_location_id = dataset_locations.id) WHERE id = REC.dataset_location_id;
	RETURN REC;
END
$$;


ALTER FUNCTION public.trfn_dataset_location_organizations() OWNER TO appdb;

--
-- Name: trfn_dataset_location_organizations_no_dupes(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_dataset_location_organizations_no_dupes() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
	IF TG_OP = 'INSERT' THEN
		IF EXISTS (SELECT * FROM dataset_location_organizations WHERE dataset_location_id = NEW.dataset_location_id AND organizationid = NEW.organizationid) THEN
			RETURN NULL;
		ELSE
			RETURN NEW;
		END IF;
	ELSIF TG_OP = 'UPDATE' THEN
		IF EXISTS (SELECT * FROM dataset_location_organizations WHERE dataset_location_id = NEW.dataset_location_id AND organizationid = NEW.organizationid) THEN
			DELETE FROM dataset_location_organizations WHERE id = NEW.id;
		ELSE
			RETURN NEW;
		END IF;
	ELSIF TG_OP = 'DELETE' THEN
		RETURN OLD;
	END IF;
END;
$$;


ALTER FUNCTION public.trfn_dataset_location_organizations_no_dupes() OWNER TO appdb;

--
-- Name: trfn_dataset_location_sites(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_dataset_location_sites() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE REC RECORD;
BEGIN
	IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN
		REC = NEW;
	ELSE
		REC = OLD;
	END IF;
	UPDATE dataset_locations SET siteid = (SELECT array_agg(siteid) FROM dataset_location_sites WHERE dataset_location_id = dataset_locations.id) WHERE id = REC.dataset_location_id;
	RETURN REC;
END
$$;


ALTER FUNCTION public.trfn_dataset_location_sites() OWNER TO appdb;

--
-- Name: trfn_dataset_location_sites_no_dupes(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_dataset_location_sites_no_dupes() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
	IF TG_OP = 'INSERT' THEN
		IF EXISTS (SELECT * FROM dataset_location_sites WHERE dataset_location_id = NEW.dataset_location_id AND siteid = NEW.siteid) THEN
			RETURN NULL;
		ELSE
			RETURN NEW;
		END IF;
	ELSIF TG_OP = 'UPDATE' THEN
		IF EXISTS (SELECT * FROM dataset_location_sites WHERE dataset_location_id = NEW.dataset_location_id AND siteid = NEW.siteid) THEN
			DELETE FROM dataset_location_sites WHERE id = NEW.id;
		ELSE
			RETURN NEW;
		END IF;
	ELSIF TG_OP = 'DELETE' THEN
		RETURN OLD;
	END IF;
END;
$$;


ALTER FUNCTION public.trfn_dataset_location_sites_no_dupes() OWNER TO appdb;

--
-- Name: trfn_disciplines_cache_delta(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_disciplines_cache_delta() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
	IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN
		PERFORM pg_notify('cache_delta', NEW || '|disciplines');
	ELSIF TG_OP = 'DELETE' THEN
		PERFORM pg_notify('cache_delta', zerorec('disciplines') || '|disciplines');
	END IF;
	RETURN NULL;
END;
$$;


ALTER FUNCTION public.trfn_disciplines_cache_delta() OWNER TO appdb;

--
-- Name: trfn_extauthors(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_extauthors() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
	IF TG_WHEN = 'BEFORE' THEN
		IF TG_OP = 'INSERT' THEN
			IF EXISTS (SELECT * FROM extauthors WHERE NOT (author, docid) IS DISTINCT FROM (NEW.author, NEW.docid)) THEN
				UPDATE extauthors SET main = NEW.main WHERE docid = NEW.docid AND author = NEW.author;
				RETURN NULL;
			ELSE
				RETURN NEW;
			END IF;			
		ELSIF TG_OP = 'UPDATE' THEN
			RETURN NEW;
		ELSIF TG_OP = 'DELETE' THEN
			RETURN OLD;
		END IF;
	ELSE
		RETURN NULL;
	END IF;
END;
$$;


ALTER FUNCTION public.trfn_extauthors() OWNER TO appdb;

--
-- Name: trfn_faqs(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_faqs() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
	IF TG_OP = 'UPDATE' OR TG_OP = 'DELETE'THEN
		INSERT INTO faq_history (faqid, question, answer, submitter, "when") VALUES (OLD.id, OLD.question, OLD.answer, OLD.submitter, OLD."when");
	END IF;
	IF TG_OP = 'DELETE' THEN RETURN OLD; ELSE RETURN NEW; END IF;
END;
$$;


ALTER FUNCTION public.trfn_faqs() OWNER TO appdb;

--
-- Name: trfn_intauthors(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_intauthors() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
	IF TG_WHEN = 'BEFORE' THEN
		IF TG_OP = 'INSERT' THEN
			IF EXISTS (SELECT * FROM intauthors WHERE NOT (docid, authorid) IS DISTINCT FROM (NEW.docid, NEW.authorid)) THEN
				UPDATE intauthors SET main = NEW.main WHERE docid = NEW.docid AND authorid = NEW.authorid;
				RETURN NULL;
			ELSE
				RETURN NEW;
			END IF;
		ELSIF TG_OP = 'UPDATE' THEN
			RETURN NEW;
		ELSIF TG_OP = 'DELETE' THEN
			RETURN OLD;
		END IF;
	ELSE
		RETURN NULL;
	END IF;
END;
$$;


ALTER FUNCTION public.trfn_intauthors() OWNER TO appdb;

--
-- Name: trfn_licenses(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_licenses() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE REL RECORD;
BEGIN
	IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN
		REL := NEW;
	ELSE
		REL := OLD;
	END IF;
	IF TG_WHEN = 'BEFORE' THEN
		IF TG_OP = 'DELETE' THEN
			DELETE FROM app_licenses WHERE licenseid = REL.id;
		END IF;
	ELSIF TG_WHEN = 'INSERT' THEN
		IF NEW.title IS NULL THEN NEW.title = NEW.name; END IF;
	END IF;
	RETURN REL;
END;
$$;


ALTER FUNCTION public.trfn_licenses() OWNER TO appdb;

--
-- Name: trfn_licenses_cache_delta(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_licenses_cache_delta() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
	IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN
		PERFORM pg_notify('cache_delta', NEW || '|licenses');
	ELSIF TG_OP = 'DELETE' THEN
		PERFORM pg_notify('cache_delta', zerorec('licenses') || '|licenses');
	END IF;
	RETURN NULL;
END;
$$;


ALTER FUNCTION public.trfn_licenses_cache_delta() OWNER TO appdb;

--
-- Name: trfn_linksdb(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_linksdb() RETURNS trigger
    LANGUAGE plpgsql
    AS $$BEGIN
    IF TG_OP = 'INSERT' THEN
        IF TG_LEVEL = 'ROW' THEN
            IF TG_WHEN = 'BEFORE' THEN
                IF EXISTS (SELECT * FROM linksdb WHERE fix_linkdb_url_bug(urlname) = fix_linkdb_url_bug(NEW.urlname) AND name = NEW.name) THEN
                    UPDATE linksdb SET 
                        urlname = fix_linkdb_url_bug(NEW.urlname),
                        parentname = NEW.parentname, 
                        baseref = NEW.baseref,
                        valid = NEW.valid,
                        result = NEW.result,
                        warning = NEW.warning,
                        info = NEW.info,
                        line = NEW.line,
                        col = NEW.col,
                        checktime = NEW.checktime,
                        dltime = NEW.dltime,
                        dlsize = NEW.dlsize,
                        cached = NEW.cached,
                        lastchecked = NOW()
                    WHERE fix_linkdb_url_bug(urlname) = fix_linkdb_url_bug(NEW.urlname) AND name = NEW.name;
                    RETURN NULL; -- CANCEL THE INSERTION SINCE IT WAS TURNED INTO AN UPDATE
				ELSE
					NEW.urlname := fix_linkdb_url_bug(urlname);
					NEW.url := fix_linkdb_url_bug(url);
					RETURN NEW;
                END IF;
            END IF; 
        END IF; 
        RETURN NEW;
	ELSIF TG_OP = 'UPDATE' THEN
        IF TG_LEVEL = 'ROW' THEN
            IF TG_WHEN = 'BEFORE' THEN   
				NEW.urlname := fix_linkdb_url_bug(NEW.urlname);
				NEW.url := fix_linkdb_url_bug(NEW.url);
            END IF; 
        END IF; 
        RETURN NEW;
/*      ELSIF TG_OP = 'DELETE' THEN
        IF TG_LEVEL = 'ROW' THEN
            IF TG_WHEN = 'BEFORE' THEN                  
                    
            END IF; 
        END IF; 
        RETURN OLD; */
    END IF; 
END;$$;


ALTER FUNCTION public.trfn_linksdb() OWNER TO appdb;

--
-- Name: trfn_mail_subscriptions(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_mail_subscriptions() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
	UPDATE mail_subscriptions SET flthash=flthash(flt) WHERE id = NEW.id;
	RETURN NEW;
END;
$$;


ALTER FUNCTION public.trfn_mail_subscriptions() OWNER TO appdb;

--
-- Name: trfn_middlewares(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_middlewares() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE REL RECORD;
BEGIN
	IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN
		REL := NEW;
	ELSE
		REL := OLD;
	END IF;
	IF TG_OP = 'DELETE' THEN
		DELETE FROM appcontact_middlewares WHERE middlewareid = REL.id;
	END IF;
	RETURN REL;
END;
$$;


ALTER FUNCTION public.trfn_middlewares() OWNER TO appdb;

--
-- Name: trfn_middlewares_cache_delta(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_middlewares_cache_delta() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
	IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN
		PERFORM pg_notify('cache_delta', NEW || '|middlewares');
	ELSIF TG_OP = 'DELETE' THEN
		PERFORM pg_notify('cache_delta', zerorec('middlewares') || '|middlewares');
	END IF;
	RETURN NULL;
END;
$$;


ALTER FUNCTION public.trfn_middlewares_cache_delta() OWNER TO appdb;

--
-- Name: trfn_organizations(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_organizations() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE vid INTEGER;
BEGIN
    IF TG_OP = 'DELETE' THEN
	IF TG_LEVEL = 'ROW' THEN
            IF TG_WHEN = 'BEFORE' THEN
		DELETE FROM relations WHERE (target_guid = OLD.guid OR subject_guid = OLD.guid);
		RETURN OLD;
            END IF;
        END IF;
    END IF;
END;$$;


ALTER FUNCTION public.trfn_organizations() OWNER TO appdb;

--
-- Name: trfn_oses_cache_delta(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_oses_cache_delta() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
	IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN
		PERFORM pg_notify('cache_delta', NEW || '|oses');
	ELSIF TG_OP = 'DELETE' THEN
		PERFORM pg_notify('cache_delta', zerorec('oses') || '|oses');
	END IF;
	RETURN NULL;
END;
$$;


ALTER FUNCTION public.trfn_oses_cache_delta() OWNER TO appdb;

--
-- Name: trfn_positiontypes_cache_delta(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_positiontypes_cache_delta() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
	IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN
		PERFORM pg_notify('cache_delta', NEW || '|positiontypes');
	ELSIF TG_OP = 'DELETE' THEN
		PERFORM pg_notify('cache_delta', zerorec('positiontypes') || '|positiontypes');
	END IF;
	RETURN NULL;
END;
$$;


ALTER FUNCTION public.trfn_positiontypes_cache_delta() OWNER TO appdb;

--
-- Name: trfn_ppl_api_log(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_ppl_api_log() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
IF TG_OP = 'INSERT' THEN
	IF TG_WHEN = 'BEFORE' THEN
		IF EXISTS (SELECT * FROM ppl_api_log
			WHERE pplid = NEW.pplid
			AND COALESCE(researcherid,0) = COALESCE(NEW.researcherid,0)
			AND COALESCE(source,-1) = COALESCE(NEW.source,-1)
			AND COALESCE(ip,'') = COALESCE(NEW.ip,'')
			AND NEW.timestamp - "timestamp" < INTERVAL '1 minute')
		THEN
			RETURN NULL;
		ELSE 
			RETURN NEW;
		END IF;
	ELSIF TG_WHEN = 'AFTER' THEN
		UPDATE researchers SET hitcount = CASE 
			WHEN (SELECT count FROM pplhitcounts WHERE pplid = NEW.pplid) IS NULL THEN 0 
			ELSE (SELECT count FROM pplhitcounts WHERE pplid = NEW.pplid) 
		END WHERE researchers.id = NEW.pplid;
		RETURN NEW;
	END IF;
ELSIF TG_OP = 'UPDATE' THEN
	RETURN NEW;
ELSIF TG_OP = 'DELETE' THEN
	RETURN OLD;
END IF;
END;
$$;


ALTER FUNCTION public.trfn_ppl_api_log() OWNER TO appdb;

--
-- Name: trfn_proglangs_cache_delta(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_proglangs_cache_delta() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
	IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN
		PERFORM pg_notify('cache_delta', NEW || '|proglangs');
	ELSIF TG_OP = 'DELETE' THEN
		PERFORM pg_notify('cache_delta', zerorec('proglangs') || '|proglangs');
	END IF;
	RETURN NULL;
END;
$$;


ALTER FUNCTION public.trfn_proglangs_cache_delta() OWNER TO appdb;

--
-- Name: trfn_projects(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_projects() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE vid INTEGER;
BEGIN
    IF TG_OP = 'DELETE' THEN
	IF TG_LEVEL = 'ROW' THEN
            IF TG_WHEN = 'BEFORE' THEN
		DELETE FROM relations WHERE (target_guid = OLD.guid OR subject_guid = OLD.guid);
		RETURN OLD;
            END IF;
        END IF;
    END IF;
END;$$;


ALTER FUNCTION public.trfn_projects() OWNER TO appdb;

--
-- Name: trfn_refresh_permissions(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_refresh_permissions() RETURNS trigger
    LANGUAGE plpgsql
    AS $$                                                                                                        
BEGIN                                                                                                     
        PERFORM request_permissions_refresh();
        IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN                                                          
                RETURN NEW;                                                                                       
        ELSE                                                                                                  
                RETURN OLD;                                                                                       
        END IF;                                                                                               
END;                                                                                                      
$$;


ALTER FUNCTION public.trfn_refresh_permissions() OWNER TO appdb;

--
-- Name: trfn_researcher_cnames(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_researcher_cnames() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE REL RECORD;
BEGIN
	IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN
		REL = NEW;
	ELSIF TG_OP = 'DELETE' THEN
		REL = OLD;
	END IF;	
	UPDATE researchers SET cname = (SELECT value FROM researcher_cnames WHERE isprimary IS TRUE AND researcherid = REL.researcherid LIMIT 1) WHERE id = REL.researcherid;
	RETURN REL;
END;
$$;


ALTER FUNCTION public.trfn_researcher_cnames() OWNER TO appdb;

--
-- Name: trfn_researchers(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_researchers() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE mFields TEXT[];
DECLARE i INT;
BEGIN
    mFields := NULL::TEXT[];
    IF TG_OP = 'INSERT' THEN
        IF TG_LEVEL = 'ROW' THEN
            IF TG_WHEN = 'BEFORE' THEN
                NEW.firstname := trim(NEW.firstname);
                NEW.lastname := trim(NEW.lastname);
                NEW.name := NEW.firstname||' '||NEW.lastname;
				IF NEW.accounttype = 1 THEN 
					NEW.gender = 'robot';
				END IF;
            ELSIF TG_WHEN = 'AFTER' THEN
                INSERT INTO news (timestamp, subjectguid, action) VALUES (NOW(), NEW.guid, 'insert');
                FOR i IN 0..5 LOOP
					PERFORM subscribe_to_notification(NEW.id, i);
				END LOOP;
				IF (NEW.cname IS NULL) THEN
					INSERT INTO researcher_cnames (researcherid, value) VALUES (NEW.id, normalize_cname(NEW.name));
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
				IF ROW(NEW.firstname, NEW.lastname, NEW.institution, NEW.countryid, NEW.positiontypeid, NEW.gender) IS DISTINCT FROM 
				ROW(OLD.firstname, OLD.lastname, OLD.institution, OLD.countryid, OLD.positiontypeid, OLD.gender) THEN
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
                IF (NEW.gender <> OLD.gender) THEN mFields := array_append(mFields,'gender'); END IF;
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
END;$$;


ALTER FUNCTION public.trfn_researchers() OWNER TO appdb;

--
-- Name: trfn_researchers_apps(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_researchers_apps() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE mFields TEXT[];
BEGIN	
	IF TG_OP = 'INSERT' THEN
		IF TG_LEVEL = 'ROW' THEN
			IF TG_WHEN = 'AFTER' THEN
				mFields = array_append(mFields,NEW.researcherid||':'||(SELECT name FROM researchers WHERE id = NEW.researcherid));
				INSERT INTO news (timestamp, subjectguid, action, fields) 
				VALUES (NOW(), (SELECT guid FROM applications WHERE id = NEW.appid LIMIT 1), 'insertcnt', mFields);
				-- NOTIFY invalidate_cache, 'permissions';
				UPDATE 
					userrequests 
				SET 
					stateid = 2
				WHERE 
					typeid = 1 AND
					stateid = 1 AND
					userguid = (SELECT guid FROM researchers WHERE id = NEW.researcherid) AND
					targetguid = (SELECT guid FROM applications WHERE id = NEW.appid);					
			END IF;
		END IF;
		RETURN NEW;
/*	ELSIF TG_OP = 'UPDATE' THEN
		IF TG_LEVEL = 'ROW' THEN
			IF TG_WHEN = 'AFTER' THEN
				
			END IF;
		END IF;
		RETURN NEW; */
	ELSIF TG_OP = 'DELETE' THEN
		IF TG_LEVEL = 'ROW' THEN
			IF TG_WHEN = 'BEFORE' THEN				
				DELETE FROM appcontact_middlewares WHERE appcontact_middlewares.appid = OLD.appid and appcontact_middlewares.researcherid = OLD.researcherid;
				DELETE FROM appcontact_vos WHERE appcontact_vos.appid = OLD.appid and appcontact_vos.researcherid = OLD.researcherid;
				DELETE FROM appcontact_otheritems WHERE appcontact_otheritems.appid = OLD.appid and appcontact_otheritems.researcherid = OLD.researcherid;
			ELSE
				-- NOTIFY invalidate_cache, 'permissions';
			END IF;
		END IF;
		RETURN OLD;
	END IF;
END;
$$;


ALTER FUNCTION public.trfn_researchers_apps() OWNER TO appdb;

--
-- Name: trfn_researchers_apps_cache_delta(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_researchers_apps_cache_delta() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
	IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN
		PERFORM pg_notify('cache_delta', NEW || '|researchers_apps');
		PERFORM pg_notify('cache_delta', (SELECT DISTINCT (appcountries.*)::record FROM appcountries WHERE appid = NEW.appid AND id = (SELECT countryid FROM researchers WHERE id = NEW.researcherid) FETCH FIRST 1 ROWS ONLY) || '|appcountries');
	ELSIF TG_OP = 'DELETE' THEN
		PERFORM pg_notify('cache_delta', zerorec('researchers_apps', ARRAY['appid'], ARRAY[OLD.appid]) || '|researchers_apps');
		PERFORM pg_notify('cache_delta', zerorec('researchers_apps', ARRAY['researcherid'], ARRAY[OLD.researcherid]) || '|researchers_apps');
		PERFORM pg_notify('cache_delta', zerorec('appcountries', ARRAY['appid'], ARRAY[OLD.appid]) || '|appcountries');
	END IF;
	RETURN NULL;
END;
$$;


ALTER FUNCTION public.trfn_researchers_apps_cache_delta() OWNER TO appdb;

--
-- Name: trfn_researchers_apps_reset_permissions(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_researchers_apps_reset_permissions() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
	DELETE FROM privileges 
	WHERE actor = (SELECT guid FROM researchers WHERE id = OLD.researcherid) AND
	object = (SELECT guid FROM applications WHERE id = OLD.appid);
	RETURN OLD;
END;
$$;


ALTER FUNCTION public.trfn_researchers_apps_reset_permissions() OWNER TO appdb;

--
-- Name: trfn_researchers_cache_delta(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_researchers_cache_delta() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE rec RECORD;
BEGIN
        IF TG_OP = 'INSERT' OR (TG_OP = 'UPDATE' AND (NEW.firstname, NEW.lastname, NEW.dateinclusion, NEW.institution, NEW.countryid, NEW.positiontypeid, NEW.gender, NEW.name, NEW.lastlogin, NEW.nodissemination, NEW.deleted) IS DISTINCT FROM (OLD.firstname, OLD.lastname, OLD.dateinclusion, OLD.institution, OLD.countryid, OLD.positiontypeid, OLD.gender, OLD.name, OLD.lastlogin, OLD.nodissemination, OLD.deleted) ) THEN
                rec := NEW;
                PERFORM pg_notify('cache_delta', rec || '|researchers');
        ELSIF TG_OP = 'DELETE' THEN
                PERFORM pg_notify('cache_delta', zerorec('researchers') || '|researchers');
        END IF;
        RETURN NULL;
END;
$$;


ALTER FUNCTION public.trfn_researchers_cache_delta() OWNER TO appdb;

--
-- Name: trfn_statuses_cache_delta(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_statuses_cache_delta() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
	IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN
		PERFORM pg_notify('cache_delta', NEW || '|statuses');
	ELSIF TG_OP = 'DELETE' THEN
		PERFORM pg_notify('cache_delta', zerorec('statuses') || '|statuses');
	END IF;
	RETURN NULL;
END;
$$;


ALTER FUNCTION public.trfn_statuses_cache_delta() OWNER TO appdb;

--
-- Name: trfn_sync_derived_dataset_disciplines(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_sync_derived_dataset_disciplines() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
	IF (TG_WHEN = 'AFTER') AND (TG_TABLE_NAME = 'datasets') AND (TG_OP = 'INSERT' OR TG_OP = 'UPDATE') THEN
		IF 
			((TG_OP = 'INSERT') AND (NOT NEW.parentid IS NULL))
		OR
			((TG_OP = 'UPDATE') AND (NEW.parentid IS DISTINCT FROM OLD.parentid) AND (NOT NEW.parentid IS NULL)) THEN		
			DELETE FROM dataset_disciplines WHERE datasetid = NEW.id;
			INSERT INTO dataset_disciplines (datasetid, disciplineid, inherited) 
				SELECT datasetid, disciplineid, inherited
				FROM dataset_disciplines
				WHERE datasetid = NEW.parentid AND inherited IS DISTINCT FROM FALSE;
		END IF;
		RETURN NEW;
	ELSIF (TG_WHEN = 'AFTER') AND (TG_TABLE_NAME = 'dataset_disciplines') THEN		
		IF (TG_OP = 'INSERT' OR TG_OP = 'UPDATE') THEN
			IF (EXISTS (SELECT * FROM datasets WHERE parentid = NEW.datasetid)) AND (NEW.inherited IS DISTINCT FROM FALSE) THEN
				INSERT INTO dataset_disciplines (datasetid, disciplineid, inherited)
					SELECT datasets.id, NEW.disciplineid, NEW.inherited 
					FROM datasets
					WHERE datasets.parentid = NEW.datasetid;
			END IF;
			RETURN NEW;
		ELSIF (TG_OP = 'DELETE') THEN
			IF EXISTS (SELECT * FROM datasets WHERE parentid = OLD.datasetid) AND (OLD.inherited IS DISTINCT FROM FALSE) THEN
				DELETE FROM 
					dataset_disciplines 
				WHERE 
					dataset_disciplines.disciplineid = OLD.disciplineid AND 
					dataset_disciplines.datasetid IN (SELECT id FROM datasets WHERE datasets.parentid = OLD.id);
			END IF;
			RETURN OLD;
		END IF;
	END IF;	
END;
$$;


ALTER FUNCTION public.trfn_sync_derived_dataset_disciplines() OWNER TO appdb;

--
-- Name: trfn_userrequests(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_userrequests() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE REL RECORD;
BEGIN
	IF TG_OP = 'UPDATE' THEN
		REL := NEW;
		REL.lastUpdated := NOW();
	ELSIF TG_OP = 'INSERT' THEN
		REL := NEW;
	ELSIF TG_OP = 'DELETE' THEN
		REL := OLD;
	END IF;
	-- NOTIFY invalidate_cache, 'permissions';
	RETURN REL;
END;
$$;


ALTER FUNCTION public.trfn_userrequests() OWNER TO appdb;

--
-- Name: trfn_vapp_versions(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_vapp_versions() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE REL RECORD;
BEGIN
	IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN
		REL := NEW;
	ELSIF TG_OP = 'DELETE' THEN 
		REL := OLD;
	END IF;
	IF TG_WHEN = 'BEFORE' THEN
		-- STUB
	ELSIF TG_WHEN = 'AFTER' THEN
		IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN
			IF NEW.archived IS TRUE AND (OLD.archived <> TRUE) THEN
				UPDATE vapp_versions SET archivedon = NOW() WHERE id = NEW.id;
			END IF;
			PERFORM set_vowide_image_state(vowide_image_list_images.id)
			FROM vowide_image_list_images
			INNER JOIN vapplists ON vapplists.id = vowide_image_list_images.vapplistid
			INNER JOIN vapp_versions ON vapp_versions.id = vapplists.vappversionid
			WHERE vapp_versions.id = NEW.id;
		ELSIF TG_OP = 'DELETE' THEN
			PERFORM set_vowide_image_state(vowide_image_list_images.id)
			FROM vowide_image_list_images
			INNER JOIN vapplists ON vapplists.id = vowide_image_list_images.vapplistid
			INNER JOIN vapp_versions ON vapp_versions.id = vapplists.vappversionid
			WHERE vapp_versions.id = OLD.id;
		END IF;
	END IF;
	RETURN REL;
END;
$$;


ALTER FUNCTION public.trfn_vapp_versions() OWNER TO appdb;

--
-- Name: trfn_vapp_versions_appxmlcache(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_vapp_versions_appxmlcache() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE REL RECORD;
DECLARE appid INT;
BEGIN
	IF TG_WHEN = 'AFTER' THEN
		IF TG_OP = 'UPDATE' OR TG_OP = 'INSERT' THEN
			REL = NEW;
		ELSE
			REL = OLD;
		END IF;
	END IF;
	appid := (SELECT vapplications.appid FROM vapplications WHERE vapplications.id = REL.vappid);
	UPDATE cache.appxmlcache 
		SET "xml" = __app_to_xml(appid)
		WHERE id = appid;
	RETURN REL;
END;
$$;


ALTER FUNCTION public.trfn_vapp_versions_appxmlcache() OWNER TO appdb;

--
-- Name: trfn_vapp_versions_news(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_vapp_versions_news() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
	IF TG_OP = 'UPDATE' THEN
		IF NEW.published IS TRUE AND NEW.enabled IS TRUE AND NEW.archived IS FALSE AND NEW.status = 'verified' THEN
			UPDATE applications SET lastupdated = NOW() WHERE applications.id = (SELECT vapplications.appid FROM vapplications WHERE vapplications.id=NEW.vappid);
			INSERT INTO news (action, subjectguid, fields, "timestamp") 
			VALUES (LOWER(TG_OP) || 'vav', (SELECT guid FROM applications WHERE id = (SELECT appid FROM vapplications WHERE id = NEW.vappid)), ARRAY['published:' || NEW.id], NEW.createdon);
		END IF;
		RETURN NEW;
	ELSIF TG_OP = 'INSERT' THEN
		IF NEW.published IS TRUE AND NEW.enabled IS TRUE THEN
			UPDATE applications SET lastupdated = NOW() WHERE applications.id = (SELECT vapplications.appid FROM vapplications WHERE vapplications.id=NEW.vappid);
			INSERT INTO news (action, subjectguid, fields, "timestamp") 
                        VALUES (LOWER(TG_OP) || 'vav', (SELECT guid FROM applications WHERE id = (SELECT appid FROM vapplications WHERE id = NEW.vappid)), ARRAY['published:' || NEW.id], NEW.createdon);
		END IF;
		RETURN NEW;
	ELSIF TG_OP = 'DELETE' THEN
		DELETE FROM news WHERE ARRAY['published:' || OLD.id] = fields AND subjectguid = (SELECT guid FROM applications WHERE id = (SELECT appid FROM vapplications WHERE id = OLD.vappid));
		RETURN OLD;
	END IF;
END;
$$;


ALTER FUNCTION public.trfn_vapp_versions_news() OWNER TO appdb;

--
-- Name: trfn_vapplications(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_vapplications() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
	IF TG_WHEN = 'AFTER' THEN
		IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN
			IF NOT NEW.appid IS NULL AND (TG_OP = 'INSERT' OR (TG_OP = 'UPDATE' AND OLD.appid IS NULL)) THEN
				UPDATE vapplications SET name = (SELECT name FROM applications WHERE applications.id = NEW.appid) WHERE id = NEW.id;
				INSERT INTO appcategories (appid,categoryid) SELECT NEW.appid, 34 WHERE NOT EXISTS (SELECT * FROM appcategories WHERE appid = NEW.appid AND categoryid = 34);
			END IF;
		END IF;
	ELSIF TG_WHEN = 'BEFORE' THEN
		IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN
			RETURN NEW;
		ELSIF TG_OP = 'DELETE' THEN
			DELETE FROM vapplists WHERE vmiinstanceid IN (SELECT id FROM vmiinstances WHERE vmiflavourid IN (SELECT id FROM vmiflavours WHERE vmiid IN (SELECT id FROM vmis WHERE vappid = OLD.id)));
			DELETE FROM vapp_versions WHERE vappid = OLD.id;
			DELETE FROM vmiinstances WHERE vmiflavourid IN (SELECT id FROM vmiflavours WHERE vmiid IN (SELECT id FROM vmis WHERE vappid = OLD.id));
			DELETE FROM vmiflavours WHERE vmiid IN (SELECT id FROM vmis WHERE vappid = OLD.id);
			DELETE FROM vmis WHERE vappid = OLD.id;
			RETURN OLD;
		END IF;
	END IF;
	RETURN NULL;
END;
$$;


ALTER FUNCTION public.trfn_vapplications() OWNER TO appdb;

--
-- Name: trfn_vo_middlewares_cache_delta(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_vo_middlewares_cache_delta() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
	IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN
		PERFORM pg_notify('cache_delta', NEW || '|vo_middlewares');
	ELSIF TG_OP = 'DELETE' THEN
		PERFORM pg_notify('cache_delta', zerorec('vo_middlewares', ARRAY['void'], ARRAY[OLD.void]) || '|vo_middlewares');
	END IF;
	RETURN NULL;
END;
$$;


ALTER FUNCTION public.trfn_vo_middlewares_cache_delta() OWNER TO appdb;

--
-- Name: trfn_vos(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_vos() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE REL RECORD;
BEGIN
    IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN
        IF TG_WHEN = 'BEFORE' THEN
            IF NEW.deleted IS TRUE AND OLD.deleted IS FALSE THEN
                NEW.deletedon := NOW();
            ELSIF NEW.deleted IS FALSE AND NOT NEW.deletedon IS NULL THEN
                NEW.deletedon := NULL;
            END IF;
			NEW.guid := uuid_generate_v5(uuid_namespace('ISO OID'), (SELECT salt FROM vo_sources WHERE id = NEW.sourceid) || 'vo:' || NEW.name);
        END IF;
        REL := NEW;
    ELSE
        REL := OLD;
    END IF;
    IF TG_OP = 'DELETE' THEN
        DELETE FROM appcontact_vos WHERE void = REL.id;
    END IF;
    RETURN REL;
END;
$$;


ALTER FUNCTION public.trfn_vos() OWNER TO appdb;

--
-- Name: trfn_vos_cache_delta(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_vos_cache_delta() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
	IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN
		PERFORM pg_notify('cache_delta', NEW || '|vos');
	ELSIF TG_OP = 'DELETE' THEN
		PERFORM pg_notify('cache_delta', zerorec('vos') || '|vos');
	END IF;
	RETURN NULL;
END;
$$;


ALTER FUNCTION public.trfn_vos_cache_delta() OWNER TO appdb;

--
-- Name: trfn_vowide_image_list_images(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_vowide_image_list_images() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE rec RECORD;
BEGIN
	IF TG_WHEN = 'BEFORE' THEN
		IF TG_OP = 'INSERT' THEN
			NEW.state := get_vowide_image_state(NEW.vapplistid);
			NEW.guid := uuid_generate_v5(uuid_namespace('ISO OID'), 'vowide_image_list_image:' || (
				SELECT name FROM vos WHERE id = (
					SELECT void FROM vowide_image_lists WHERE id = NEW.vowide_image_list_id
				)
			) || ':' || (
				SELECT guid FROM vmiinstances WHERE id = (
					SELECT vmiinstanceid FROM vaviews WHERE vapplistid = NEW.vapplistid
				)
			));
			RETURN NEW;
		ELSIF TG_OP = 'UPDATE' THEN
			RETURN NEW;
		ELSIF TG_OP = 'DELETE' THEN
			RETURN OLD;
		END IF;
	ELSE
		RETURN NULL;
	END IF;	
END;
$$;


ALTER FUNCTION public.trfn_vowide_image_list_images() OWNER TO appdb;

--
-- Name: trfn_vowide_image_lists(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION trfn_vowide_image_lists() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
	IF TG_WHEN = 'BEFORE' THEN
		IF TG_OP = 'INSERT' THEN
			NEW.guid := uuid_generate_v5(uuid_namespace('ISO OID'), 'vowide_image_list:' || (SELECT name FROM vos WHERE id = NEW.void));
			RETURN NEW;
		ELSIF TG_OP = 'UPDATE' THEN
			RETURN NEW;
		ELSIF TG_OP = 'DELETE' THEN
			RETURN OLD;
		END IF;
	ELSIF TG_WHEN = 'AFTER' THEN
		IF TG_OP = 'INSERT' THEN
			IF NEW.state = 'draft'::e_vowide_image_state THEN
				INSERT INTO vowide_image_list_images (vowide_image_list_id, vapplistid, guid) 
				SELECT NEW.id, vapplistid, guid
				FROM vowide_image_list_images 
				WHERE vowide_image_list_id = (SELECT id FROM vowide_image_lists WHERE void = NEW.void AND state = 'published'::e_vowide_image_state);
			END IF;			
		END IF;
		RETURN NULL;
	END IF;	
END;
$$;


ALTER FUNCTION public.trfn_vowide_image_lists() OWNER TO appdb;

--
-- Name: unsubscribe_from_notification(integer, integer, integer, text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION unsubscribe_from_notification(m_researcherid integer, m_notificationtype integer, m_delivery integer DEFAULT NULL::integer, m_payload text DEFAULT NULL::text) RETURNS boolean
    LANGUAGE plpgsql
    AS $$
BEGIN
	RETURN manage_notifications('UNSUBSCRIBE', m_researcherid, m_notificationtype, m_delivery, m_payload)::BOOLEAN;
	
END;
$$;


ALTER FUNCTION public.unsubscribe_from_notification(m_researcherid integer, m_notificationtype integer, m_delivery integer, m_payload text) OWNER TO appdb;

--
-- Name: FUNCTION unsubscribe_from_notification(m_researcherid integer, m_notificationtype integer, m_delivery integer, m_payload text); Type: COMMENT; Schema: public; Owner: appdb
--

COMMENT ON FUNCTION unsubscribe_from_notification(m_researcherid integer, m_notificationtype integer, m_delivery integer, m_payload text) IS 'See manager_notifications function for usage';


--
-- Name: update_vowide_image_list(integer, integer, integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION update_vowide_image_list(_void integer, _vappversionid integer DEFAULT NULL::integer, _userid integer DEFAULT NULL::integer) RETURNS void
    LANGUAGE plpgsql
    AS $_$
DECLARE a1 int[];
DECLARE old_vappverid int;
BEGIN
	IF NOT $2 IS NULL THEN
		$2 = (SELECT DISTINCT vappversionid
		FROM vowide_image_list_images
		INNER JOIN vapplists ON vapplists.id = vowide_image_list_images.vapplistid
		WHERE vowide_image_list_images.vowide_image_list_id = (SELECT id FROM vowide_image_lists WHERE void = $1 AND state = 'draft')
		AND vappversionid IN (SELECT vappversionid FROM vaviews WHERE vaviews.va_id IN (SELECT va_id FROM vaviews WHERE vappversionid = $2)) AND vappversionid <> $2);
	END IF;
	BEGIN	
		SELECT array_agg(vapplists.id)
		FROM vapplists 
		WHERE
			vapplists.vappversionid IN (
				SELECT id FROM vapp_versions WHERE vappid IN (
					SELECT va_id FROM vaviews WHERE vapplistid IN (
						SELECT vapplistid FROM vowide_image_list_images 
						WHERE vowide_image_list_id = (SELECT id FROM vowide_image_lists WHERE void = $1 AND state = 'draft')
						AND state <> 'up-to-date'
					) AND CASE WHEN $2 IS NULL THEN TRUE ELSE va_id IN (SELECT va_id FROM vaviews AS vav WHERE vav.vappversionid = $2) END
				)
				AND published AND NOT archived AND enabled
			)
		INTO a1;

		RAISE NOTICE '%', a1;

		RAISE NOTICE '%', (SELECT array_agg(vapplistid) FROM vowide_image_list_images
		WHERE vowide_image_list_id = (SELECT id FROM vowide_image_lists WHERE void = $1 AND state = 'draft')
		AND CASE 
			WHEN NOT $2 IS NULL THEN 
				vapplistid IN (SELECT id FROM vapplists INNER JOIN vaviews ON vaviews.vappversionid = $2)
			ELSE 
				TRUE
		END
		AND state <> 'up-to-date');

		DELETE FROM vowide_image_list_images
		WHERE vowide_image_list_id = (SELECT id FROM vowide_image_lists WHERE void = $1 AND state = 'draft')
		AND CASE 
			WHEN NOT $2 IS NULL THEN 
				vapplistid IN (SELECT id FROM vapplists INNER JOIN vaviews ON vaviews.vappversionid = vapplists.vappversionid AND vaviews.vappversionid = $2)
			ELSE 
				TRUE
		END
		AND state <> 'up-to-date';
		
		INSERT INTO vowide_image_list_images (vowide_image_list_id, vapplistid) 
		SELECT DISTINCT (SELECT id FROM vowide_image_lists WHERE void = $1 AND state = 'draft'), id 
		FROM UNNEST(a1) AS id
		EXCEPT SELECT vowide_image_list_id, vapplistid FROM vowide_image_list_images;

		IF NOT $3 IS NULL THEN
			UPDATE vowide_image_lists SET alteredby = $3 WHERE id = (SELECT id FROM vowide_image_lists WHERE void = $1 AND state = 'draft');
		END IF;
	END;
END;
$_$;


ALTER FUNCTION public.update_vowide_image_list(_void integer, _vappversionid integer, _userid integer) OWNER TO appdb;

--
-- Name: user_profile_edit_perms(integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION user_profile_edit_perms(mid integer) RETURNS record
    LANGUAGE sql
    AS $_$
SELECT EXISTS (SELECT * FROM actor_group_members INNER JOIN researchers ON researchers.guid = actor_group_members.actorid WHERE actor_group_members.groupid IN (-1,-2) AND researchers.id = $1)
$_$;


ALTER FUNCTION public.user_profile_edit_perms(mid integer) OWNER TO appdb;

--
-- Name: user_profile_edit_perms(integer, integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION user_profile_edit_perms(mid integer, mid2 integer) RETURNS record
    LANGUAGE sql STABLE
    AS $_$
SELECT 
	EXISTS (SELECT actor_group_members.id FROM _actor_group_members AS actor_group_members INNER JOIN researchers ON researchers.guid = actor_group_members.actorid WHERE actor_group_members.groupid IN (-1,-2) AND researchers.id = $1),
	EXISTS (SELECT actor_group_members.id FROM _actor_group_members AS actor_group_members INNER JOIN researchers ON researchers.guid = actor_group_members.actorid WHERE actor_group_members.groupid = -3 AND researchers.id = $1 AND actor_group_members.payload = researchers.countryid::text),
	EXISTS (SELECT actor_group_members.id FROM _actor_group_members AS actor_group_members INNER JOIN researchers ON researchers.guid = actor_group_members.actorid WHERE actor_group_members.groupid IN (-1,-2) AND researchers.id = $2),	
	EXISTS (SELECT actor_group_members.id FROM _actor_group_members AS actor_group_members INNER JOIN researchers ON researchers.guid = actor_group_members.actorid WHERE actor_group_members.groupid = -3 AND researchers.id = $2 AND actor_group_members.payload = researchers.countryid::text),
	EXISTS (SELECT * FROM permissions INNER JOIN researchers ON researchers.guid = permissions.actor WHERE researchers.id = $1 AND actionid = 21 AND (permissions.object IS NULL OR permissions.object = (SELECT guid FROM researchers WHERE id = $2)))
$_$;


ALTER FUNCTION public.user_profile_edit_perms(mid integer, mid2 integer) OWNER TO appdb;

--
-- Name: uuid_generate_v1(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION uuid_generate_v1() RETURNS uuid
    LANGUAGE c STRICT
    AS '$libdir/uuid-ossp', 'uuid_generate_v1';


ALTER FUNCTION public.uuid_generate_v1() OWNER TO appdb;

--
-- Name: uuid_generate_v1mc(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION uuid_generate_v1mc() RETURNS uuid
    LANGUAGE c STRICT
    AS '$libdir/uuid-ossp', 'uuid_generate_v1mc';


ALTER FUNCTION public.uuid_generate_v1mc() OWNER TO appdb;

--
-- Name: uuid_generate_v3(uuid, text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION uuid_generate_v3(namespace uuid, name text) RETURNS uuid
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/uuid-ossp', 'uuid_generate_v3';


ALTER FUNCTION public.uuid_generate_v3(namespace uuid, name text) OWNER TO appdb;

--
-- Name: uuid_nil(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION uuid_nil() RETURNS uuid
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/uuid-ossp', 'uuid_nil';


ALTER FUNCTION public.uuid_nil() OWNER TO appdb;

--
-- Name: uuid_ns_dns(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION uuid_ns_dns() RETURNS uuid
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/uuid-ossp', 'uuid_ns_dns';


ALTER FUNCTION public.uuid_ns_dns() OWNER TO appdb;

--
-- Name: uuid_ns_oid(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION uuid_ns_oid() RETURNS uuid
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/uuid-ossp', 'uuid_ns_oid';


ALTER FUNCTION public.uuid_ns_oid() OWNER TO appdb;

--
-- Name: uuid_ns_url(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION uuid_ns_url() RETURNS uuid
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/uuid-ossp', 'uuid_ns_url';


ALTER FUNCTION public.uuid_ns_url() OWNER TO appdb;

--
-- Name: uuid_ns_x500(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION uuid_ns_x500() RETURNS uuid
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/uuid-ossp', 'uuid_ns_x500';


ALTER FUNCTION public.uuid_ns_x500() OWNER TO appdb;

--
-- Name: va_categories(); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION va_categories() RETURNS SETOF integer
    LANGUAGE sql STRICT
    AS $$
WITH RECURSIVE lvl(cid, l, cname, pid, o) AS (
	SELECT id, 0, name, parentid, '' FROM categories WHERE id = 34
	UNION ALL
	SELECT 
		id, 
		l+1,
		name,
		parentid,
		CASE WHEN o IS NULL THEN CASE COALESCE(ord, 0) WHEN 0 THEN 'Z' ELSE ord::text END || ' ' || name ELSE o || '_' || name END
	FROM lvl, categories WHERE NOT categories.parentid IS DISTINCT FROM cid
)
SELECT cid FROM lvl 
WHERE l>=0
ORDER BY o;
$$;


ALTER FUNCTION public.va_categories() OWNER TO appdb;

--
-- Name: va_provider_to_xml(text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION va_provider_to_xml(mid text) RETURNS SETOF xml
    LANGUAGE plpgsql
    AS $$
BEGIN
RETURN QUERY
SELECT 
	xmlelement(
		name "virtualization:provider", 
		xmlattributes(
			va_providers.id,
			beta,
			in_production,
			node_monitored
		),
		xmlelement(name "provider:name", sitename)
	)
FROM
	va_providers
WHERE id = mid;
END;
$$;


ALTER FUNCTION public.va_provider_to_xml(mid text) OWNER TO appdb;

--
-- Name: va_provider_to_xml_ext(text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION va_provider_to_xml_ext(mid text) RETURNS SETOF xml
    LANGUAGE plpgsql
    AS $$
BEGIN
RETURN QUERY
SELECT 
	xmlelement(
		name "virtualization:provider", 
		xmlattributes(
			va_providers.id,
			beta,
			in_production,
			node_monitored
		),
		xmlelement(name "provider:name", sitename),
		xmlelement(name "provider:url", url),
		CASE WHEN EXISTS (SELECT * FROM va_provider_endpoints WHERE va_provider_endpoints.va_provider_id = va_providers.id) THEN array_to_string(array_agg( 
			DISTINCT xmlelement(name "provider:endpoint_url", endpoint_url)::text
		),'')::xml END,
		xmlelement(name "provider:gocdb_url", gocdb_url),
		CASE WHEN COALESCE(host_dn, '') <> '' THEN xmlelement(name "provider:dn", host_dn) END,
		CASE WHEN COALESCE(host_ip, '') <> '' THEN xmlelement(name "provider:ip", host_ip) END,
		CASE WHEN COALESCE(host_os_id, 0) <> 0 THEN xmlelement(
			name "provider:os", 
			xmlattributes(host_os_id AS id),
			oses.name
		) END,
		CASE WHEN COALESCE(host_arch_id, 0) <> 0 THEN xmlelement(
			name "provider:arch", 
			xmlattributes(host_arch_id AS id),
			archs.name
		) END,
		country_to_xml(country_id),
		CASE WHEN EXISTS(SELECT * FROM va_provider_templates WHERE va_provider_templates.va_provider_id = va_providers.id) THEN
		array_to_string(array_agg(DISTINCT
			xmlelement(name "provider:template",
				 xmlattributes(
					va_provider_templates.group_hash AS group_hash
				 ),
				 xmlelement(name "provider_template:resource_name", resource_name),
				 xmlelement(name "provider_template:main_memory_size", memsize),
				 xmlelement(name "provider_template:logical_cpus", logical_cpus),
				 xmlelement(name "provider_template:physical_cpus", physical_cpus),
				 xmlelement(name "provider_template:cpu_multiplicity", cpu_multiplicity),
				 xmlelement(name "provider_template:resource_manager", resource_manager),
				 xmlelement(name "provider_template:computing_manager", computing_manager),
				 xmlelement(name "provider_template:os_family", os_family),
				 xmlelement(name "provider_template:connectivity_in", connectivity_in),
				 xmlelement(name "provider_template:connectivity_out", connectivity_out),
				 xmlelement(name "provider_template:cpu_model", cpu_model),
				 xmlelement(name "provider_template:resource_id", resource_id)
			)::text
		), '')::xml
		END,
		CASE WHEN EXISTS(SELECT * FROM va_provider_images WHERE va_provider_images.va_provider_id = va_providers.id) THEN
		(
			SELECT xmlagg(
				xmlelement(name "provider:image",
					xmlattributes(
						content_type,
						mp_uri,
						va_provider_image_id,
						va_provider_images.vmiinstanceid,
						va_provider_images.vowide_vmiinstanceid,	
						va_provider_images.good_vmiinstanceid
					)
				)
			) FROM va_provider_images WHERE va_provider_id = va_providers.id
		)
		END
	)
FROM
	va_providers 
	LEFT JOIN oses ON oses.id = host_os_id
	LEFT JOIN archs ON archs.id = host_arch_id
	LEFT JOIN va_provider_endpoints ON va_provider_endpoints.va_provider_id = va_providers.id
	LEFT JOIN va_provider_templates ON va_provider_templates.va_provider_id = va_providers.id
WHERE va_providers.id = mid
	GROUP BY 
		va_providers.id,
		va_providers.beta,
		va_providers.in_production,
		va_providers.node_monitored,
		va_providers.sitename,
		va_providers.url,
		va_providers.gocdb_url,
		va_providers.host_dn,
		va_providers.host_ip,
		va_providers.host_os_id,
		va_providers.host_arch_id,
		oses.name,
		archs.name,
		country_id
;
END;
$$;


ALTER FUNCTION public.va_provider_to_xml_ext(mid text) OWNER TO appdb;

--
-- Name: valid_relation(uuid, integer, uuid); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION valid_relation(uuid, integer, uuid) RETURNS boolean
    LANGUAGE sql STABLE
    AS $_$
SELECT CASE WHEN EXISTS (SELECT * FROM find_relationtype($1, (SELECT verbid FROM relationtypes WHERE id = $2), $3)) THEN TRUE ELSE FALSE END;
$_$;


ALTER FUNCTION public.valid_relation(uuid, integer, uuid) OWNER TO appdb;

--
-- Name: vapp_image_providers_to_xml(integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION vapp_image_providers_to_xml(_appid integer) RETURNS SETOF xml
    LANGUAGE sql
    AS $_$
 WITH hypervisors AS (
	 WITH x AS (
		 SELECT vmiflavours_2.id,
		    unnest(vmiflavours_2.hypervisors) AS y
		   FROM vmiflavours vmiflavours_2
		)
	 SELECT vmiflavours_1.id AS vmiflavourid,
	    xmlagg(XMLELEMENT(NAME "virtualization:hypervisor", XMLATTRIBUTES(( SELECT hypervisors_1.id
		   FROM public.hypervisors hypervisors_1
		  WHERE hypervisors_1.name::text = x.y::text) AS id), x.y)) AS hypervisor
	   FROM vmiflavours vmiflavours_1
	JOIN x ON x.id = vmiflavours_1.id
	GROUP BY vmiflavours_1.id
)
SELECT
	xmlelement(
		name "virtualization:image",
		xmlattributes(
			vaviews.vmiinstanceid,
			vaviews.vmiinstance_guid AS identifier,
			vaviews.vmiinstance_version,
			vaviews.va_version_archived AS archived,
			vaviews.va_version_enabled AS enabled,
			CASE WHEN vaviews.va_version_expireson >= NOW() THEN FALSE ELSE TRUE END AS isexpired
		),
		hypervisors.hypervisor::text::xml,
--		XMLELEMENT(NAME "virtualization:hypervisors", array_to_string(vaviews.hypervisors, ',')::xml), 
		XMLELEMENT(NAME "virtualization:os", XMLATTRIBUTES(oses.id AS id, vaviews.osversion AS version, oses.os_family_id as family_id), oses.name), 
		XMLELEMENT(NAME "virtualization:arch", XMLATTRIBUTES(archs.id AS id), archs.name),
		vmiinst_cntxscripts_to_xml(vaviews.vmiinstanceid),
--		XMLELEMENT(NAME "virtualization:location", vaviews.uri),
--		XMLELEMENT(NAME "virtualization:checksum", XMLATTRIBUTES(vaviews.checksumfunc AS checkfunc), vaviews.checksum),
--		XMLELEMENT(NAME "virtualization:osversion", vaviews.osversion), 
		array_to_string(array_agg(DISTINCT 
			xmlelement(name "virtualization:provider",
				xmlattributes(
					va_provider_images.va_provider_id as provider_id,
					va_provider_images.va_provider_image_id as occi_id,
					vowide_image_lists.void,
					va_provider_images.vmiinstanceid as vmiinstanceid
				)
			)::text
		),'')::xml
)
FROM 
	applications
	INNER JOIN vaviews ON vaviews.appid = applications.id
	INNER JOIN __va_provider_images AS va_provider_images ON va_provider_images.vmiinstanceid = vaviews.vmiinstanceid
	LEFT OUTER JOIN hypervisors ON hypervisors.vmiflavourid = vaviews.vmiflavourid
	LEFT OUTER JOIN archs ON archs.id = vaviews.archid
	LEFT OUTER JOIN oses ON oses.id = vaviews.osid
	LEFT OUTER JOIN vmiformats ON vmiformats.name::text = vaviews.format
	LEFT OUTER JOIN app_vos ON app_vos.appid = applications.id
	LEFT OUTER JOIN vowide_image_list_images ON vowide_image_list_images.id = va_provider_images.vowide_vmiinstanceid
	LEFT OUTER JOIN vowide_image_lists ON vowide_image_lists.id = vowide_image_list_images.vowide_image_list_id AND (vowide_image_lists.state::text = 'published' OR vowide_image_lists.state::text = 'obsolete')
WHERE  
	vaviews.vmiinstanceid = get_good_vmiinstanceid(va_provider_images.vmiinstanceid) AND
	vaviews.va_version_published AND 
--	NOT vaviews.va_version_archived AND
	applications.id = $1
GROUP BY 
	applications.id, 
	--vaviews.uri,
	--vaviews.checksumfunc,
	--vaviews.checksum,
	vaviews.osversion,
	--vaviews.hypervisors,
	hypervisors.hypervisor::text,
	--vaviews.va_id,
	--vaviews.vappversionid,
	vaviews.vmiinstanceid, 
	vaviews.vmiflavourid, 
	vaviews.vmiinstance_guid,
	vaviews.vmiinstance_version,
	vaviews.va_version_archived,
	vaviews.va_version_enabled,
	vaviews.va_version_expireson,
	archs.id, 
	oses.id,
	vmiformats.id,
	app_vos.appid;
$_$;


ALTER FUNCTION public.vapp_image_providers_to_xml(_appid integer) OWNER TO appdb;

--
-- Name: vapp_old_archived_versions(integer, integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION vapp_old_archived_versions(app_id integer, from_index integer) RETURNS SETOF integer
    LANGUAGE plpgsql
    AS $$
BEGIN

	RETURN query SELECT tobedeleted.versionid FROM (SELECT DISTINCT vaviews.vappversionid AS versionid, vaviews.va_version_archivedon AS archivedon FROM vaviews 
		WHERE va_version_archived = true AND vaviews.appid = app_id
		GROUP BY vaviews.vappversionid, vaviews.va_version_archivedon
		ORDER BY vaviews.va_version_archivedon desc
		OFFSET from_index) as tobedeleted ORDER BY tobedeleted.versionid ASC;

END;
$$;


ALTER FUNCTION public.vapp_old_archived_versions(app_id integer, from_index integer) OWNER TO appdb;

--
-- Name: vapp_to_xml(integer, name); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION vapp_to_xml(id integer, tbl name) RETURNS SETOF xml
    LANGUAGE plpgsql
    AS $_$
DECLARE _tbl ALIAS FOR $2;
BEGIN
	RETURN QUERY
	SELECT "xml" FROM vapp_to_xml WHERE CASE tbl WHEN 'applications' THEN appid WHEN 'vapplications' THEN vappid ELSE NULL END = id;
END;
$_$;


ALTER FUNCTION public.vapp_to_xml(id integer, tbl name) OWNER TO appdb;

--
-- Name: vapp_version_to_json(text[]); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION vapp_version_to_json(sdata text[]) RETURNS text
    LANGUAGE plpgsql
    AS $_$
DECLARE tmpint INT;
DECLARE vavids INT[];
DECLARE s TEXT[];
DECLARE i INT;
BEGIN
	vavids = NULL::int[];
	FOR i IN 1..ARRAY_LENGTH(sdata, 1) LOOP
		IF LEFT(TRIM(sdata[i]), 10) = 'published:' THEN
			BEGIN
				tmpint := REPLACE(TRIM(sdata[i]), 'published:', '');
			EXCEPTION 
				WHEN others THEN tmpint = 0;
			END;
			vavids := array_append(vavids, tmpint);
		END IF;
	END LOOP;
	s := NULL::TEXT[];
	FOR i IN SELECT DISTINCT UNNEST(vavids) LOOP
		s := array_append(s, REGEXP_REPLACE(REGEXP_REPLACE(vapp_version_to_json(i), '.+"vapp_version": *', ''), '}$', ''));
	END LOOP;
	RETURN '{' ||
  REGEXP_REPLACE(REGEXP_REPLACE(app_to_json((SELECT appid FROM vapplications WHERE id = (SELECT vappid FROM vapp_versions WHERE id = i))), '^{', ''), '}$', '')::text || ', ' ||
  '"vapp_version": [' ||
  array_to_string(s, ', ') ||
  ']' ||
  '}';
END;
$_$;


ALTER FUNCTION public.vapp_version_to_json(sdata text[]) OWNER TO appdb;

--
-- Name: vapp_version_to_json(integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION vapp_version_to_json(mid integer) RETURNS text
    LANGUAGE sql
    AS $_$
SELECT '{"vapp_version": {' ||
  '"id": "' || vapp_versions.id || '", ' ||  
  '"version": "' || vapp_versions.version || '", ' ||  
  '"guid": "' || vapp_versions.guid || '", ' ||  
  '"notes": "' || REPLACE(vapp_versions.notes, '"', '\"') || '", ' ||  
  '"vappid": "' || vapp_versions.vappid || '", ' ||  
  '"published": "' || vapp_versions.published || '", ' ||  
  '"createdon": "' || vapp_versions.createdon || '", ' ||  
  '"expireson": ' || COALESCE('"' || vapp_versions.expireson::text || '"', 'null') || ', ' ||  
  '"enabled": "' || vapp_versions.enabled || '", ' ||  
  '"archived": "' || vapp_versions.archived || '", ' ||  
  '"status": "' || vapp_versions.status || '", ' ||  
  '"archivedon": ' || COALESCE('"' || vapp_versions.archivedon::text || '"', 'null') || ', ' ||
  REGEXP_REPLACE(REGEXP_REPLACE(app_to_json((SELECT appid FROM vapplications WHERE id = (SELECT vappid FROM vapp_versions WHERE id = $1))), '^{', ''), '}$', '')::text || 
'"}'::text 
FROM vapp_versions WHERE id = $1;
$_$;


ALTER FUNCTION public.vapp_version_to_json(mid integer) OWNER TO appdb;

--
-- Name: vappliance_latest_version(integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION vappliance_latest_version(appid integer) RETURNS TABLE(id integer, version text, enabled boolean, isprivate boolean, isexpired boolean, guid uuid)
    LANGUAGE sql STABLE
    AS $_$
SELECT distinct on(vaviews.appid)
 	vaviews.vappversionid AS id, 
	vaviews.va_version AS version, 
	vaviews.va_version_enabled AS enabled, 
	vaviews.imglst_private AS isprivate,
	CASE WHEN vaviews.va_version_expireson >= NOW() THEN FALSE ELSE TRUE END AS isexpired,
	vaviews.va_version_guid AS guid
FROM vaviews
WHERE vaviews.appid = $1 AND vaviews.va_version_published = true AND vaviews.va_version_archived = false 
$_$;


ALTER FUNCTION public.vappliance_latest_version(appid integer) OWNER TO appdb;

--
-- Name: vappliance_site_count(integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION vappliance_site_count(appid integer) RETURNS integer
    LANGUAGE sql
    AS $_$
SELECT COUNT(p.sitename)::INTEGER FROM (
	SELECT vp.sitename  FROM
	va_providers AS vp
	INNER JOIN __va_provider_images AS vi ON vi.va_provider_id = vp.id
	INNER JOIN vaviews AS vv ON vv.vmiinstanceid = vi.vmiinstanceid
	WHERE vv.appid = $1 AND vv.va_version_published = true AND vv.va_version_enabled = true AND vv.vmiinstanceid = get_good_vmiinstanceid(vi.vmiinstanceid)
	GROUP BY vp.sitename
) AS p
$_$;


ALTER FUNCTION public.vappliance_site_count(appid integer) OWNER TO appdb;

--
-- Name: vmiinst_cntxscripts_to_xml(integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION vmiinst_cntxscripts_to_xml(vmiinstance_id integer) RETURNS xml
    LANGUAGE plpgsql
    AS $$
BEGIN
RETURN (
SELECT xmlagg(
	XMLELEMENT(NAME "virtualization:contextscript", XMLATTRIBUTES(contextscripts.id AS id, vmiinstance_contextscripts.addedon AS addedon, vmiinstance_contextscripts.id AS relationid ),
	CASE WHEN NOT applications.id IS NULL THEN 
		XMLELEMENT(NAME "application:application", 
			XMLATTRIBUTES(applications.id, applications.cname, applications.guid, applications.deleted, applications.moderated),
			XMLELEMENT(NAME "application:name", applications.name))
	ELSE NULL END,
	XMLELEMENT(NAME "virtualization:url", contextscripts.url ),
	XMLELEMENT(NAME "virtualization:title", contextscripts.title ),
	XMLELEMENT(NAME "virtualization:description", contextscripts.description ),
	XMLELEMENT(NAME "virtualization:name", contextscripts.name ),
	XMLELEMENT(NAME "virtualization:format", XMLATTRIBUTES(contextscripts.formatid AS id, contextformats.name AS name)),
	XMLELEMENT(NAME "virtualization:checksum", XMLATTRIBUTES(contextscripts.checksumfunc::text AS hashtype), contextscripts.checksum ),
	XMLELEMENT(NAME "virtualization:size", contextscripts.size ),
	researcher_to_xml(vmiinstance_contextscripts.addedby, 'addedby'::text)
)) AS xml
FROM vmiinstances
INNER JOIN vmiinstance_contextscripts ON vmiinstance_contextscripts.vmiinstanceid = vmiinstances.id
INNER JOIN contextscripts ON contextscripts.id = vmiinstance_contextscripts.contextscriptid
INNER JOIN contextformats ON contextformats.id = contextscripts.formatid
LEFT OUTER JOIN context_script_assocs ON context_script_assocs.scriptid = contextscripts.id
LEFT OUTER JOIN contexts ON contexts.id = context_script_assocs.contextid
LEFT OUTER JOIN applications ON applications.id = contexts.appid
WHERE vmiinstances.id = vmiinstance_id);
END;
$$;


ALTER FUNCTION public.vmiinst_cntxscripts_to_xml(vmiinstance_id integer) OWNER TO appdb;

--
-- Name: vo_logistics(text, text, text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION vo_logistics(m_fltstr text, m_from text, m_where text) RETURNS xml
    LANGUAGE plpgsql
    AS $$
DECLARE h TEXT[];
DECLARE hh TEXT;
DECLARE fl TEXT[];
DECLARE fr TEXT[];
DECLARE w TEXT[];
DECLARE i INT;
DECLARE len INT;
BEGIN  
        IF m_fltstr IS NULL THEN m_fltstr := ''; END IF;
        IF m_from IS NULL THEN m_from := ''; END IF;
        IF m_where IS NULL THEN m_where := ''; END IF;
		m_fltstr := TRIM(m_fltstr);
		m_from := TRIM(m_from);
		m_where := TRIM(m_where);
		IF SUBSTRING(m_fltstr, 1, 1) = '{' THEN
			fl := m_fltstr::text[];
			fr := m_from::text[];
			w := m_where::text[];
		ELSE
			fl := ('{"' || REPLACE(m_fltstr, '"', '\"') || '"}')::text[];
			fr := ('{"' || REPLACE(m_from, '"', '\"') || '"}')::text[];
			w := ('{"' ||  REPLACE(m_where, '"', '\"') || '"}')::text[];
		END IF;
		h := NULL::TEXT[];
		IF m_fltstr = '' THEN
			len := 0;
			hh := MD5(m_from || ' ' || m_where);
			IF NOT EXISTS (SELECT hash FROM cache.filtercache WHERE hash = hh) THEN
				PERFORM filtervos(m_fltstr, m_from, m_where);
			END IF;
			h := ARRAY['cache.filtercache_' || hh];
		ELSE
			len := ARRAY_LENGTH(fl, 1);
		END IF;
		FOR i IN 1..len LOOP
			m_fltstr = TRIM(fl[i]);
			m_from = TRIM(fr[i]);
			m_where = TRIM(w[i]);
			hh := MD5(m_from || ' ' || m_where);
			IF NOT EXISTS (SELECT hash FROM cache.filtercache WHERE hash = hh) THEN
				PERFORM filtervos(m_fltstr, m_from, m_where);
			END IF;
			hh := 'cache.filtercache_' || hh;
			h := array_append(h, hh);
		END LOOP;        
        RETURN xmlelement(name "vo:logistics",
                xmlconcat(
						(SELECT xmlagg(xmlelement(name "logistics:middleware", xmlattributes(t.name as "text", t.count as "count", t.id::text::text::text as "id"))) FROM count_vo_matches('middleware', h, isPrivateJoin(fl[ARRAY_LENGTH(fl, 1)], 'middleware')) as t(name TEXT, count bigint, id text)),                       
						(SELECT xmlagg(xmlelement(name "logistics:discipline", xmlattributes(t.name as "text", t.count as "count", t.id::text::text as "id"))) FROM count_vo_matches('discipline', h, isPrivateJoin(fl[ARRAY_LENGTH(fl, 1)], 'discipline')) as t(name TEXT, count bigint, id text)),
                        (SELECT xmlagg(xmlelement(name "logistics:scope", xmlattributes(t.name as "text", t.count as "count", t.id::text::text::text as "id"))) FROM count_vo_matches('scope', h) as t(name TEXT, count bigint, id text)),
						(SELECT xmlagg(xmlelement(name "logistics:storetype", xmlattributes(t.name as "text", t.count as "count", t.id::text::text::text as "id"))) FROM count_vo_matches('storetype', h) as t(name TEXT, count bigint, id text)),
						(SELECT xmlagg(xmlelement(name "logistics:phonebook", xmlattributes(t.name as "text", t.count as "count", t.id::text::text::text as "id"))) FROM 
(
WITH c AS (SELECT * FROM cached_ids(h) AS id)
SELECT l AS "name", COUNT(DISTINCT vos.id) AS count, n::text AS id FROM 
(
WITH RECURSIVE t(n) AS (
	VALUES (1)
	UNION ALL
	SELECT n+1 FROM t WHERE n < 28
)
SELECT 
CASE 
WHEN n<=26 THEN 
	SUBSTRING('ABCDEFGHIJKLMNOPQRSTUVWXYZ',n,1)
WHEN n=27 THEN 
	'0-9'
ELSE 
	'#'
END AS l,
CASE 
WHEN n<=26 THEN 
	'^' || SUBSTRING('ABCDEFGHIJKLMNOPQRSTUVWXYZ',n,1) || '.+'
WHEN n=27 THEN 
	'^[0-9].+'
ELSE 
	'^[^A-Za-z0-9].+'
END AS p,
n
FROM t) AS q
INNER JOIN vos ON vos.name ~* p
WHERE vos.id::text IN (SELECT id FROM c)
GROUP BY l, n
ORDER BY n
) AS t
)
                )
        );
END;
$$;


ALTER FUNCTION public.vo_logistics(m_fltstr text, m_from text, m_where text) OWNER TO appdb;

--
-- Name: vo_to_xml(integer[]); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION vo_to_xml(mid integer[]) RETURNS SETOF xml
    LANGUAGE plpgsql
    AS $$
BEGIN
	IF NOT EXISTS (SELECT * FROM vos WHERE id = ANY(mid)) THEN
		RETURN QUERY SELECT NULL::xml FROM vos WHERE FALSE;
	END IF;
	RETURN QUERY 
	WITH vos AS (SELECT DISTINCT (vos.replace_vo_dupe).*, vos.logoid FROM vos WHERE vos.id = ANY(mid))
	SELECT 
		xmlelement(
			name "vo:vo", 
			xmlattributes(
				v.id as id, 
				v.name as name, 
				v.alias as alias,
				v.status as status,
				v.scope as scope,
				v.validated as "validatedOn",
				d."name" as discipline,
				v.sourceid as sourceid,
				v.logoid as logoid
			),
			discipline_to_xml(disciplineid),
			v.description
		) 
	FROM 
		vos AS v
		LEFT OUTER JOIN domains as d ON d.id = v.domainid
	ORDER BY 
		idx(mid, v.id);
END;
$$;


ALTER FUNCTION public.vo_to_xml(mid integer[]) OWNER TO appdb;

--
-- Name: vo_to_xml(integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION vo_to_xml(mid integer) RETURNS xml
    LANGUAGE sql
    AS $_$SELECT vo_to_xml(ARRAY[$1])$_$;


ALTER FUNCTION public.vo_to_xml(mid integer) OWNER TO appdb;

--
-- Name: vo_to_xml_ext(integer[]); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION vo_to_xml_ext(mid integer[]) RETURNS SETOF xml
    LANGUAGE plpgsql
    AS $$
BEGIN
	RETURN QUERY
	SELECT x::xml FROM (SELECT DISTINCT vo_to_xml_ext(t.id)::text AS x FROM (SELECT UNNEST(mid) AS id) AS t) AS tt;
END;
$$;


ALTER FUNCTION public.vo_to_xml_ext(mid integer[]) OWNER TO appdb;

--
-- Name: vo_to_xml_ext(integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION vo_to_xml_ext(mid integer) RETURNS SETOF xml
    LANGUAGE plpgsql
    AS $_$
BEGIN
	RETURN QUERY 
		WITH vos AS ( SELECT DISTINCT (vos.replace_vo_dupe).* FROM vos WHERE vos.id = $1 )
		SELECT
		xmlelement(
			name "vo:vo", 
			xmlattributes(
				v.id as id, 
				v.name as name, 
				v.alias as alias,
				v.status as status,
				v.scope as scope,
				v.validated as "validatedOn",
				d."name" as discipline,
				v.sourceid as sourceid
			),
			CASE WHEN TRIM(COALESCE(v.homepage, '')) <> '' THEN
			xmlelement(
				name "vo:url",
				xmlattributes(
					'homepage' as "type"
				),
				v.homepage
			) END,
			CASE WHEN TRIM(COALESCE(v.enrollment, '')) <> '' THEN
			xmlelement(
				name "vo:url",
				xmlattributes(
					'enrollment' as "type"
				),
				v.enrollment
			) END,
			CASE WHEN TRIM(COALESCE(v.aup, '')) <> '' THEN
			xmlelement(
				name "vo:aup",
				v.aup
			) END,
			xmlelement(
				name "vo:description",
				v.description
			),
			CASE WHEN COUNT(res.*) > 0 THEN
			array_to_string(array_agg(DISTINCT
				xmlelement(
					name "vo:resource",
					xmlattributes(
						res.name as "type"
					),
					res.value
				)::text
			), '')::xml END,
			CASE WHEN COUNT(con.*) > 0 THEN
			array_to_string(array_agg(DISTINCT
				xmlelement(
					name "vo:contact",
					xmlattributes(
						con.role AS "role",
						con.name AS "name",
						array_to_string(con.email, ', ') AS "email",
						CASE WHEN con.researcherid IS NULL THEN
							'external'
						ELSE
							'internal'
						END as "type",
						con.researcherid AS id,
						con.cname AS cname
					)/*,
					CASE WHEN NOT con.researcherid IS NULL THEN
						researcher_to_xml(con.researcherid::int)
					END*/
				)::text
			), '')::xml END,
			CASE WHEN COUNT(vomses.*) > 0 THEN
			array_to_string(array_agg(DISTINCT
				xmlelement(
					name "vo:voms",
					xmlattributes(
						vomses.hostname,
						vomses.https_port,
						vomses.vomses_port AS "voms_port",
						vomses.is_admin AS "admin"						
					),
					vomses.member_list_url
				)::text
			 ), '')::xml END,
			vowide_image_list_to_xml(v.id),
			discipline_to_xml(disciplineid)
		)
	FROM 
		vos AS v		
		LEFT OUTER JOIN domains as d ON d.id = v.domainid
		LEFT OUTER JOIN vo_resources AS res ON res.void = v.id
		LEFT OUTER JOIN vo_contacts AS con ON con.void = v.id
		LEFT OUTER JOIN vomses ON vomses.void = v.id
	GROUP BY
		v.id,
		v.name,
		v.description,
		v.scope,
		v.alias,
		v.validated,
		v.aup,
		v.homepage,
		v.enrollment,
		d.name,
		v.sourceid,
		v.status,
		v.disciplineid
	;

END;
$_$;


ALTER FUNCTION public.vo_to_xml_ext(mid integer) OWNER TO appdb;

--
-- Name: vodiscisleaf(integer, integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION vodiscisleaf(did integer, vid integer) RETURNS boolean
    LANGUAGE plpgsql STABLE
    AS $$
BEGIN
	--RETURN EXISTS (SELECT * FROM vos WHERE id = vid AND (SELECT parentid FROM disciplines WHERE id = did) = ANY(vos.disciplineid));
	RETURN NOT EXISTS (SELECT id FROM disciplines WHERE parentid = did INTERSECT SELECT UNNEST(disciplineid) FROM vos WHERE id = vid);
END
$$;


ALTER FUNCTION public.vodiscisleaf(did integer, vid integer) OWNER TO appdb;

--
-- Name: vowide_image_list_to_xml(integer); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION vowide_image_list_to_xml(void integer) RETURNS xml
    LANGUAGE sql
    AS $_$
	WITH images AS (
		SELECT DISTINCT ON (image::text, imagelistid) imagelistid, image FROM (
		SELECT 
			vowide_image_list_images.vowide_image_list_id AS imagelistid,
			xmlagg(
				xmlelement(
					name "vo:image",
					xmlattributes(				
						vowide_image_list_images.id AS id,
						vowide_image_list_images.state::TEXT AS state,
						vowide_image_list_images.guid AS guid,
						vappversionid AS va_versionid,
						va_version AS va_version,
						va_version_expireson AS expireson,
						vmiinstanceid AS vmiinstanceid,
						applications.name AS name,
						applications.cname AS cname,
						applications.id AS appid,
						CASE applications.deleted OR applications.moderated 
						WHEN TRUE THEN 
							CASE applications.deleted 
							WHEN TRUE THEN 'deleted' 
							ELSE 'moderated' 
							END 
						ELSE NULL 
						END AS app_state
					)
				)
			) AS image
		FROM vowide_image_list_images
		INNER JOIN vaviews ON vaviews.vapplistid = vowide_image_list_images.vapplistid
		INNER JOIN applications ON applications.id = vaviews.appid
		GROUP BY vowide_image_list_images.vowide_image_list_id
		) AS __images
	)
	SELECT array_to_string(array_agg(imagelist::text), '')::xml AS imagelist FROM (
	SELECT 
		array_to_string(array_agg(DISTINCT
		xmlelement(
			name "vo:imagelist",
			xmlattributes(
				vowide_image_lists.id AS id,
				vowide_image_lists.guid AS guid,
				vowide_image_lists.state::TEXT AS state,
				vowide_image_lists.published_on AS publishedOn,
				vowide_image_lists.expires_on AS expiresOn,
				vowide_image_lists.notes AS notes,
				vowide_image_lists.title AS title
			),
			images.image
		)::text
	), '')::xml
	AS imagelist
	FROM vowide_image_lists
	INNER JOIN images ON images.imagelistid = vowide_image_lists.id
	WHERE void = $1
	GROUP BY vowide_image_lists.id 
	) as t
$_$;


ALTER FUNCTION public.vowide_image_list_to_xml(void integer) OWNER TO appdb;

--
-- Name: word_count(text, text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION word_count(hay text, needle text) RETURNS integer
    LANGUAGE plpgsql
    AS $$
BEGIN
	RETURN (LENGTH(hay) - LENGTH(REGEXP_REPLACE(hay, needle, '', 'ig'))) / LENGTH(needle);
END;
$$;


ALTER FUNCTION public.word_count(hay text, needle text) OWNER TO appdb;

--
-- Name: xml_encode_special_chars(text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION xml_encode_special_chars(text) RETURNS text
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/pgxml', 'xml_encode_special_chars';


ALTER FUNCTION public.xml_encode_special_chars(text) OWNER TO appdb;

--
-- Name: xml_is_well_formed(text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION xml_is_well_formed(text) RETURNS boolean
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/pgxml', 'xml_is_well_formed';


ALTER FUNCTION public.xml_is_well_formed(text) OWNER TO appdb;

--
-- Name: xml_valid(text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION xml_valid(text) RETURNS boolean
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/pgxml', 'xml_is_well_formed';


ALTER FUNCTION public.xml_valid(text) OWNER TO appdb;

--
-- Name: xpath_bool(text, text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION xpath_bool(text, text) RETURNS boolean
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/pgxml', 'xpath_bool';


ALTER FUNCTION public.xpath_bool(text, text) OWNER TO appdb;

--
-- Name: xpath_list(text, text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION xpath_list(text, text) RETURNS text
    LANGUAGE sql IMMUTABLE STRICT
    AS $_$SELECT xpath_list($1,$2,',')$_$;


ALTER FUNCTION public.xpath_list(text, text) OWNER TO appdb;

--
-- Name: xpath_list(text, text, text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION xpath_list(text, text, text) RETURNS text
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/pgxml', 'xpath_list';


ALTER FUNCTION public.xpath_list(text, text, text) OWNER TO appdb;

--
-- Name: xpath_nodeset(text, text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION xpath_nodeset(text, text) RETURNS text
    LANGUAGE sql IMMUTABLE STRICT
    AS $_$SELECT xpath_nodeset($1,$2,'','')$_$;


ALTER FUNCTION public.xpath_nodeset(text, text) OWNER TO appdb;

--
-- Name: xpath_nodeset(text, text, text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION xpath_nodeset(text, text, text) RETURNS text
    LANGUAGE sql IMMUTABLE STRICT
    AS $_$SELECT xpath_nodeset($1,$2,'',$3)$_$;


ALTER FUNCTION public.xpath_nodeset(text, text, text) OWNER TO appdb;

--
-- Name: xpath_nodeset(text, text, text, text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION xpath_nodeset(text, text, text, text) RETURNS text
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/pgxml', 'xpath_nodeset';


ALTER FUNCTION public.xpath_nodeset(text, text, text, text) OWNER TO appdb;

--
-- Name: xpath_number(text, text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION xpath_number(text, text) RETURNS real
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/pgxml', 'xpath_number';


ALTER FUNCTION public.xpath_number(text, text) OWNER TO appdb;

--
-- Name: xpath_string(text, text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION xpath_string(text, text) RETURNS text
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/pgxml', 'xpath_string';


ALTER FUNCTION public.xpath_string(text, text) OWNER TO appdb;

--
-- Name: xpath_table(text, text, text, text, text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION xpath_table(text, text, text, text, text) RETURNS SETOF record
    LANGUAGE c STABLE STRICT
    AS '$libdir/pgxml', 'xpath_table';


ALTER FUNCTION public.xpath_table(text, text, text, text, text) OWNER TO appdb;

--
-- Name: xslt_process(text, text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION xslt_process(text, text) RETURNS text
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/pgxml', 'xslt_process';


ALTER FUNCTION public.xslt_process(text, text) OWNER TO appdb;

--
-- Name: xslt_process(text, text, text); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION xslt_process(text, text, text) RETURNS text
    LANGUAGE c STRICT
    AS '$libdir/pgxml', 'xslt_process';


ALTER FUNCTION public.xslt_process(text, text, text) OWNER TO appdb;

--
-- Name: zerorec(text, text[], integer[]); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION zerorec(tbl text, cols text[], vals integer[]) RETURNS text
    LANGUAGE plpgsql
    AS $$
BEGIN
	RETURN zerorec(tbl, cols, vals::text[]);
END;
$$;


ALTER FUNCTION public.zerorec(tbl text, cols text[], vals integer[]) OWNER TO appdb;

--
-- Name: zerorec(text, text[], text[]); Type: FUNCTION; Schema: public; Owner: appdb
--

CREATE FUNCTION zerorec(tbl text, cols text[] DEFAULT NULL::text[], vals text[] DEFAULT NULL::text[]) RETURNS text
    LANGUAGE plpgsql
    AS $$
DECLARE i RECORD;
DECLARE s text[];
DECLARE ss text;
DECLARE j INT;
BEGIN
	s := NULL::text[];
	FOR i IN SELECT column_name, data_type AS dt, REGEXP_REPLACE(udt_name,'^_','') AS data_type FROM INFORMATION_SCHEMA.columns WHERE table_name = tbl ORDER BY ordinal_position ASC LOOP
		ss := NULL;
		IF NOT cols IS NULL THEN
			FOR j IN 1..ARRAY_LENGTH(cols,1) LOOP
				IF i.column_name = cols[j] THEN
					ss := vals[j];
				END IF;
			END LOOP;
		END IF;
		IF ss IS NULL THEN
			CASE 
			WHEN i.data_type LIKE 'int%' THEN
				ss := '0';
			WHEN i.data_type LIKE 'float%' THEN
				ss := '0.0';
			WHEN i.data_type = 'text' OR i.data_type = 'bytea' OR i.data_type LIKE '%char%' THEN
				ss := '''''';
			WHEN i.data_type = 'bool' OR i.data_type LIKE '%time%' OR i.data_type LIKE '%date%' OR i.data_type = 'uuid' THEN
				ss := '';
			ELSE
				RAISE NOTICE 'Warning: Unknown postgresql data type % for column % encountered in function "zerorec"', i.data_type, tbl;
				ss := '';
			END CASE;
			IF i.dt = 'ARRAY' THEN ss := 'ARRAY[' || ss || ']'; END IF;
		END IF;
		s := array_append(s, ss);
	END LOOP;
	RETURN '(' || array_to_string(s, ',') || ')';
END;
$$;


ALTER FUNCTION public.zerorec(tbl text, cols text[], vals text[]) OWNER TO appdb;

SET search_path = researchers, pg_catalog;

--
-- Name: any(integer); Type: FUNCTION; Schema: researchers; Owner: appdb
--

CREATE FUNCTION "any"(mid integer) RETURNS text
    LANGUAGE sql STABLE
    AS $_$SELECT public.any('researchers', $1);$_$;


ALTER FUNCTION researchers."any"(mid integer) OWNER TO appdb;

--
-- Name: substring(text, integer, integer); Type: FUNCTION; Schema: researchers; Owner: appdb
--

CREATE FUNCTION "substring"(text, integer, integer) RETURNS text
    LANGUAGE sql IMMUTABLE
    AS $_$SELECT SUBSTRING($1, $2, $3);$_$;


ALTER FUNCTION researchers."substring"(text, integer, integer) OWNER TO appdb;

SET search_path = sci_class, pg_catalog;

--
-- Name: _clone(text, text); Type: FUNCTION; Schema: sci_class; Owner: appdb
--

CREATE FUNCTION _clone(oldver text, newver text) RETURNS boolean
    LANGUAGE plpgsql
    AS $$
DECLARE i RECORD;
BEGIN
	IF EXISTS (SELECT * FROM sci_class.cverids WHERE version = newver) THEN
		RAISE NOTICE 'Cannot clone into an existing version. Action aborted';
		RETURN FALSE;
	
	END IF;
	INSERT INTO sci_class.cverids (version) VALUES (newver);
	FOR i IN 
		SELECT 
			cprops.* 
		FROM 
			sci_class.cvers
			INNER JOIN sci_class.cprops ON cprops.id = cvers.cpropid
		WHERE 
			version = oldver
	LOOP
		PERFORM 
			sci_class.setprop(newver, i.cid, i.cpropid, i.val) 
		FROM
			sci_class.cvers
			INNER JOIN sci_class.cprops ON cprops.id = cvers.cpropid
		WHERE 
			sci_class.cvers.version = oldver;
	END LOOP;
	RETURN TRUE;
END;
$$;


ALTER FUNCTION sci_class._clone(oldver text, newver text) OWNER TO appdb;

--
-- Name: clone(text, text); Type: FUNCTION; Schema: sci_class; Owner: appdb
--

CREATE FUNCTION clone(oldver text, newver text) RETURNS boolean
    LANGUAGE plpgsql
    AS $$
DECLARE i RECORD;
BEGIN
	IF EXISTS (SELECT * FROM sci_class.cverids WHERE version = newver) THEN
		RAISE NOTICE 'Cannot clone into an existing version. Action aborted';
		RETURN FALSE;
	
	END IF;
	INSERT INTO sci_class.cverids (version) VALUES (newver);
	INSERT INTO sci_class.cvers (version, cpropid) SELECT newver, cpropid FROM sci_class.cvers WHERE version = oldver;
	RETURN TRUE;
END;
$$;


ALTER FUNCTION sci_class.clone(oldver text, newver text) OWNER TO appdb;

--
-- Name: getprop(text, integer, integer); Type: FUNCTION; Schema: sci_class; Owner: appdb
--

CREATE FUNCTION getprop(ver text, _cid integer, _cpropid integer) RETURNS text
    LANGUAGE sql
    AS $_$SELECT 
	sci_class.cprops.val 
FROM 
	sci_class.cvers
	INNER JOIN sci_class.cprops ON cprops.id = cvers.cpropid
	INNER JOIN sci_class.cids ON cids.id = cprops.cid
WHERE 
	sci_class.cvers.version=$1
	AND sci_class.cids.id = $2
	AND sci_class.cprops.cpropid = $3$_$;


ALTER FUNCTION sci_class.getprop(ver text, _cid integer, _cpropid integer) OWNER TO appdb;

--
-- Name: getprop(text, integer, text); Type: FUNCTION; Schema: sci_class; Owner: appdb
--

CREATE FUNCTION getprop(ver text, _cid integer, _cpropid text) RETURNS text
    LANGUAGE sql
    AS $_$SELECT sci_class.getprop($1, $2, (SELECT id FROM sci_class.cpropids WHERE name = $3))$_$;


ALTER FUNCTION sci_class.getprop(ver text, _cid integer, _cpropid text) OWNER TO appdb;

--
-- Name: import(text, integer, integer); Type: FUNCTION; Schema: sci_class; Owner: appdb
--

CREATE FUNCTION import(ver text, _parentid integer DEFAULT NULL::integer, _newid integer DEFAULT NULL::integer) RETURNS void
    LANGUAGE plpgsql
    AS $$
DECLARE i RECORD;
DECLARE j RECORD;
DECLARE new_cid INT;
DECLARE new_cpropid INT;
BEGIN	
	IF _parentid IS NULL THEN
		FOR i IN SELECT * FROM disciplines WHERE parentid IS NULL LOOP
			INSERT INTO sci_class.cids (id) VALUES (nextval('sci_class.cids_id_seq')) RETURNING id INTO new_cid;
			INSERT INTO sci_class.cprops (cid, cpropid, val) VALUES (new_cid, (SELECT id FROM sci_class.cpropids WHERE name = 'name'), i.name) RETURNING id INTO new_cpropid;
			INSERT INTO sci_class.cvers (version, cpropid) VALUES (ver, new_cpropid);
			INSERT INTO sci_class.cprops (cid, cpropid, val) VALUES (new_cid, (SELECT id FROM sci_class.cpropids WHERE name = 'order'), i.ord) RETURNING id INTO new_cpropid;
			INSERT INTO sci_class.cvers (version, cpropid) VALUES (ver, new_cpropid);
			PERFORM sci_class.import(ver, i.id, new_cid);
		END LOOP;
	ELSE
		FOR i IN SELECT * FROM disciplines WHERE parentid = _parentid LOOP
			INSERT INTO sci_class.cids (id) VALUES (nextval('sci_class.cids_id_seq')) RETURNING id INTO new_cid;
			INSERT INTO sci_class.cprops (cid, cpropid, val) VALUES (new_cid, (SELECT id FROM sci_class.cpropids WHERE name = 'name'), i.name) RETURNING id INTO new_cpropid;	
			INSERT INTO sci_class.cvers (version, cpropid) VALUES (ver, new_cpropid);
			INSERT INTO sci_class.cprops (cid, cpropid, val) VALUES (new_cid, (SELECT id FROM sci_class.cpropids WHERE name = 'order'), i.ord) RETURNING id INTO new_cpropid;
			INSERT INTO sci_class.cvers (version, cpropid) VALUES (ver, new_cpropid);			
			INSERT INTO sci_class.cprops (cid, cpropid, val) VALUES (new_cid, (SELECT id FROM sci_class.cpropids WHERE name = 'parentid'), _newid) RETURNING id INTO new_cpropid;
			INSERT INTO sci_class.cvers (version, cpropid) VALUES (ver, new_cpropid);
			PERFORM sci_class.import(ver, i.id, new_cid);
		END LOOP;
	END IF;
END;
$$;


ALTER FUNCTION sci_class.import(ver text, _parentid integer, _newid integer) OWNER TO appdb;

--
-- Name: setprop(text, integer, integer, text); Type: FUNCTION; Schema: sci_class; Owner: appdb
--

CREATE FUNCTION setprop(ver text, _cid integer, _cpropid integer, _val text) RETURNS integer
    LANGUAGE plpgsql
    AS $_$
DECLARE _pid INT;
DECLARE _oldpid INT;
DECLARE pcount INT;
BEGIN
	_pid := (SELECT 
		sci_class.cprops.id
	FROM 
		sci_class.cvers
		INNER JOIN sci_class.cprops ON cprops.id = cvers.cpropid
		INNER JOIN sci_class.cids ON cids.id = cprops.cid
	WHERE 
		sci_class.cvers.version=$1
		AND sci_class.cids.id = $2
		AND sci_class.cprops.cpropid = $3
	);
	IF NOT _pid IS NULL THEN
		pcount := (SELECT COUNT(*) FROM sci_class.cvers WHERE cpropid = _pid);
		IF pcount = 1 THEN
			-- modify existing non-shared property value
			UPDATE sci_class.cprops SET val = _val WHERE id = _pid;
		ELSE
			-- create a new or link to an existing older version of a property value since existing is shared
			_oldpid := (SELECT id FROM sci_class.cprops WHERE cid = _cid AND cpropid = _cpropid AND val = _val);
			IF _oldpid IS NULL THEN
				-- create a new property value				
				DELETE FROM sci_class.cvers WHERE cpropid = _pid AND version = ver;								
				INSERT INTO sci_class.cprops (cid, cpropid, val) VALUES (_cid, _cpropid, _val) RETURNING id INTO _pid;				
				INSERT INTO sci_class.cvers (version, cpropid) VALUES (ver, _pid);
			ELSE
				-- link to existing older version of property value
				DELETE FROM sci_class.cvers WHERE cpropid = _pid AND version = ver;
				INSERT INTO sci_class.cvers (version, cpropid) VALUES (ver, _oldpid);
				_pid := _oldpid;
			END IF;
		END IF;
	ELSE
		IF _val IS NULL THEN
			DELETE FROM sci_class.cvers WHERE cpropid = _pid AND version = ver;
			-- a trigger should take care of deleting orphaned cprops as well
		ELSE
			-- create a new or link to an existing older version of a property value since existing is shared
			_oldpid := (SELECT id FROM sci_class.cprops WHERE cid = _cid AND cpropid = _cpropid AND val = _val);
			IF _oldpid IS NULL THEN
				-- create a new property value
				INSERT INTO sci_class.cprops (cid, cpropid, val) VALUES (_cid, _cpropid, _val) RETURNING id INTO _pid;
				INSERT INTO sci_class.cvers (version, cpropid) VALUES (ver, _pid);
			ELSE
				-- link to existing older version of property value
				DELETE FROM sci_class.cvers WHERE cpropid = _pid AND version = ver;
				INSERT INTO sci_class.cvers (version, cpropid) VALUES (ver, _oldpid);
				_pid := _oldpid;
			END IF;
		END IF;
	END IF;
	RETURN _pid;
END;
$_$;


ALTER FUNCTION sci_class.setprop(ver text, _cid integer, _cpropid integer, _val text) OWNER TO appdb;

--
-- Name: setprop(text, integer, text, text); Type: FUNCTION; Schema: sci_class; Owner: appdb
--

CREATE FUNCTION setprop(ver text, _cid integer, _cpropid text, _val text) RETURNS integer
    LANGUAGE sql
    AS $_$SELECT sci_class.setprop($1, $2, (SELECT id FROM sci_class.cpropids WHERE name = $3), $4)$_$;


ALTER FUNCTION sci_class.setprop(ver text, _cid integer, _cpropid text, _val text) OWNER TO appdb;

--
-- Name: toxml(integer); Type: FUNCTION; Schema: sci_class; Owner: appdb
--

CREATE FUNCTION toxml(id integer) RETURNS SETOF xml
    LANGUAGE sql
    AS $_$SELECT sci_class.toxml(version) FROM sci_class.cverids AS v WHERE v.id = $1;$_$;


ALTER FUNCTION sci_class.toxml(id integer) OWNER TO appdb;

--
-- Name: toxml(text); Type: FUNCTION; Schema: sci_class; Owner: appdb
--

CREATE FUNCTION toxml(ver text) RETURNS SETOF xml
    LANGUAGE plpgsql
    AS $$
BEGIN
	RETURN QUERY
	WITH discs AS (SELECT /*DISTINCT ON (id)*/ sci_class.toxml(ver, id) as x, id FROM sci_class.cids)
	SELECT 
		xmlelement(
			name "classification:version",
			xmlattributes(
				cverids.id,
				cverids.version,
				cverids.createdon,
				cverids.publishedon,
				cverids.archivedon,
				cverids.state
			)
		)
	FROM 
		sci_class.cverids
	WHERE 
		cverids.version = ver;
END;
$$;


ALTER FUNCTION sci_class.toxml(ver text) OWNER TO appdb;

--
-- Name: toxml(text, integer); Type: FUNCTION; Schema: sci_class; Owner: appdb
--

CREATE FUNCTION toxml(ver text, _cid integer) RETURNS SETOF xml
    LANGUAGE plpgsql
    AS $$
BEGIN
	RETURN QUERY
	SELECT /*DISTINCT ON (cid)*/ xmlelement(
		name "classification:discipline",
		xmlattributes(
			cid AS id,
			sci_class.getprop(ver, _cid, 2) AS parentid,
			sci_class.getprop(ver, _cid, 3) AS "order", 
			sci_class.getprop(ver, _cid, 1) AS "value"
		)
	)
	FROM
		sci_class.cvers
		INNER JOIN sci_class.cprops ON cprops.id = cvers.cpropid
		INNER JOIN sci_class.cids ON cids.id = cprops.cid
	WHERE 
		sci_class.cvers.version = ver
		AND sci_class.cids.id = _cid;
END;
$$;


ALTER FUNCTION sci_class.toxml(ver text, _cid integer) OWNER TO appdb;

--
-- Name: toxmlext(integer); Type: FUNCTION; Schema: sci_class; Owner: appdb
--

CREATE FUNCTION toxmlext(id integer) RETURNS SETOF xml
    LANGUAGE sql
    AS $_$SELECT sci_class.toxmlext(version) FROM sci_class.cverids AS v WHERE v.id = $1;$_$;


ALTER FUNCTION sci_class.toxmlext(id integer) OWNER TO appdb;

--
-- Name: toxmlext(text); Type: FUNCTION; Schema: sci_class; Owner: appdb
--

CREATE FUNCTION toxmlext(ver text) RETURNS SETOF xml
    LANGUAGE plpgsql
    AS $$
BEGIN
	RETURN QUERY
	WITH discs AS (SELECT /*DISTINCT ON (id)*/ sci_class.toxml(ver, id) as x, id FROM sci_class.cids)
	SELECT 
		xmlelement(
			name "classification:version",
			xmlattributes(
				cverids.id,
				cverids.version,
				cverids.createdon,
				cverids.publishedon,
				cverids.archivedon,
				cverids.state
			),
			array_to_string(array_agg(DISTINCT discs.x::TEXT), '')::XML
		)
	FROM 
		sci_class.cverids
		INNER JOIN sci_class.cvers ON cvers.version = cverids.version
		INNER JOIN sci_class.cprops ON cprops.id = cvers.cpropid
		INNER JOIN sci_class.cids ON cids.id = cprops.cid
		INNER JOIN discs ON discs.id = cids.id
	WHERE 
		cverids.version = ver
	GROUP BY 
		cverids.id,
		cverids.version
	;
END;
$$;


ALTER FUNCTION sci_class.toxmlext(ver text) OWNER TO appdb;

--
-- Name: trfn_cprops_delete_orphans(); Type: FUNCTION; Schema: sci_class; Owner: appdb
--

CREATE FUNCTION trfn_cprops_delete_orphans() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
	DELETE FROM cids WHERE id = OLD.cid AND id NOT IN (SELECT cid FROM cprops);
	RETURN OLD;
END;
$$;


ALTER FUNCTION sci_class.trfn_cprops_delete_orphans() OWNER TO appdb;

--
-- Name: trfn_cvers_delete_orphans(); Type: FUNCTION; Schema: sci_class; Owner: appdb
--

CREATE FUNCTION trfn_cvers_delete_orphans() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
	DELETE FROM sci_class.cprops WHERE id = OLD.cpropid AND id NOT IN (SELECT cpropid FROM sci_class.cvers);
	RETURN OLD;
END;
$$;


ALTER FUNCTION sci_class.trfn_cvers_delete_orphans() OWNER TO appdb;

SET search_path = sites, pg_catalog;

--
-- Name: any(text); Type: FUNCTION; Schema: sites; Owner: appdb
--

CREATE FUNCTION "any"(mid text) RETURNS text
    LANGUAGE sql STABLE
    AS $_$SELECT public.any('sites', $1);$_$;


ALTER FUNCTION sites."any"(mid text) OWNER TO appdb;

SET search_path = stats, pg_catalog;

--
-- Name: make_all_stats(); Type: FUNCTION; Schema: stats; Owner: appdb
--

CREATE FUNCTION make_all_stats() RETURNS void
    LANGUAGE plpgsql
    AS $$
BEGIN
	PERFORM stats.make_store_stats();
	PERFORM stats.make_app_disc_stats();
	PERFORM stats.make_app_cat_stats();
	PERFORM stats.make_vo_disc_stats();
	PERFORM stats.make_app_vo_stats();
	PERFORM stats.make_app_vo_cat_disc_history();
END;
$$;


ALTER FUNCTION stats.make_all_stats() OWNER TO appdb;

--
-- Name: make_app_cat_stats(); Type: FUNCTION; Schema: stats; Owner: appdb
--

CREATE FUNCTION make_app_cat_stats() RETURNS void
    LANGUAGE plpgsql
    AS $$
BEGIN
	DELETE FROM stats.app_cat_stats WHERE theday = NOW()::date;
	INSERT INTO stats.app_cat_stats (metatype, cnt, categoryid) 
		SELECT applications.metatype, COUNT(*), categories.id
		FROM categories
		INNER JOIN applications ON categories.id = ANY(applications.categoryid)
		WHERE NOT applications.deleted
		GROUP BY applications.metatype, categories.id;
END;
$$;


ALTER FUNCTION stats.make_app_cat_stats() OWNER TO appdb;

--
-- Name: make_app_disc_stats(); Type: FUNCTION; Schema: stats; Owner: appdb
--

CREATE FUNCTION make_app_disc_stats() RETURNS void
    LANGUAGE plpgsql
    AS $$
BEGIN
	DELETE FROM stats.app_disc_stats WHERE theday = NOW()::date;
	INSERT INTO stats.app_disc_stats (metatype, cnt, disciplineid) 
		SELECT applications.metatype, COUNT(*), disciplines.id
		FROM disciplines
		INNER JOIN applications ON disciplines.id = ANY(applications.disciplineid)
		WHERE NOT applications.deleted
		GROUP BY applications.metatype, disciplines.id;
END;
$$;


ALTER FUNCTION stats.make_app_disc_stats() OWNER TO appdb;

--
-- Name: make_app_vo_cat_disc_history(); Type: FUNCTION; Schema: stats; Owner: appdb
--

CREATE FUNCTION make_app_vo_cat_disc_history() RETURNS void
    LANGUAGE plpgsql
    AS $$
BEGIN
	DELETE FROM stats.app_vo_cat_disc_history WHERE theday = NOW()::date;
	INSERT INTO stats.app_vo_cat_disc_history (metatype, appid, void, disciplineid, categoryid)
		SELECT applications.metatype, applications.id, ARRAY_AGG(app_vos.void), applications.disciplineid, applications.categoryid
		FROM applications
		LEFT OUTER JOIN app_vos ON app_vos.appid = applications.id
		LEFT OUTER JOIN vos ON app_vos.void = vos.id
		WHERE NOT (applications.deleted OR applications.moderated) AND NOT vos.deleted IS FALSE
		GROUP BY applications.id, applications.metatype, applications.disciplineid, applications.categoryid;
END;
$$;


ALTER FUNCTION stats.make_app_vo_cat_disc_history() OWNER TO appdb;

--
-- Name: make_app_vo_stats(); Type: FUNCTION; Schema: stats; Owner: appdb
--

CREATE FUNCTION make_app_vo_stats() RETURNS void
    LANGUAGE plpgsql
    AS $$
BEGIN
	DELETE FROM stats.app_vo_stats WHERE theday = NOW()::date;
	INSERT INTO stats.app_vo_stats (metatype, cnt, void)
		SELECT applications.metatype, COUNT(*), vos.id
		FROM vos
		INNER JOIN app_vos ON app_vos.void = vos.id
		INNER JOIN applications ON applications.id = app_vos.appid
		WHERE NOT applications.deleted AND NOT vos.deleted
		GROUP BY vos.id, applications.metatype;
END;
$$;


ALTER FUNCTION stats.make_app_vo_stats() OWNER TO appdb;

--
-- Name: make_store_stats(); Type: FUNCTION; Schema: stats; Owner: appdb
--

CREATE FUNCTION make_store_stats() RETURNS void
    LANGUAGE plpgsql
    AS $$
BEGIN
DELETE FROM stats.storestats WHERE theday = NOW()::date;
INSERT INTO stats.storestats (what, cnt)
	SELECT CASE metatype WHEN 0 THEN 'app' WHEN 1 THEN 'va' WHEN 2 THEN 'sa'END, COUNT(*) FROM applications WHERE NOT (deleted OR moderated) GROUP BY metatype
UNION ALL
	SELECT 'ppl', COUNT(*) FROM researchers WHERE NOT deleted
UNION ALL
	SELECT 'vo', COUNT(*) FROM vos WHERE NOT deleted
UNION ALL
	SELECT 'vap', COUNT(*) FROM va_providers
UNION ALL
	SELECT 'site', COUNT(*) FROM sites WHERE NOT deleted
UNION ALL
	SELECT 'ds', COUNT(*) FROM datasets
UNION ALL
	SELECT 'dsr', COUNT(*) FROM dataset_locations WHERE NOT is_master;
END;
$$;


ALTER FUNCTION stats.make_store_stats() OWNER TO appdb;

--
-- Name: make_vo_disc_stats(); Type: FUNCTION; Schema: stats; Owner: appdb
--

CREATE FUNCTION make_vo_disc_stats() RETURNS void
    LANGUAGE plpgsql
    AS $$
BEGIN
	DELETE FROM stats.vo_disc_stats WHERE theday = NOW()::date;
	INSERT INTO stats.vo_disc_stats (cnt, disciplineid) 
		SELECT COUNT(*), disciplines.id
		FROM disciplines
		INNER JOIN vos ON disciplines.id = ANY(vos.disciplineid)
		WHERE NOT vos.deleted
		GROUP BY disciplines.id;
END;
$$;


ALTER FUNCTION stats.make_vo_disc_stats() OWNER TO appdb;

SET search_path = statuses, pg_catalog;

--
-- Name: any(integer); Type: FUNCTION; Schema: statuses; Owner: appdb
--

CREATE FUNCTION "any"(mid integer) RETURNS text
    LANGUAGE sql STABLE
    AS $_$SELECT public.any('statuses', $1);$_$;


ALTER FUNCTION statuses."any"(mid integer) OWNER TO appdb;

SET search_path = vos, pg_catalog;

--
-- Name: any(integer); Type: FUNCTION; Schema: vos; Owner: appdb
--

CREATE FUNCTION "any"(mid integer) RETURNS text
    LANGUAGE sql STABLE
    AS $_$SELECT public.any('vos', $1);$_$;


ALTER FUNCTION vos."any"(mid integer) OWNER TO appdb;

SET search_path = public, pg_catalog;

--
-- Name: array_accum(anyelement); Type: AGGREGATE; Schema: public; Owner: appdb
--

CREATE AGGREGATE array_accum(anyelement) (
    SFUNC = array_append,
    STYPE = anyarray,
    INITCOND = '{}'
);


ALTER AGGREGATE public.array_accum(anyelement) OWNER TO appdb;

--
-- Name: ~~~; Type: OPERATOR; Schema: public; Owner: appdb
--

CREATE OPERATOR ~~~ (
    PROCEDURE = fn_rlike,
    LEFTARG = text,
    RIGHTARG = text,
    COMMUTATOR = ~~
);


ALTER OPERATOR public.~~~ (text, text) OWNER TO appdb;

--
-- Name: ~~~@; Type: OPERATOR; Schema: public; Owner: appdb
--

CREATE OPERATOR ~~~@ (
    PROCEDURE = fn_rilike,
    LEFTARG = text,
    RIGHTARG = text,
    COMMUTATOR = ~~
);


ALTER OPERATOR public.~~~@ (text, text) OWNER TO appdb;

SET search_path = app_licenses, pg_catalog;

--
-- Name: any; Type: TABLE; Schema: app_licenses; Owner: appdb; Tablespace: 
--

CREATE TABLE "any" (
    id integer NOT NULL,
    "any" text
);


ALTER TABLE "any" OWNER TO appdb;

SET search_path = app_middlewares, pg_catalog;

--
-- Name: any; Type: TABLE; Schema: app_middlewares; Owner: appdb; Tablespace: 
--

CREATE TABLE "any" (
    id integer NOT NULL,
    "any" text
);


ALTER TABLE "any" OWNER TO appdb;

SET search_path = applications, pg_catalog;

--
-- Name: any; Type: TABLE; Schema: applications; Owner: appdb; Tablespace: 
--

CREATE TABLE "any" (
    id integer NOT NULL,
    "any" text
);


ALTER TABLE "any" OWNER TO appdb;

SET search_path = archs, pg_catalog;

--
-- Name: any; Type: TABLE; Schema: archs; Owner: appdb; Tablespace: 
--

CREATE TABLE "any" (
    id integer NOT NULL,
    "any" text
);


ALTER TABLE "any" OWNER TO appdb;

SET search_path = cache, pg_catalog;

--
-- Name: _filtercache_4a31d1fb959a6969a9689cf1287be48b; Type: TABLE; Schema: cache; Owner: appdb; Tablespace: 
--

CREATE TABLE _filtercache_4a31d1fb959a6969a9689cf1287be48b (
    app public.applications,
    rank integer
);


ALTER TABLE _filtercache_4a31d1fb959a6969a9689cf1287be48b OWNER TO appdb;

--
-- Name: appprivsxmlcache; Type: TABLE; Schema: cache; Owner: appdb; Tablespace: 
--

CREATE TABLE appprivsxmlcache (
    id bigint NOT NULL,
    appid integer NOT NULL,
    privs xml
);


ALTER TABLE appprivsxmlcache OWNER TO appdb;

--
-- Name: appprivsxmlcache_id_seq; Type: SEQUENCE; Schema: cache; Owner: appdb
--

CREATE SEQUENCE appprivsxmlcache_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE appprivsxmlcache_id_seq OWNER TO appdb;

--
-- Name: appprivsxmlcache_id_seq; Type: SEQUENCE OWNED BY; Schema: cache; Owner: appdb
--

ALTER SEQUENCE appprivsxmlcache_id_seq OWNED BY appprivsxmlcache.id;


--
-- Name: appxmlcache; Type: TABLE; Schema: cache; Owner: appdb; Tablespace: 
--

CREATE TABLE appxmlcache (
    id integer NOT NULL,
    xml xml
);


ALTER TABLE appxmlcache OWNER TO appdb;

--
-- Name: filtercache; Type: TABLE; Schema: cache; Owner: appdb; Tablespace: 
--

CREATE TABLE filtercache (
    hash text NOT NULL,
    m_from text,
    m_where text,
    m_when timestamp without time zone DEFAULT now() NOT NULL,
    fltstr text DEFAULT ''::text NOT NULL,
    usecount integer DEFAULT 0 NOT NULL,
    invalid boolean DEFAULT false NOT NULL
);


ALTER TABLE filtercache OWNER TO appdb;

SET search_path = categories, pg_catalog;

--
-- Name: any; Type: TABLE; Schema: categories; Owner: appdb; Tablespace: 
--

CREATE TABLE "any" (
    id integer NOT NULL,
    "any" text
);


ALTER TABLE "any" OWNER TO appdb;

SET search_path = contacts, pg_catalog;

--
-- Name: any; Type: TABLE; Schema: contacts; Owner: appdb; Tablespace: 
--

CREATE TABLE "any" (
    id integer NOT NULL,
    "any" text
);


ALTER TABLE "any" OWNER TO appdb;

SET search_path = contacttypes, pg_catalog;

--
-- Name: any; Type: TABLE; Schema: contacttypes; Owner: appdb; Tablespace: 
--

CREATE TABLE "any" (
    id integer NOT NULL,
    "any" text
);


ALTER TABLE "any" OWNER TO appdb;

SET search_path = countries, pg_catalog;

--
-- Name: any; Type: TABLE; Schema: countries; Owner: appdb; Tablespace: 
--

CREATE TABLE "any" (
    id integer NOT NULL,
    "any" text
);


ALTER TABLE "any" OWNER TO appdb;

SET search_path = disciplines, pg_catalog;

--
-- Name: any; Type: TABLE; Schema: disciplines; Owner: appdb; Tablespace: 
--

CREATE TABLE "any" (
    id integer NOT NULL,
    "any" text
);


ALTER TABLE "any" OWNER TO appdb;

SET search_path = ebiperun, pg_catalog;

--
-- Name: vo_members; Type: TABLE; Schema: ebiperun; Owner: appdb; Tablespace: 
--

CREATE TABLE vo_members (
    uservo text,
    certdn text,
    ca text,
    vo text,
    last_update timestamp without time zone,
    first_update timestamp without time zone
);


ALTER TABLE vo_members OWNER TO appdb;

--
-- Name: vos; Type: TABLE; Schema: ebiperun; Owner: appdb; Tablespace: 
--

CREATE TABLE vos (
    name text NOT NULL,
    scope text,
    validated timestamp without time zone,
    description text,
    homepage text,
    enrollment text,
    aup text,
    domainname text,
    alias text,
    status text
);


ALTER TABLE vos OWNER TO appdb;

SET search_path = egiops, pg_catalog;

--
-- Name: vos; Type: TABLE; Schema: egiops; Owner: appdb; Tablespace: 
--

CREATE TABLE vos (
    name text NOT NULL,
    scope text,
    validated timestamp without time zone,
    description text,
    homepage text,
    enrollment text,
    aup text,
    domainname text,
    alias text,
    status text,
    disciplineid integer[]
);


ALTER TABLE vos OWNER TO appdb;

SET search_path = elixir, pg_catalog;

--
-- Name: discipline_topics; Type: TABLE; Schema: elixir; Owner: appdb; Tablespace: 
--

CREATE TABLE discipline_topics (
    topic_id integer NOT NULL,
    topic_uri text NOT NULL,
    topic_label text NOT NULL,
    discipline_id integer
);


ALTER TABLE discipline_topics OWNER TO appdb;

SET search_path = gocdb, pg_catalog;

--
-- Name: sites_id_seq; Type: SEQUENCE; Schema: gocdb; Owner: appdb
--

CREATE SEQUENCE sites_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE sites_id_seq OWNER TO appdb;

--
-- Name: sites_id_seq; Type: SEQUENCE OWNED BY; Schema: gocdb; Owner: appdb
--

ALTER SEQUENCE sites_id_seq OWNED BY sites.id;


SET search_path = harvest, pg_catalog;

--
-- Name: archives; Type: TABLE; Schema: harvest; Owner: appdb; Tablespace: 
--

CREATE TABLE archives (
    archive_id integer NOT NULL,
    harvester_plugin text NOT NULL,
    schema_plugin text,
    public_archive_id text,
    user_id integer NOT NULL,
    title text NOT NULL,
    url text NOT NULL,
    enabled boolean NOT NULL
);


ALTER TABLE archives OWNER TO appdb;

--
-- Name: contactpersons; Type: TABLE; Schema: harvest; Owner: appdb; Tablespace: 
--

CREATE TABLE contactpersons (
    id integer NOT NULL,
    fullname text,
    email text,
    phone text,
    fax integer,
    researcherid integer,
    identifier text
);


ALTER TABLE contactpersons OWNER TO appdb;

--
-- Name: contactpersons_id_seq; Type: SEQUENCE; Schema: harvest; Owner: appdb
--

CREATE SEQUENCE contactpersons_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE contactpersons_id_seq OWNER TO appdb;

--
-- Name: contactpersons_id_seq; Type: SEQUENCE OWNED BY; Schema: harvest; Owner: appdb
--

ALTER SEQUENCE contactpersons_id_seq OWNED BY contactpersons.id;


--
-- Name: records_additional; Type: TABLE; Schema: harvest; Owner: appdb; Tablespace: 
--

CREATE TABLE records_additional (
    record_id bigint NOT NULL,
    name text NOT NULL,
    value text,
    record_additional_id bigint NOT NULL
);


ALTER TABLE records_additional OWNER TO appdb;

--
-- Name: institutions; Type: MATERIALIZED VIEW; Schema: harvest; Owner: appdb; Tablespace: 
--

CREATE MATERIALIZED VIEW institutions AS
 SELECT records_additional.record_id,
    records_additional.value AS institution
   FROM records_additional
  WHERE ((records_additional.name = 'legalname'::text) OR (records_additional.name = 'legashortlname'::text))
  WITH NO DATA;


ALTER TABLE institutions OWNER TO appdb;

--
-- Name: institutions2; Type: MATERIALIZED VIEW; Schema: harvest; Owner: appdb; Tablespace: 
--

CREATE MATERIALIZED VIEW institutions2 AS
 SELECT ra1.record_id,
    ra1.value AS institution,
    ra2.value AS institution2
   FROM (records_additional ra1
     JOIN records_additional ra2 ON ((ra1.record_id = ra2.record_id)))
  WHERE ((ra1.name = 'legalname'::text) AND (ra2.name = 'legashortlname'::text))
  WITH NO DATA;


ALTER TABLE institutions2 OWNER TO appdb;

--
-- Name: raw_fields; Type: TABLE; Schema: harvest; Owner: appdb; Tablespace: 
--

CREATE TABLE raw_fields (
    raw_field_id integer NOT NULL,
    name text NOT NULL,
    schema_plugin_id integer NOT NULL
);


ALTER TABLE raw_fields OWNER TO appdb;

--
-- Name: search_keyword_list; Type: TABLE; Schema: harvest; Owner: appdb; Tablespace: 
--

CREATE TABLE search_keyword_list (
    keyword_id bigint NOT NULL,
    keyword_text text NOT NULL
);


ALTER TABLE search_keyword_list OWNER TO appdb;

--
-- Name: search_object_keywords; Type: TABLE; Schema: harvest; Owner: appdb; Tablespace: 
--

CREATE TABLE search_object_keywords (
    object_id bigint NOT NULL,
    keyword_id bigint NOT NULL,
    pos integer NOT NULL,
    search_object_keywords_id bigint NOT NULL
);


ALTER TABLE search_object_keywords OWNER TO appdb;

--
-- Name: search_objects; Type: TABLE; Schema: harvest; Owner: appdb; Tablespace: 
--

CREATE TABLE search_objects (
    object_id bigint NOT NULL,
    archive_id bigint NOT NULL,
    record_id bigint NOT NULL,
    raw_field_id integer NOT NULL
);


ALTER TABLE search_objects OWNER TO appdb;

--
-- Name: keywords; Type: VIEW; Schema: harvest; Owner: appdb
--

CREATE VIEW keywords AS
 SELECT search_keyword_list.keyword_id,
    search_objects.object_id,
    search_objects.archive_id,
    search_objects.record_id,
    raw_fields.raw_field_id,
    raw_fields.name,
    search_keyword_list.keyword_text AS value
   FROM (((search_keyword_list
     JOIN search_object_keywords ON ((search_object_keywords.keyword_id = search_keyword_list.keyword_id)))
     JOIN search_objects ON ((search_objects.object_id = search_object_keywords.object_id)))
     JOIN raw_fields ON ((raw_fields.raw_field_id = search_objects.raw_field_id)));


ALTER TABLE keywords OWNER TO appdb;

--
-- Name: projectcontactpersons; Type: TABLE; Schema: harvest; Owner: appdb; Tablespace: 
--

CREATE TABLE projectcontactpersons (
    projectid integer NOT NULL,
    contactpersonid integer NOT NULL
);


ALTER TABLE projectcontactpersons OWNER TO appdb;

--
-- Name: records; Type: TABLE; Schema: harvest; Owner: appdb; Tablespace: 
--

CREATE TABLE records (
    record_id bigint NOT NULL,
    archive_id bigint NOT NULL,
    schema_plugin_id bigint NOT NULL,
    external_identifier text NOT NULL,
    appdb_identifier text NOT NULL,
    datestamp text,
    contents text NOT NULL
);


ALTER TABLE records OWNER TO appdb;

--
-- Name: records_additional_record_additional_id_seq; Type: SEQUENCE; Schema: harvest; Owner: appdb
--

CREATE SEQUENCE records_additional_record_additional_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE records_additional_record_additional_id_seq OWNER TO appdb;

--
-- Name: records_additional_record_additional_id_seq; Type: SEQUENCE OWNED BY; Schema: harvest; Owner: appdb
--

ALTER SEQUENCE records_additional_record_additional_id_seq OWNED BY records_additional.record_additional_id;


SET search_path = public, pg_catalog;

--
-- Name: organizations; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE organizations (
    id integer NOT NULL,
    name text,
    shortname text,
    websiteurl text,
    countryid integer,
    addedon timestamp without time zone DEFAULT now() NOT NULL,
    addedby integer,
    guid uuid DEFAULT uuid_generate_v4(),
    identifier text,
    sourceid integer,
    deletedon timestamp without time zone,
    deletedby integer,
    ext_identifier text,
    moderated boolean DEFAULT false,
    deleted boolean DEFAULT false
);


ALTER TABLE organizations OWNER TO appdb;

--
-- Name: projects; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE projects (
    id integer NOT NULL,
    code text,
    acronym text,
    title text,
    startdate date,
    enddate date,
    callidentifier text,
    websiteurl text,
    keywords text,
    duration text,
    contracttypeid integer,
    fundingid integer,
    addedon timestamp without time zone DEFAULT now() NOT NULL,
    addedby integer,
    guid uuid DEFAULT uuid_generate_v4(),
    identifier text,
    sourceid integer,
    deletedon timestamp without time zone,
    deletedby integer,
    ext_identifier text,
    moderated boolean DEFAULT false,
    deleted boolean DEFAULT false
);


ALTER TABLE projects OWNER TO appdb;

SET search_path = harvest, pg_catalog;

--
-- Name: records_local_additional; Type: VIEW; Schema: harvest; Owner: appdb
--

CREATE VIEW records_local_additional AS
 SELECT orgs.record_id,
    orgs.name,
    orgs.value,
    orgs.archive_id,
    orgs.raw_field_id,
    orgs.raw_field_name,
    orgs.pos
   FROM ( SELECT o.guid AS record_id,
            'country_iso'::text AS name,
            c.isocode AS value,
            3 AS archive_id,
            316 AS raw_field_id,
            'project.rel.country.classid'::text AS raw_field_name,
            1 AS pos
           FROM (public.organizations o
             JOIN public.countries c ON ((c.id = o.countryid)))
          WHERE (o.sourceid = 1)
        UNION ALL
         SELECT o.guid AS record_id,
            'country_name'::text AS name,
            c.name AS value,
            3 AS archive_id,
            316 AS raw_field_id,
            'project.rel.country.classname'::text AS raw_field_name,
            1 AS pos
           FROM (public.organizations o
             JOIN public.countries c ON ((c.id = o.countryid)))
          WHERE (o.sourceid = 1)
        UNION ALL
         SELECT o.guid AS record_id,
            'legashortlname'::text AS name,
            o.shortname AS value,
            3 AS archive_id,
            318 AS raw_field_id,
            'project.rel.legalshortname'::text AS raw_field_name,
            2 AS pos
           FROM (public.organizations o
             JOIN public.countries c ON ((c.id = o.countryid)))
          WHERE (o.sourceid = 1)
        UNION ALL
         SELECT o.guid AS record_id,
            'legalname'::text AS name,
            o.name AS value,
            3 AS archive_id,
            317 AS raw_field_id,
            'project.rel.legalname'::text AS raw_field_name,
            3 AS pos
           FROM (public.organizations o
             JOIN public.countries c ON ((c.id = o.countryid)))
          WHERE (o.sourceid = 1)
        UNION ALL
         SELECT o.guid AS record_id,
            'ga'::text AS name,
            o.code AS value,
            1 AS archive_id,
            281 AS raw_field_id,
            'code'::text AS raw_field_name,
            1 AS pos
           FROM public.projects o
          WHERE (o.sourceid = 1)
        UNION ALL
         SELECT o.guid AS record_id,
            'acronym'::text AS name,
            o.acronym AS value,
            1 AS archive_id,
            282 AS raw_field_id,
            'acronym'::text AS raw_field_name,
            2 AS pos
           FROM public.projects o
          WHERE (o.sourceid = 1)
        UNION ALL
         SELECT o.guid AS record_id,
            'title'::text AS name,
            o.title AS value,
            1 AS archive_id,
            283 AS raw_field_id,
            'title'::text AS raw_field_name,
            3 AS pos
           FROM public.projects o
          WHERE (o.sourceid = 1)) orgs
  ORDER BY orgs.record_id, orgs.name;


ALTER TABLE records_local_additional OWNER TO appdb;

--
-- Name: records_relations; Type: TABLE; Schema: harvest; Owner: appdb; Tablespace: 
--

CREATE TABLE records_relations (
    archive_id bigint NOT NULL,
    base_id bigint NOT NULL,
    rel_id bigint NOT NULL
);


ALTER TABLE records_relations OWNER TO appdb;

--
-- Name: registered_records; Type: VIEW; Schema: harvest; Owner: appdb
--

CREATE VIEW registered_records AS
 SELECT 'organization'::public.e_entity AS e_entity,
    r.record_id,
    o.guid,
    o.identifier,
    r.archive_id
   FROM (public.organizations o
     LEFT JOIN records r ON ((r.appdb_identifier = o.identifier)))
UNION ALL
 SELECT 'project'::public.e_entity AS e_entity,
    r.record_id,
    o.guid,
    o.identifier,
    r.archive_id
   FROM (public.projects o
     LEFT JOIN records r ON ((r.appdb_identifier = o.identifier)));


ALTER TABLE registered_records OWNER TO appdb;

--
-- Name: search_object_keywords_search_object_keywords_id_seq; Type: SEQUENCE; Schema: harvest; Owner: appdb
--

CREATE SEQUENCE search_object_keywords_search_object_keywords_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE search_object_keywords_search_object_keywords_id_seq OWNER TO appdb;

--
-- Name: search_object_keywords_search_object_keywords_id_seq; Type: SEQUENCE OWNED BY; Schema: harvest; Owner: appdb
--

ALTER SEQUENCE search_object_keywords_search_object_keywords_id_seq OWNED BY search_object_keywords.search_object_keywords_id;


--
-- Name: search_record_ids; Type: MATERIALIZED VIEW; Schema: harvest; Owner: appdb; Tablespace: 
--

CREATE MATERIALIZED VIEW search_record_ids AS
 SELECT search_objects.record_id,
    search_objects.raw_field_id,
    lower(search_keyword_list.keyword_text) AS keyword_text,
    search_objects.archive_id,
    archives.enabled
   FROM (((search_object_keywords
     JOIN search_objects ON ((search_objects.object_id = search_object_keywords.object_id)))
     JOIN archives ON ((archives.archive_id = search_objects.archive_id)))
     JOIN search_keyword_list ON ((search_keyword_list.keyword_id = search_object_keywords.keyword_id)))
  WHERE (archives.enabled IS TRUE)
  WITH NO DATA;


ALTER TABLE search_record_ids OWNER TO appdb;

SET search_path = licenses, pg_catalog;

--
-- Name: any; Type: TABLE; Schema: licenses; Owner: appdb; Tablespace: 
--

CREATE TABLE "any" (
    id integer NOT NULL,
    "any" text
);


ALTER TABLE "any" OWNER TO appdb;

SET search_path = middlewares, pg_catalog;

--
-- Name: any; Type: TABLE; Schema: middlewares; Owner: appdb; Tablespace: 
--

CREATE TABLE "any" (
    id integer NOT NULL,
    "any" text
);


ALTER TABLE "any" OWNER TO appdb;

SET search_path = oses, pg_catalog;

--
-- Name: any; Type: TABLE; Schema: oses; Owner: appdb; Tablespace: 
--

CREATE TABLE "any" (
    id integer NOT NULL,
    "any" text
);


ALTER TABLE "any" OWNER TO appdb;

SET search_path = perun, pg_catalog;

--
-- Name: vos; Type: TABLE; Schema: perun; Owner: appdb; Tablespace: 
--

CREATE TABLE vos (
    name text NOT NULL,
    validated timestamp without time zone,
    description text,
    homepage text,
    enrollment text,
    alias text,
    status text
);


ALTER TABLE vos OWNER TO appdb;

SET search_path = positiontypes, pg_catalog;

--
-- Name: any; Type: TABLE; Schema: positiontypes; Owner: appdb; Tablespace: 
--

CREATE TABLE "any" (
    id integer NOT NULL,
    "any" text
);


ALTER TABLE "any" OWNER TO appdb;

SET search_path = proglangs, pg_catalog;

--
-- Name: any; Type: TABLE; Schema: proglangs; Owner: appdb; Tablespace: 
--

CREATE TABLE "any" (
    id integer NOT NULL,
    "any" text
);


ALTER TABLE "any" OWNER TO appdb;

SET search_path = public, pg_catalog;

--
-- Name: __actor_group_members_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE __actor_group_members_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE __actor_group_members_id_seq OWNER TO appdb;

--
-- Name: __actor_group_members_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE __actor_group_members_id_seq OWNED BY __actor_group_members.id;


--
-- Name: __app_tags; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE __app_tags (
    id integer NOT NULL,
    appid integer NOT NULL,
    researcherid integer,
    tag text NOT NULL
);


ALTER TABLE __app_tags OWNER TO appdb;

--
-- Name: __app_vos; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE __app_vos (
    void integer NOT NULL,
    appid integer NOT NULL
);


ALTER TABLE __app_vos OWNER TO appdb;

--
-- Name: __va_provider_images; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE __va_provider_images (
    id bigint NOT NULL,
    va_provider_id text NOT NULL,
    vmiinstanceid integer NOT NULL,
    content_type text,
    va_provider_image_id text,
    mp_uri text,
    vowide_vmiinstanceid integer
);


ALTER TABLE __va_provider_images OWNER TO appdb;

--
-- Name: abusereports; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE abusereports (
    id integer NOT NULL,
    submitterid integer NOT NULL,
    offender uuid NOT NULL,
    "timestamp" timestamp without time zone DEFAULT now() NOT NULL,
    reason integer,
    comment text,
    state integer DEFAULT 0 NOT NULL
);


ALTER TABLE abusereports OWNER TO appdb;

--
-- Name: abusereports_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE abusereports_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE abusereports_id_seq OWNER TO appdb;

--
-- Name: abusereports_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE abusereports_id_seq OWNED BY abusereports.id;


--
-- Name: access_token_netfilters; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE access_token_netfilters (
    netfilter text NOT NULL,
    tokenid bigint NOT NULL
);


ALTER TABLE access_token_netfilters OWNER TO appdb;

--
-- Name: access_tokens; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE access_tokens (
    id bigint NOT NULL,
    token uuid DEFAULT uuid_generate_v4() NOT NULL,
    actor uuid NOT NULL,
    createdon timestamp without time zone DEFAULT now() NOT NULL,
    type e_access_token_types DEFAULT 'personal'::e_access_token_types NOT NULL,
    addedby integer,
    CONSTRAINT access_tokens_actor_check CHECK (is_valid_actor_guid(actor))
);


ALTER TABLE access_tokens OWNER TO appdb;

--
-- Name: access_tokens_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE access_tokens_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE access_tokens_id_seq OWNER TO appdb;

--
-- Name: access_tokens_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE access_tokens_id_seq OWNED BY access_tokens.id;


--
-- Name: account_types; Type: VIEW; Schema: public; Owner: appdb
--

CREATE VIEW account_types AS
 SELECT e.enumsortorder AS id,
    e.enumlabel AS name
   FROM (pg_enum e
     JOIN pg_type t ON ((e.enumtypid = t.oid)))
  WHERE (t.typname = 'e_account_type'::name);


ALTER TABLE account_types OWNER TO appdb;

--
-- Name: actions_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE actions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE actions_id_seq OWNER TO appdb;

--
-- Name: actions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE actions_id_seq OWNED BY actions.id;


--
-- Name: actor_groups_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE actor_groups_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE actor_groups_id_seq OWNER TO appdb;

--
-- Name: actor_groups_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE actor_groups_id_seq OWNED BY actor_groups.id;


--
-- Name: app_order_hack; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE app_order_hack (
    appid integer NOT NULL
);


ALTER TABLE app_order_hack OWNER TO appdb;

--
-- Name: news; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE news (
    id integer NOT NULL,
    "timestamp" timestamp without time zone,
    action character varying(20),
    subjectguid uuid,
    fields text[]
);


ALTER TABLE news OWNER TO appdb;

--
-- Name: vapp_versions; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE vapp_versions (
    id integer NOT NULL,
    version text NOT NULL,
    guid uuid DEFAULT uuid_generate_v4() NOT NULL,
    notes text,
    vappid integer NOT NULL,
    published boolean DEFAULT false NOT NULL,
    createdon timestamp without time zone DEFAULT now() NOT NULL,
    expireson timestamp without time zone,
    enabled boolean DEFAULT true NOT NULL,
    archived boolean DEFAULT false NOT NULL,
    status text DEFAULT 'init'::text NOT NULL,
    archivedon timestamp without time zone,
    submissionid integer DEFAULT 0 NOT NULL,
    isexternal boolean DEFAULT false NOT NULL
);


ALTER TABLE vapp_versions OWNER TO appdb;

--
-- Name: vapplications; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE vapplications (
    id integer NOT NULL,
    name text,
    appid integer,
    guid uuid DEFAULT uuid_generate_v4() NOT NULL,
    imglst_private boolean DEFAULT false NOT NULL
);


ALTER TABLE vapplications OWNER TO appdb;

--
-- Name: aggregate_news; Type: MATERIALIZED VIEW; Schema: public; Owner: appdb; Tablespace: 
--

CREATE MATERIALIZED VIEW aggregate_news AS
 WITH objects AS (
         SELECT applications.id AS objectid,
            applications.name AS objectname,
            applications.guid,
            'app'::text AS objecttype
           FROM applications
        UNION ALL
         SELECT researchers.id,
            researchers.name,
            researchers.guid,
            'ppl'::text AS text
           FROM researchers
        UNION ALL
         SELECT appdocuments.id,
            appdocuments.title,
            appdocuments.guid,
            'doc'::text AS text
           FROM appdocuments
        UNION ALL
         SELECT appratings.id,
            appratings.comment,
            appratings.guid,
            'cmm'::text AS text
           FROM appratings
        UNION ALL
         SELECT userrequests.id,
            ''::text AS text,
            userrequests.guid,
            'req'::text AS text
           FROM userrequests
        UNION ALL
         SELECT vapp_versions.id,
            vapplications.name,
            vapp_versions.guid,
            'vav'::text AS text
           FROM (vapp_versions
             JOIN vapplications ON ((vapplications.id = vapp_versions.vappid)))
        )
 SELECT max(nnn.id) AS id,
    max(nnn."timestamp") AS "timestamp",
    nnn.action,
    nnn.subjectguid,
    objects.objectid AS subjectid,
    objects.objectname AS subjectname,
    objects.objecttype AS subjecttype,
    array_to_string(array_sort_unique(string_to_array(array_to_string(array_agg(array_to_string(nnn.fields, ','::text)), ','::text), ','::text)), ','::text) AS fields,
        CASE objects.objecttype
            WHEN 'app'::text THEN app_to_json(objects.objectid)
            WHEN 'vav'::text THEN vapp_version_to_json(objects.objectid)
            ELSE ''::text
        END AS subjectdata
   FROM (( SELECT DISTINCT max(nn.id) OVER (PARTITION BY nn.id2, (nn.dt <= '25:00:00'::interval), nn.subjectguid, nn.action) AS id,
            max(nn."timestamp") OVER (PARTITION BY nn.id2, (nn.dt <= '25:00:00'::interval), nn.subjectguid, nn.action) AS "timestamp",
            nn.action,
            nn.subjectguid,
            nn.fields
           FROM ( SELECT n.id,
                    n."timestamp",
                    n.action,
                    n.subjectguid,
                    n.fields,
                    n.dt,
                    n.id2
                   FROM ( SELECT n1.id,
                            n1."timestamp",
                            n1.action,
                            n1.subjectguid,
                            n1.fields,
                            (n1."timestamp" - n2."timestamp") AS dt,
                            n2.id AS id2
                           FROM (news n1
                             JOIN news n2 ON ((((n1.action)::text = (n2.action)::text) AND (n1.subjectguid = n2.subjectguid))))
                          WHERE (n2."timestamp" <= n1."timestamp")) n
                  WHERE (n.dt <= '25:00:00'::interval)
                  ORDER BY n."timestamp" DESC, n.id DESC, n.id2 DESC) nn
          ORDER BY max(nn."timestamp") OVER (PARTITION BY nn.id2, (nn.dt <= '25:00:00'::interval), nn.subjectguid, nn.action) DESC) nnn
     JOIN objects ON ((objects.guid = nnn.subjectguid)))
  GROUP BY nnn.action, nnn.subjectguid, objects.objectid, objects.objectname, objects.objecttype, nnn."timestamp"
 HAVING ((
        CASE objects.objecttype
            WHEN 'app'::text THEN ( SELECT (NOT ((applications.deleted OR applications.moderated) OR (applications.id IN ( SELECT app_order_hack.appid
                       FROM app_order_hack))))
               FROM applications
              WHERE (applications.id = objects.objectid))
            WHEN 'ppl'::text THEN ( SELECT (NOT researchers.deleted)
               FROM researchers
              WHERE (researchers.id = objects.objectid))
            ELSE true
        END IS TRUE) AND (
        CASE nnn.action
            WHEN 'update'::text THEN (objects.objecttype <> 'doc'::text)
            ELSE true
        END IS TRUE))
  ORDER BY max(nnn."timestamp") DESC, max(nnn.id) DESC
  WITH NO DATA;


ALTER TABLE aggregate_news OWNER TO appdb;

--
-- Name: aggregate_news2; Type: VIEW; Schema: public; Owner: appdb
--

CREATE VIEW aggregate_news2 AS
 WITH objects AS (
         SELECT applications.id AS objectid,
            applications.name AS objectname,
            applications.guid,
            'app'::text AS objecttype
           FROM applications
        UNION ALL
         SELECT researchers.id,
            researchers.name,
            researchers.guid,
            'ppl'::text AS text
           FROM researchers
        UNION ALL
         SELECT appdocuments.id,
            appdocuments.title,
            appdocuments.guid,
            'doc'::text AS text
           FROM appdocuments
        UNION ALL
         SELECT appratings.id,
            appratings.comment,
            appratings.guid,
            'cmm'::text AS text
           FROM appratings
        UNION ALL
         SELECT userrequests.id,
            ''::text AS text,
            userrequests.guid,
            'req'::text AS text
           FROM userrequests
        )
 SELECT max(n.id) AS id,
    max(n."timestamp") AS "timestamp",
    n.action,
    n.subjectguid,
    objects.objectid AS subjectid,
    objects.objectname AS subjectname,
    objects.objecttype AS subjecttype,
    array_to_string(array_sort_unique(string_to_array(array_to_string(array_agg(array_to_string(n.fields, ','::text)), ','::text), ','::text)), ','::text) AS fields,
        CASE objects.objecttype
            WHEN 'app'::text THEN app_to_json(objects.objectid)
            ELSE ''::text
        END AS subjectdata
   FROM (( SELECT news.id,
            news."timestamp",
            news.action,
            news.subjectguid,
            news.fields,
            COALESCE(( SELECT min(n2.id ORDER BY n2.id DESC) AS min
                   FROM news n2
                  WHERE ((((n2.subjectguid = news.subjectguid) AND ((n2.action)::text = (news.action)::text)) AND ((news."timestamp" - n2."timestamp") <= '25:00:00'::interval)) AND (date_part('epoch'::text, (news."timestamp" - n2."timestamp")) > (0)::double precision))
                 LIMIT 1), news.id) AS parentid
           FROM news
          ORDER BY news."timestamp" DESC) n
     JOIN objects ON ((objects.guid = n.subjectguid)))
  GROUP BY n.action, n.subjectguid, n.parentid, objects.objectid, objects.objectname, objects.objecttype
 HAVING ((
        CASE objects.objecttype
            WHEN 'app'::text THEN ( SELECT (NOT (applications.deleted OR applications.moderated))
               FROM applications
              WHERE (applications.id = objects.objectid))
            WHEN 'ppl'::text THEN ( SELECT (NOT researchers.deleted)
               FROM researchers
              WHERE (researchers.id = objects.objectid))
            ELSE true
        END IS TRUE) AND (
        CASE n.action
            WHEN 'update'::text THEN (objects.objecttype <> 'doc'::text)
            ELSE true
        END IS TRUE))
  ORDER BY max(n."timestamp") DESC, max(n.id) DESC;


ALTER TABLE aggregate_news2 OWNER TO appdb;

--
-- Name: newsviews; Type: VIEW; Schema: public; Owner: appdb
--

CREATE VIEW newsviews AS
 SELECT news.id,
    news."timestamp",
    news.action,
    news.subjectguid,
    applications.id AS subjectid,
    applications.name AS subjectname,
    'app'::text AS subjecttype,
    news.fields
   FROM (news
     JOIN applications ON ((((applications.guid)::text)::uuid = news.subjectguid)))
UNION
 SELECT news.id,
    news."timestamp",
    news.action,
    news.subjectguid,
    researchers.id AS subjectid,
    ((researchers.firstname || ' '::text) || researchers.lastname) AS subjectname,
    'ppl'::text AS subjecttype,
    news.fields
   FROM (news
     JOIN researchers ON ((((researchers.guid)::text)::uuid = news.subjectguid)))
UNION
 SELECT news.id,
    news."timestamp",
    news.action,
    news.subjectguid,
    appdocuments.id AS subjectid,
    appdocuments.title AS subjectname,
    'doc'::text AS subjecttype,
    news.fields
   FROM (news
     JOIN appdocuments ON ((((appdocuments.guid)::text)::uuid = news.subjectguid)))
UNION
 SELECT news.id,
    news."timestamp",
    news.action,
    news.subjectguid,
    appratings.id AS subjectid,
    appratings.comment AS subjectname,
    'cmm'::text AS subjecttype,
    news.fields
   FROM (news
     JOIN appratings ON ((appratings.guid = news.subjectguid)))
UNION
 SELECT news.id,
    news."timestamp",
    news.action,
    news.subjectguid,
    NULL::integer AS subjectid,
    NULL::text AS subjectname,
    'cnt'::text AS subjecttype,
    news.fields
   FROM (news
     JOIN researchers_apps ON ((researchers_apps.guid = news.subjectguid)))
  ORDER BY 2 DESC;


ALTER TABLE newsviews OWNER TO appdb;

--
-- Name: aggregate_news_old; Type: VIEW; Schema: public; Owner: appdb
--

CREATE VIEW aggregate_news_old AS
 SELECT DISTINCT ON (news."timestamp", news.action, news.subjectguid) news.id,
    news."timestamp",
    news.action,
    news.subjectguid,
    news.subjectid,
    news.subjectname,
    news.subjecttype,
    ( SELECT array_to_string(array_sort_unique(string_to_array(array_to_string(array_agg(array_to_string(nv.fields, ','::text)), ','::text), ','::text)), ','::text) AS array_to_string
           FROM newsviews nv
          WHERE ((((((nv.action)::text = (news.action)::text) AND (nv.subjectguid = news.subjectguid)) AND ((date_part('epoch'::text, news."timestamp") - date_part('epoch'::text, nv."timestamp")) < (90000)::double precision)) AND (nv."timestamp" <= news."timestamp")) AND (NOT (nv.fields IS NULL)))) AS fields,
        CASE
            WHEN (news.subjecttype = 'app'::text) THEN app_to_json(news.subjectid)
            ELSE ''::text
        END AS subjectdata
   FROM newsviews news
  WHERE ((((NOT (news.id IN ( SELECT t3.i2
           FROM ( SELECT DISTINCT t2.t1 AS "timestamp",
                    t2.si1 AS subjectid,
                    t2.st1 AS subjecttype,
                    t2.a1 AS action,
                    t2.i1,
                    t2.i2
                   FROM ( SELECT (date_part('epoch'::text, t1.t1) - date_part('epoch'::text, t1.t2)) AS dt,
                            t1.si1,
                            t1.st1,
                            t1.a1,
                            t1.t1,
                            t1.t2,
                            t1.i1,
                            t1.i2
                           FROM ( SELECT n1."timestamp" AS t1,
                                    n1.subjectid AS si1,
                                    n1.subjecttype AS st1,
                                    n1.action AS a1,
                                    n1.id AS i1,
                                    n2."timestamp" AS t2,
                                    n2.subjectid AS si2,
                                    n2.subjecttype AS st2,
                                    n2.action AS a2,
                                    n2.id AS i2
                                   FROM newsviews n1,
                                    newsviews n2) t1
                          WHERE ((((t1.si1 = t1.si2) AND (t1.st1 = t1.st2)) AND ((t1.a1)::text = (t1.a2)::text)) AND ((date_part('epoch'::text, t1.t1) - date_part('epoch'::text, t1.t2)) > (0)::double precision))) t2
                  WHERE (t2.dt < (90000)::double precision)) t3))) AND ((news.action)::text <> 'delete'::text)) AND
        CASE
            WHEN ((news.action)::text = 'update'::text) THEN (news.subjecttype <> 'doc'::text)
            ELSE true
        END) AND
        CASE
            WHEN (news.subjecttype = 'app'::text) THEN (NOT (news.subjectid IN ( SELECT applications.id
               FROM applications
              WHERE ((applications.moderated IS TRUE) OR (applications.deleted IS TRUE)))))
            ELSE true
        END)
  ORDER BY news."timestamp" DESC;


ALTER TABLE aggregate_news_old OWNER TO appdb;

--
-- Name: apikey_netfilters; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE apikey_netfilters (
    netfilter text NOT NULL,
    keyid integer NOT NULL
);


ALTER TABLE apikey_netfilters OWNER TO appdb;

--
-- Name: apikeys; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE apikeys (
    key uuid DEFAULT uuid_generate_v4() NOT NULL,
    authmethods integer DEFAULT 1,
    ownerid integer,
    createdon timestamp without time zone DEFAULT now(),
    id integer NOT NULL,
    sysaccountid integer
);


ALTER TABLE apikeys OWNER TO appdb;

--
-- Name: apikeys_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE apikeys_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE apikeys_id_seq OWNER TO appdb;

--
-- Name: apikeys_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE apikeys_id_seq OWNED BY apikeys.id;


--
-- Name: app_api_log; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE app_api_log (
    id integer NOT NULL,
    appid integer NOT NULL,
    "timestamp" timestamp without time zone DEFAULT now() NOT NULL,
    researcherid integer,
    source integer,
    ip text
);


ALTER TABLE app_api_log OWNER TO appdb;

--
-- Name: app_api_log_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE app_api_log_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE app_api_log_id_seq OWNER TO appdb;

--
-- Name: app_api_log_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE app_api_log_id_seq OWNED BY app_api_log.id;


--
-- Name: app_archs; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE app_archs (
    appid integer NOT NULL,
    archid integer NOT NULL
);


ALTER TABLE app_archs OWNER TO appdb;

--
-- Name: app_cnames; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE app_cnames (
    id integer NOT NULL,
    created timestamp without time zone DEFAULT now(),
    enabled boolean DEFAULT true,
    isprimary boolean DEFAULT true,
    value text,
    appid integer NOT NULL
);


ALTER TABLE app_cnames OWNER TO appdb;

--
-- Name: app_cnames_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE app_cnames_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE app_cnames_id_seq OWNER TO appdb;

--
-- Name: app_cnames_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE app_cnames_id_seq OWNED BY app_cnames.id;


--
-- Name: app_data; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE app_data (
    id integer NOT NULL,
    appid integer NOT NULL,
    data bytea,
    description text
);


ALTER TABLE app_data OWNER TO appdb;

--
-- Name: app_del_infos; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE app_del_infos (
    id integer NOT NULL,
    appid integer NOT NULL,
    deletedby integer,
    deletedon timestamp without time zone DEFAULT now() NOT NULL,
    roleid integer
);


ALTER TABLE app_del_infos OWNER TO appdb;

--
-- Name: app_del_infos_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE app_del_infos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE app_del_infos_id_seq OWNER TO appdb;

--
-- Name: app_del_infos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE app_del_infos_id_seq OWNED BY app_del_infos.id;


--
-- Name: app_licenses; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE app_licenses (
    id integer NOT NULL,
    appid integer NOT NULL,
    licenseid integer,
    title text,
    comment text,
    link text
);


ALTER TABLE app_licenses OWNER TO appdb;

--
-- Name: app_licenses_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE app_licenses_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE app_licenses_id_seq OWNER TO appdb;

--
-- Name: app_licenses_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE app_licenses_id_seq OWNED BY app_licenses.id;


--
-- Name: app_middlewares; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE app_middlewares (
    appid integer DEFAULT 0 NOT NULL,
    middlewareid integer DEFAULT 0 NOT NULL,
    comment text,
    id integer NOT NULL,
    link text
);


ALTER TABLE app_middlewares OWNER TO appdb;

--
-- Name: app_middlewares_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE app_middlewares_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE app_middlewares_id_seq OWNER TO appdb;

--
-- Name: app_middlewares_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE app_middlewares_id_seq OWNED BY app_middlewares.id;


--
-- Name: app_mod_infos; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE app_mod_infos (
    id integer NOT NULL,
    appid integer NOT NULL,
    moddedby integer,
    moddedon timestamp without time zone DEFAULT now() NOT NULL,
    modreason text
);


ALTER TABLE app_mod_infos OWNER TO appdb;

--
-- Name: app_mod_infos_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE app_mod_infos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE app_mod_infos_id_seq OWNER TO appdb;

--
-- Name: app_mod_infos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE app_mod_infos_id_seq OWNED BY app_mod_infos.id;


--
-- Name: app_oses; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE app_oses (
    appid integer NOT NULL,
    osid integer NOT NULL,
    osversion text
);


ALTER TABLE app_oses OWNER TO appdb;

--
-- Name: app_popularities; Type: VIEW; Schema: public; Owner: appdb
--

CREATE VIEW app_popularities AS
 SELECT app_api_log.appid,
    ((((count(app_api_log.appid))::double precision * (100)::double precision) / ( SELECT (count(*))::double precision AS count
           FROM app_api_log app_api_log_1)))::numeric(5,2) AS popularity
   FROM app_api_log
  GROUP BY app_api_log.appid;


ALTER TABLE app_popularities OWNER TO appdb;

--
-- Name: app_releases; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE app_releases (
    id integer NOT NULL,
    appid integer NOT NULL,
    release text NOT NULL,
    series text NOT NULL,
    state integer NOT NULL,
    addedon timestamp without time zone DEFAULT now() NOT NULL,
    publishedon timestamp without time zone,
    manager integer,
    lastupdated timestamp without time zone DEFAULT now() NOT NULL,
    releaseid integer NOT NULL
);


ALTER TABLE app_releases OWNER TO appdb;

--
-- Name: app_release_count; Type: VIEW; Schema: public; Owner: appdb
--

CREATE VIEW app_release_count AS
 SELECT applications.id AS appid,
    COALESCE(( SELECT count(*) AS count
           FROM app_releases
          WHERE ((app_releases.state = ANY (ARRAY[2, 3])) AND (app_releases.appid = applications.id))
          GROUP BY app_releases.appid), (0)::bigint) AS relcount
   FROM applications
  ORDER BY COALESCE(( SELECT count(*) AS count
           FROM app_releases
          WHERE ((app_releases.state = ANY (ARRAY[2, 3])) AND (app_releases.appid = applications.id))
          GROUP BY app_releases.appid), (0)::bigint) DESC;


ALTER TABLE app_release_count OWNER TO appdb;

--
-- Name: app_releases_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE app_releases_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE app_releases_id_seq OWNER TO appdb;

--
-- Name: app_releases_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE app_releases_id_seq OWNED BY app_releases.id;


--
-- Name: context_script_assocs; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE context_script_assocs (
    id integer NOT NULL,
    contextid integer NOT NULL,
    scriptid integer NOT NULL,
    addedby integer,
    addedon timestamp without time zone DEFAULT now() NOT NULL
);


ALTER TABLE context_script_assocs OWNER TO appdb;

--
-- Name: contexts; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE contexts (
    id integer NOT NULL,
    appid integer NOT NULL,
    addedby integer,
    addedon timestamp without time zone DEFAULT now() NOT NULL,
    guid uuid DEFAULT uuid_generate_v4(),
    lastupdatedby integer,
    lastupdatedon timestamp without time zone DEFAULT now() NOT NULL,
    version text,
    description text
);


ALTER TABLE contexts OWNER TO appdb;

--
-- Name: contextscripts; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE contextscripts (
    id integer NOT NULL,
    name text NOT NULL,
    title text,
    description text,
    url text NOT NULL,
    formatid integer NOT NULL,
    checksum text,
    checksumfunc e_hashfuncs,
    size integer,
    guid uuid DEFAULT uuid_generate_v4(),
    addedby integer,
    addedon timestamp without time zone DEFAULT now() NOT NULL,
    lastupdatedby integer,
    lastupdatedon timestamp without time zone DEFAULT now()
);


ALTER TABLE contextscripts OWNER TO appdb;

--
-- Name: vapplists; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE vapplists (
    id integer NOT NULL,
    vappversionid integer NOT NULL,
    vmiinstanceid integer NOT NULL
);


ALTER TABLE vapplists OWNER TO appdb;

--
-- Name: vmiflavours; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE vmiflavours (
    id integer NOT NULL,
    vmiid integer NOT NULL,
    hypervisors e_hypervisors[] NOT NULL,
    archid integer NOT NULL,
    osid integer NOT NULL,
    osversion text,
    format text
);


ALTER TABLE vmiflavours OWNER TO appdb;

--
-- Name: vmiinstances; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE vmiinstances (
    id integer NOT NULL,
    size bigint NOT NULL,
    uri text NOT NULL,
    version text NOT NULL,
    checksum text NOT NULL,
    checksumfunc e_hashfuncs,
    notes text,
    guid uuid DEFAULT uuid_generate_v4() NOT NULL,
    addedon timestamp without time zone DEFAULT now() NOT NULL,
    addedby integer NOT NULL,
    vmiflavourid integer NOT NULL,
    autointegrity boolean DEFAULT true NOT NULL,
    coreminimum integer DEFAULT 0,
    ramminimum bigint DEFAULT 0,
    lastupdatedby integer,
    lastupdatedon timestamp without time zone,
    description text,
    title text,
    integrity_status text,
    integrity_message text,
    ramrecommend bigint DEFAULT 0,
    corerecommend integer DEFAULT 0,
    accessinfo text,
    enabled boolean DEFAULT true NOT NULL,
    initialsize bigint,
    initialchecksum text,
    ovfurl text
);


ALTER TABLE vmiinstances OWNER TO appdb;

--
-- Name: vmis; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE vmis (
    id integer NOT NULL,
    name text NOT NULL,
    description text NOT NULL,
    guid uuid DEFAULT uuid_generate_v4() NOT NULL,
    vappid integer NOT NULL,
    notes text,
    groupname text DEFAULT ''::text NOT NULL
);


ALTER TABLE vmis OWNER TO appdb;

--
-- Name: vaviews; Type: VIEW; Schema: public; Owner: appdb
--

CREATE VIEW vaviews AS
 SELECT vapplists.id AS vapplistid,
    vapplists.vappversionid,
    vapplists.vmiinstanceid,
    vmiinstances.size,
    vmiinstances.uri,
    vmiinstances.version AS vmiinstance_version,
    vmiinstances.checksum,
    vmiinstances.checksumfunc,
    vmiinstances.notes AS vmiinstance_notes,
    vmiinstances.guid AS vmiinstance_guid,
    vmiinstances.addedon AS vmiinstance_addedon,
    vmiinstances.addedby AS vmiinstance_addedby,
    vmiinstances.vmiflavourid,
    vmiinstances.autointegrity,
    vmiinstances.coreminimum,
    vmiinstances.ramminimum,
    vmiinstances.lastupdatedby AS vmiinstance_lastupdatedby,
    vmiinstances.lastupdatedon AS vmiinstance_lastupdatedon,
    vmiinstances.description AS vmiinstance_description,
    vmiinstances.title AS vmiinstance_title,
    vmiinstances.integrity_status,
    vmiinstances.integrity_message,
    vmiinstances.ramrecommend,
    vmiinstances.corerecommend,
    vmiinstances.accessinfo,
    vmiinstances.enabled AS vmiinstance_enabled,
    vmiinstances.initialsize,
    vmiinstances.initialchecksum,
    vmiinstances.ovfurl,
    vmiflavours.vmiid,
    vmiflavours.hypervisors,
    vmiflavours.archid,
    vmiflavours.osid,
    vmiflavours.osversion,
    vmiflavours.format,
    vmis.name AS vmi_name,
    vmis.description AS vmi_description,
    vmis.guid AS vmi_guid,
    vmis.vappid AS va_id,
    vmis.notes AS vmi_notes,
    vmis.groupname,
    vapplications.name AS va_name,
    vapplications.appid,
    vapplications.guid AS va_guid,
    vapplications.imglst_private,
    vapp_versions.version AS va_version,
    vapp_versions.guid AS va_version_guid,
    vapp_versions.notes AS va_version_notes,
    vapp_versions.published AS va_version_published,
    vapp_versions.createdon AS va_version_createdon,
    vapp_versions.expireson AS va_version_expireson,
    vapp_versions.enabled AS va_version_enabled,
    vapp_versions.archived AS va_version_archived,
    vapp_versions.status AS va_version_status,
    vapp_versions.archivedon AS va_version_archivedon,
    vapp_versions.submissionid,
    vapp_versions.isexternal AS va_version_isexternal,
    applications.name AS appname,
    applications.cname AS appcname
   FROM ((((((vapplists
     JOIN vmiinstances ON ((vmiinstances.id = vapplists.vmiinstanceid)))
     JOIN vmiflavours ON ((vmiflavours.id = vmiinstances.vmiflavourid)))
     JOIN vmis ON ((vmis.id = vmiflavours.vmiid)))
     JOIN vapplications ON ((vapplications.id = vmis.vappid)))
     JOIN vapp_versions ON ((vapp_versions.id = vapplists.vappversionid)))
     JOIN applications ON ((applications.id = vapplications.appid)));


ALTER TABLE vaviews OWNER TO appdb;

--
-- Name: vmiinstance_contextscripts; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE vmiinstance_contextscripts (
    id integer NOT NULL,
    vmiinstanceid integer NOT NULL,
    contextscriptid integer NOT NULL,
    addedon timestamp without time zone DEFAULT now() NOT NULL,
    addedby integer
);


ALTER TABLE vmiinstance_contextscripts OWNER TO appdb;

--
-- Name: vowide_image_list_images; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE vowide_image_list_images (
    id integer NOT NULL,
    vowide_image_list_id integer,
    vapplistid integer,
    guid uuid DEFAULT uuid_generate_v4() NOT NULL,
    state e_vowide_image_state DEFAULT 'draft'::e_vowide_image_state NOT NULL
);


ALTER TABLE vowide_image_list_images OWNER TO appdb;

--
-- Name: vowide_image_lists; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE vowide_image_lists (
    id integer NOT NULL,
    void integer,
    guid uuid DEFAULT uuid_generate_v4() NOT NULL,
    state e_vowide_image_state DEFAULT 'draft'::e_vowide_image_state NOT NULL,
    expires_on timestamp without time zone DEFAULT '2500-01-01 00:00:00'::timestamp without time zone NOT NULL,
    published_on timestamp without time zone,
    notes text,
    title text,
    alteredby integer,
    lastmodified timestamp without time zone DEFAULT now() NOT NULL,
    publishedby integer
);


ALTER TABLE vowide_image_lists OWNER TO appdb;

--
-- Name: app_vos; Type: VIEW; Schema: public; Owner: appdb
--

CREATE VIEW app_vos AS
 SELECT DISTINCT vaviews.appid,
    vowide_image_lists.void
   FROM ((vowide_image_list_images
     JOIN vowide_image_lists ON ((vowide_image_list_images.vowide_image_list_id = vowide_image_lists.id)))
     JOIN vaviews ON ((vaviews.vapplistid = vowide_image_list_images.vapplistid)))
  WHERE (vowide_image_lists.state = 'published'::e_vowide_image_state)
UNION
 SELECT DISTINCT applications.id AS appid,
    vowide_image_lists.void
   FROM (((((((vowide_image_list_images
     JOIN vowide_image_lists ON ((vowide_image_list_images.vowide_image_list_id = vowide_image_lists.id)))
     JOIN vaviews ON ((vaviews.vapplistid = vowide_image_list_images.vapplistid)))
     JOIN vmiinstance_contextscripts vcs ON ((vcs.vmiinstanceid = vaviews.vmiinstanceid)))
     JOIN contextscripts cs ON ((cs.id = vcs.contextscriptid)))
     JOIN context_script_assocs ON ((context_script_assocs.scriptid = vcs.contextscriptid)))
     JOIN contexts ON ((contexts.id = context_script_assocs.contextid)))
     JOIN applications ON ((applications.id = contexts.appid)))
  WHERE ((vowide_image_lists.state = 'published'::e_vowide_image_state) AND (applications.metatype = 2))
UNION
 SELECT __app_vos.appid,
    __app_vos.void
   FROM __app_vos;


ALTER TABLE app_vos OWNER TO appdb;

--
-- Name: positiontypes; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE positiontypes (
    id integer NOT NULL,
    description text NOT NULL,
    ord integer
);


ALTER TABLE positiontypes OWNER TO appdb;

--
-- Name: appautocountries; Type: VIEW; Schema: public; Owner: appdb
--

CREATE VIEW appautocountries AS
 SELECT DISTINCT applications.id,
    researchers.positiontypeid,
    countries.id AS countryid
   FROM ((((applications
     JOIN researchers_apps ON ((applications.id = researchers_apps.appid)))
     JOIN researchers ON ((researchers.id = researchers_apps.researcherid)))
     JOIN countries ON ((countries.id = researchers.countryid)))
     JOIN positiontypes ON ((positiontypes.id = researchers.positiontypeid)))
  WHERE (positiontypes.ord = ( SELECT min(pt.ord) AS min
           FROM ((((applications apps2
             JOIN researchers_apps ra ON ((apps2.id = ra.appid)))
             JOIN researchers r ON ((r.id = ra.researcherid)))
             JOIN countries c ON ((c.id = r.countryid)))
             JOIN positiontypes pt ON ((pt.id = r.positiontypeid)))
          WHERE ((apps2.id = applications.id) AND (c.id = countries.id))))
  ORDER BY applications.id;


ALTER TABLE appautocountries OWNER TO appdb;

--
-- Name: appcountries; Type: VIEW; Schema: public; Owner: appdb
--

CREATE VIEW appcountries AS
 SELECT DISTINCT t.id,
    t.name,
    t.continent,
    t.isocode,
    t.regionid,
    t.appid,
    max(t.inherited) AS inherited
   FROM ( SELECT countries.id,
            countries.name,
            countries.continent,
            countries.isocode,
            countries.regionid,
            appautocountries.id AS appid,
            1 AS inherited
           FROM (appautocountries
             LEFT JOIN countries ON ((countries.id = appautocountries.countryid)))
        UNION
         SELECT countries.id,
            countries.name,
            countries.continent,
            countries.isocode,
            countries.regionid,
            appmanualcountries.appid,
            0 AS inherited
           FROM (appmanualcountries
             LEFT JOIN countries ON ((countries.id = appmanualcountries.countryid)))
  ORDER BY 6, 1, 7 DESC) t
  GROUP BY t.id, t.name, t.continent, t.isocode, t.regionid, t.appid;


ALTER TABLE appcountries OWNER TO appdb;

--
-- Name: middlewares; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE middlewares (
    id integer NOT NULL,
    name text,
    link text
);


ALTER TABLE middlewares OWNER TO appdb;

--
-- Name: app_tags; Type: VIEW; Schema: public; Owner: appdb
--

CREATE VIEW app_tags AS
 SELECT DISTINCT t.id,
    t.appid,
    t.researcherid,
    t.tag
   FROM ( SELECT __app_tags.id,
            __app_tags.appid,
            __app_tags.researcherid,
            __app_tags.tag
           FROM __app_tags
        UNION
         SELECT DISTINCT NULL::integer AS int4,
            applications.id,
            NULL::integer AS int4,
            vos.name
           FROM ((applications
             JOIN app_vos ON ((app_vos.appid = applications.id)))
             JOIN vos ON ((vos.id = app_vos.void)))
          WHERE ((NOT (vos.name IS NULL)) AND (NOT vos.deleted))
        UNION
         SELECT DISTINCT NULL::integer AS int4,
            applications.id,
            NULL::integer AS int4,
            appcountries.name
           FROM (applications
             JOIN appcountries ON ((appcountries.appid = applications.id)))
          WHERE (NOT (appcountries.name IS NULL))
        UNION
         SELECT DISTINCT NULL::integer AS int4,
            applications.id,
            NULL::integer AS int4,
                CASE
                    WHEN (middlewares.id = 5) THEN app_middlewares.comment
                    ELSE middlewares.name
                END AS name
           FROM ((applications
             JOIN app_middlewares ON ((app_middlewares.appid = applications.id)))
             JOIN middlewares ON ((middlewares.id = app_middlewares.middlewareid)))
          WHERE (NOT (
                CASE
                    WHEN (middlewares.id = 5) THEN app_middlewares.comment
                    ELSE middlewares.name
                END IS NULL))) t;


ALTER TABLE app_tags OWNER TO appdb;

--
-- Name: app_tags_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE app_tags_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE app_tags_id_seq OWNER TO appdb;

--
-- Name: app_tags_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE app_tags_id_seq OWNED BY __app_tags.id;


--
-- Name: app_urls; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE app_urls (
    id integer NOT NULL,
    appid integer NOT NULL,
    url text NOT NULL,
    description text,
    ord integer DEFAULT 0 NOT NULL,
    title text
);


ALTER TABLE app_urls OWNER TO appdb;

--
-- Name: app_urls_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE app_urls_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE app_urls_id_seq OWNER TO appdb;

--
-- Name: app_urls_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE app_urls_id_seq OWNED BY app_urls.id;


--
-- Name: app_validation_log; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE app_validation_log (
    id integer NOT NULL,
    appid integer NOT NULL,
    lastsent timestamp without time zone DEFAULT now() NOT NULL
);


ALTER TABLE app_validation_log OWNER TO appdb;

--
-- Name: app_validation_log_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE app_validation_log_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE app_validation_log_id_seq OWNER TO appdb;

--
-- Name: app_validation_log_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE app_validation_log_id_seq OWNED BY app_validation_log.id;


--
-- Name: appbookmarks; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE appbookmarks (
    appid integer NOT NULL,
    researcherid integer NOT NULL
);


ALTER TABLE appbookmarks OWNER TO appdb;

--
-- Name: appcategories; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE appcategories (
    id integer NOT NULL,
    appid integer NOT NULL,
    categoryid integer NOT NULL,
    isprimary boolean DEFAULT false,
    inherited boolean DEFAULT false,
    CONSTRAINT topcatprime CHECK ((NOT ((isprimary IS TRUE) AND (category_level(categoryid) > 0))))
);


ALTER TABLE appcategories OWNER TO appdb;

--
-- Name: appcategories_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE appcategories_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE appcategories_id_seq OWNER TO appdb;

--
-- Name: appcategories_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE appcategories_id_seq OWNED BY appcategories.id;


--
-- Name: appcontact_middlewares; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE appcontact_middlewares (
    appid integer NOT NULL,
    researcherid integer NOT NULL,
    appmiddlewareid integer NOT NULL,
    note text
);


ALTER TABLE appcontact_middlewares OWNER TO appdb;

--
-- Name: appcontact_otheritems; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE appcontact_otheritems (
    appid integer NOT NULL,
    researcherid integer NOT NULL,
    item text NOT NULL,
    note text
);


ALTER TABLE appcontact_otheritems OWNER TO appdb;

--
-- Name: appcontact_vos; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE appcontact_vos (
    appid integer NOT NULL,
    researcherid integer NOT NULL,
    void integer NOT NULL,
    note text
);


ALTER TABLE appcontact_vos OWNER TO appdb;

--
-- Name: appcontact_items; Type: VIEW; Schema: public; Owner: appdb
--

CREATE VIEW appcontact_items AS
 SELECT appcontact_vos.appid,
    appcontact_vos.researcherid,
    appcontact_vos.void AS itemid,
    'vo'::text AS itemtype,
    vos.name AS item,
    appcontact_vos.note
   FROM (appcontact_vos
     JOIN vos ON ((vos.id = appcontact_vos.void)))
UNION
 SELECT appcontact_middlewares.appid,
    appcontact_middlewares.researcherid,
    middlewares.id AS itemid,
    'middleware'::text AS itemtype,
        CASE middlewares.id
            WHEN 5 THEN ( SELECT app_middlewares_1.comment
               FROM app_middlewares app_middlewares_1
              WHERE (app_middlewares_1.id = appcontact_middlewares.appmiddlewareid))
            ELSE middlewares.name
        END AS item,
    appcontact_middlewares.note
   FROM ((appcontact_middlewares
     JOIN app_middlewares ON ((app_middlewares.id = appcontact_middlewares.appmiddlewareid)))
     JOIN middlewares ON ((middlewares.id = app_middlewares.middlewareid)))
UNION
 SELECT appcontact_otheritems.appid,
    appcontact_otheritems.researcherid,
    NULL::integer AS itemid,
    'other'::text AS itemtype,
    appcontact_otheritems.item,
    appcontact_otheritems.note
   FROM appcontact_otheritems;


ALTER TABLE appcontact_items OWNER TO appdb;

--
-- Name: appdisciplines; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE appdisciplines (
    id integer NOT NULL,
    appid integer NOT NULL,
    disciplineid integer NOT NULL,
    inherited boolean
);


ALTER TABLE appdisciplines OWNER TO appdb;

--
-- Name: appdisciplines_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE appdisciplines_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE appdisciplines_id_seq OWNER TO appdb;

--
-- Name: appdisciplines_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE appdisciplines_id_seq OWNED BY appdisciplines.id;


--
-- Name: appdocuments_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE appdocuments_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE appdocuments_id_seq OWNER TO appdb;

--
-- Name: appdocuments_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE appdocuments_id_seq OWNED BY appdocuments.id;


--
-- Name: appdomains; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE appdomains (
    id integer NOT NULL,
    appid integer NOT NULL,
    domainid integer NOT NULL
);


ALTER TABLE appdomains OWNER TO appdb;

--
-- Name: appgroups; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE appgroups (
    id integer NOT NULL,
    name text NOT NULL
);


ALTER TABLE appgroups OWNER TO appdb;

--
-- Name: appgroups_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE appgroups_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE appgroups_id_seq OWNER TO appdb;

--
-- Name: appgroups_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE appgroups_id_seq OWNED BY appgroups.id;


--
-- Name: applications_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE applications_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE applications_id_seq OWNER TO appdb;

--
-- Name: applications_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE applications_id_seq OWNED BY applications.id;


--
-- Name: applogos; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE applogos (
    appid integer NOT NULL,
    logo bytea
);


ALTER TABLE applogos OWNER TO appdb;

--
-- Name: appmodhistories; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE appmodhistories (
    id integer NOT NULL,
    researcherid integer NOT NULL,
    datemod timestamp without time zone DEFAULT now() NOT NULL,
    appid integer NOT NULL
);


ALTER TABLE appmodhistories OWNER TO appdb;

--
-- Name: appteam; Type: VIEW; Schema: public; Owner: appdb
--

CREATE VIEW appteam AS
 SELECT applications.id AS appid,
    researchers.id,
    researchers.firstname,
    researchers.lastname,
    researchers.dateinclusion,
    researchers.institution,
    researchers.countryid,
    researchers.positiontypeid
   FROM ((applications
     JOIN researchers_apps ON ((researchers_apps.appid = applications.id)))
     JOIN researchers ON ((researchers.id = researchers_apps.researcherid)));


ALTER TABLE appteam OWNER TO appdb;

--
-- Name: appviews; Type: VIEW; Schema: public; Owner: appdb
--

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
    appcountries.id AS countryid,
    appcountries.regionid,
    __app_vos.void,
    ((((appteam.lastname || ' '::text) || appteam.firstname) || ' '::text) || appteam.institution) AS persondata,
        CASE
            WHEN (EXISTS ( SELECT appdocuments.id
               FROM appdocuments
              WHERE (appdocuments.appid = applications.id))) THEN 1
            ELSE 0
        END AS hasdocs,
    applications.guid,
    applications.deleted,
    applications.moderated,
    applications.categoryid,
    applications.metatype
   FROM (((((applications
     LEFT JOIN appcountries ON ((appcountries.appid = applications.id)))
     LEFT JOIN __app_vos ON ((__app_vos.appid = applications.id)))
     LEFT JOIN appteam ON ((appteam.appid = applications.id)))
     LEFT JOIN app_middlewares ON ((app_middlewares.appid = applications.id)))
     LEFT JOIN applogos ON ((applogos.appid = applications.id)))
  ORDER BY applications.id;


ALTER TABLE appviews OWNER TO appdb;

--
-- Name: apppercountries; Type: VIEW; Schema: public; Owner: appdb
--

CREATE VIEW apppercountries AS
 SELECT countries.id,
    countries.name,
    countries.isocode,
    ( SELECT count(DISTINCT appviews.id) AS count
           FROM appviews
          WHERE (((appviews.countryid = countries.id) AND (appviews.deleted IS FALSE)) AND (appviews.moderated IS FALSE))) AS sum
   FROM countries;


ALTER TABLE apppercountries OWNER TO appdb;

--
-- Name: appproglangs; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE appproglangs (
    appid integer NOT NULL,
    proglangid integer NOT NULL
);


ALTER TABLE appproglangs OWNER TO appdb;

--
-- Name: appratings_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE appratings_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE appratings_id_seq OWNER TO appdb;

--
-- Name: appratings_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE appratings_id_seq OWNED BY appratings.id;


--
-- Name: appsubdomains; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE appsubdomains (
    id integer NOT NULL,
    appid integer NOT NULL,
    subdomainid integer NOT NULL
);


ALTER TABLE appsubdomains OWNER TO appdb;

--
-- Name: archs_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE archs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE archs_id_seq OWNER TO appdb;

--
-- Name: archs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE archs_id_seq OWNED BY archs.id;


--
-- Name: extauthors; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE extauthors (
    id integer NOT NULL,
    docid integer,
    author text NOT NULL,
    main boolean DEFAULT false NOT NULL
);


ALTER TABLE extauthors OWNER TO appdb;

--
-- Name: intauthors; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE intauthors (
    id integer NOT NULL,
    docid integer,
    authorid integer NOT NULL,
    main boolean DEFAULT false NOT NULL
);


ALTER TABLE intauthors OWNER TO appdb;

--
-- Name: authors; Type: VIEW; Schema: public; Owner: appdb
--

CREATE VIEW authors AS
 SELECT intauthors.id,
    intauthors.docid,
    intauthors.authorid,
    ((researchers.lastname || ' '::text) || researchers.firstname) AS fullname,
    intauthors.main
   FROM (intauthors
     JOIN researchers ON ((researchers.id = intauthors.authorid)))
UNION
 SELECT extauthors.id,
    extauthors.docid,
    NULL::integer AS authorid,
    extauthors.author AS fullname,
    extauthors.main
   FROM extauthors;


ALTER TABLE authors OWNER TO appdb;

--
-- Name: categories; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE categories (
    id integer NOT NULL,
    name text,
    ord integer DEFAULT 0 NOT NULL,
    parentid integer
);


ALTER TABLE categories OWNER TO appdb;

--
-- Name: categories_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE categories_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE categories_id_seq OWNER TO appdb;

--
-- Name: categories_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE categories_id_seq OWNED BY categories.id;


--
-- Name: category_help; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE category_help (
    id integer NOT NULL,
    categoryid integer,
    type integer DEFAULT 0 NOT NULL,
    data text NOT NULL
);


ALTER TABLE category_help OWNER TO appdb;

--
-- Name: category_help_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE category_help_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE category_help_id_seq OWNER TO appdb;

--
-- Name: category_help_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE category_help_id_seq OWNED BY category_help.id;


--
-- Name: config; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE config (
    var text,
    data text
);


ALTER TABLE config OWNER TO appdb;

--
-- Name: contacts_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE contacts_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE contacts_id_seq OWNER TO appdb;

--
-- Name: contacts_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE contacts_id_seq OWNED BY contacts.id;


--
-- Name: contacttypes; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE contacttypes (
    id integer NOT NULL,
    description text NOT NULL
);


ALTER TABLE contacttypes OWNER TO appdb;

--
-- Name: context_script_assocs_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE context_script_assocs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE context_script_assocs_id_seq OWNER TO appdb;

--
-- Name: context_script_assocs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE context_script_assocs_id_seq OWNED BY context_script_assocs.id;


--
-- Name: contextformats; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE contextformats (
    id integer NOT NULL,
    name text NOT NULL,
    description text
);


ALTER TABLE contextformats OWNER TO appdb;

--
-- Name: contextformats_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE contextformats_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE contextformats_id_seq OWNER TO appdb;

--
-- Name: contextformats_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE contextformats_id_seq OWNED BY contextformats.id;


--
-- Name: contexts_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE contexts_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE contexts_id_seq OWNER TO appdb;

--
-- Name: contexts_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE contexts_id_seq OWNED BY contexts.id;


--
-- Name: contextscripts_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE contextscripts_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE contextscripts_id_seq OWNER TO appdb;

--
-- Name: contextscripts_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE contextscripts_id_seq OWNED BY contextscripts.id;


--
-- Name: contracttypes; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE contracttypes (
    id integer NOT NULL,
    name text NOT NULL,
    title text,
    groupname text
);


ALTER TABLE contracttypes OWNER TO appdb;

--
-- Name: contracttypes_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE contracttypes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE contracttypes_id_seq OWNER TO appdb;

--
-- Name: contracttypes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE contracttypes_id_seq OWNED BY contracttypes.id;


--
-- Name: countries_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE countries_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE countries_id_seq OWNER TO appdb;

--
-- Name: countries_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE countries_id_seq OWNED BY countries.id;


--
-- Name: countryregions; Type: VIEW; Schema: public; Owner: appdb
--

CREATE VIEW countryregions AS
 SELECT regions.id AS regionid,
    regions.name AS region,
    countries.id AS countryid,
    countries.name AS country,
    countries.isocode,
    countries.continent
   FROM (countries
     JOIN regions ON ((regions.id = countries.regionid)))
  ORDER BY regions.name;


ALTER TABLE countryregions OWNER TO appdb;

--
-- Name: dataset_categories; Type: VIEW; Schema: public; Owner: appdb
--

CREATE VIEW dataset_categories AS
 SELECT e.enumsortorder AS id,
    e.enumlabel AS name
   FROM (pg_enum e
     JOIN pg_type t ON ((e.enumtypid = t.oid)))
  WHERE (t.typname = 'e_dataset_category'::name);


ALTER TABLE dataset_categories OWNER TO appdb;

--
-- Name: dataset_conn_types; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE dataset_conn_types (
    id integer NOT NULL,
    name text NOT NULL
);


ALTER TABLE dataset_conn_types OWNER TO appdb;

--
-- Name: dataset_conn_types_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE dataset_conn_types_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE dataset_conn_types_id_seq OWNER TO appdb;

--
-- Name: dataset_conn_types_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE dataset_conn_types_id_seq OWNED BY dataset_conn_types.id;


--
-- Name: dataset_disciplines; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE dataset_disciplines (
    id integer NOT NULL,
    datasetid integer NOT NULL,
    disciplineid integer NOT NULL,
    inherited boolean,
    CONSTRAINT validate_derived_dataset_disciplines CHECK (derived_dataset_discipline_valid(datasetid, disciplineid))
);


ALTER TABLE dataset_disciplines OWNER TO appdb;

--
-- Name: dataset_disciplines_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE dataset_disciplines_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE dataset_disciplines_id_seq OWNER TO appdb;

--
-- Name: dataset_disciplines_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE dataset_disciplines_id_seq OWNED BY dataset_disciplines.id;


--
-- Name: dataset_exchange_formats; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE dataset_exchange_formats (
    id integer NOT NULL,
    shortname text NOT NULL,
    name text NOT NULL
);


ALTER TABLE dataset_exchange_formats OWNER TO appdb;

--
-- Name: dataset_exchange_formats_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE dataset_exchange_formats_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE dataset_exchange_formats_id_seq OWNER TO appdb;

--
-- Name: dataset_exchange_formats_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE dataset_exchange_formats_id_seq OWNED BY dataset_exchange_formats.id;


--
-- Name: dataset_licenses; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE dataset_licenses (
    id integer NOT NULL,
    datasetid integer NOT NULL,
    licenseid integer,
    title text,
    comment text,
    link text
);


ALTER TABLE dataset_licenses OWNER TO appdb;

--
-- Name: dataset_licenses_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE dataset_licenses_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE dataset_licenses_id_seq OWNER TO appdb;

--
-- Name: dataset_licenses_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE dataset_licenses_id_seq OWNED BY dataset_licenses.id;


--
-- Name: dataset_location_organizations; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE dataset_location_organizations (
    id integer NOT NULL,
    dataset_location_id integer NOT NULL,
    organizationid integer NOT NULL
);


ALTER TABLE dataset_location_organizations OWNER TO appdb;

--
-- Name: dataset_location_organizations_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE dataset_location_organizations_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE dataset_location_organizations_id_seq OWNER TO appdb;

--
-- Name: dataset_location_organizations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE dataset_location_organizations_id_seq OWNED BY dataset_location_organizations.id;


--
-- Name: dataset_location_sites; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE dataset_location_sites (
    id integer NOT NULL,
    dataset_location_id integer NOT NULL,
    siteid text NOT NULL
);


ALTER TABLE dataset_location_sites OWNER TO appdb;

--
-- Name: dataset_location_sites_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE dataset_location_sites_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE dataset_location_sites_id_seq OWNER TO appdb;

--
-- Name: dataset_location_sites_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE dataset_location_sites_id_seq OWNED BY dataset_location_sites.id;


--
-- Name: dataset_locations; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE dataset_locations (
    id integer NOT NULL,
    addedby integer NOT NULL,
    addedon date DEFAULT now() NOT NULL,
    uri text NOT NULL,
    is_master boolean DEFAULT false NOT NULL,
    exchange_fmt integer NOT NULL,
    connection_type integer NOT NULL,
    is_public boolean DEFAULT true NOT NULL,
    notes text,
    dataset_version_id integer NOT NULL,
    siteid text[],
    organizationid integer[]
);


ALTER TABLE dataset_locations OWNER TO appdb;

--
-- Name: dataset_locations_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE dataset_locations_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE dataset_locations_id_seq OWNER TO appdb;

--
-- Name: dataset_locations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE dataset_locations_id_seq OWNED BY dataset_locations.id;


--
-- Name: dataset_versions; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE dataset_versions (
    id integer NOT NULL,
    datasetid integer NOT NULL,
    version text NOT NULL,
    notes text,
    size numeric(40,0),
    addedby integer,
    addedon timestamp without time zone DEFAULT now() NOT NULL,
    guid uuid DEFAULT uuid_generate_v4() NOT NULL,
    parentid integer,
    CONSTRAINT valid_parentid CHECK (dataset_version_parentid_valid(id, parentid, datasetid))
);


ALTER TABLE dataset_versions OWNER TO appdb;

--
-- Name: dataset_versions_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE dataset_versions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE dataset_versions_id_seq OWNER TO appdb;

--
-- Name: dataset_versions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE dataset_versions_id_seq OWNED BY dataset_versions.id;


--
-- Name: datasets; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE datasets (
    id integer NOT NULL,
    name text NOT NULL,
    description text,
    disciplineid integer[],
    addedby integer NOT NULL,
    addedon date DEFAULT now() NOT NULL,
    tags text[],
    guid uuid DEFAULT uuid_generate_v4(),
    homepage text,
    category e_dataset_category NOT NULL,
    elixir_url text,
    parentid integer,
    CONSTRAINT valid_parent CHECK (dataset_parentid_valid(id, parentid))
);


ALTER TABLE datasets OWNER TO appdb;

--
-- Name: datasets_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE datasets_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE datasets_id_seq OWNER TO appdb;

--
-- Name: datasets_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE datasets_id_seq OWNED BY datasets.id;


--
-- Name: discipline_help; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE discipline_help (
    id integer NOT NULL,
    disciplineid integer,
    type integer DEFAULT 0 NOT NULL,
    data text NOT NULL
);


ALTER TABLE discipline_help OWNER TO appdb;

--
-- Name: discipline_help_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE discipline_help_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE discipline_help_id_seq OWNER TO appdb;

--
-- Name: discipline_help_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE discipline_help_id_seq OWNED BY discipline_help.id;


--
-- Name: dissemination; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE dissemination (
    id integer NOT NULL,
    composerid integer NOT NULL,
    recipients integer[] NOT NULL,
    filter text,
    subject text NOT NULL,
    message text NOT NULL,
    senton timestamp without time zone DEFAULT now() NOT NULL
);


ALTER TABLE dissemination OWNER TO appdb;

--
-- Name: dissemination_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE dissemination_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE dissemination_id_seq OWNER TO appdb;

--
-- Name: dissemination_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE dissemination_id_seq OWNED BY dissemination.id;


--
-- Name: doctypes; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE doctypes (
    id integer NOT NULL,
    description text NOT NULL
);


ALTER TABLE doctypes OWNER TO appdb;

--
-- Name: domains; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE domains (
    id integer NOT NULL,
    name text NOT NULL
);


ALTER TABLE domains OWNER TO appdb;

--
-- Name: editable_apps; Type: VIEW; Schema: public; Owner: appdb
--

CREATE VIEW editable_apps AS
 SELECT DISTINCT applications.id AS appid,
    permissions.actor
   FROM (applications
     LEFT JOIN permissions ON (((permissions.object = applications.guid) OR (permissions.object IS NULL))))
  WHERE (permissions.actionid = ANY (app_metadata_actions()));


ALTER TABLE editable_apps OWNER TO appdb;

--
-- Name: entityguids; Type: VIEW; Schema: public; Owner: appdb
--

CREATE VIEW entityguids AS
 SELECT 'software'::e_entity AS entitytype,
    applications.guid
   FROM applications
  WHERE (applications.metatype = 0)
UNION ALL
 SELECT 'vappliance'::e_entity AS entitytype,
    applications.guid
   FROM applications
  WHERE (applications.metatype = 1)
UNION ALL
 SELECT 'swappliance'::e_entity AS entitytype,
    applications.guid
   FROM applications
  WHERE (applications.metatype = 2)
UNION ALL
 SELECT 'person'::e_entity AS entitytype,
    researchers.guid
   FROM researchers
  WHERE (NOT researchers.deleted)
UNION ALL
 SELECT 'vo'::e_entity AS entitytype,
    vos.guid
   FROM vos
  WHERE (NOT vos.deleted)
UNION ALL
 SELECT 'organization'::e_entity AS entitytype,
    organizations.guid
   FROM organizations
  WHERE (organizations.deletedby IS NULL)
UNION ALL
 SELECT 'project'::e_entity AS entitytype,
    projects.guid
   FROM projects
  WHERE (projects.deletedby IS NULL)
UNION ALL
 SELECT 'publication'::e_entity AS entitytype,
    appdocuments.guid
   FROM appdocuments;


ALTER TABLE entityguids OWNER TO appdb;

--
-- Name: relations; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE relations (
    id integer NOT NULL,
    reltypeid integer NOT NULL,
    target_guid uuid NOT NULL,
    subject_guid uuid NOT NULL,
    parentid integer,
    addedon timestamp without time zone DEFAULT now() NOT NULL,
    addedby integer,
    denyon timestamp without time zone,
    denyby integer,
    guid uuid DEFAULT uuid_generate_v4(),
    hiddenon timestamp without time zone,
    hiddenby integer,
    CONSTRAINT chk_subject_type CHECK (valid_relation(subject_guid, reltypeid, target_guid))
);


ALTER TABLE relations OWNER TO appdb;

--
-- Name: relationverbs; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE relationverbs (
    id integer NOT NULL,
    name text NOT NULL,
    dname text NOT NULL,
    rname text NOT NULL
);


ALTER TABLE relationverbs OWNER TO appdb;

--
-- Name: entityrelations; Type: VIEW; Schema: public; Owner: appdb
--

CREATE VIEW entityrelations AS
 SELECT r.id,
    r.reltypeid,
    rv.id AS verbid,
    r.target_guid,
    rt.target_type,
    rt.actionid,
    rv.name AS verb,
    rv.dname AS verbname,
    rv.rname AS verbrname,
    r.subject_guid,
    rt.subject_type,
    rt.guid AS reltypeguid,
    r.guid,
    r.addedon,
    r.addedby,
    r.denyon,
    r.denyby,
    r.hiddenon,
    r.hiddenby
   FROM ((relations r
     JOIN relationtypes rt ON ((rt.id = r.reltypeid)))
     JOIN relationverbs rv ON ((rv.id = rt.verbid)));


ALTER TABLE entityrelations OWNER TO appdb;

--
-- Name: entitysources; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE entitysources (
    id integer NOT NULL,
    name text NOT NULL,
    description text
);


ALTER TABLE entitysources OWNER TO appdb;

--
-- Name: entitysources_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE entitysources_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE entitysources_id_seq OWNER TO appdb;

--
-- Name: entitysources_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE entitysources_id_seq OWNED BY entitysources.id;


--
-- Name: entitytypes; Type: VIEW; Schema: public; Owner: appdb
--

CREATE VIEW entitytypes AS
 SELECT e.enumsortorder AS id,
    e.enumlabel AS name
   FROM (pg_enum e
     JOIN pg_type t ON ((e.enumtypid = t.oid)))
  WHERE (t.typname = 'e_entity'::name)
  ORDER BY e.enumsortorder;


ALTER TABLE entitytypes OWNER TO appdb;

--
-- Name: extauthors_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE extauthors_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE extauthors_id_seq OWNER TO appdb;

--
-- Name: extauthors_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE extauthors_id_seq OWNED BY extauthors.id;


--
-- Name: faq_history; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE faq_history (
    id integer NOT NULL,
    faqid integer,
    question text,
    answer text,
    submitter integer,
    "when" timestamp without time zone
);


ALTER TABLE faq_history OWNER TO appdb;

--
-- Name: faq_history_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE faq_history_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE faq_history_id_seq OWNER TO appdb;

--
-- Name: faq_history_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE faq_history_id_seq OWNED BY faq_history.id;


--
-- Name: faqs; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE faqs (
    id integer NOT NULL,
    question text NOT NULL,
    answer text NOT NULL,
    submitter integer NOT NULL,
    "when" timestamp without time zone DEFAULT now() NOT NULL,
    ord integer,
    locked boolean DEFAULT false
);


ALTER TABLE faqs OWNER TO appdb;

--
-- Name: faqs_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE faqs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE faqs_id_seq OWNER TO appdb;

--
-- Name: faqs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE faqs_id_seq OWNED BY faqs.id;


--
-- Name: fundings; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE fundings (
    id integer NOT NULL,
    name text NOT NULL,
    description text,
    parentid integer,
    identifier text
);


ALTER TABLE fundings OWNER TO appdb;

--
-- Name: fundings_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE fundings_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE fundings_id_seq OWNER TO appdb;

--
-- Name: fundings_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE fundings_id_seq OWNED BY fundings.id;


--
-- Name: hashfuncs; Type: VIEW; Schema: public; Owner: appdb
--

CREATE VIEW hashfuncs AS
 SELECT e.enumsortorder AS id,
    e.enumlabel AS name
   FROM (pg_enum e
     JOIN pg_type t ON ((e.enumtypid = t.oid)))
  WHERE (t.typname = 'e_hashfuncs'::name);


ALTER TABLE hashfuncs OWNER TO appdb;

--
-- Name: hitcounts; Type: VIEW; Schema: public; Owner: appdb
--

CREATE VIEW hitcounts AS
 SELECT count(*) AS count,
    app_api_log.appid
   FROM app_api_log
  GROUP BY app_api_log.appid;


ALTER TABLE hitcounts OWNER TO appdb;

--
-- Name: hypervisors; Type: VIEW; Schema: public; Owner: appdb
--

CREATE VIEW hypervisors AS
 SELECT e.enumsortorder AS id,
    e.enumlabel AS name
   FROM (pg_enum e
     JOIN pg_type t ON ((e.enumtypid = t.oid)))
  WHERE (t.typname = 'e_hypervisors'::name);


ALTER TABLE hypervisors OWNER TO appdb;

--
-- Name: idps; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE idps (
    id integer NOT NULL,
    entityid text NOT NULL,
    title text NOT NULL,
    displaynames text[],
    descr text,
    country text DEFAULT '_all_'::text NOT NULL,
    auth text DEFAULT 'saml'::text NOT NULL,
    weight integer DEFAULT 0 NOT NULL,
    enabled boolean DEFAULT false NOT NULL,
    lat numeric,
    lon numeric
);


ALTER TABLE idps OWNER TO appdb;

--
-- Name: idps_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE idps_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE idps_id_seq OWNER TO appdb;

--
-- Name: idps_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE idps_id_seq OWNED BY idps.id;


--
-- Name: intauthors_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE intauthors_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE intauthors_id_seq OWNER TO appdb;

--
-- Name: intauthors_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE intauthors_id_seq OWNED BY intauthors.id;


--
-- Name: version; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE version (
    version character varying(10),
    major integer,
    minor integer,
    revision integer,
    notes text
);


ALTER TABLE version OWNER TO appdb;

--
-- Name: latestversion; Type: VIEW; Schema: public; Owner: appdb
--

CREATE VIEW latestversion AS
 SELECT version.version,
    version.major,
    version.minor,
    version.revision,
    version.notes
   FROM version
  ORDER BY version.major DESC, version.minor DESC, version.revision DESC
 LIMIT 1;


ALTER TABLE latestversion OWNER TO appdb;

--
-- Name: ldap_attr_mappings; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE ldap_attr_mappings (
    id integer NOT NULL,
    oc_map_id integer NOT NULL,
    name text NOT NULL,
    sel_expr text NOT NULL,
    sel_expr_u text,
    from_tbls text NOT NULL,
    join_where text,
    add_proc text,
    delete_proc text,
    param_order integer NOT NULL,
    expect_return integer NOT NULL
);


ALTER TABLE ldap_attr_mappings OWNER TO appdb;

--
-- Name: ldap_attr_mappings_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE ldap_attr_mappings_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE ldap_attr_mappings_id_seq OWNER TO appdb;

--
-- Name: ldap_attr_mappings_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE ldap_attr_mappings_id_seq OWNED BY ldap_attr_mappings.id;


--
-- Name: vldap_groups; Type: VIEW; Schema: public; Owner: appdb
--

CREATE VIEW vldap_groups AS
 SELECT applications.id,
    applications.name,
    ('g'::text || (applications.id)::text) AS groupname
   FROM applications
  WHERE (((applications.metatype = ANY (ARRAY[0, 1])) AND (applications.deleted = false)) AND (applications.moderated = false));


ALTER TABLE vldap_groups OWNER TO appdb;

--
-- Name: vldap_users; Type: VIEW; Schema: public; Owner: appdb
--

CREATE VIEW vldap_users AS
 SELECT researchers.id,
    ('p'::text || (researchers.id)::text) AS username,
    NULL::text AS password,
    researchers.firstname AS first_name,
    researchers.lastname AS last_name,
    ((researchers.firstname || ' '::text) || researchers.lastname) AS fullname
   FROM researchers
  WHERE (researchers.deleted = false);


ALTER TABLE vldap_users OWNER TO appdb;

--
-- Name: VIEW vldap_users; Type: COMMENT; Schema: public; Owner: appdb
--

COMMENT ON VIEW vldap_users IS 'fake username and password after multiple account support, until further notice';


--
-- Name: ldap_entries; Type: VIEW; Schema: public; Owner: appdb
--

CREATE VIEW ldap_entries AS
 SELECT (100000 + vldap_users.id) AS id,
    lower((('cn='::text || vldap_users.username) || ',ou=people,dc=appdb'::text)) AS dn,
    1 AS oc_map_id,
    0 AS parent,
    vldap_users.id AS keyval
   FROM vldap_users
UNION
 SELECT (200000 + vldap_groups.id) AS id,
    lower((('cn=g'::text || vldap_groups.id) || ',ou=groups,dc=appdb'::text)) AS dn,
    2 AS oc_map_id,
    0 AS parent,
    vldap_groups.id AS keyval
   FROM vldap_groups;


ALTER TABLE ldap_entries OWNER TO appdb;

--
-- Name: ldap_entry_objclasses; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE ldap_entry_objclasses (
    entry_id integer NOT NULL,
    oc_name text
);


ALTER TABLE ldap_entry_objclasses OWNER TO appdb;

--
-- Name: ldap_oc_mappings; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE ldap_oc_mappings (
    id integer NOT NULL,
    name text NOT NULL,
    keytbl text NOT NULL,
    keycol text NOT NULL,
    create_proc text,
    delete_proc text,
    expect_return integer NOT NULL
);


ALTER TABLE ldap_oc_mappings OWNER TO appdb;

--
-- Name: ldap_oc_mappings_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE ldap_oc_mappings_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE ldap_oc_mappings_id_seq OWNER TO appdb;

--
-- Name: ldap_oc_mappings_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE ldap_oc_mappings_id_seq OWNED BY ldap_oc_mappings.id;


--
-- Name: licenses; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE licenses (
    id integer NOT NULL,
    name text NOT NULL,
    title text NOT NULL,
    link text NOT NULL,
    "group" text
);


ALTER TABLE licenses OWNER TO appdb;

--
-- Name: licenses_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE licenses_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE licenses_id_seq OWNER TO appdb;

--
-- Name: licenses_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE licenses_id_seq OWNED BY licenses.id;


--
-- Name: linksdb; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE linksdb (
    urlname text,
    parentname text,
    baseref text,
    valid integer,
    result text,
    warning text,
    info text,
    url text,
    line integer,
    col integer,
    name text,
    checktime integer,
    dltime integer,
    dlsize integer,
    cached integer,
    firstchecked timestamp without time zone DEFAULT now() NOT NULL,
    lastchecked timestamp without time zone DEFAULT now() NOT NULL,
    level integer
);


ALTER TABLE linksdb OWNER TO appdb;

--
-- Name: linksdb_new; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE linksdb_new (
    urlname text,
    parentname text,
    baseref text,
    valid integer,
    result text,
    warning text,
    info text,
    url text,
    line integer,
    col integer,
    name text,
    checktime integer,
    dltime integer,
    dlsize integer,
    cached integer,
    firstchecked timestamp without time zone DEFAULT now() NOT NULL,
    lastchecked timestamp without time zone DEFAULT now() NOT NULL
);


ALTER TABLE linksdb_new OWNER TO appdb;

--
-- Name: linksdb_x; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE linksdb_x (
    urlname text,
    parentname text,
    baseref text,
    valid integer,
    result text,
    warning text,
    info text,
    url text,
    line integer,
    col integer,
    name text,
    checktime integer,
    dltime integer,
    dlsize integer,
    cached integer,
    firstchecked timestamp without time zone NOT NULL,
    lastchecked timestamp without time zone NOT NULL,
    level integer
);


ALTER TABLE linksdb_x OWNER TO appdb;

--
-- Name: url_whitelist; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE url_whitelist (
    id integer NOT NULL,
    url text,
    since timestamp without time zone DEFAULT now() NOT NULL
);


ALTER TABLE url_whitelist OWNER TO appdb;

--
-- Name: linkstatuses; Type: VIEW; Schema: public; Owner: appdb
--

CREATE VIEW linkstatuses AS
 SELECT DISTINCT ON (t.linkid, t.linktype) applications.name AS appname,
    applications.addedby AS ownerid,
    ((researchers.firstname || ' '::text) || researchers.lastname) AS ownername,
    ( SELECT contacts.data
           FROM contacts
          WHERE ((contacts.contacttypeid = 7) AND (contacts.researcherid = applications.addedby))
         LIMIT 1) AS contact,
        CASE t.linktype
            WHEN 'APP'::text THEN ( SELECT app_urls.description
               FROM app_urls
              WHERE ((app_urls.id)::text = t.linkid))
            WHEN 'DOC'::text THEN ( SELECT appdocuments.title
               FROM appdocuments
              WHERE ((appdocuments.id)::text = t.linkid))
            ELSE NULL::text
        END AS title,
    t.linkid,
    t.appid,
    t.linktype,
    t.urlname,
    t.parentname,
    t.baseref,
    t.valid,
    t.result,
    t.warning,
    t.info,
    t.url,
    t.line,
    t.col,
    t.name,
    t.checktime,
    t.dltime,
    t.dlsize,
    t.cached,
    t.firstchecked,
    t.lastchecked,
    t.age,
    ((( SELECT count(*) AS count
           FROM url_whitelist
          WHERE (url_whitelist.url = t.url)))::integer)::boolean AS whitelisted
   FROM ((( SELECT "substring"(linksdb.name, 4) AS linkid,
                CASE
                    WHEN ("substring"(linksdb.name, 1, 3) = 'APP'::text) THEN ( SELECT app_urls.appid
                       FROM app_urls
                      WHERE ((app_urls.id)::text = "substring"(linksdb.name, 4)))
                    WHEN ("substring"(linksdb.name, 1, 3) = 'DOC'::text) THEN ( SELECT appdocuments.appid
                       FROM appdocuments
                      WHERE ((appdocuments.id)::text = "substring"(linksdb.name, 4)))
                    ELSE NULL::integer
                END AS appid,
            "substring"(linksdb.name, 1, 3) AS linktype,
            linksdb.urlname,
            linksdb.parentname,
            linksdb.baseref,
            linksdb.valid,
            linksdb.result,
            linksdb.warning,
            linksdb.info,
            linksdb.url,
            linksdb.line,
            linksdb.col,
            linksdb.name,
            linksdb.checktime,
            linksdb.dltime,
            linksdb.dlsize,
            linksdb.cached,
            linksdb.firstchecked,
            linksdb.lastchecked,
            (linksdb.lastchecked - linksdb.firstchecked) AS age
           FROM linksdb) t
     JOIN applications ON ((applications.id = t.appid)))
     LEFT JOIN researchers ON ((researchers.id = applications.addedby)));


ALTER TABLE linkstatuses OWNER TO appdb;

--
-- Name: mail_subscriptions; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE mail_subscriptions (
    id integer NOT NULL,
    name text,
    subjecttype text,
    events integer,
    researcherid integer NOT NULL,
    delivery integer,
    flt text DEFAULT ''::text NOT NULL,
    unsubscribe_pwd uuid DEFAULT uuid_generate_v4() NOT NULL,
    flthash numeric
);


ALTER TABLE mail_subscriptions OWNER TO appdb;

--
-- Name: mail_subscriptions_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE mail_subscriptions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE mail_subscriptions_id_seq OWNER TO appdb;

--
-- Name: mail_subscriptions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE mail_subscriptions_id_seq OWNED BY mail_subscriptions.id;


--
-- Name: messages; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE messages (
    id integer NOT NULL,
    receiverid integer NOT NULL,
    senderid integer,
    msg text,
    senton timestamp without time zone DEFAULT now(),
    isread boolean DEFAULT false NOT NULL
);


ALTER TABLE messages OWNER TO appdb;

--
-- Name: messages_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE messages_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE messages_id_seq OWNER TO appdb;

--
-- Name: messages_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE messages_id_seq OWNED BY messages.id;


--
-- Name: middlewares_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE middlewares_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE middlewares_id_seq OWNER TO appdb;

--
-- Name: middlewares_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE middlewares_id_seq OWNED BY middlewares.id;


--
-- Name: news_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE news_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE news_id_seq OWNER TO appdb;

--
-- Name: news_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE news_id_seq OWNED BY news.id;


--
-- Name: ngis; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE ngis (
    id integer NOT NULL,
    name text,
    description text,
    countryid integer,
    url text,
    european boolean,
    logo bytea
);


ALTER TABLE ngis OWNER TO appdb;

--
-- Name: ngis_hidden; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE ngis_hidden (
    id integer NOT NULL,
    name text,
    description text,
    countryid integer,
    url text,
    european boolean,
    logo bytea
);


ALTER TABLE ngis_hidden OWNER TO appdb;

--
-- Name: ngis_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE ngis_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE ngis_id_seq OWNER TO appdb;

--
-- Name: ngis_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE ngis_id_seq OWNED BY ngis.id;


--
-- Name: nonvalidated_apps_per_owner; Type: VIEW; Schema: public; Owner: appdb
--

CREATE VIEW nonvalidated_apps_per_owner AS
 SELECT researchers.id AS ownerid,
    contacts.data AS contact,
    researchers.firstname,
    researchers.lastname,
    array_to_string(array_agg((applications.id)::text), ';'::text) AS appids,
    array_to_string(array_agg(applications.name), ';'::text) AS appnames,
    max(app_validation_log.lastsent) AS lastsent
   FROM (((applications
     JOIN researchers ON (((researchers.id = applications.addedby) OR (researchers.id = applications.owner))))
     LEFT JOIN contacts ON ((((contacts.researcherid = researchers.id) AND (contacts.contacttypeid = 7)) AND (contacts.isprimary IS TRUE))))
     LEFT JOIN app_validation_log ON ((app_validation_log.appid = applications.id)))
  WHERE (((((applications.lastupdated >= (now() - (( SELECT config.data
           FROM config
          WHERE (config.var = 'app_validation_period'::text)
         LIMIT 1))::interval)) AND (applications.lastupdated <= now())) IS FALSE) AND (applications.deleted IS FALSE)) AND (applications.moderated IS FALSE))
  GROUP BY researchers.id, contacts.data, researchers.firstname, researchers.lastname
 HAVING ((max(app_validation_log.lastsent) IS NULL) OR (now() >= (max(app_validation_log.lastsent) + (( SELECT config.data
           FROM config
          WHERE (config.var = 'app_validation_period2'::text)
         LIMIT 1))::interval)))
  ORDER BY researchers.id;


ALTER TABLE nonvalidated_apps_per_owner OWNER TO appdb;

--
-- Name: organizations_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE organizations_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE organizations_id_seq OWNER TO appdb;

--
-- Name: organizations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE organizations_id_seq OWNED BY organizations.id;


--
-- Name: orgs; Type: VIEW; Schema: public; Owner: appdb
--

CREATE VIEW orgs AS
 WITH ra AS (
         SELECT records_additional.record_id,
            records_additional.name,
            records_additional.value
           FROM harvest.records_additional
        )
 SELECT 0 AS id,
    ( SELECT ra.value
           FROM ra
          WHERE ((ra.name = 'legalname'::text) AND (ra.record_id = r.record_id))) AS name,
    ( SELECT ra.value
           FROM ra
          WHERE ((ra.name = 'legalshortlname'::text) AND (ra.record_id = r.record_id))) AS shortname,
    ( SELECT ra.value
           FROM ra
          WHERE ((ra.name = 'legalname'::text) AND (ra.record_id = r.record_id))) AS websiteurl,
    ( SELECT ra.value
           FROM ra
          WHERE ((ra.name = 'country_iso'::text) AND (ra.record_id = r.record_id))) AS countryid,
    NULL::timestamp without time zone AS addedon,
    NULL::integer AS addedby,
    NULL::uuid AS guid,
    r.appdb_identifier AS identifier,
    NULL::integer AS sourceid,
    NULL::timestamp without time zone AS deletedon,
    NULL::integer AS deletedby,
    r.external_identifier AS ext_identifier,
    NULL::boolean AS moderated,
    NULL::boolean AS deleted
   FROM harvest.records r
  WHERE (r.archive_id = 3);


ALTER TABLE orgs OWNER TO appdb;

--
-- Name: os_families; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE os_families (
    id integer NOT NULL,
    name text NOT NULL
);


ALTER TABLE os_families OWNER TO appdb;

--
-- Name: os_families_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE os_families_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE os_families_id_seq OWNER TO appdb;

--
-- Name: os_families_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE os_families_id_seq OWNED BY os_families.id;


--
-- Name: oses_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE oses_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE oses_id_seq OWNER TO appdb;

--
-- Name: oses_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE oses_id_seq OWNED BY oses.id;


--
-- Name: pending_accounts; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE pending_accounts (
    id integer NOT NULL,
    code uuid DEFAULT uuid_generate_v4() NOT NULL,
    researcherid bigint NOT NULL,
    accountid text NOT NULL,
    account_type e_account_type NOT NULL,
    account_name text,
    resolved boolean DEFAULT false NOT NULL,
    resolvedon timestamp without time zone,
    addedon timestamp without time zone DEFAULT now() NOT NULL
);


ALTER TABLE pending_accounts OWNER TO appdb;

--
-- Name: pending_accounts_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE pending_accounts_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE pending_accounts_id_seq OWNER TO appdb;

--
-- Name: pending_accounts_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE pending_accounts_id_seq OWNED BY pending_accounts.id;


--
-- Name: people_harvest_inst_matches; Type: MATERIALIZED VIEW; Schema: public; Owner: appdb; Tablespace: 
--

CREATE MATERIALIZED VIEW people_harvest_inst_matches AS
 SELECT DISTINCT n2.record_id,
    n1.institution AS personinst,
    n2.institution AS harvestinst,
    (n1.institution <-> n2.institution) AS dist
   FROM (researchers n1
     JOIN harvest.institutions n2 ON ((lower(n1.institution) % lower(n2.institution))))
  ORDER BY (n1.institution <-> n2.institution)
  WITH NO DATA;


ALTER TABLE people_harvest_inst_matches OWNER TO appdb;

--
-- Name: people_harvest_inst_matches2; Type: MATERIALIZED VIEW; Schema: public; Owner: appdb; Tablespace: 
--

CREATE MATERIALIZED VIEW people_harvest_inst_matches2 AS
 SELECT t1.record_id,
    t1.personinst,
    t1.harvestinst1,
    t1.harvestinst2,
    t1.dist1,
    t1.dist2,
    LEAST(t1.dist1, t1.dist2) AS dist
   FROM ( SELECT DISTINCT n2.record_id,
            n1.institution AS personinst,
            n2.institution2 AS harvestinst1,
            n2.institution AS harvestinst2,
            (n1.institution <-> n2.institution2) AS dist1,
            (n1.institution <-> n2.institution) AS dist2
           FROM (researchers n1
             JOIN harvest.institutions2 n2 ON (((lower(n1.institution) % lower(n2.institution)) OR (lower(n1.institution) % lower(n2.institution2)))))) t1
  ORDER BY LEAST(t1.dist1, t1.dist2)
  WITH NO DATA;


ALTER TABLE people_harvest_inst_matches2 OWNER TO appdb;

--
-- Name: perms; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE perms (
    researcherid integer NOT NULL,
    actionid integer NOT NULL,
    object uuid,
    id integer NOT NULL
);


ALTER TABLE perms OWNER TO appdb;

--
-- Name: positiontypes_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE positiontypes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE positiontypes_id_seq OWNER TO appdb;

--
-- Name: positiontypes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE positiontypes_id_seq OWNED BY positiontypes.id;


--
-- Name: ppl_api_log; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE ppl_api_log (
    id integer NOT NULL,
    pplid integer NOT NULL,
    "timestamp" timestamp without time zone DEFAULT now() NOT NULL,
    researcherid integer,
    source integer,
    ip text
);


ALTER TABLE ppl_api_log OWNER TO appdb;

--
-- Name: ppl_api_log_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE ppl_api_log_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE ppl_api_log_id_seq OWNER TO appdb;

--
-- Name: ppl_api_log_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE ppl_api_log_id_seq OWNED BY ppl_api_log.id;


--
-- Name: ppl_del_infos; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE ppl_del_infos (
    id integer NOT NULL,
    researcherid integer NOT NULL,
    deletedby integer,
    deletedon timestamp without time zone DEFAULT now() NOT NULL,
    roleid integer
);


ALTER TABLE ppl_del_infos OWNER TO appdb;

--
-- Name: ppl_del_infos_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE ppl_del_infos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE ppl_del_infos_id_seq OWNER TO appdb;

--
-- Name: ppl_del_infos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE ppl_del_infos_id_seq OWNED BY ppl_del_infos.id;


--
-- Name: pplcontacts; Type: VIEW; Schema: public; Owner: appdb
--

CREATE VIEW pplcontacts AS
 SELECT researchers.id,
    array_to_string(ARRAY( SELECT c.data
           FROM contacts c
          WHERE (c.researcherid = researchers.id)), ','::text) AS contactdata
   FROM (contacts
     JOIN researchers ON ((researchers.id = contacts.researcherid)))
  GROUP BY researchers.id;


ALTER TABLE pplcontacts OWNER TO appdb;

--
-- Name: pplhitcounts; Type: VIEW; Schema: public; Owner: appdb
--

CREATE VIEW pplhitcounts AS
 SELECT count(*) AS count,
    ppl_api_log.pplid
   FROM ppl_api_log
  GROUP BY ppl_api_log.pplid;


ALTER TABLE pplhitcounts OWNER TO appdb;

--
-- Name: pplproglangs; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE pplproglangs (
    researcherid integer NOT NULL,
    proglangid integer NOT NULL
);


ALTER TABLE pplproglangs OWNER TO appdb;

--
-- Name: researcherimages; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE researcherimages (
    researcherid integer NOT NULL,
    image bytea
);


ALTER TABLE researcherimages OWNER TO appdb;

--
-- Name: pplviews; Type: VIEW; Schema: public; Owner: appdb
--

CREATE VIEW pplviews AS
 SELECT researchers.id,
    researchers.firstname,
    researchers.lastname,
    researchers.dateinclusion,
    researchers.institution,
    researchers.countryid,
    researchers.positiontypeid,
    researcherimages.image,
    ((researchers.lastname || ' '::text) || researchers.firstname) AS name,
    countries.regionid,
    ( SELECT count(DISTINCT authors.docid) AS count
           FROM authors
          WHERE (authors.authorid = researchers.id)) AS doccount,
        CASE
            WHEN (EXISTS ( SELECT authors.docid
               FROM authors
              WHERE (authors.authorid = researchers.id))) THEN 1
            ELSE 0
        END AS hasdocs,
    researchers.guid
   FROM ((researchers
     JOIN countries ON ((countries.id = researchers.countryid)))
     LEFT JOIN researcherimages ON ((researcherimages.researcherid = researchers.id)));


ALTER TABLE pplviews OWNER TO appdb;

--
-- Name: privileges_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE privileges_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE privileges_id_seq OWNER TO appdb;

--
-- Name: privileges_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE privileges_id_seq OWNED BY privileges.id;


--
-- Name: procs; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE procs (
    cpu numeric,
    name text
);


ALTER TABLE procs OWNER TO appdb;

--
-- Name: proglangs; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE proglangs (
    id integer NOT NULL,
    name text NOT NULL
);


ALTER TABLE proglangs OWNER TO appdb;

--
-- Name: proglangs_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE proglangs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE proglangs_id_seq OWNER TO appdb;

--
-- Name: proglangs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE proglangs_id_seq OWNED BY proglangs.id;


--
-- Name: projects_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE projects_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE projects_id_seq OWNER TO appdb;

--
-- Name: projects_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE projects_id_seq OWNED BY projects.id;


--
-- Name: rankedids; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE rankedids (
    id integer,
    rank integer DEFAULT 0 NOT NULL
);


ALTER TABLE rankedids OWNER TO appdb;

--
-- Name: rankedidstxt; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE rankedidstxt (
    id text,
    rank integer DEFAULT 0 NOT NULL
);


ALTER TABLE rankedidstxt OWNER TO appdb;

--
-- Name: relations_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE relations_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE relations_id_seq OWNER TO appdb;

--
-- Name: relations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE relations_id_seq OWNED BY relations.id;


--
-- Name: relationtypes_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE relationtypes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE relationtypes_id_seq OWNER TO appdb;

--
-- Name: relationtypes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE relationtypes_id_seq OWNED BY relationtypes.id;


--
-- Name: relationverbs_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE relationverbs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE relationverbs_id_seq OWNER TO appdb;

--
-- Name: relationverbs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE relationverbs_id_seq OWNED BY relationverbs.id;


--
-- Name: rep_test; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE rep_test (
    val text
);


ALTER TABLE rep_test OWNER TO appdb;

--
-- Name: researcher_cnames; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE researcher_cnames (
    id integer NOT NULL,
    created timestamp without time zone DEFAULT now(),
    enabled boolean DEFAULT true,
    isprimary boolean DEFAULT true,
    value text,
    researcherid integer NOT NULL
);


ALTER TABLE researcher_cnames OWNER TO appdb;

--
-- Name: researcher_cnames_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE researcher_cnames_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE researcher_cnames_id_seq OWNER TO appdb;

--
-- Name: researcher_cnames_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE researcher_cnames_id_seq OWNED BY researcher_cnames.id;


--
-- Name: researcher_institution_local_organization_map; Type: VIEW; Schema: public; Owner: appdb
--

CREATE VIEW researcher_institution_local_organization_map AS
 WITH orgs AS (
         SELECT org.guid,
            org.shortname,
            org.name
           FROM organizations org
          WHERE (org.sourceid = 1)
        )
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'OACT'::text)))
  WHERE (btrim(r.institution) ~~* 'osservatorio astrofisico di catania'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'LOA'::text)))
  WHERE (btrim(r.institution) ~~* 'loa'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'LNCC'::text)))
  WHERE (btrim(r.institution) ~~* 'lncc'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'ERI'::text)))
  WHERE (btrim(r.institution) ~~* 'eri'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'CREATIS'::text)))
  WHERE (btrim(r.institution) ~~* 'cnrs - creatis'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'UFJF'::text)))
  WHERE (btrim(r.institution) ~~* 'ufjf'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'IRI'::text)))
  WHERE (btrim(r.institution) ~~* 'iri%/%narss'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'UAEM'::text)))
  WHERE (btrim(r.institution) ~~* 'uaem'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'VU-ITPA'::text)))
  WHERE (btrim(r.institution) ~~* 'institute of theoretical physics and astronomy, vilnius university (vu itpa)'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'VU-ITPA'::text)))
  WHERE (btrim(r.institution) ~~* 'physics faculty of vilnius university, and institute of theoretical physics and astronomy of vilnius university'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'CIB'::text)))
  WHERE (btrim(r.institution) ~~* 'cib-csic'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'LISA'::text)))
  WHERE (btrim(r.institution) ~~* 'lisa/cnrs'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'SANU'::text)))
  WHERE (btrim(r.institution) ~~* 'center for scientific research of serbian academy of science and art and university of kragujevac'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'NIKHEF'::text)))
  WHERE (btrim(r.institution) ~~* 'nikhef'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'PSNC'::text)))
  WHERE (btrim(r.institution) ~~* 'poznan supercomputing and networking centre'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'PSNC'::text)))
  WHERE (btrim(r.institution) ~~* 'psnc'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'PSNC'::text)))
  WHERE (btrim(r.institution) ~~* 'ibch pas - psnc'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'RRZK'::text)))
  WHERE (btrim(r.institution) ~~* 'rrzk'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'SLAC'::text)))
  WHERE (btrim(r.institution) ~~* 'slac'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'SOLIHULL'::text)))
  WHERE (btrim(r.institution) ~~* 'solihull college'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'UOK'::text)))
  WHERE (btrim(r.institution) ~~* 'taras shevchenko national university of kyiv'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'WORLDFISH'::text)))
  WHERE (btrim(r.institution) ~~* '%the worldfish center%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'TRC'::text)))
  WHERE (btrim(r.institution) ~~* 'trc'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'UCL'::text)))
  WHERE (btrim(r.institution) ~~* 'ucl'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'UNICO INFORMATICA'::text)))
  WHERE (btrim(r.institution) ~~* 'unico informatica s.r.l.'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'UNISALENTO'::text)))
  WHERE (btrim(r.institution) ~~* 'unisalento.it'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'UCALDAS'::text)))
  WHERE (btrim(r.institution) ~~* 'universidad de caldas'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'UNIKORE'::text)))
  WHERE (btrim(r.institution) ~~* 'universita%kore di enna'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'RCUB'::text)))
  WHERE (btrim(r.institution) ~~* 'university of belgrade computing centre'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'UOG-DMOD'::text)))
  WHERE (btrim(r.institution) ~~* 'university of glasgow, device modeling group'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'UOFS'::text)))
  WHERE (btrim(r.institution) ~~* 'university of the free state'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'IFLP'::text)))
  WHERE (btrim(r.institution) ~~* 'unlp-iflp'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'GRyCAP-UPV'::text)))
  WHERE (btrim(r.institution) ~~* 'GRyCAP-UPV'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'SNIC'::text)))
  WHERE (btrim(r.institution) ~~* 'vr-snic'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'APACHE'::text)))
  WHERE (btrim(r.institution) ~~* 'apache'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'AUTODESK'::text)))
  WHERE (btrim(r.institution) ~~* 'autodesk research'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'BITP'::text)))
  WHERE (btrim(r.institution) ~~* 'bogolyubov institute for theoretical physics'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'BIFI'::text)))
  WHERE (btrim(r.institution) ~~* 'bifi'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'UNIZAR-I3A'::text)))
  WHERE (btrim(r.institution) ~~* 'UNIZAR-I3A'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'CEFET-RJ'::text)))
  WHERE (btrim(r.institution) ~~* 'cefet-rj'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'CEFET-RJ'::text)))
  WHERE (btrim(r.institution) ~~* 'cefet/rj'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'CETA-CIEMAT'::text)))
  WHERE (btrim(r.institution) ~~* 'ceta-ciemat'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'CMRC'::text)))
  WHERE (btrim(r.institution) ~~* 'cmrc'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'CNRS-CC-IN2P3'::text)))
  WHERE (btrim(r.institution) ~~* 'cnrs-ccin2p3'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'CNRS-IDGC'::text)))
  WHERE (btrim(r.institution) ~~* 'cnrs - idgc'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'CNRS-IN2P3'::text)))
  WHERE (btrim(r.institution) ~~* 'cnrs-in2p3'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'CNRS-IN2P3'::text)))
  WHERE (btrim(r.institution) ~~* 'in2p3'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'CNRS-CPPM-IN2P3'::text)))
  WHERE (btrim(r.institution) ~~* 'cppm-in2p3-cnrs'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'CSCS'::text)))
  WHERE (btrim(r.institution) ~~* 'cscs'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'EMBL-EBI'::text)))
  WHERE (btrim(r.institution) ~~* 'embl-ebi'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'HITEC2000'::text)))
  WHERE (btrim(r.institution) ~~* 'hitec2000'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'UPV-I3M'::text)))
  WHERE (btrim(r.institution) ~~* 'i3m'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'UW-ICM'::text)))
  WHERE (btrim(r.institution) ~~* 'icm'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'UW-ICM'::text)))
  WHERE (btrim(r.institution) ~~* 'icm uw'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'CNR-IMATI'::text)))
  WHERE (btrim(r.institution) ~~* 'imati cnr'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'CNR-ITB'::text)))
  WHERE (btrim(r.institution) ~~* 'crn-itb'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'USP-INCOR'::text)))
  WHERE ((btrim(r.institution) ~~* 'incor-usp'::text) OR (btrim(r.institution) ~~* '%(incor)%'::text))
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'INFN-CNAF'::text)))
  WHERE (btrim(r.institution) ~~* 'infn cnaf'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'IST-IPFN'::text)))
  WHERE (btrim(r.institution) ~~* 'ipfn/ist'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'ISCPIF'::text)))
  WHERE (btrim(r.institution) ~~* 'iscpif'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'IPCF'::text)))
  WHERE (btrim(r.institution) ~~* 'istituto per i processi chimico-fisici'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'UNL-ITQB'::text)))
  WHERE (btrim(r.institution) ~~* 'ITQB-UNL'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'LRZ'::text)))
  WHERE (btrim(r.institution) ~~* 'leibniz rechenzentrum'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'MARGI'::text)))
  WHERE (btrim(r.institution) ~~* 'margi'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'MERACA'::text)))
  WHERE (btrim(r.institution) ~~* 'meraka'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'MERACA'::text)))
  WHERE (btrim(r.institution) ~~* 'meraka institute'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'MTA-LPDS'::text)))
  WHERE (btrim(r.institution) ~~* 'mta sztaki lpds'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'IPP-BAS'::text)))
  WHERE (btrim(r.institution) ~~* '%IPP-BAS%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'IOCH-BAS'::text)))
  WHERE (btrim(r.institution) ~~* '%IOCH-BAS%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'UNINETT-SIGMA'::text)))
  WHERE (btrim(r.institution) ~~* '%UNINETT%SIGMA%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'URA'::text)))
  WHERE (btrim(r.institution) ~~* 'URA'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'EURATOM'::text)))
  WHERE (btrim(r.institution) ~~* '%EURATOM%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'GIRESUN'::text)))
  WHERE (btrim(r.institution) ~~* '%giresun%university%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    orgs.guid AS orgguid,
    r.institution,
    orgs.name,
    orgs.shortname
   FROM (researchers r
     JOIN orgs ON ((orgs.shortname ~~ 'NLESC'::text)))
  WHERE (btrim(r.institution) ~~* 'netherlands esciencecenter'::text);


ALTER TABLE researcher_institution_local_organization_map OWNER TO appdb;

--
-- Name: researcher_institution_organization_map; Type: VIEW; Schema: public; Owner: appdb
--

CREATE VIEW researcher_institution_organization_map AS
 WITH recs AS (
         SELECT ra.record_id,
            ra.value
           FROM (harvest.records_additional ra
             JOIN harvest.records r ON ((r.record_id = ra.record_id)))
          WHERE ((r.archive_id = 3) AND (ra.name = 'legalname'::text))
        )
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~* '%COMETA CONSORZIO MULTI ENTE PER LAPROMOZIONE%'::text)))
  WHERE (btrim(r.institution) ~~* '%consorzio cometa%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%UNIVERSITA DEGLI STUDI DI PALERMO%'::text)))
  WHERE (btrim(r.institution) ~~* 'universit%di palermo'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%UNIVERSITA DEGLI STUDI DI CATANIA%'::text)))
  WHERE ((btrim(r.institution) ~~* 'universit% catania%'::text) OR (btrim(r.institution) ~~* 'unict'::text))
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%ISTITUTO NAZIONALE DI FISICA NUCLEARE%'::text)))
  WHERE ((((btrim(r.institution) ~~* '%infn%'::text) OR (btrim(r.institution) ~~* '%istituto nazionale di fisica nucleare%'::text)) OR (btrim(r.institution) ~~* '%laboratorio nazionale del sud%'::text)) OR (btrim(r.institution) ~~* 'arcem'::text))
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%UNIVERSITA DEGLI STUDI DI MESSINA%'::text)))
  WHERE (btrim(r.institution) ~~* 'universit% messina%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%STICHTING EUROPEAN GRID INITIATIVE%'::text)))
  WHERE (btrim(r.institution) ~~* '%egi.eu%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%ISTITUTO NAZIONALE DI GEOFISICA E VULCANOLOGIA%'::text)))
  WHERE (btrim(r.institution) ~~* '%istituto nazionale di geofisica e vulcanologia%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%EUROPEAN ORGANIZATION FOR NUCLEAR RESEARCH%'::text)))
  WHERE (btrim(r.institution) ~~* '%cern%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%ISTITUTO NAZIONALE DI ASTROFISICA%'::text)))
  WHERE (((btrim(r.institution) ~~* '%inaf-osservatorio astronomico%'::text) OR (btrim(r.institution) ~~* 'inaf%'::text)) OR (btrim(r.institution) ~~* '%istituto%astrofisica%palermo%'::text))
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%UNIVERSITY OF CYPRUS'::text)))
  WHERE ((btrim(r.institution) ~~* '%cyprus%'::text) OR (btrim(r.institution) ~~* 'ucy'::text))
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%CESNET,  ZAJMOVE SDRUZENI PRAVNICKYCH OSOB%'::text)))
  WHERE (btrim(r.institution) ~~* '%cesnet%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%MAGYAR TUDOMANYOS AKADEMIA SZAMITASTECHNIKAI ES AUTOMATIZALASI KUTATO INTEZET%'::text)))
  WHERE (btrim(r.institution) ~~* '%mta sztaki%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'Bulgarian Academy of Sciences'::text)))
  WHERE (btrim(r.institution) ~~* '%ipp-bas%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%CENTRO DE INVESTIGACIONES ENERGETICAS, MEDIOAMBIENTALES Y TECNOLOGICAS-CIEMAT%'::text)))
  WHERE (btrim(r.institution) ~~* '%ciemat%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%INSTITUTE OF ACCELERATING SYSTEMS AND APPLICATIONS%'::text)))
  WHERE (btrim(r.institution) ~~* '%iasa%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%CENTRE NATIONAL DE LA RECHERCHE SCIENTIFIQUE%'::text)))
  WHERE (btrim(r.institution) ~~* '%cnrs%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%ACADEMIA SINICA%'::text)))
  WHERE (btrim(r.institution) ~~* '%asgc%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%UNIVERSIDADE FEDERAL DO RIO DE JANEIRO%'::text)))
  WHERE (btrim(r.institution) ~~* '%ufrj%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%CSC-TIETEEN TIETOTEKNIIKAN KESKUS OY%'::text)))
  WHERE ((btrim(r.institution) ~~* 'csc'::text) OR (btrim(r.institution) ~~* 'csc %'::text))
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%SCIENCE AND TECHNOLOGY FACILITIES COUNCIL%'::text)))
  WHERE (btrim(r.institution) ~~* '%stfc%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%INSTITUT ZA FIZIKU%'::text)))
  WHERE (btrim(r.institution) ~~* '%institute of physics belgrade%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%ASTRONOMSKA OPSERVATORIJA%'::text)))
  WHERE (btrim(r.institution) ~~* '%astronomical%observatory%belgrade%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%UNIVERSITA DEGLI STUDI DI GENOVA%'::text)))
  WHERE (btrim(r.institution) ~~* '%universita%di%genova%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%NATIONAL AND KAPODISTRIAN UNIVERSITY OF ATHENS%'::text)))
  WHERE (btrim(r.institution) ~~* '%uoa%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%ETHNICON METSOVION POLYTECHNION%'::text)))
  WHERE (btrim(r.institution) ~~* '%ntua%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%AGRICULTURAL UNIVERSITY OF ATHENS%'::text)))
  WHERE (btrim(r.institution) ~~* '%aua%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%SURFSARA BV%'::text)))
  WHERE (btrim(r.institution) ~~* '%sara%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%Karlsruher Institut fuer Technologie%'::text)))
  WHERE (btrim(r.institution) ~~* '%karlsruhe%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%AKADEMIA GORNICZO-HUTNICZA IM. STANISLAWA STASZICA W KRAKOWIE%'::text)))
  WHERE (btrim(r.institution) ~~* '%acc cyfronet agh%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%INSTITUTE OF TECHNOLOGY TALLAGHT%'::text)))
  WHERE (btrim(r.institution) ~~* '%itt%dublin%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%UNIVERSIDAD NACIONAL DE RIO CUARTO%'::text)))
  WHERE (btrim(r.institution) ~~* '%unrc%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%TURKIYE BILIMSEL VE TEKNOLOJIK ARASTIRMA KURUMU%'::text)))
  WHERE (btrim(r.institution) ~~* '%tubitak%ulakbim%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%EL COLEGIO DE LA FRONTERA SUR%'::text)))
  WHERE (btrim(r.institution) ~~* '%ufro%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%UNIVERSIDAD COMPLUTENSE DE MADRID%'::text)))
  WHERE ((btrim(r.institution) ~~* '%ucm%'::text) OR (btrim(r.institution) ~~* '%universidad complutense de madrid%'::text))
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%INSTITUTE OF INFORMATION AND COMMUNICATION TECHNOLOGIES%'::text)))
  WHERE (btrim(r.institution) ~~* '%iict%bas%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%UNIVERSITA DEGLI STUDI DI PERUGIA%'::text)))
  WHERE (btrim(r.institution) ~~* '%unipg%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%INSTYTUT CHEMII BIOORGANICZNEJ PAN%'::text)))
  WHERE (btrim(r.institution) ~~* '%IBCh PAS%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%USTAV INFORMATIKY,  SLOVENSKA AKADEMIA VIED%'::text)))
  WHERE (btrim(r.institution) ~~* '%ui%sav%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%UNIVERSITY OF GLASGOW%'::text)))
  WHERE (btrim(r.institution) ~~* '%glasgow%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%V.M. GLUSHKOV INSTITUTE OF CYBERNETICS OF NATIONAL ACADEMY OF SCIENCES OF UKRAINE%'::text)))
  WHERE (btrim(r.institution) ~~* '%glushkov%cybernetics%ukraine%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'ACADEMY OF ATHENS%'::text)))
  WHERE (btrim(r.institution) ~~* '%aoa%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%Academisch Medisch Centrum bij de Universiteit van Amsterdam%'::text)))
  WHERE ((btrim(r.institution) ~~* '%amc%'::text) OR (btrim(r.institution) ~~* '%academisch medisch centrum universiteit van amsterdam%'::text))
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%CENTRO DE GESTION DE LA INFORMACION Y DESARROLLO DE LA ENERGIA%'::text)))
  WHERE (btrim(r.institution) ~~* '%cubaenergia%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%CONSIGLIO NAZIONALE DELLE RICERCHE%'::text)))
  WHERE ((btrim(r.institution) ~~* '%cnr-itb%'::text) OR (btrim(r.institution) ~~* 'italian national research council'::text))
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%Trinity College Dublin%'::text)))
  WHERE ((btrim(r.institution) ~~* '%trinity college dublin%'::text) OR (btrim(r.institution) ~~* 'tcd'::text))
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%TECHNISCHE UNIVERSITEIT DELFT%'::text)))
  WHERE (btrim(r.institution) ~~* '%delft university of technology%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%UNIVERSIDAD NACIONAL AUTONOMA DE MEXICO%'::text)))
  WHERE (btrim(r.institution) ~~* '%unam%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%University of Westminster%'::text)))
  WHERE (btrim(r.institution) ~~* '%university of westminster%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%BUDAPESTI MUSZAKI ES GAZDASAGTUDOMANYI EGYETEM%'::text)))
  WHERE (btrim(r.institution) ~~* '%bme%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%UNIVERSIDAD DE ZARAGOZA%'::text)))
  WHERE (btrim(r.institution) ~~* '%unizar%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%NATIONAL SCIENCE &amp; TECHNOLOGY DEVELOPMENT AGENCY%'::text)))
  WHERE (btrim(r.institution) ~~* '%nectec%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%SVEUCILISTE U ZAGREBU SVEUCILISNI RACUNSKI CENTAR%'::text)))
  WHERE (btrim(r.institution) ~~* '%srce%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%NATIONAL OBSERVATORY OF ATHENS%'::text)))
  WHERE (btrim(r.institution) ~~* '%noa%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%STICHTING ASTRONOMISCH ONDERZOEK IN NEDERLAND%'::text)))
  WHERE (btrim(r.institution) ~~* 'astron'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%FUNDACAO OSWALDO CRUZ%'::text)))
  WHERE (btrim(r.institution) ~~* 'fiocruz'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%FORSCHUNGSZENTRUM JUELICH GMBH%'::text)))
  WHERE (btrim(r.institution) ~~* '%forschungszentrum jülich%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%FUNDACION CENTRO TECNOLOGICO DE SUPERCOMPUTACION DE GALICIA%'::text)))
  WHERE (btrim(r.institution) ~~* '%cesga%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%RESEARCH AND EDUCATIONAL NETWORKING ASSOCIATION OF MOLDOVA%'::text)))
  WHERE (btrim(r.institution) ~~* '%renam%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%THE STEPHAN ANGELOFF INSTITUTE OF MICROBIOLOGY, BULGARIAN ACADEMY OF SCIENCES%'::text)))
  WHERE (btrim(r.institution) ~~* '%im-bas%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%LATVIJAS UNIVERSITATES MATEMATIKAS UN INFORMATIKAS INSTITUTS%'::text)))
  WHERE (btrim(r.institution) ~~* '%imcs-ul%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%Imperial College London%'::text)))
  WHERE (btrim(r.institution) ~~* '%imperial college london%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%UNIVERSITA DEGLI STUDI DI TRIESTE%'::text)))
  WHERE (btrim(r.institution) ~~* '%trieste%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((btrim(recs.value) ~~ 'KOBENHAVNS UNIVERSITET'::text)))
  WHERE (btrim(r.institution) ~~* '%ucph%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%National Authority for Remote Sensing and Space Sciences%'::text)))
  WHERE (btrim(r.institution) ~~* '%narss%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%CGGVERITAS SERVICES SA%'::text)))
  WHERE (btrim(r.institution) ~~* '%cggveritas%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%UNIVERSIDAD DE LOS ANDES FUNDACION%'::text)))
  WHERE (btrim(r.institution) ~~* '%uniandes%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%UNIVERSITY COLLEGE DUBLIN, NATIONAL UNIVERSITY OF IRELAND, DUBLIN%'::text)))
  WHERE (btrim(r.institution) ~~* 'ucd'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%JOHANN WOLFGANG GOETHE UNIVERSITAET FRANKFURT AM MAIN%'::text)))
  WHERE (btrim(r.institution) ~~* '%goethe university frankfurt am main%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%UNIVERSIDAD DE CANTABRIA%'::text)))
  WHERE (btrim(r.institution) ~~* '%cantabria%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%AGENCIA ESTATAL CONSEJO SUPERIOR DE INVESTIGACIONES CIENTIFICAS%'::text)))
  WHERE (btrim(r.institution) ~~* '%csic%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%INTER UNIVERSITY COMPUTATION CENTRE%'::text)))
  WHERE (btrim(r.institution) ~~* '%iucc%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%GEORGIAN RESEARCH AND EDUCATIONAL NETWORKING ASSOCIATION%'::text)))
  WHERE (btrim(r.institution) ~~* '%grena%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%GREEK RESEARCH AND TECHNOLOGY NETWORK S.A.%'::text)))
  WHERE (btrim(r.institution) ~~* '%grnet%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%UNIVERSITY OF NIS- FACULTY OF ELECTRONIC ENGINEERING%'::text)))
  WHERE (btrim(r.institution) ~~* '%faculty of electronic engineering of the university of nis%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%HIGHER INSTITUTE FOR APPLIED SCIENCES AND TECHNOLOGY%'::text)))
  WHERE (btrim(r.institution) ~~* '%hiast%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%LABORATORIO DE INSTRUMENTACAO E FISICA EXPERIMENTAL DE PARTICULAS%'::text)))
  WHERE (btrim(r.institution) ~~* 'lip'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%STIFTUNG DEUTSCHES ELEKTRONEN-SYNCHROTRON DESY%'::text)))
  WHERE (btrim(r.institution) ~~* '%desy%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%REDE NACIONAL DE ENSINO E PESQUISA%'::text)))
  WHERE (btrim(r.institution) ~~* 'rnp'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%KONINKLIJK NEDERLANDS METEOROLOGISCH INSTITUUT-KNMI%'::text)))
  WHERE (btrim(r.institution) ~~* '%royal netherlands meteorological institute%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((btrim(recs.value) ~~ 'NATIONAL ACADEMY OF SCIENCES OF THE REPUBLIC OF ARMENIA'::text)))
  WHERE (btrim(r.institution) ~~* 'sci'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'SWITCH'::text)))
  WHERE (btrim(r.institution) ~~* 'switch'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%INSTITUTUL NATIONAL DE CERCETARE-DEZVOLTARE PENTRU TEHNOLOGII IZOTOPICE SI MOLECULARE-INCDTIM CLUJ-NAPOCA%'::text)))
  WHERE (btrim(r.institution) ~~* '%technical university of cluj-napoca%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%UNIVERSITY OF MELBOURNE%'::text)))
  WHERE (btrim(r.institution) ~~* '%the university of melbourne%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%Universidade dos AÃ§ores%'::text)))
  WHERE (btrim(r.institution) ~~* 'ua'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%Universidade Federal de Campina Grande%'::text)))
  WHERE (btrim(r.institution) ~~* 'ufcg'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'UNIVERSITAET INNSBRUCK%'::text)))
  WHERE (btrim(r.institution) ~~* 'uibk'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'Ss. CYRIL AND METHODIUS UNIVERSITY IN SKOPJE'::text)))
  WHERE (btrim(r.institution) ~~* 'ukim'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'UMEA UNIVERSITET'::text)))
  WHERE (btrim(r.institution) ~~* 'umea'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'UNIVERSIDADE DO MINHO'::text)))
  WHERE (btrim(r.institution) ~~* 'uminho'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'FundaÃ§Ã£o Universidade de Brasilia'::text)))
  WHERE (btrim(r.institution) ~~* 'unb'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'UNINETT AS'::text)))
  WHERE (btrim(r.institution) ~~* 'UNINETT'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%UNIVERSIDADE DE SANTIAGO DE COMPOSTELA%'::text)))
  WHERE (btrim(r.institution) ~~* '%compostela%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%UNIVERSITA DEGLI STUDI DI TORINO%'::text)))
  WHERE (btrim(r.institution) ~~* '%torino%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%UNIVERSITA DEGLI STUDI DI MESSINA%'::text)))
  WHERE (btrim(r.institution) ~~* '%messina%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ '%UNIVERSITAT AUTONOMA DE BARCELONA%'::text)))
  WHERE (btrim(r.institution) ~~* '%universitat autònoma de barcelona%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'UNIVERSITEIT VAN AMSTERDAM'::text)))
  WHERE ((btrim(r.institution) ~~* '%van amsterdam%'::text) OR (btrim(r.institution) ~~* '%university of amsterdam%'::text))
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'RIJKSUNIVERSITEIT GRONINGEN'::text)))
  WHERE (btrim(r.institution) ~~* '%university%groningen%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'UNIVERSITI PUTRA MALAYSIA'::text)))
  WHERE (btrim(r.institution) ~~* '%putra%malaysia%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'UNIVERSITA DEGLI STUDI DI BARI ALDO MORO'::text)))
  WHERE (btrim(r.institution) ~~* '%university of bari%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'ALMA MATER STUDIORUM-UNIVERSITA DI BOLOGNA'::text)))
  WHERE (btrim(r.institution) ~~* '%university of bologna%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'UNIVERSITA DEGLI STUDI DI FIRENZE'::text)))
  WHERE (btrim(r.institution) ~~* '%university of florence%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'ALBERT-LUDWIGS-UNIVERSITAET FREIBURG'::text)))
  WHERE (btrim(r.institution) ~~* '%university of freiburg%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'UNIVERSITA DEGLI STUDI DI MILANO'::text)))
  WHERE (btrim(r.institution) ~~* '%university of milan%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'UNIVERSITY OF NIGERIA'::text)))
  WHERE (btrim(r.institution) ~~* '%university of nigeria%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'UNIVERSITAET SIEGEN'::text)))
  WHERE (btrim(r.institution) ~~* '%university of siegen%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'UNIVERSITAET ZUERICH'::text)))
  WHERE ((btrim(r.institution) ~~* '%university of zurich%'::text) OR (btrim(r.institution) ~~* 'uzh'::text))
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'UNIVERSIDAD NACIONAL DE LA PLATA'::text)))
  WHERE (btrim(r.institution) ~~* '%unlp%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'UNIVERSITAT POLITECNICA DE VALENCIA'::text)))
  WHERE ((btrim(r.institution) ~~* 'upv'::text) OR (btrim(r.institution) ~~* '%-upv'::text))
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'UNIVERSITE DES SCIENCES ET LA TECHNOLOGIE HOUARI BOUMEDIENE'::text)))
  WHERE (btrim(r.institution) ~~* 'usthb'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'UNIVERSITATEA TEHNICA CLUJ-NAPOCA'::text)))
  WHERE ((btrim(r.institution) ~~* 'utc'::text) OR (btrim(r.institution) ~~* 'utcn'::text))
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'UNIVERSIDAD TECNICA FEDERICO SANTA MARIA'::text)))
  WHERE (btrim(r.institution) ~~* 'utfsm'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'UNIVERSIDAD TECNICA PARTICULAR DE LOJA'::text)))
  WHERE (btrim(r.institution) ~~* 'utpl'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'UNIVERSITEIT UTRECHT'::text)))
  WHERE (btrim(r.institution) ~~* '%utrecht university%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'UNIVERSITEIT VAN AMSTERDAM'::text)))
  WHERE ((btrim(r.institution) ~~* 'uva'::text) OR (btrim(r.institution) ~~* '% uva'::text))
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'UZHGORODSKYI NACIONALNYI UNIVERSITET'::text)))
  WHERE (btrim(r.institution) ~~* 'uzhgorod national university'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'WITS HEALTH CONSORTIUM(PTY)  LTD'::text)))
  WHERE (btrim(r.institution) ~~* 'wits'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'Academisch Medisch Centrum bij de Universiteit van Amsterdam'::text)))
  WHERE (btrim(r.institution) ~~* '%academic medical center%amsterdam'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'ACRI-ST SAS'::text)))
  WHERE (btrim(r.institution) ~~* '%acri-st%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'CENTRE FOR RESEARCH AND TECHNOLOGY HELLAS'::text)))
  WHERE (btrim(r.institution) ~~* '%certh%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'Arnes'::text)))
  WHERE (btrim(r.institution) ~~* 'arnes'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'BARCELONA SUPERCOMPUTING CENTER - CENTRO NACIONAL DE SUPERCOMPUTACION'::text)))
  WHERE (btrim(r.institution) ~~* '%barcelona supercomputing center%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'UNIVERZITA KARLOVA V PRAZE'::text)))
  WHERE (btrim(r.institution) ~~* 'charles university in prague'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'CLOUDBROKER GMBH'::text)))
  WHERE (btrim(r.institution) ~~* 'cloudbroker gmbh'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'FUNDACION CENTRO NACIONAL DE INVESTIGACIONES ONCOLOGICAS CARLOS III'::text)))
  WHERE (btrim(r.institution) ~~* 'cnio'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'Centro de Referencia em Informacao Ambiental'::text)))
  WHERE (btrim(r.institution) ~~* 'cria'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'COMPUTER TECHNOLOGY INSTITUTE &amp; PRESS DIOPHANTUS'::text)))
  WHERE (btrim(r.institution) ~~* 'cti'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'DELIVERY OF ADVANCED NETWORK TECHNOLOGY TO EUROPE LIMITED'::text)))
  WHERE (btrim(r.institution) ~~* '%dante%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'NATIONAL ASSOCIATION OF RESEARCH AND EDUCATIONAL E-INFRASTRUCTURES "E-ARENA" AUTONOMOUS NON-COMMERCIAL ORGANIZATION'::text)))
  WHERE (btrim(r.institution) ~~* 'e-arena'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'SINCROTRONE TRIESTE SCPA'::text)))
  WHERE (btrim(r.institution) ~~* 'elettra.eu'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'European Life-science Infrastructure for Biological Information'::text)))
  WHERE (btrim(r.institution) ~~* 'elixir'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'European Molecular Biology Laboratory'::text)))
  WHERE (btrim(r.institution) ~~* 'embl%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'ENGINEERING - INGEGNERIA INFORMATICA SPA'::text)))
  WHERE (btrim(r.institution) ~~* 'engineering ingegneria informatica'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'ELEKTROTEHNICKI FAKULTET UNIVERZITET U BEOGRADU'::text)))
  WHERE (btrim(r.institution) ~~* 'faculty of electrical engineering of the university of belgrade'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'University of Split, Faculty of Electrical Engineering, Mechanical Engineering and Naval Architecture'::text)))
  WHERE (btrim(r.institution) ~~* 'faculty of mechanical engineering and naval architecture'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'FRIEDRICH MIESCHER INSTITUTE FOR BIOMEDICAL RESEARCH'::text)))
  WHERE (btrim(r.institution) ~~* 'fmi'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'FRAUNHOFER-GESELLSCHAFT ZUR FOERDERUNG DER ANGEWANDTEN FORSCHUNG E.V'::text)))
  WHERE (btrim(r.institution) ~~* 'fraunhofer'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'FRIEDRICH-SCHILLER-UNIVERSITAET JENA'::text)))
  WHERE (btrim(r.institution) ~~* 'friedrich%jena'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'FORSCHUNGSZENTRUM JUELICH GMBH'::text)))
  WHERE (btrim(r.institution) ~~* 'fzj'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'CONSORTIUM GARR'::text)))
  WHERE (btrim(r.institution) ~~* 'garr%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'GESELLSCHAFT FUR WISSENSCHAFTLICHE DATENVERARBEITUNG MBH GOTTINGEN'::text)))
  WHERE (btrim(r.institution) ~~* 'gwdg'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'HAROKOPIO UNIVERSITY'::text)))
  WHERE (btrim(r.institution) ~~* 'hua'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'INSTITUTUL NATIONAL DE CERCETARE-DEZVOLTARE IN INFORMATICA - ICI BUCURESTI'::text)))
  WHERE (btrim(r.institution) ~~* 'ici'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'UNIVERSIDADE DE AVEIRO'::text)))
  WHERE (btrim(r.institution) ~~* '%aveiro%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'STICHTING VOOR FUNDAMENTEEL ONDERZOEK DER MATERIE - FOM'::text)))
  WHERE (btrim(r.institution) ~~* 'fom %'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'HELMHOLTZ ZENTRUM FUR OZEANFORSCHUNG KIEL'::text)))
  WHERE (btrim(r.institution) ~~* '%geomar%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'INSTITUTE FOR INFORMATICS AND AUTOMATION PROBLEMS OF THE NATIONAL ACADEMY OF SCIENCES OF THE REPUBLIC OF ARMENIA'::text)))
  WHERE (btrim(r.institution) ~~* '%IIAP NAS RA%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'INSTITUTE FOR LOW TEMPERATURE PHYSICS AND ENGINEERING of NASU'::text)))
  WHERE (btrim(r.institution) ~~* '%ILTPE%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'INSTITUTE OF MATHEMATICS AND INFORMATICS AT THE BULGARIAN ACADEMY OF SCIENCE'::text)))
  WHERE (btrim(r.institution) ~~* '%imi-bas%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'UNIVERSIDADE DE SAO PAULO'::text)))
  WHERE (btrim(r.institution) ~~* '%usp%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'RUDER BOSKOVIC INSTITUTE'::text)))
  WHERE (btrim(r.institution) ~~* '%rudjer%boskovi%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'VILNIAUS UNIVERSITETAS'::text)))
  WHERE (btrim(r.institution) ~~* '%vilnius university%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'VILNIAUS GEDIMINO TECHNIKOS UNIVERSITETAS'::text)))
  WHERE (btrim(r.institution) ~~* '%VGTU%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'TECHNISCHE UNIVERSITAET MUENCHEN'::text)))
  WHERE (btrim(r.institution) ~~* '%technische universitaat munchen%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'UNIVERSIDADE DO PORTO'::text)))
  WHERE (btrim(r.institution) ~~* 'up'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'INSTITUT NATIONAL DE LA RECHERCHE AGRONOMIQUE'::text)))
  WHERE (btrim(r.institution) ~~* 'inra'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'USTAV INFORMATIKY,  SLOVENSKA AKADEMIA VIED'::text)))
  WHERE (btrim(r.institution) ~~* 'institute%informatics'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'INSTITUTO SUPERIOR TECNICO'::text)))
  WHERE (btrim(r.institution) ~~* 'ipfn/ist'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'INSTITUT DE PHYSIQUE DU GLOBE DE PARIS'::text)))
  WHERE (btrim(r.institution) ~~* 'IPGP/CNRS'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'UNIVERSIDADE NOVA DE LISBOA'::text)))
  WHERE (btrim(r.institution) ~~* 'ITQB-UNL'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'JOINT INSTITUTE FOR NUCLEAR RESEARCH'::text)))
  WHERE (btrim(r.institution) ~~* 'JINR'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'INSTITUT JOZEF STEFAN'::text)))
  WHERE (btrim(r.institution) ~~* 'JSI'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'FORSCHUNGSZENTRUM JUELICH GMBH'::text)))
  WHERE (btrim(r.institution) ~~* 'juelich'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'KAUNO TECHNOLOGIJOS UNIVERSITETAS'::text)))
  WHERE (btrim(r.institution) ~~* '%Kaunas%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'KOREA INSTITUTE OF SCIENCE AND TECHNOLOGY INFORMATION'::text)))
  WHERE (btrim(r.institution) ~~* 'KISTI'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'LUNDS UNIVERSITET'::text)))
  WHERE (btrim(r.institution) ~~* '%Lund University%'::text)
UNION ALL
 SELECT r.id,
    r.guid,
    recs.record_id,
    r.institution,
    recs.value AS legalname
   FROM (researchers r
     JOIN recs ON ((recs.value ~~ 'MIDDLE EAST TECHNICAL UNIVERSITY'::text)))
  WHERE (btrim(r.institution) ~~* 'metu'::text);


ALTER TABLE researcher_institution_organization_map OWNER TO appdb;

--
-- Name: researchers_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE researchers_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE researchers_id_seq OWNER TO appdb;

--
-- Name: researchers_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE researchers_id_seq OWNED BY researchers.id;


--
-- Name: va_provider_images; Type: VIEW; Schema: public; Owner: appdb
--

CREATE VIEW va_provider_images AS
 SELECT __va_provider_images.id,
    __va_provider_images.va_provider_id,
    __va_provider_images.vmiinstanceid,
    __va_provider_images.content_type,
    __va_provider_images.va_provider_image_id,
    __va_provider_images.mp_uri,
    __va_provider_images.vowide_vmiinstanceid,
    get_good_vmiinstanceid(__va_provider_images.vmiinstanceid) AS good_vmiinstanceid
   FROM __va_provider_images;


ALTER TABLE va_provider_images OWNER TO appdb;

--
-- Name: site_services_xml; Type: MATERIALIZED VIEW; Schema: public; Owner: appdb; Tablespace: 
--

CREATE MATERIALIZED VIEW site_services_xml AS
 SELECT va_providers.sitename,
    XMLELEMENT(NAME "site:service", XMLATTRIBUTES('occi' AS type, va_providers.id AS id, va_providers.hostname AS host, count(DISTINCT va_provider_images.good_vmiinstanceid) AS instances, va_providers.beta AS beta, va_providers.in_production AS in_production), xmlagg(XMLELEMENT(NAME "siteservice:image", XMLATTRIBUTES(va_provider_images.vmiinstanceid AS id, va_provider_images.good_vmiinstanceid AS goodid)))) AS x
   FROM ((va_providers
     LEFT JOIN va_provider_images ON ((va_provider_images.va_provider_id = va_providers.id)))
     LEFT JOIN vaviews ON (((vaviews.vmiinstanceid = va_provider_images.vmiinstanceid) AND (NOT (vaviews.appid IN ( SELECT app_order_hack.appid
           FROM app_order_hack))))))
  GROUP BY va_providers.id, va_providers.hostname, va_providers.beta, va_providers.in_production, va_providers.sitename
  WITH NO DATA;


ALTER TABLE site_services_xml OWNER TO appdb;

--
-- Name: ssp_kvstore; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE ssp_kvstore (
    _type character varying(30) NOT NULL,
    _key character varying(50) NOT NULL,
    _value text NOT NULL,
    _expire timestamp without time zone
);


ALTER TABLE ssp_kvstore OWNER TO appdb;

--
-- Name: ssp_saml_logoutstore; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE ssp_saml_logoutstore (
    _authsource character varying(30) NOT NULL,
    _nameid character varying(40) NOT NULL,
    _sessionindex character varying(50) NOT NULL,
    _expire timestamp without time zone NOT NULL,
    _sessionid character varying(50) NOT NULL
);


ALTER TABLE ssp_saml_logoutstore OWNER TO appdb;

--
-- Name: ssp_tableversion; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE ssp_tableversion (
    _name character varying(30) NOT NULL,
    _version integer NOT NULL
);


ALTER TABLE ssp_tableversion OWNER TO appdb;

--
-- Name: statuses; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE statuses (
    id integer NOT NULL,
    name character varying(60) NOT NULL
);


ALTER TABLE statuses OWNER TO appdb;

--
-- Name: subdomains; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE subdomains (
    id integer NOT NULL,
    name text NOT NULL
);


ALTER TABLE subdomains OWNER TO appdb;

--
-- Name: swappliance_report; Type: VIEW; Schema: public; Owner: appdb
--

CREATE VIEW swappliance_report AS
 SELECT DISTINCT apps.id,
    apps.name,
    apps.cname,
    apps.deleted,
    apps.moderated,
    swapps.id AS swappid,
    swapps.name AS swappname,
    swapps.cname AS swappcname,
    owners.id AS swappownerid,
    ((owners.firstname || ' '::text) || owners.lastname) AS swappownername,
    contacts.data AS swappowneremail,
    vaviews.va_version_archived AS isarchived,
        CASE
            WHEN (vaviews.va_version_expireson < now()) THEN date_part('day'::text, (now() - (vaviews.va_version_expireson)::timestamp with time zone))
            ELSE (0)::double precision
        END AS days_expired,
        CASE
            WHEN (vaviews.va_version_expireson >= now()) THEN date_part('day'::text, ((vaviews.va_version_expireson)::timestamp with time zone - now()))
            ELSE (0)::double precision
        END AS days_to_expire,
        CASE
            WHEN vaviews.va_version_archived THEN date_part('day'::text, (now() - (vaviews.va_version_archivedon)::timestamp with time zone))
            ELSE (0)::double precision
        END AS days_archived
   FROM (((((((((contexts
     JOIN context_script_assocs ON ((context_script_assocs.contextid = contexts.id)))
     JOIN contextscripts cs ON ((cs.id = context_script_assocs.scriptid)))
     JOIN vmiinstance_contextscripts vcs ON ((vcs.contextscriptid = cs.id)))
     JOIN vaviews ON ((vaviews.vmiinstanceid = vcs.vmiinstanceid)))
     JOIN applications apps ON ((apps.id = vaviews.appid)))
     JOIN applications swapps ON ((swapps.id = contexts.appid)))
     JOIN researchers owners ON ((owners.id = swapps.owner)))
     JOIN contacts ON (((contacts.researcherid = owners.id) AND (contacts.isprimary = true))))
     JOIN contacttypes ON ((contacttypes.id = contacts.contacttypeid)))
  WHERE ((((apps.metatype = 1) AND (vaviews.va_version_published = true)) AND (contacttypes.description = 'e-mail'::text)) AND ((((vaviews.va_version_archived = true) OR (apps.deleted = true)) OR (apps.moderated = true)) OR (vaviews.va_version_expireson < now())));


ALTER TABLE swappliance_report OWNER TO appdb;

--
-- Name: test1; Type: MATERIALIZED VIEW; Schema: public; Owner: appdb; Tablespace: 
--

CREATE MATERIALIZED VIEW test1 AS
 SELECT 1 AS int4
  WITH NO DATA;


ALTER TABLE test1 OWNER TO appdb;

--
-- Name: url_whitelist_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE url_whitelist_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE url_whitelist_id_seq OWNER TO appdb;

--
-- Name: url_whitelist_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE url_whitelist_id_seq OWNED BY url_whitelist.id;


--
-- Name: user_account_states; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE user_account_states (
    id integer NOT NULL,
    name text NOT NULL,
    description text
);


ALTER TABLE user_account_states OWNER TO appdb;

--
-- Name: user_account_states_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE user_account_states_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE user_account_states_id_seq OWNER TO appdb;

--
-- Name: user_account_states_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE user_account_states_id_seq OWNED BY user_account_states.id;


--
-- Name: user_accounts_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE user_accounts_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE user_accounts_id_seq OWNER TO appdb;

--
-- Name: user_accounts_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE user_accounts_id_seq OWNED BY user_accounts.id;


--
-- Name: user_credentials; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE user_credentials (
    id integer NOT NULL,
    researcherid bigint NOT NULL,
    sessionid text NOT NULL,
    token text NOT NULL,
    addedon timestamp without time zone DEFAULT now() NOT NULL
);


ALTER TABLE user_credentials OWNER TO appdb;

--
-- Name: user_credentials_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE user_credentials_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE user_credentials_id_seq OWNER TO appdb;

--
-- Name: user_credentials_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE user_credentials_id_seq OWNED BY user_credentials.id;


--
-- Name: userrequests_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE userrequests_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE userrequests_id_seq OWNER TO appdb;

--
-- Name: userrequests_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE userrequests_id_seq OWNED BY userrequests.id;


--
-- Name: userrequeststates; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE userrequeststates (
    id integer NOT NULL,
    name text,
    description text
);


ALTER TABLE userrequeststates OWNER TO appdb;

--
-- Name: userrequeststates_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE userrequeststates_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE userrequeststates_id_seq OWNER TO appdb;

--
-- Name: userrequeststates_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE userrequeststates_id_seq OWNED BY userrequeststates.id;


--
-- Name: userrequesttypes_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE userrequesttypes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE userrequesttypes_id_seq OWNER TO appdb;

--
-- Name: userrequesttypes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE userrequesttypes_id_seq OWNED BY userrequesttypes.id;


--
-- Name: va_provider_endpoints; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE va_provider_endpoints (
    id bigint NOT NULL,
    va_provider_id text NOT NULL,
    endpoint_url text,
    deployment_type text
);


ALTER TABLE va_provider_endpoints OWNER TO appdb;

--
-- Name: va_provider_endpoints_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE va_provider_endpoints_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE va_provider_endpoints_id_seq OWNER TO appdb;

--
-- Name: va_provider_endpoints_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE va_provider_endpoints_id_seq OWNED BY va_provider_endpoints.id;


--
-- Name: va_provider_images_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE va_provider_images_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE va_provider_images_id_seq OWNER TO appdb;

--
-- Name: va_provider_images_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE va_provider_images_id_seq OWNED BY __va_provider_images.id;


--
-- Name: va_provider_templates_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE va_provider_templates_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE va_provider_templates_id_seq OWNER TO appdb;

--
-- Name: va_provider_templates_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE va_provider_templates_id_seq OWNED BY va_provider_templates.id;


--
-- Name: vapp_to_xml; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE vapp_to_xml (
    appid integer,
    vappid integer,
    xml xml
);


ALTER TABLE vapp_to_xml OWNER TO appdb;

--
-- Name: vapp_versions_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE vapp_versions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE vapp_versions_id_seq OWNER TO appdb;

--
-- Name: vapp_versions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE vapp_versions_id_seq OWNED BY vapp_versions.id;


--
-- Name: vapplications_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE vapplications_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE vapplications_id_seq OWNER TO appdb;

--
-- Name: vapplications_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE vapplications_id_seq OWNED BY vapplications.id;


--
-- Name: vapplists_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE vapplists_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE vapplists_id_seq OWNER TO appdb;

--
-- Name: vapplists_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE vapplists_id_seq OWNED BY vapplists.id;


--
-- Name: vapps_of_swapps_to_xml; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE vapps_of_swapps_to_xml (
    id integer,
    xml xml
);


ALTER TABLE vapps_of_swapps_to_xml OWNER TO appdb;

--
-- Name: vappviews; Type: VIEW; Schema: public; Owner: appdb
--

CREATE VIEW vappviews AS
 SELECT vapplications.id AS vapplicationid,
    vapp_versions.id AS vappversionid,
    vmis.id AS vmiid,
    vmiinstances.id AS vmiinstanceid,
    vmiflavours.id AS vmiflavourid,
    vapp_versions.guid AS vappversionguid,
    vmis.guid AS vmiguid,
    vmiinstances.guid AS vmiinstanceguid,
    vapplications.name AS vapplicationname,
    vapp_versions.version AS vappversionversion,
    vmis.groupname AS vmigroupname,
    vmiinstances.version AS instanceversion
   FROM (((((vmiinstances
     JOIN vapplists ON ((vapplists.vmiinstanceid = vmiinstances.id)))
     JOIN vapp_versions ON ((vapp_versions.id = vapplists.vappversionid)))
     JOIN vapplications ON ((vapplications.id = vapp_versions.vappid)))
     JOIN vmiflavours ON ((vmiflavours.id = vmiinstances.vmiflavourid)))
     JOIN vmis ON ((vmis.id = vmiflavours.vmiid)))
  ORDER BY vapplications.id, vapp_versions.id, vmis.id, vmiinstances.id, vmiflavours.id;


ALTER TABLE vappviews OWNER TO appdb;

--
-- Name: vldap_group_members; Type: VIEW; Schema: public; Owner: appdb
--

CREATE VIEW vldap_group_members AS
 SELECT researchers.id AS user_id,
    editable_apps.appid AS group_id
   FROM (editable_apps
     JOIN researchers ON ((researchers.guid = editable_apps.actor)));


ALTER TABLE vldap_group_members OWNER TO appdb;

--
-- Name: vmcaster_requests; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE vmcaster_requests (
    id integer NOT NULL,
    status text,
    username text,
    password text,
    authtype text,
    errormessage text,
    insertedon timestamp without time zone DEFAULT now() NOT NULL,
    lastsubmitted timestamp without time zone DEFAULT now() NOT NULL,
    ip text,
    input_vmil text,
    produced_xml text,
    appid integer,
    action text DEFAULT ''::text NOT NULL,
    entity text DEFAULT ''::text NOT NULL,
    ldap_sn text DEFAULT ''::text NOT NULL,
    ldap_dn text DEFAULT ''::text NOT NULL,
    ldap_email text DEFAULT ''::text NOT NULL,
    ldap_displayname text DEFAULT ''::text NOT NULL,
    ldap_cn text DEFAULT ''::text NOT NULL,
    ldap_usercertificatesubject text DEFAULT ''::text NOT NULL,
    ldap_givenname text DEFAULT ''::text NOT NULL,
    rid integer,
    uid integer DEFAULT 0 NOT NULL,
    CONSTRAINT vmcaster_requests_authtype_check CHECK ((authtype = ANY (ARRAY['sso'::text, 'x509'::text]))),
    CONSTRAINT vmcaster_requests_status_check CHECK ((status = ANY (ARRAY['pending'::text, 'success'::text, 'failed'::text])))
);


ALTER TABLE vmcaster_requests OWNER TO appdb;

--
-- Name: vmcaster_requests_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE vmcaster_requests_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE vmcaster_requests_id_seq OWNER TO appdb;

--
-- Name: vmcaster_requests_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE vmcaster_requests_id_seq OWNED BY vmcaster_requests.id;


--
-- Name: vmiflavours_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE vmiflavours_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE vmiflavours_id_seq OWNER TO appdb;

--
-- Name: vmiflavours_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE vmiflavours_id_seq OWNED BY vmiflavours.id;


--
-- Name: vmiformats; Type: VIEW; Schema: public; Owner: appdb
--

CREATE VIEW vmiformats AS
 SELECT e.enumsortorder AS id,
    e.enumlabel AS name
   FROM (pg_enum e
     JOIN pg_type t ON ((e.enumtypid = t.oid)))
  WHERE (t.typname = 'e_vmiformats'::name);


ALTER TABLE vmiformats OWNER TO appdb;

--
-- Name: vmiinstance_contextscripts_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE vmiinstance_contextscripts_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE vmiinstance_contextscripts_id_seq OWNER TO appdb;

--
-- Name: vmiinstance_contextscripts_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE vmiinstance_contextscripts_id_seq OWNED BY vmiinstance_contextscripts.id;


--
-- Name: vmiinstances_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE vmiinstances_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE vmiinstances_id_seq OWNER TO appdb;

--
-- Name: vmiinstances_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE vmiinstances_id_seq OWNED BY vmiinstances.id;


--
-- Name: vmis_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE vmis_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE vmis_id_seq OWNER TO appdb;

--
-- Name: vmis_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE vmis_id_seq OWNED BY vmis.id;


--
-- Name: vo_dupes; Type: VIEW; Schema: public; Owner: appdb
--

CREATE VIEW vo_dupes AS
 SELECT v1.id AS egiid,
    v2.id AS ebiid
   FROM vos v1,
    vos v2
  WHERE (((lower(v1.name) = lower(v2.name)) AND (v1.sourceid = 1)) AND (v2.sourceid = 2));


ALTER TABLE vo_dupes OWNER TO appdb;

--
-- Name: vo_middlewares; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE vo_middlewares (
    void integer NOT NULL,
    middlewareid integer NOT NULL
);


ALTER TABLE vo_middlewares OWNER TO appdb;

--
-- Name: vo_obsolete_images; Type: VIEW; Schema: public; Owner: appdb
--

CREATE VIEW vo_obsolete_images AS
 SELECT vowide_image_lists.void,
    vos.name AS voname,
    bool_or(applications.deleted) AS hasdeleted,
    bool_or((vaviews.va_version_expireson < (now())::date)) AS hasexpired,
    bool_or((vaviews.va_version_archived AND vaviews.va_version_published)) AS hasoutdated,
    (('['::text || string_agg(DISTINCT (((((((((((((((((('{"id":"'::text || (applications.id)::text) || '"'::text) || ',"cname":"'::text) || replace(applications.cname, '"'::text, '\"'::text)) || '"'::text) || ',"name":"'::text) || replace(applications.name, '"'::text, '\"'::text)) || '"'::text) || ', "expired":"'::text) || ((vaviews.va_version_expireson < (now())::date))::text) || '"'::text) || ',"outdated":"'::text) || ((vaviews.va_version_archived AND vaviews.va_version_published))::text) || '"'::text) || ',"deleted":"'::text) || (applications.deleted)::text) || '"'::text) || '}'::text), ','::text)) || ']'::text) AS apps
   FROM (((((vaviews
     JOIN vowide_image_list_images ON ((vowide_image_list_images.vapplistid = vaviews.vapplistid)))
     JOIN vowide_image_lists ON ((vowide_image_lists.id = vowide_image_list_images.vowide_image_list_id)))
     JOIN vos ON ((vos.id = vowide_image_lists.void)))
     JOIN applications ON ((applications.id = vaviews.appid)))
     JOIN vapplists ON ((vapplists.id = vowide_image_list_images.vapplistid)))
  WHERE (((vowide_image_lists.state = 'published'::e_vowide_image_state) AND (vaviews.vappversionid = vapplists.vappversionid)) AND ((NOT (vowide_image_list_images.state = 'up-to-date'::e_vowide_image_state)) OR (vaviews.va_version_expireson < (now())::date)))
  GROUP BY vos.name, vowide_image_lists.void;


ALTER TABLE vo_obsolete_images OWNER TO appdb;

--
-- Name: vo_resources; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE vo_resources (
    void integer NOT NULL,
    name text NOT NULL,
    value text
);


ALTER TABLE vo_resources OWNER TO appdb;

--
-- Name: vo_sources; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE vo_sources (
    id integer NOT NULL,
    name text NOT NULL,
    url text NOT NULL,
    members_url text NOT NULL,
    logo text,
    salt text NOT NULL,
    priority integer DEFAULT 0 NOT NULL,
    enabled boolean DEFAULT true NOT NULL
);


ALTER TABLE vo_sources OWNER TO appdb;

--
-- Name: vo_sources_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE vo_sources_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE vo_sources_id_seq OWNER TO appdb;

--
-- Name: vo_sources_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE vo_sources_id_seq OWNED BY vo_sources.id;


--
-- Name: vomses; Type: TABLE; Schema: public; Owner: appdb; Tablespace: 
--

CREATE TABLE vomses (
    void integer NOT NULL,
    hostname text NOT NULL,
    https_port integer NOT NULL,
    vomses_port integer NOT NULL,
    is_admin boolean NOT NULL,
    member_list_url text NOT NULL
);


ALTER TABLE vomses OWNER TO appdb;

--
-- Name: vos_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE vos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE vos_id_seq OWNER TO appdb;

--
-- Name: vos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE vos_id_seq OWNED BY vos.id;


--
-- Name: vowide_image_list_images_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE vowide_image_list_images_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE vowide_image_list_images_id_seq OWNER TO appdb;

--
-- Name: vowide_image_list_images_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE vowide_image_list_images_id_seq OWNED BY vowide_image_list_images.id;


--
-- Name: vowide_image_lists_id_seq; Type: SEQUENCE; Schema: public; Owner: appdb
--

CREATE SEQUENCE vowide_image_lists_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE vowide_image_lists_id_seq OWNER TO appdb;

--
-- Name: vowide_image_lists_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: appdb
--

ALTER SEQUENCE vowide_image_lists_id_seq OWNED BY vowide_image_lists.id;


--
-- Name: vowide_image_states; Type: VIEW; Schema: public; Owner: appdb
--

CREATE VIEW vowide_image_states AS
 SELECT e.enumsortorder AS id,
    e.enumlabel AS name
   FROM (pg_enum e
     JOIN pg_type t ON ((e.enumtypid = t.oid)))
  WHERE (t.typname = 'e_vowide_image_state'::name);


ALTER TABLE vowide_image_states OWNER TO appdb;

--
-- Name: working_vowide_image_lists; Type: VIEW; Schema: public; Owner: appdb
--

CREATE VIEW working_vowide_image_lists AS
 SELECT vos.id AS void,
    vos.name AS voname,
    vmiinstances.id AS vmiinstanceid,
    vmiinstances.guid AS vmiinstance_guid,
    vapp_versions.id AS vappversionid,
    vapp_versions.version AS vappversion,
    vapplications.id AS vappid,
    vapplications.name AS vappname,
    vowide_image_list_images.id AS vowide_image_id,
    vowide_image_list_images.guid AS vowide_image_guid,
    vowide_image_list_images.state AS vowide_image_state,
    vowide_image_lists.state AS vowide_list_state
   FROM ((((((((vowide_image_lists
     JOIN vowide_image_list_images ON ((vowide_image_list_images.vowide_image_list_id = vowide_image_lists.id)))
     JOIN vos ON ((vos.id = vowide_image_lists.void)))
     JOIN vapplists ON ((vapplists.id = vowide_image_list_images.vapplistid)))
     JOIN vmiinstances ON ((vmiinstances.id = vapplists.vmiinstanceid)))
     JOIN vapp_versions ON ((vapp_versions.id = vapplists.vappversionid)))
     JOIN vmiflavours ON ((vmiflavours.id = vmiinstances.vmiflavourid)))
     JOIN vmis ON ((vmis.id = vmiflavours.vmiid)))
     JOIN vapplications ON ((vapplications.id = vmis.vappid)))
  WHERE ((((vapp_versions.published AND (NOT vapp_versions.archived)) AND vapp_versions.enabled) AND (vowide_image_lists.state <> 'obsolete'::e_vowide_image_state)) AND (vowide_image_list_images.state <> 'obsolete'::e_vowide_image_state));


ALTER TABLE working_vowide_image_lists OWNER TO appdb;

SET search_path = researchers, pg_catalog;

--
-- Name: any; Type: TABLE; Schema: researchers; Owner: appdb; Tablespace: 
--

CREATE TABLE "any" (
    id integer NOT NULL,
    "any" text
);


ALTER TABLE "any" OWNER TO appdb;

SET search_path = sci_class, pg_catalog;

--
-- Name: cids; Type: TABLE; Schema: sci_class; Owner: appdb; Tablespace: 
--

CREATE TABLE cids (
    id integer NOT NULL
);


ALTER TABLE cids OWNER TO appdb;

--
-- Name: cids_id_seq; Type: SEQUENCE; Schema: sci_class; Owner: appdb
--

CREATE SEQUENCE cids_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE cids_id_seq OWNER TO appdb;

--
-- Name: cids_id_seq; Type: SEQUENCE OWNED BY; Schema: sci_class; Owner: appdb
--

ALTER SEQUENCE cids_id_seq OWNED BY cids.id;


--
-- Name: cpropids; Type: TABLE; Schema: sci_class; Owner: appdb; Tablespace: 
--

CREATE TABLE cpropids (
    id integer NOT NULL,
    name text NOT NULL
);


ALTER TABLE cpropids OWNER TO appdb;

--
-- Name: cpropids_id_seq; Type: SEQUENCE; Schema: sci_class; Owner: appdb
--

CREATE SEQUENCE cpropids_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE cpropids_id_seq OWNER TO appdb;

--
-- Name: cpropids_id_seq; Type: SEQUENCE OWNED BY; Schema: sci_class; Owner: appdb
--

ALTER SEQUENCE cpropids_id_seq OWNED BY cpropids.id;


--
-- Name: cprops; Type: TABLE; Schema: sci_class; Owner: appdb; Tablespace: 
--

CREATE TABLE cprops (
    id integer NOT NULL,
    cid integer NOT NULL,
    cpropid integer NOT NULL,
    val text NOT NULL
);


ALTER TABLE cprops OWNER TO appdb;

--
-- Name: cprops_id_seq; Type: SEQUENCE; Schema: sci_class; Owner: appdb
--

CREATE SEQUENCE cprops_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE cprops_id_seq OWNER TO appdb;

--
-- Name: cprops_id_seq; Type: SEQUENCE OWNED BY; Schema: sci_class; Owner: appdb
--

ALTER SEQUENCE cprops_id_seq OWNED BY cprops.id;


--
-- Name: cverids; Type: TABLE; Schema: sci_class; Owner: appdb; Tablespace: 
--

CREATE TABLE cverids (
    id integer NOT NULL,
    version text NOT NULL,
    createdon timestamp without time zone DEFAULT now() NOT NULL,
    publishedon timestamp without time zone,
    archivedon timestamp without time zone,
    state e_version_state DEFAULT 'under-devel'::e_version_state NOT NULL
);


ALTER TABLE cverids OWNER TO appdb;

--
-- Name: cverids_id_seq; Type: SEQUENCE; Schema: sci_class; Owner: appdb
--

CREATE SEQUENCE cverids_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE cverids_id_seq OWNER TO appdb;

--
-- Name: cverids_id_seq; Type: SEQUENCE OWNED BY; Schema: sci_class; Owner: appdb
--

ALTER SEQUENCE cverids_id_seq OWNED BY cverids.id;


--
-- Name: cvers; Type: TABLE; Schema: sci_class; Owner: appdb; Tablespace: 
--

CREATE TABLE cvers (
    id integer NOT NULL,
    version text NOT NULL,
    cpropid integer NOT NULL
);


ALTER TABLE cvers OWNER TO appdb;

--
-- Name: cvers_id_seq; Type: SEQUENCE; Schema: sci_class; Owner: appdb
--

CREATE SEQUENCE cvers_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE cvers_id_seq OWNER TO appdb;

--
-- Name: cvers_id_seq; Type: SEQUENCE OWNED BY; Schema: sci_class; Owner: appdb
--

ALTER SEQUENCE cvers_id_seq OWNED BY cvers.id;


--
-- Name: workingprops; Type: TABLE; Schema: sci_class; Owner: appdb; Tablespace: 
--

CREATE TABLE workingprops (
    cid integer,
    cpropid integer,
    cpropname text,
    cpropvalue text,
    disciplineid integer
);


ALTER TABLE workingprops OWNER TO appdb;

SET search_path = sites, pg_catalog;

--
-- Name: any; Type: TABLE; Schema: sites; Owner: appdb; Tablespace: 
--

CREATE TABLE "any" (
    id text NOT NULL,
    "any" text
);


ALTER TABLE "any" OWNER TO appdb;

SET search_path = stats, pg_catalog;

--
-- Name: app_cat_stats; Type: TABLE; Schema: stats; Owner: appdb; Tablespace: 
--

CREATE TABLE app_cat_stats (
    id integer NOT NULL,
    theday date DEFAULT (now())::date NOT NULL,
    metatype integer NOT NULL,
    cnt integer NOT NULL,
    categoryid integer NOT NULL
);


ALTER TABLE app_cat_stats OWNER TO appdb;

--
-- Name: app_cat_stats_id_seq; Type: SEQUENCE; Schema: stats; Owner: appdb
--

CREATE SEQUENCE app_cat_stats_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE app_cat_stats_id_seq OWNER TO appdb;

--
-- Name: app_cat_stats_id_seq; Type: SEQUENCE OWNED BY; Schema: stats; Owner: appdb
--

ALTER SEQUENCE app_cat_stats_id_seq OWNED BY app_cat_stats.id;


--
-- Name: app_disc_stats; Type: TABLE; Schema: stats; Owner: appdb; Tablespace: 
--

CREATE TABLE app_disc_stats (
    id integer NOT NULL,
    theday date DEFAULT (now())::date NOT NULL,
    metatype integer NOT NULL,
    cnt integer NOT NULL,
    disciplineid integer NOT NULL
);


ALTER TABLE app_disc_stats OWNER TO appdb;

--
-- Name: app_disc_stats_id_seq; Type: SEQUENCE; Schema: stats; Owner: appdb
--

CREATE SEQUENCE app_disc_stats_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE app_disc_stats_id_seq OWNER TO appdb;

--
-- Name: app_disc_stats_id_seq; Type: SEQUENCE OWNED BY; Schema: stats; Owner: appdb
--

ALTER SEQUENCE app_disc_stats_id_seq OWNED BY app_disc_stats.id;


--
-- Name: app_vo_cat_disc_history; Type: TABLE; Schema: stats; Owner: appdb; Tablespace: 
--

CREATE TABLE app_vo_cat_disc_history (
    id integer NOT NULL,
    theday date DEFAULT (now())::date NOT NULL,
    metatype integer NOT NULL,
    appid integer NOT NULL,
    void integer[],
    disciplineid integer[],
    categoryid integer[]
);


ALTER TABLE app_vo_cat_disc_history OWNER TO appdb;

--
-- Name: app_vo_cat_disc_history_id_seq; Type: SEQUENCE; Schema: stats; Owner: appdb
--

CREATE SEQUENCE app_vo_cat_disc_history_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE app_vo_cat_disc_history_id_seq OWNER TO appdb;

--
-- Name: app_vo_cat_disc_history_id_seq; Type: SEQUENCE OWNED BY; Schema: stats; Owner: appdb
--

ALTER SEQUENCE app_vo_cat_disc_history_id_seq OWNED BY app_vo_cat_disc_history.id;


--
-- Name: app_vo_stats; Type: TABLE; Schema: stats; Owner: appdb; Tablespace: 
--

CREATE TABLE app_vo_stats (
    id integer NOT NULL,
    theday date DEFAULT (now())::date NOT NULL,
    metatype integer NOT NULL,
    cnt integer NOT NULL,
    void integer NOT NULL
);


ALTER TABLE app_vo_stats OWNER TO appdb;

--
-- Name: app_vo_stats_id_seq; Type: SEQUENCE; Schema: stats; Owner: appdb
--

CREATE SEQUENCE app_vo_stats_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE app_vo_stats_id_seq OWNER TO appdb;

--
-- Name: app_vo_stats_id_seq; Type: SEQUENCE OWNED BY; Schema: stats; Owner: appdb
--

ALTER SEQUENCE app_vo_stats_id_seq OWNED BY app_vo_stats.id;


--
-- Name: storestats; Type: TABLE; Schema: stats; Owner: appdb; Tablespace: 
--

CREATE TABLE storestats (
    id integer NOT NULL,
    theday date DEFAULT (now())::date NOT NULL,
    what text NOT NULL,
    cnt integer NOT NULL
);


ALTER TABLE storestats OWNER TO appdb;

--
-- Name: storestats_id_seq; Type: SEQUENCE; Schema: stats; Owner: appdb
--

CREATE SEQUENCE storestats_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE storestats_id_seq OWNER TO appdb;

--
-- Name: storestats_id_seq; Type: SEQUENCE OWNED BY; Schema: stats; Owner: appdb
--

ALTER SEQUENCE storestats_id_seq OWNED BY storestats.id;


--
-- Name: vo_disc_stats; Type: TABLE; Schema: stats; Owner: appdb; Tablespace: 
--

CREATE TABLE vo_disc_stats (
    id integer NOT NULL,
    theday date DEFAULT (now())::date NOT NULL,
    cnt integer NOT NULL,
    disciplineid integer NOT NULL
);


ALTER TABLE vo_disc_stats OWNER TO appdb;

--
-- Name: vo_disc_stats_id_seq; Type: SEQUENCE; Schema: stats; Owner: appdb
--

CREATE SEQUENCE vo_disc_stats_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE vo_disc_stats_id_seq OWNER TO appdb;

--
-- Name: vo_disc_stats_id_seq; Type: SEQUENCE OWNED BY; Schema: stats; Owner: appdb
--

ALTER SEQUENCE vo_disc_stats_id_seq OWNED BY vo_disc_stats.id;


SET search_path = statuses, pg_catalog;

--
-- Name: any; Type: TABLE; Schema: statuses; Owner: appdb; Tablespace: 
--

CREATE TABLE "any" (
    id integer NOT NULL,
    "any" text
);


ALTER TABLE "any" OWNER TO appdb;

SET search_path = vos, pg_catalog;

--
-- Name: any; Type: TABLE; Schema: vos; Owner: appdb; Tablespace: 
--

CREATE TABLE "any" (
    id integer NOT NULL,
    "any" text
);


ALTER TABLE "any" OWNER TO appdb;

SET search_path = cache, pg_catalog;

--
-- Name: id; Type: DEFAULT; Schema: cache; Owner: appdb
--

ALTER TABLE ONLY appprivsxmlcache ALTER COLUMN id SET DEFAULT nextval('appprivsxmlcache_id_seq'::regclass);


SET search_path = gocdb, pg_catalog;

--
-- Name: id; Type: DEFAULT; Schema: gocdb; Owner: appdb
--

ALTER TABLE ONLY sites ALTER COLUMN id SET DEFAULT nextval('sites_id_seq'::regclass);


SET search_path = harvest, pg_catalog;

--
-- Name: id; Type: DEFAULT; Schema: harvest; Owner: appdb
--

ALTER TABLE ONLY contactpersons ALTER COLUMN id SET DEFAULT nextval('contactpersons_id_seq'::regclass);


--
-- Name: record_additional_id; Type: DEFAULT; Schema: harvest; Owner: appdb
--

ALTER TABLE ONLY records_additional ALTER COLUMN record_additional_id SET DEFAULT nextval('records_additional_record_additional_id_seq'::regclass);


--
-- Name: search_object_keywords_id; Type: DEFAULT; Schema: harvest; Owner: appdb
--

ALTER TABLE ONLY search_object_keywords ALTER COLUMN search_object_keywords_id SET DEFAULT nextval('search_object_keywords_search_object_keywords_id_seq'::regclass);


SET search_path = public, pg_catalog;

--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY __actor_group_members ALTER COLUMN id SET DEFAULT nextval('__actor_group_members_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY __app_tags ALTER COLUMN id SET DEFAULT nextval('app_tags_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY __va_provider_images ALTER COLUMN id SET DEFAULT nextval('va_provider_images_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY abusereports ALTER COLUMN id SET DEFAULT nextval('abusereports_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY access_tokens ALTER COLUMN id SET DEFAULT nextval('access_tokens_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY actions ALTER COLUMN id SET DEFAULT nextval('actions_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY actor_groups ALTER COLUMN id SET DEFAULT nextval('actor_groups_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY apikeys ALTER COLUMN id SET DEFAULT nextval('apikeys_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY app_api_log ALTER COLUMN id SET DEFAULT nextval('app_api_log_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY app_cnames ALTER COLUMN id SET DEFAULT nextval('app_cnames_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY app_del_infos ALTER COLUMN id SET DEFAULT nextval('app_del_infos_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY app_licenses ALTER COLUMN id SET DEFAULT nextval('app_licenses_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY app_middlewares ALTER COLUMN id SET DEFAULT nextval('app_middlewares_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY app_mod_infos ALTER COLUMN id SET DEFAULT nextval('app_mod_infos_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY app_releases ALTER COLUMN id SET DEFAULT nextval('app_releases_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY app_urls ALTER COLUMN id SET DEFAULT nextval('app_urls_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY app_validation_log ALTER COLUMN id SET DEFAULT nextval('app_validation_log_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY appcategories ALTER COLUMN id SET DEFAULT nextval('appcategories_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY appdisciplines ALTER COLUMN id SET DEFAULT nextval('appdisciplines_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY appdocuments ALTER COLUMN id SET DEFAULT nextval('appdocuments_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY appgroups ALTER COLUMN id SET DEFAULT nextval('appgroups_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY applications ALTER COLUMN id SET DEFAULT nextval('applications_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY appratings ALTER COLUMN id SET DEFAULT nextval('appratings_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY archs ALTER COLUMN id SET DEFAULT nextval('archs_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY categories ALTER COLUMN id SET DEFAULT nextval('categories_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY category_help ALTER COLUMN id SET DEFAULT nextval('category_help_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY contacts ALTER COLUMN id SET DEFAULT nextval('contacts_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY context_script_assocs ALTER COLUMN id SET DEFAULT nextval('context_script_assocs_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY contextformats ALTER COLUMN id SET DEFAULT nextval('contextformats_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY contexts ALTER COLUMN id SET DEFAULT nextval('contexts_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY contextscripts ALTER COLUMN id SET DEFAULT nextval('contextscripts_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY contracttypes ALTER COLUMN id SET DEFAULT nextval('contracttypes_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY countries ALTER COLUMN id SET DEFAULT nextval('countries_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY dataset_conn_types ALTER COLUMN id SET DEFAULT nextval('dataset_conn_types_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY dataset_disciplines ALTER COLUMN id SET DEFAULT nextval('dataset_disciplines_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY dataset_exchange_formats ALTER COLUMN id SET DEFAULT nextval('dataset_exchange_formats_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY dataset_licenses ALTER COLUMN id SET DEFAULT nextval('dataset_licenses_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY dataset_location_organizations ALTER COLUMN id SET DEFAULT nextval('dataset_location_organizations_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY dataset_location_sites ALTER COLUMN id SET DEFAULT nextval('dataset_location_sites_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY dataset_locations ALTER COLUMN id SET DEFAULT nextval('dataset_locations_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY dataset_versions ALTER COLUMN id SET DEFAULT nextval('dataset_versions_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY datasets ALTER COLUMN id SET DEFAULT nextval('datasets_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY discipline_help ALTER COLUMN id SET DEFAULT nextval('discipline_help_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY dissemination ALTER COLUMN id SET DEFAULT nextval('dissemination_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY entitysources ALTER COLUMN id SET DEFAULT nextval('entitysources_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY extauthors ALTER COLUMN id SET DEFAULT nextval('extauthors_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY faq_history ALTER COLUMN id SET DEFAULT nextval('faq_history_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY faqs ALTER COLUMN id SET DEFAULT nextval('faqs_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY fundings ALTER COLUMN id SET DEFAULT nextval('fundings_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY idps ALTER COLUMN id SET DEFAULT nextval('idps_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY intauthors ALTER COLUMN id SET DEFAULT nextval('intauthors_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY ldap_attr_mappings ALTER COLUMN id SET DEFAULT nextval('ldap_attr_mappings_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY ldap_oc_mappings ALTER COLUMN id SET DEFAULT nextval('ldap_oc_mappings_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY licenses ALTER COLUMN id SET DEFAULT nextval('licenses_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY mail_subscriptions ALTER COLUMN id SET DEFAULT nextval('mail_subscriptions_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY messages ALTER COLUMN id SET DEFAULT nextval('messages_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY middlewares ALTER COLUMN id SET DEFAULT nextval('middlewares_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY news ALTER COLUMN id SET DEFAULT nextval('news_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY ngis ALTER COLUMN id SET DEFAULT nextval('ngis_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY organizations ALTER COLUMN id SET DEFAULT nextval('organizations_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY os_families ALTER COLUMN id SET DEFAULT nextval('os_families_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY oses ALTER COLUMN id SET DEFAULT nextval('oses_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY pending_accounts ALTER COLUMN id SET DEFAULT nextval('pending_accounts_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY positiontypes ALTER COLUMN id SET DEFAULT nextval('positiontypes_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY ppl_api_log ALTER COLUMN id SET DEFAULT nextval('ppl_api_log_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY ppl_del_infos ALTER COLUMN id SET DEFAULT nextval('ppl_del_infos_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY privileges ALTER COLUMN id SET DEFAULT nextval('privileges_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY proglangs ALTER COLUMN id SET DEFAULT nextval('proglangs_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY projects ALTER COLUMN id SET DEFAULT nextval('projects_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY relations ALTER COLUMN id SET DEFAULT nextval('relations_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY relationtypes ALTER COLUMN id SET DEFAULT nextval('relationtypes_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY relationverbs ALTER COLUMN id SET DEFAULT nextval('relationverbs_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY researcher_cnames ALTER COLUMN id SET DEFAULT nextval('researcher_cnames_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY researchers ALTER COLUMN id SET DEFAULT nextval('researchers_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY url_whitelist ALTER COLUMN id SET DEFAULT nextval('url_whitelist_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY user_account_states ALTER COLUMN id SET DEFAULT nextval('user_account_states_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY user_accounts ALTER COLUMN id SET DEFAULT nextval('user_accounts_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY user_credentials ALTER COLUMN id SET DEFAULT nextval('user_credentials_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY userrequests ALTER COLUMN id SET DEFAULT nextval('userrequests_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY userrequeststates ALTER COLUMN id SET DEFAULT nextval('userrequeststates_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY userrequesttypes ALTER COLUMN id SET DEFAULT nextval('userrequesttypes_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY va_provider_endpoints ALTER COLUMN id SET DEFAULT nextval('va_provider_endpoints_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY va_provider_templates ALTER COLUMN id SET DEFAULT nextval('va_provider_templates_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY vapp_versions ALTER COLUMN id SET DEFAULT nextval('vapp_versions_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY vapplications ALTER COLUMN id SET DEFAULT nextval('vapplications_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY vapplists ALTER COLUMN id SET DEFAULT nextval('vapplists_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY vmcaster_requests ALTER COLUMN id SET DEFAULT nextval('vmcaster_requests_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY vmiflavours ALTER COLUMN id SET DEFAULT nextval('vmiflavours_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY vmiinstance_contextscripts ALTER COLUMN id SET DEFAULT nextval('vmiinstance_contextscripts_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY vmiinstances ALTER COLUMN id SET DEFAULT nextval('vmiinstances_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY vmis ALTER COLUMN id SET DEFAULT nextval('vmis_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY vo_sources ALTER COLUMN id SET DEFAULT nextval('vo_sources_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY vos ALTER COLUMN id SET DEFAULT nextval('vos_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY vowide_image_list_images ALTER COLUMN id SET DEFAULT nextval('vowide_image_list_images_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY vowide_image_lists ALTER COLUMN id SET DEFAULT nextval('vowide_image_lists_id_seq'::regclass);


SET search_path = sci_class, pg_catalog;

--
-- Name: id; Type: DEFAULT; Schema: sci_class; Owner: appdb
--

ALTER TABLE ONLY cids ALTER COLUMN id SET DEFAULT nextval('cids_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: sci_class; Owner: appdb
--

ALTER TABLE ONLY cpropids ALTER COLUMN id SET DEFAULT nextval('cpropids_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: sci_class; Owner: appdb
--

ALTER TABLE ONLY cprops ALTER COLUMN id SET DEFAULT nextval('cprops_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: sci_class; Owner: appdb
--

ALTER TABLE ONLY cverids ALTER COLUMN id SET DEFAULT nextval('cverids_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: sci_class; Owner: appdb
--

ALTER TABLE ONLY cvers ALTER COLUMN id SET DEFAULT nextval('cvers_id_seq'::regclass);


SET search_path = stats, pg_catalog;

--
-- Name: id; Type: DEFAULT; Schema: stats; Owner: appdb
--

ALTER TABLE ONLY app_cat_stats ALTER COLUMN id SET DEFAULT nextval('app_cat_stats_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: stats; Owner: appdb
--

ALTER TABLE ONLY app_disc_stats ALTER COLUMN id SET DEFAULT nextval('app_disc_stats_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: stats; Owner: appdb
--

ALTER TABLE ONLY app_vo_cat_disc_history ALTER COLUMN id SET DEFAULT nextval('app_vo_cat_disc_history_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: stats; Owner: appdb
--

ALTER TABLE ONLY app_vo_stats ALTER COLUMN id SET DEFAULT nextval('app_vo_stats_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: stats; Owner: appdb
--

ALTER TABLE ONLY storestats ALTER COLUMN id SET DEFAULT nextval('storestats_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: stats; Owner: appdb
--

ALTER TABLE ONLY vo_disc_stats ALTER COLUMN id SET DEFAULT nextval('vo_disc_stats_id_seq'::regclass);


SET search_path = sci_class, pg_catalog;

--
-- Name: cverids_pkey; Type: CONSTRAINT; Schema: sci_class; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY cverids
    ADD CONSTRAINT cverids_pkey PRIMARY KEY (version);


SET search_path = public, pg_catalog;

--
-- Name: disc_to_sciclass; Type: MATERIALIZED VIEW; Schema: public; Owner: appdb; Tablespace: 
--

CREATE MATERIALIZED VIEW disc_to_sciclass AS
 SELECT cids.id AS sciclassid,
    disciplines.id AS disciplineid,
    disciplines.parentid,
    disciplines.ord
   FROM (sci_class.cids
     JOIN disciplines ON ((disciplines.name = sci_class.getprop(( SELECT cverids.version
           FROM sci_class.cverids
          WHERE (cverids.state = 'stable'::sci_class.e_version_state)
          GROUP BY cverids.version
         HAVING (cverids.createdon = max(cverids.createdon))), cids.id, 1))))
  WITH NO DATA;


ALTER TABLE disc_to_sciclass OWNER TO appdb;

SET search_path = cache, pg_catalog;

--
-- Name: appprivsxmlcache_pkey; Type: CONSTRAINT; Schema: cache; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY appprivsxmlcache
    ADD CONSTRAINT appprivsxmlcache_pkey PRIMARY KEY (id);


--
-- Name: appxmlcache_pkey; Type: CONSTRAINT; Schema: cache; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY appxmlcache
    ADD CONSTRAINT appxmlcache_pkey PRIMARY KEY (id);


--
-- Name: filtercache_pkey; Type: CONSTRAINT; Schema: cache; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY filtercache
    ADD CONSTRAINT filtercache_pkey PRIMARY KEY (hash);


SET search_path = egiops, pg_catalog;

--
-- Name: vos_pkey; Type: CONSTRAINT; Schema: egiops; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY vos
    ADD CONSTRAINT vos_pkey PRIMARY KEY (name);


SET search_path = gocdb, pg_catalog;

--
-- Name: gocdb_sites_pkey; Type: CONSTRAINT; Schema: gocdb; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY sites
    ADD CONSTRAINT gocdb_sites_pkey PRIMARY KEY (id);


--
-- Name: gocdb_sites_pkey_key; Type: CONSTRAINT; Schema: gocdb; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY sites
    ADD CONSTRAINT gocdb_sites_pkey_key UNIQUE (pkey);


--
-- Name: va_providers_pkey_key; Type: CONSTRAINT; Schema: gocdb; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY va_providers
    ADD CONSTRAINT va_providers_pkey_key UNIQUE (pkey);


SET search_path = harvest, pg_catalog;

--
-- Name: archives_pkey; Type: CONSTRAINT; Schema: harvest; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY archives
    ADD CONSTRAINT archives_pkey PRIMARY KEY (archive_id);


--
-- Name: contactpersons_fullname_key; Type: CONSTRAINT; Schema: harvest; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY contactpersons
    ADD CONSTRAINT contactpersons_fullname_key UNIQUE (fullname);


--
-- Name: contactpersons_identifier_key; Type: CONSTRAINT; Schema: harvest; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY contactpersons
    ADD CONSTRAINT contactpersons_identifier_key UNIQUE (identifier);


--
-- Name: contactpersons_pkey; Type: CONSTRAINT; Schema: harvest; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY contactpersons
    ADD CONSTRAINT contactpersons_pkey PRIMARY KEY (id);


--
-- Name: pk_raw_field_id; Type: CONSTRAINT; Schema: harvest; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY raw_fields
    ADD CONSTRAINT pk_raw_field_id PRIMARY KEY (raw_field_id);


--
-- Name: projectcontactpersons_projectid_contactpersonid_key; Type: CONSTRAINT; Schema: harvest; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY projectcontactpersons
    ADD CONSTRAINT projectcontactpersons_projectid_contactpersonid_key UNIQUE (projectid, contactpersonid);


--
-- Name: records_additional_pkey; Type: CONSTRAINT; Schema: harvest; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY records_additional
    ADD CONSTRAINT records_additional_pkey PRIMARY KEY (record_additional_id);


--
-- Name: records_pkey; Type: CONSTRAINT; Schema: harvest; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY records
    ADD CONSTRAINT records_pkey PRIMARY KEY (record_id);


--
-- Name: search_keyword_list_pkey; Type: CONSTRAINT; Schema: harvest; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY search_keyword_list
    ADD CONSTRAINT search_keyword_list_pkey PRIMARY KEY (keyword_id);


--
-- Name: search_object_keywords_pkey; Type: CONSTRAINT; Schema: harvest; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY search_object_keywords
    ADD CONSTRAINT search_object_keywords_pkey PRIMARY KEY (search_object_keywords_id);


--
-- Name: search_objects_pkey; Type: CONSTRAINT; Schema: harvest; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY search_objects
    ADD CONSTRAINT search_objects_pkey PRIMARY KEY (object_id);


SET search_path = oses, pg_catalog;

--
-- Name: any_pkey; Type: CONSTRAINT; Schema: oses; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY "any"
    ADD CONSTRAINT any_pkey PRIMARY KEY (id);


SET search_path = perun, pg_catalog;

--
-- Name: vos_pkey; Type: CONSTRAINT; Schema: perun; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY vos
    ADD CONSTRAINT vos_pkey PRIMARY KEY (name);


SET search_path = public, pg_catalog;

--
-- Name: __actor_group_members_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY __actor_group_members
    ADD CONSTRAINT __actor_group_members_pkey PRIMARY KEY (id);


--
-- Name: abusereports_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY abusereports
    ADD CONSTRAINT abusereports_pkey PRIMARY KEY (id);


--
-- Name: access_token_netfilters_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY access_token_netfilters
    ADD CONSTRAINT access_token_netfilters_pkey PRIMARY KEY (tokenid, netfilter);


--
-- Name: access_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY access_tokens
    ADD CONSTRAINT access_tokens_pkey PRIMARY KEY (id);


--
-- Name: actions_pk_vos; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY actions
    ADD CONSTRAINT actions_pk_vos PRIMARY KEY (id);


--
-- Name: actor_groups_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY actor_groups
    ADD CONSTRAINT actor_groups_pkey PRIMARY KEY (id);


--
-- Name: apikey_netfilters_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY apikey_netfilters
    ADD CONSTRAINT apikey_netfilters_pkey PRIMARY KEY (keyid, netfilter);


--
-- Name: apikeys_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY apikeys
    ADD CONSTRAINT apikeys_pkey PRIMARY KEY (id);


--
-- Name: app_api_logger_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY app_api_log
    ADD CONSTRAINT app_api_logger_pkey PRIMARY KEY (id);


--
-- Name: app_cnames_isprimary_appid_key; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY app_cnames
    ADD CONSTRAINT app_cnames_isprimary_appid_key UNIQUE (isprimary, appid);


--
-- Name: app_cnames_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY app_cnames
    ADD CONSTRAINT app_cnames_pkey PRIMARY KEY (id);


--
-- Name: app_data_pk_app_vos; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY app_data
    ADD CONSTRAINT app_data_pk_app_vos PRIMARY KEY (id);


--
-- Name: app_del_infos_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY app_del_infos
    ADD CONSTRAINT app_del_infos_pkey PRIMARY KEY (id);


--
-- Name: app_licenses_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY app_licenses
    ADD CONSTRAINT app_licenses_pkey PRIMARY KEY (id);


--
-- Name: app_middlewares_appid_middlewareid_comment_key; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY app_middlewares
    ADD CONSTRAINT app_middlewares_appid_middlewareid_comment_key UNIQUE (appid, middlewareid, comment);


--
-- Name: app_middlewares_pk_app_data; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY app_middlewares
    ADD CONSTRAINT app_middlewares_pk_app_data PRIMARY KEY (id);


--
-- Name: app_mod_infos_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY app_mod_infos
    ADD CONSTRAINT app_mod_infos_pkey PRIMARY KEY (id);


--
-- Name: app_releases_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY app_releases
    ADD CONSTRAINT app_releases_pkey PRIMARY KEY (id);


--
-- Name: app_releases_releaseid_key; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY app_releases
    ADD CONSTRAINT app_releases_releaseid_key UNIQUE (releaseid);


--
-- Name: app_tags_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY __app_tags
    ADD CONSTRAINT app_tags_pkey PRIMARY KEY (id);


--
-- Name: app_urls_pk_app_middlewares; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY app_urls
    ADD CONSTRAINT app_urls_pk_app_middlewares PRIMARY KEY (id);


--
-- Name: app_validation_log_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY app_validation_log
    ADD CONSTRAINT app_validation_log_pkey PRIMARY KEY (id);


--
-- Name: app_vos_pk_appmodhistories; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY __app_vos
    ADD CONSTRAINT app_vos_pk_appmodhistories PRIMARY KEY (void, appid);


--
-- Name: appbookmarks_pk_actions; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY appbookmarks
    ADD CONSTRAINT appbookmarks_pk_actions PRIMARY KEY (appid, researcherid);


--
-- Name: appcategories_appid_categoryid_key; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY appcategories
    ADD CONSTRAINT appcategories_appid_categoryid_key UNIQUE (appid, categoryid);


--
-- Name: appcategories_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY appcategories
    ADD CONSTRAINT appcategories_pkey PRIMARY KEY (id);


--
-- Name: appcontact_middlewares_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY appcontact_middlewares
    ADD CONSTRAINT appcontact_middlewares_pkey PRIMARY KEY (appid, researcherid, appmiddlewareid);


--
-- Name: appcontact_otheritems_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY appcontact_otheritems
    ADD CONSTRAINT appcontact_otheritems_pkey PRIMARY KEY (appid, researcherid, item);


--
-- Name: appcontact_vos_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY appcontact_vos
    ADD CONSTRAINT appcontact_vos_pkey PRIMARY KEY (appid, researcherid, void);


--
-- Name: appdisciplines_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY appdisciplines
    ADD CONSTRAINT appdisciplines_pkey PRIMARY KEY (id);


--
-- Name: appdocuments_pk_appbookmarks; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY appdocuments
    ADD CONSTRAINT appdocuments_pk_appbookmarks PRIMARY KEY (id);


--
-- Name: appdomains_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY appdomains
    ADD CONSTRAINT appdomains_pkey PRIMARY KEY (id);


--
-- Name: appgroups_pk_appdocuments; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY appgroups
    ADD CONSTRAINT appgroups_pk_appdocuments PRIMARY KEY (id);


--
-- Name: applications_pk_app_urls; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY applications
    ADD CONSTRAINT applications_pk_app_urls PRIMARY KEY (id);


--
-- Name: applogos_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY applogos
    ADD CONSTRAINT applogos_pkey PRIMARY KEY (appid);


--
-- Name: appmanualcountries_pk_appgroups; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY appmanualcountries
    ADD CONSTRAINT appmanualcountries_pk_appgroups PRIMARY KEY (appid, countryid);


--
-- Name: appmodhistories_pk_appmanualcountries; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY appmodhistories
    ADD CONSTRAINT appmodhistories_pk_appmanualcountries PRIMARY KEY (id);


--
-- Name: appratings_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY appratings
    ADD CONSTRAINT appratings_pkey PRIMARY KEY (id);


--
-- Name: appsubdomains_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY appsubdomains
    ADD CONSTRAINT appsubdomains_pkey PRIMARY KEY (id);


--
-- Name: archs_name_key; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY archs
    ADD CONSTRAINT archs_name_key UNIQUE (name);


--
-- Name: archs_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY archs
    ADD CONSTRAINT archs_pkey PRIMARY KEY (id);


--
-- Name: categories_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY categories
    ADD CONSTRAINT categories_pkey PRIMARY KEY (id);


--
-- Name: category_help_categoryid_type_key; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY category_help
    ADD CONSTRAINT category_help_categoryid_type_key UNIQUE (categoryid, type);


--
-- Name: category_help_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY category_help
    ADD CONSTRAINT category_help_pkey PRIMARY KEY (id);


--
-- Name: contacts_pk_contacttypes; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY contacts
    ADD CONSTRAINT contacts_pk_contacttypes PRIMARY KEY (id);


--
-- Name: contacttype_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY contracttypes
    ADD CONSTRAINT contacttype_pkey PRIMARY KEY (id);


--
-- Name: contacttypes_pk_applications; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY contacttypes
    ADD CONSTRAINT contacttypes_pk_applications PRIMARY KEY (id);


--
-- Name: context_contextscripts_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY context_script_assocs
    ADD CONSTRAINT context_contextscripts_pkey PRIMARY KEY (id);


--
-- Name: contextformats_name_key; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY contextformats
    ADD CONSTRAINT contextformats_name_key UNIQUE (name);


--
-- Name: contextformats_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY contextformats
    ADD CONSTRAINT contextformats_pkey PRIMARY KEY (id);


--
-- Name: contexts_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY contexts
    ADD CONSTRAINT contexts_pkey PRIMARY KEY (id);


--
-- Name: contextscripts_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY contextscripts
    ADD CONSTRAINT contextscripts_pkey PRIMARY KEY (id);


--
-- Name: contracttypes_name_key; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY contracttypes
    ADD CONSTRAINT contracttypes_name_key UNIQUE (name);


--
-- Name: countries_pk_contacts; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY countries
    ADD CONSTRAINT countries_pk_contacts PRIMARY KEY (id);


--
-- Name: dataset_conn_types_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY dataset_conn_types
    ADD CONSTRAINT dataset_conn_types_pkey PRIMARY KEY (id);


--
-- Name: dataset_disciplines_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY dataset_disciplines
    ADD CONSTRAINT dataset_disciplines_pkey PRIMARY KEY (id);


--
-- Name: dataset_exchange_formats_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY dataset_exchange_formats
    ADD CONSTRAINT dataset_exchange_formats_pkey PRIMARY KEY (id);


--
-- Name: dataset_licenses_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY dataset_licenses
    ADD CONSTRAINT dataset_licenses_pkey PRIMARY KEY (id);


--
-- Name: dataset_location_organizations_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY dataset_location_organizations
    ADD CONSTRAINT dataset_location_organizations_pkey PRIMARY KEY (id);


--
-- Name: dataset_location_sites_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY dataset_location_sites
    ADD CONSTRAINT dataset_location_sites_pkey PRIMARY KEY (id);


--
-- Name: dataset_locations_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY dataset_locations
    ADD CONSTRAINT dataset_locations_pkey PRIMARY KEY (id);


--
-- Name: dataset_versions_pkey1; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY dataset_versions
    ADD CONSTRAINT dataset_versions_pkey1 PRIMARY KEY (id);


--
-- Name: datasets_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY datasets
    ADD CONSTRAINT datasets_pkey PRIMARY KEY (id);


--
-- Name: discipline_help_disciplineid_type_key; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY discipline_help
    ADD CONSTRAINT discipline_help_disciplineid_type_key UNIQUE (disciplineid, type);


--
-- Name: discipline_help_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY discipline_help
    ADD CONSTRAINT discipline_help_pkey PRIMARY KEY (id);


--
-- Name: disciplines_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY disciplines
    ADD CONSTRAINT disciplines_pkey PRIMARY KEY (id);


--
-- Name: dissemination_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY dissemination
    ADD CONSTRAINT dissemination_pkey PRIMARY KEY (id);


--
-- Name: doctypes_pk_countries; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY doctypes
    ADD CONSTRAINT doctypes_pk_countries PRIMARY KEY (id);


--
-- Name: domains_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY domains
    ADD CONSTRAINT domains_pkey PRIMARY KEY (id);


--
-- Name: entitysources_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY entitysources
    ADD CONSTRAINT entitysources_pkey PRIMARY KEY (id);


--
-- Name: extauthors_docid_author_key; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY extauthors
    ADD CONSTRAINT extauthors_docid_author_key UNIQUE (docid, author);


--
-- Name: extauthors_pk_domains; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY extauthors
    ADD CONSTRAINT extauthors_pk_domains PRIMARY KEY (id);


--
-- Name: faq_history_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY faq_history
    ADD CONSTRAINT faq_history_pkey PRIMARY KEY (id);


--
-- Name: faqs_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY faqs
    ADD CONSTRAINT faqs_pkey PRIMARY KEY (id);


--
-- Name: fundings_identifier_key; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY fundings
    ADD CONSTRAINT fundings_identifier_key UNIQUE (identifier);


--
-- Name: fundings_name_key; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY fundings
    ADD CONSTRAINT fundings_name_key UNIQUE (name);


--
-- Name: fundings_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY fundings
    ADD CONSTRAINT fundings_pkey PRIMARY KEY (id);


--
-- Name: idps_entityid_key; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY idps
    ADD CONSTRAINT idps_entityid_key UNIQUE (entityid);


--
-- Name: idps_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY idps
    ADD CONSTRAINT idps_pkey PRIMARY KEY (id);


--
-- Name: intauthors_docid_authorid_key; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY intauthors
    ADD CONSTRAINT intauthors_docid_authorid_key UNIQUE (docid, authorid);


--
-- Name: intauthors_pk_extauthors; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY intauthors
    ADD CONSTRAINT intauthors_pk_extauthors PRIMARY KEY (id);


--
-- Name: ldap_attr_mappings_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY ldap_attr_mappings
    ADD CONSTRAINT ldap_attr_mappings_pkey PRIMARY KEY (id);


--
-- Name: ldap_oc_mappings_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY ldap_oc_mappings
    ADD CONSTRAINT ldap_oc_mappings_pkey PRIMARY KEY (id);


--
-- Name: licenses_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY licenses
    ADD CONSTRAINT licenses_pkey PRIMARY KEY (id);


--
-- Name: mail_subscriptions_name_researcherid_key; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY mail_subscriptions
    ADD CONSTRAINT mail_subscriptions_name_researcherid_key UNIQUE (name, researcherid);


--
-- Name: mail_subscriptions_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY mail_subscriptions
    ADD CONSTRAINT mail_subscriptions_pkey PRIMARY KEY (id);


--
-- Name: messages_pk_intauthors; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY messages
    ADD CONSTRAINT messages_pk_intauthors PRIMARY KEY (id);


--
-- Name: middlewares_pk_messages; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY middlewares
    ADD CONSTRAINT middlewares_pk_messages PRIMARY KEY (id);


--
-- Name: news_pk_middlewares; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY news
    ADD CONSTRAINT news_pk_middlewares PRIMARY KEY (id);


--
-- Name: ngis_pk_; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY ngis
    ADD CONSTRAINT ngis_pk_ PRIMARY KEY (id);


--
-- Name: organizations_identifier_key; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY organizations
    ADD CONSTRAINT organizations_identifier_key UNIQUE (identifier);


--
-- Name: organizations_name_key; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY organizations
    ADD CONSTRAINT organizations_name_key UNIQUE (name);


--
-- Name: organizations_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY organizations
    ADD CONSTRAINT organizations_pkey PRIMARY KEY (id);


--
-- Name: os_families_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY os_families
    ADD CONSTRAINT os_families_pkey PRIMARY KEY (id);


--
-- Name: oses_name_family_key; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY oses
    ADD CONSTRAINT oses_name_family_key UNIQUE (name, os_family_id);


--
-- Name: oses_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY oses
    ADD CONSTRAINT oses_pkey PRIMARY KEY (id);


--
-- Name: pending_accounts_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY pending_accounts
    ADD CONSTRAINT pending_accounts_pkey PRIMARY KEY (id);


--
-- Name: pk_app_archs; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY app_archs
    ADD CONSTRAINT pk_app_archs PRIMARY KEY (appid, archid);


--
-- Name: pk_app_oses; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY app_oses
    ADD CONSTRAINT pk_app_oses PRIMARY KEY (appid, osid);


--
-- Name: pk_appproglangs; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY appproglangs
    ADD CONSTRAINT pk_appproglangs PRIMARY KEY (appid, proglangid);


--
-- Name: pk_pplproglangs; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY pplproglangs
    ADD CONSTRAINT pk_pplproglangs PRIMARY KEY (researcherid, proglangid);


--
-- Name: pk_vomses; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY vomses
    ADD CONSTRAINT pk_vomses PRIMARY KEY (void, hostname);


--
-- Name: positiontypes_pk_permissions; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY positiontypes
    ADD CONSTRAINT positiontypes_pk_permissions PRIMARY KEY (id);


--
-- Name: ppl_api_logger_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY ppl_api_log
    ADD CONSTRAINT ppl_api_logger_pkey PRIMARY KEY (id);


--
-- Name: ppl_del_infos_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY ppl_del_infos
    ADD CONSTRAINT ppl_del_infos_pkey PRIMARY KEY (id);


--
-- Name: privileges_pk_positiontypes; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY privileges
    ADD CONSTRAINT privileges_pk_positiontypes PRIMARY KEY (id);


--
-- Name: proglangs_name_key; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY proglangs
    ADD CONSTRAINT proglangs_name_key UNIQUE (name);


--
-- Name: proglangs_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY proglangs
    ADD CONSTRAINT proglangs_pkey PRIMARY KEY (id);


--
-- Name: projects_identifier_key; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY projects
    ADD CONSTRAINT projects_identifier_key UNIQUE (identifier);


--
-- Name: projects_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY projects
    ADD CONSTRAINT projects_pkey PRIMARY KEY (id);


--
-- Name: regions_pk_privileges; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY regions
    ADD CONSTRAINT regions_pk_privileges PRIMARY KEY (id);


--
-- Name: relations_pk; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY relations
    ADD CONSTRAINT relations_pk PRIMARY KEY (id);


--
-- Name: relationstypes_pk; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY relationtypes
    ADD CONSTRAINT relationstypes_pk PRIMARY KEY (id);


--
-- Name: relationtypes_objtype_verbid_subtype_key; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY relationtypes
    ADD CONSTRAINT relationtypes_objtype_verbid_subtype_key UNIQUE (target_type, verbid, subject_type);


--
-- Name: relationverbs_pk; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY relationverbs
    ADD CONSTRAINT relationverbs_pk PRIMARY KEY (id);


--
-- Name: researcher_cnames_isprimary_researcherid_key; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY researcher_cnames
    ADD CONSTRAINT researcher_cnames_isprimary_researcherid_key UNIQUE (isprimary, researcherid);


--
-- Name: researcher_cnames_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY researcher_cnames
    ADD CONSTRAINT researcher_cnames_pkey PRIMARY KEY (id);


--
-- Name: researcherimages_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY researcherimages
    ADD CONSTRAINT researcherimages_pkey PRIMARY KEY (researcherid);


--
-- Name: researchers_apps_pk_researchers; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY researchers_apps
    ADD CONSTRAINT researchers_apps_pk_researchers PRIMARY KEY (appid, researcherid);


--
-- Name: researchers_pk_regions; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY researchers
    ADD CONSTRAINT researchers_pk_regions PRIMARY KEY (id);


--
-- Name: ssp_kvstore_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY ssp_kvstore
    ADD CONSTRAINT ssp_kvstore_pkey PRIMARY KEY (_key, _type);


--
-- Name: ssp_saml_logoutstore__authsource__nameid__sessionindex_key; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY ssp_saml_logoutstore
    ADD CONSTRAINT ssp_saml_logoutstore__authsource__nameid__sessionindex_key UNIQUE (_authsource, _nameid, _sessionindex);


--
-- Name: ssp_tableversion__name_key; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY ssp_tableversion
    ADD CONSTRAINT ssp_tableversion__name_key UNIQUE (_name);


--
-- Name: statuses_pk_researchers_apps; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY statuses
    ADD CONSTRAINT statuses_pk_researchers_apps PRIMARY KEY (id);


--
-- Name: subdomains_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY subdomains
    ADD CONSTRAINT subdomains_pkey PRIMARY KEY (id);


--
-- Name: uniq_ds_loc_org; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY dataset_location_organizations
    ADD CONSTRAINT uniq_ds_loc_org UNIQUE (dataset_location_id, organizationid);


--
-- Name: uniq_ds_loc_site; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY dataset_location_sites
    ADD CONSTRAINT uniq_ds_loc_site UNIQUE (dataset_location_id, siteid);


--
-- Name: unique_app_licenses_appid_licenseid_title; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY app_licenses
    ADD CONSTRAINT unique_app_licenses_appid_licenseid_title UNIQUE (appid, licenseid, title);


--
-- Name: unique_dataset_licenses_dsid_licenseid_title; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY dataset_licenses
    ADD CONSTRAINT unique_dataset_licenses_dsid_licenseid_title UNIQUE (datasetid, licenseid, title);


--
-- Name: user_account_states_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY user_account_states
    ADD CONSTRAINT user_account_states_pkey PRIMARY KEY (id);


--
-- Name: user_accounts_accountid_account_type_key; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY user_accounts
    ADD CONSTRAINT user_accounts_accountid_account_type_key UNIQUE (accountid, account_type);


--
-- Name: user_accounts_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY user_accounts
    ADD CONSTRAINT user_accounts_pkey PRIMARY KEY (id);


--
-- Name: user_credentials_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY user_credentials
    ADD CONSTRAINT user_credentials_pkey PRIMARY KEY (id);


--
-- Name: userrequests_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY userrequests
    ADD CONSTRAINT userrequests_pkey PRIMARY KEY (id);


--
-- Name: userrequeststates_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY userrequeststates
    ADD CONSTRAINT userrequeststates_pkey PRIMARY KEY (id);


--
-- Name: userrequesttypes_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY userrequesttypes
    ADD CONSTRAINT userrequesttypes_pkey PRIMARY KEY (id);


--
-- Name: va_provider_endpoints_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY va_provider_endpoints
    ADD CONSTRAINT va_provider_endpoints_pkey PRIMARY KEY (id);


--
-- Name: va_provider_images_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY __va_provider_images
    ADD CONSTRAINT va_provider_images_pkey PRIMARY KEY (id);


--
-- Name: va_provider_templates_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY va_provider_templates
    ADD CONSTRAINT va_provider_templates_pkey PRIMARY KEY (id);


--
-- Name: vapp_versions_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY vapp_versions
    ADD CONSTRAINT vapp_versions_pkey PRIMARY KEY (id);


--
-- Name: vapplications_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY vapplications
    ADD CONSTRAINT vapplications_pkey PRIMARY KEY (id);


--
-- Name: vapplists_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY vapplists
    ADD CONSTRAINT vapplists_pkey PRIMARY KEY (id);


--
-- Name: vmcaster_requests_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY vmcaster_requests
    ADD CONSTRAINT vmcaster_requests_pkey PRIMARY KEY (id);


--
-- Name: vmiflavours_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY vmiflavours
    ADD CONSTRAINT vmiflavours_pkey PRIMARY KEY (id);


--
-- Name: vmiinstance_contextscripts_vmiinstanceid_contextscriptid_key; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY vmiinstance_contextscripts
    ADD CONSTRAINT vmiinstance_contextscripts_vmiinstanceid_contextscriptid_key UNIQUE (vmiinstanceid, contextscriptid);


--
-- Name: vmiinstances_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY vmiinstances
    ADD CONSTRAINT vmiinstances_pkey PRIMARY KEY (id);


--
-- Name: vmininstance_contextscripts_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY vmiinstance_contextscripts
    ADD CONSTRAINT vmininstance_contextscripts_pkey PRIMARY KEY (id);


--
-- Name: vmis_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY vmis
    ADD CONSTRAINT vmis_pkey PRIMARY KEY (id);


--
-- Name: vo_middlewares_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY vo_middlewares
    ADD CONSTRAINT vo_middlewares_pkey PRIMARY KEY (void, middlewareid);


--
-- Name: vo_resources_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY vo_resources
    ADD CONSTRAINT vo_resources_pkey PRIMARY KEY (void, name);


--
-- Name: vo_sources_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY vo_sources
    ADD CONSTRAINT vo_sources_pkey PRIMARY KEY (id);


--
-- Name: vos_pk_ngis; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY vos
    ADD CONSTRAINT vos_pk_ngis PRIMARY KEY (id);


--
-- Name: vowide_image_list_images_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY vowide_image_list_images
    ADD CONSTRAINT vowide_image_list_images_pkey PRIMARY KEY (id);


--
-- Name: vowide_image_list_images_vowide_image_list_id_vapplistid_key; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY vowide_image_list_images
    ADD CONSTRAINT vowide_image_list_images_vowide_image_list_id_vapplistid_key UNIQUE (vowide_image_list_id, vapplistid);


--
-- Name: vowide_image_lists_pkey; Type: CONSTRAINT; Schema: public; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY vowide_image_lists
    ADD CONSTRAINT vowide_image_lists_pkey PRIMARY KEY (id);


SET search_path = sci_class, pg_catalog;

--
-- Name: cids_pkey; Type: CONSTRAINT; Schema: sci_class; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY cids
    ADD CONSTRAINT cids_pkey PRIMARY KEY (id);


--
-- Name: cpropids_name_key; Type: CONSTRAINT; Schema: sci_class; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY cpropids
    ADD CONSTRAINT cpropids_name_key UNIQUE (name);


--
-- Name: cpropids_pkey; Type: CONSTRAINT; Schema: sci_class; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY cpropids
    ADD CONSTRAINT cpropids_pkey PRIMARY KEY (id);


--
-- Name: cprops_cid_cpropid_val_key; Type: CONSTRAINT; Schema: sci_class; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY cprops
    ADD CONSTRAINT cprops_cid_cpropid_val_key UNIQUE (cid, cpropid, val);


--
-- Name: cprops_pkey; Type: CONSTRAINT; Schema: sci_class; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY cprops
    ADD CONSTRAINT cprops_pkey PRIMARY KEY (id);


--
-- Name: cvers_pkey; Type: CONSTRAINT; Schema: sci_class; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY cvers
    ADD CONSTRAINT cvers_pkey PRIMARY KEY (id);


--
-- Name: cvers_version_cpropid_key; Type: CONSTRAINT; Schema: sci_class; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY cvers
    ADD CONSTRAINT cvers_version_cpropid_key UNIQUE (version, cpropid);


SET search_path = stats, pg_catalog;

--
-- Name: app_cat_stats_pkey; Type: CONSTRAINT; Schema: stats; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY app_cat_stats
    ADD CONSTRAINT app_cat_stats_pkey PRIMARY KEY (id);


--
-- Name: app_disc_stats_pkey; Type: CONSTRAINT; Schema: stats; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY app_disc_stats
    ADD CONSTRAINT app_disc_stats_pkey PRIMARY KEY (id);


--
-- Name: app_vo_cat_disc_history_pkey; Type: CONSTRAINT; Schema: stats; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY app_vo_cat_disc_history
    ADD CONSTRAINT app_vo_cat_disc_history_pkey PRIMARY KEY (id);


--
-- Name: app_vo_stats_pkey; Type: CONSTRAINT; Schema: stats; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY app_vo_stats
    ADD CONSTRAINT app_vo_stats_pkey PRIMARY KEY (id);


--
-- Name: storestats_pkey; Type: CONSTRAINT; Schema: stats; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY storestats
    ADD CONSTRAINT storestats_pkey PRIMARY KEY (id);


--
-- Name: uniq_day_app; Type: CONSTRAINT; Schema: stats; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY app_vo_cat_disc_history
    ADD CONSTRAINT uniq_day_app UNIQUE (theday, appid);


--
-- Name: uniq_day_cat_type; Type: CONSTRAINT; Schema: stats; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY app_cat_stats
    ADD CONSTRAINT uniq_day_cat_type UNIQUE (theday, categoryid, metatype);


--
-- Name: uniq_day_disc; Type: CONSTRAINT; Schema: stats; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY vo_disc_stats
    ADD CONSTRAINT uniq_day_disc UNIQUE (theday, disciplineid);


--
-- Name: uniq_day_disc_type; Type: CONSTRAINT; Schema: stats; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY app_disc_stats
    ADD CONSTRAINT uniq_day_disc_type UNIQUE (theday, disciplineid, metatype);


--
-- Name: uniq_day_void_type; Type: CONSTRAINT; Schema: stats; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY app_vo_stats
    ADD CONSTRAINT uniq_day_void_type UNIQUE (theday, void, metatype);


--
-- Name: uniq_daytype; Type: CONSTRAINT; Schema: stats; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY storestats
    ADD CONSTRAINT uniq_daytype UNIQUE (theday, what);


--
-- Name: vo_disc_stats_pkey; Type: CONSTRAINT; Schema: stats; Owner: appdb; Tablespace: 
--

ALTER TABLE ONLY vo_disc_stats
    ADD CONSTRAINT vo_disc_stats_pkey PRIMARY KEY (id);


SET search_path = egiops, pg_catalog;

--
-- Name: vo_contacts_dn_idx; Type: INDEX; Schema: egiops; Owner: appdb; Tablespace: 
--

CREATE INDEX vo_contacts_dn_idx ON vo_contacts USING btree (dn);


--
-- Name: vo_contacts_dn_idx1; Type: INDEX; Schema: egiops; Owner: appdb; Tablespace: 
--

CREATE INDEX vo_contacts_dn_idx1 ON vo_contacts USING btree (dn);


--
-- Name: vo_members_certdn_idx; Type: INDEX; Schema: egiops; Owner: appdb; Tablespace: 
--

CREATE INDEX vo_members_certdn_idx ON vo_members USING btree (certdn);


--
-- Name: vo_members_certdn_idx1; Type: INDEX; Schema: egiops; Owner: appdb; Tablespace: 
--

CREATE INDEX vo_members_certdn_idx1 ON vo_members USING btree (certdn);


SET search_path = harvest, pg_catalog;

--
-- Name: archives_enabled_idx; Type: INDEX; Schema: harvest; Owner: appdb; Tablespace: 
--

CREATE INDEX archives_enabled_idx ON archives USING btree (enabled) WHERE (enabled IS TRUE);


--
-- Name: harvest_records_additional_name_idx; Type: INDEX; Schema: harvest; Owner: appdb; Tablespace: 
--

CREATE INDEX harvest_records_additional_name_idx ON records_additional USING btree (name);


--
-- Name: institutions2_value_gist_idx; Type: INDEX; Schema: harvest; Owner: appdb; Tablespace: 
--

CREATE INDEX institutions2_value_gist_idx ON institutions2 USING gist (lower(institution) public.gist_trgm_ops);


--
-- Name: institutions2b_value_gist_idx; Type: INDEX; Schema: harvest; Owner: appdb; Tablespace: 
--

CREATE INDEX institutions2b_value_gist_idx ON institutions2 USING gist (lower(institution2) public.gist_trgm_ops);


--
-- Name: institutions_value_gist_idx; Type: INDEX; Schema: harvest; Owner: appdb; Tablespace: 
--

CREATE INDEX institutions_value_gist_idx ON institutions USING gist (lower(institution) public.gist_trgm_ops);


--
-- Name: raw_fields_name_idx; Type: INDEX; Schema: harvest; Owner: appdb; Tablespace: 
--

CREATE INDEX raw_fields_name_idx ON raw_fields USING btree (name);


--
-- Name: records_additional_value_idx; Type: INDEX; Schema: harvest; Owner: appdb; Tablespace: 
--

CREATE INDEX records_additional_value_idx ON records_additional USING gist (lower(value) public.gist_trgm_ops);


--
-- Name: records_appdb_identifier_idx; Type: INDEX; Schema: harvest; Owner: appdb; Tablespace: 
--

CREATE INDEX records_appdb_identifier_idx ON records USING btree (appdb_identifier);


--
-- Name: records_archive_id_idx; Type: INDEX; Schema: harvest; Owner: appdb; Tablespace: 
--

CREATE INDEX records_archive_id_idx ON records USING btree (archive_id);


--
-- Name: records_relations_archive_id_idx; Type: INDEX; Schema: harvest; Owner: appdb; Tablespace: 
--

CREATE INDEX records_relations_archive_id_idx ON records_relations USING btree (archive_id);


--
-- Name: search_keyword_list_keyword_text_idx_lower; Type: INDEX; Schema: harvest; Owner: appdb; Tablespace: 
--

CREATE INDEX search_keyword_list_keyword_text_idx_lower ON search_keyword_list USING gin (lower(keyword_text) public.gin_trgm_ops);


--
-- Name: search_objects_archive_id_idx; Type: INDEX; Schema: harvest; Owner: appdb; Tablespace: 
--

CREATE INDEX search_objects_archive_id_idx ON search_objects USING btree (archive_id);


--
-- Name: search_record_ids_archive_id_idx; Type: INDEX; Schema: harvest; Owner: appdb; Tablespace: 
--

CREATE INDEX search_record_ids_archive_id_idx ON search_record_ids USING btree (archive_id);


--
-- Name: search_record_ids_keyword_text_idx; Type: INDEX; Schema: harvest; Owner: appdb; Tablespace: 
--

CREATE INDEX search_record_ids_keyword_text_idx ON search_record_ids USING gin (keyword_text public.gin_trgm_ops);


SET search_path = public, pg_catalog;

--
-- Name: app_middlewares_comment_idx; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX app_middlewares_comment_idx ON app_middlewares USING btree (comment);


--
-- Name: app_middlewares_comment_low_idx; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX app_middlewares_comment_low_idx ON app_middlewares USING btree (lower(comment));


--
-- Name: app_tags_appid_upper_idx; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE UNIQUE INDEX app_tags_appid_upper_idx ON __app_tags USING btree (appid, upper(tag));


--
-- Name: app_urls_fk_app_url; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX app_urls_fk_app_url ON app_urls USING btree (appid);


--
-- Name: appdocuments_fk_appdoc_app; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX appdocuments_fk_appdoc_app ON appdocuments USING btree (appid);


--
-- Name: appdocuments_fk_appdoc_doctype; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX appdocuments_fk_appdoc_doctype ON appdocuments USING btree (doctypeid);


--
-- Name: applications_abstract_idx; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX applications_abstract_idx ON applications USING btree (abstract);


--
-- Name: applications_abstract_low_idx; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX applications_abstract_low_idx ON applications USING btree (lower(abstract));


--
-- Name: applications_cname_idx; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX applications_cname_idx ON applications USING btree (cname);


--
-- Name: applications_cname_low_idx; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX applications_cname_low_idx ON applications USING btree (lower(cname));


--
-- Name: applications_description_idx; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX applications_description_idx ON applications USING btree (description);


--
-- Name: applications_description_low_idx; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX applications_description_low_idx ON applications USING btree (lower(description));


--
-- Name: applications_fk_app_addedby; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX applications_fk_app_addedby ON applications USING btree (addedby);


--
-- Name: applications_fk_app_status; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX applications_fk_app_status ON applications USING btree (statusid);


--
-- Name: applications_name_idx; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX applications_name_idx ON applications USING btree (name);


--
-- Name: applications_name_low_idx; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX applications_name_low_idx ON applications USING btree (lower(name));


--
-- Name: appmanualcountries_fk_app_countries_app; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX appmanualcountries_fk_app_countries_app ON appmanualcountries USING btree (appid);


--
-- Name: appmanualcountries_fk_app_countries_country; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX appmanualcountries_fk_app_countries_country ON appmanualcountries USING btree (countryid);


--
-- Name: appmodhistories_fk_appmod_app; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX appmodhistories_fk_appmod_app ON appmodhistories USING btree (appid);


--
-- Name: appmodhistories_fk_appmod_researcher; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX appmodhistories_fk_appmod_researcher ON appmodhistories USING btree (researcherid);


--
-- Name: archs_name_idx; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX archs_name_idx ON archs USING btree (name);


--
-- Name: archs_name_low_idx; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX archs_name_low_idx ON archs USING btree (lower(name));


--
-- Name: categories_name_idx; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX categories_name_idx ON categories USING btree (name);


--
-- Name: categories_name_low_idx; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX categories_name_low_idx ON categories USING btree (lower(name));


--
-- Name: contacts_data_idx; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX contacts_data_idx ON contacts USING btree (data);


--
-- Name: contacts_data_low_idx; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX contacts_data_low_idx ON contacts USING btree (lower(data));


--
-- Name: contacts_fk_contact_researcher; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX contacts_fk_contact_researcher ON contacts USING btree (researcherid);


--
-- Name: contacttypes_description_idx; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX contacttypes_description_idx ON contacttypes USING btree (description);


--
-- Name: contacttypes_description_low_idx; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX contacttypes_description_low_idx ON contacttypes USING btree (lower(description));


--
-- Name: countries_fk_country_region; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX countries_fk_country_region ON countries USING btree (regionid);


--
-- Name: countries_isocode_idx; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX countries_isocode_idx ON countries USING btree (isocode);


--
-- Name: countries_isocode_low_idx; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX countries_isocode_low_idx ON countries USING btree (lower(isocode));


--
-- Name: countries_name_idx; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX countries_name_idx ON countries USING btree (name);


--
-- Name: countries_name_low_idx; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX countries_name_low_idx ON countries USING btree (lower(name));


--
-- Name: disciplines_name_idx; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX disciplines_name_idx ON disciplines USING btree (name);


--
-- Name: disciplines_name_low_idx; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX disciplines_name_low_idx ON disciplines USING btree (lower(name));


--
-- Name: fk_app_data; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX fk_app_data ON app_data USING btree (appid);


--
-- Name: fk_app_licenses_app; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX fk_app_licenses_app ON app_licenses USING btree (appid, licenseid);


--
-- Name: fk_app_middlewares_app; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX fk_app_middlewares_app ON app_middlewares USING btree (appid);


--
-- Name: fk_app_middlewares_middleware; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX fk_app_middlewares_middleware ON app_middlewares USING btree (middlewareid);


--
-- Name: fk_app_vos_app; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX fk_app_vos_app ON __app_vos USING btree (appid);


--
-- Name: fk_app_vos_vo; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX fk_app_vos_vo ON __app_vos USING btree (void);


--
-- Name: fk_appbookmarks_researcher; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX fk_appbookmarks_researcher ON appbookmarks USING btree (researcherid);


--
-- Name: fk_contacts_type; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX fk_contacts_type ON contacts USING btree (contacttypeid);


--
-- Name: fk_extauthors_doc; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX fk_extauthors_doc ON extauthors USING btree (docid);


--
-- Name: fk_intauthors_auth; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX fk_intauthors_auth ON intauthors USING btree (authorid);


--
-- Name: fk_intauthors_doc; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX fk_intauthors_doc ON intauthors USING btree (docid);


--
-- Name: fk_messages_receiver; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX fk_messages_receiver ON messages USING btree (receiverid);


--
-- Name: fk_messages_sender; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX fk_messages_sender ON messages USING btree (senderid);


--
-- Name: idx___actor_group_members_actorid; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX idx___actor_group_members_actorid ON __actor_group_members USING btree (actorid);


--
-- Name: idx___actor_group_members_payload; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX idx___actor_group_members_payload ON __actor_group_members USING btree (payload);


--
-- Name: idx__actor_group_members_actorid; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX idx__actor_group_members_actorid ON _actor_group_members USING btree (actorid);


--
-- Name: idx__actor_group_members_payload; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX idx__actor_group_members_payload ON _actor_group_members USING btree (payload);


--
-- Name: idx__actor_group_members_unique; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE UNIQUE INDEX idx__actor_group_members_unique ON _actor_group_members USING btree (groupid, actorid, payload);


--
-- Name: idx_access_tokens_actor_token_type; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX idx_access_tokens_actor_token_type ON access_tokens USING btree (actor, token, type);


--
-- Name: idx_actions_app_actions; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX idx_actions_app_actions ON actions USING btree (id) WHERE (id = ANY (app_actions()));


--
-- Name: idx_actions_app_meta_actions; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX idx_actions_app_meta_actions ON actions USING btree (id) WHERE (id = ANY (app_metadata_actions()));


--
-- Name: idx_actor_group_members_unique; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE UNIQUE INDEX idx_actor_group_members_unique ON _actor_group_members2 USING btree (groupid, actorid, payload);


--
-- Name: idx_actor_groups_guid; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX idx_actor_groups_guid ON actor_groups USING btree (guid);


--
-- Name: idx_app_cnames_enabled_unique; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE UNIQUE INDEX idx_app_cnames_enabled_unique ON app_cnames USING btree (value, enabled) WHERE (enabled IS TRUE);


--
-- Name: idx_applications_cateogryid; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX idx_applications_cateogryid ON applications USING btree (has_34_in_array(categoryid));


--
-- Name: idx_applications_deleted_moderated; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX idx_applications_deleted_moderated ON applications USING btree (deleted, moderated);


--
-- Name: idx_applications_guid; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX idx_applications_guid ON applications USING btree (guid);


--
-- Name: idx_contacts_email; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX idx_contacts_email ON contacts USING btree (contacttypeid) WHERE (contacttypeid = 7);


--
-- Name: idx_permissions_actionid; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX idx_permissions_actionid ON permissions USING btree (actionid);


--
-- Name: idx_permissions_actionid_object_actor; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX idx_permissions_actionid_object_actor ON permissions USING btree (actionid, object, actor);


--
-- Name: idx_permissions_actor; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX idx_permissions_actor ON permissions USING btree (actor);


--
-- Name: idx_permissions_object; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX idx_permissions_object ON permissions USING btree (object);


--
-- Name: idx_permissions_unique; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE UNIQUE INDEX idx_permissions_unique ON permissions USING btree (actionid, actor, object);


--
-- Name: idx_privileges_actor; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX idx_privileges_actor ON privileges USING btree (actor);


--
-- Name: idx_privileges_object; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX idx_privileges_object ON privileges USING btree (object);


--
-- Name: idx_privileges_object_app_actions; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX idx_privileges_object_app_actions ON privileges USING btree (object, actionid) WHERE (actionid = ANY (app_actions()));


--
-- Name: idx_privileges_revoked; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX idx_privileges_revoked ON privileges USING btree (revoked);


--
-- Name: idx_relations_objguid; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX idx_relations_objguid ON relations USING btree (target_guid);


--
-- Name: idx_relations_subguid; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX idx_relations_subguid ON relations USING btree (subject_guid);


--
-- Name: idx_researcher_cnames_enabled_unique; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE UNIQUE INDEX idx_researcher_cnames_enabled_unique ON researcher_cnames USING btree (value, enabled) WHERE (enabled IS TRUE);


--
-- Name: idx_researchers_deleted; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX idx_researchers_deleted ON researchers USING btree (deleted);


--
-- Name: idx_researchers_guid; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX idx_researchers_guid ON researchers USING btree (guid);


--
-- Name: idx_userrequests_guid; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX idx_userrequests_guid ON userrequests USING btree (guid);


--
-- Name: idx_vos_lowercase_name; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX idx_vos_lowercase_name ON vos USING btree (lower(name));


--
-- Name: idx_vos_unique_lowercase_name_src; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE UNIQUE INDEX idx_vos_unique_lowercase_name_src ON vos USING btree (lower(name), sourceid);


--
-- Name: idx_vowide_image_list_images_guid; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX idx_vowide_image_list_images_guid ON vowide_image_list_images USING btree (guid);


--
-- Name: idx_vowide_image_lists_guid; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX idx_vowide_image_lists_guid ON vowide_image_lists USING btree (guid);


--
-- Name: middlewares_name_idx; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX middlewares_name_idx ON middlewares USING btree (name);


--
-- Name: middlewares_name_low_idx; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX middlewares_name_low_idx ON middlewares USING btree (lower(name));


--
-- Name: ngis_fk_ngi_country; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX ngis_fk_ngi_country ON ngis USING btree (countryid);


--
-- Name: organizations_identifier_idx; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX organizations_identifier_idx ON organizations USING btree (identifier);


--
-- Name: oses_name_idx; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX oses_name_idx ON oses USING btree (name);


--
-- Name: oses_name_low_idx; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX oses_name_low_idx ON oses USING btree (lower(name));


--
-- Name: positiontypes_idx; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX positiontypes_idx ON positiontypes USING btree (description);


--
-- Name: positiontypes_low_idx; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX positiontypes_low_idx ON positiontypes USING btree (lower(description));


--
-- Name: privileges_fk_priviledges_action; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX privileges_fk_priviledges_action ON privileges USING btree (actionid);


--
-- Name: proglangs_name_idx; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX proglangs_name_idx ON proglangs USING btree (name);


--
-- Name: proglangs_name_low_idx; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX proglangs_name_low_idx ON proglangs USING btree (lower(name));


--
-- Name: projects_identifier_idx; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX projects_identifier_idx ON projects USING btree (identifier);


--
-- Name: researchers_apps_fk_researcher_app_app; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX researchers_apps_fk_researcher_app_app ON researchers_apps USING btree (appid);


--
-- Name: researchers_apps_fk_researcher_app_researcher; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX researchers_apps_fk_researcher_app_researcher ON researchers_apps USING btree (researcherid);


--
-- Name: researchers_cname_idx; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX researchers_cname_idx ON researchers USING btree (cname);


--
-- Name: researchers_cname_low_idx; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX researchers_cname_low_idx ON researchers USING btree (lower(cname));


--
-- Name: researchers_firstname_idx; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX researchers_firstname_idx ON researchers USING btree (firstname);


--
-- Name: researchers_firstname_low_idx; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX researchers_firstname_low_idx ON researchers USING btree (lower(firstname));


--
-- Name: researchers_fk_researcher_country; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX researchers_fk_researcher_country ON researchers USING btree (countryid);


--
-- Name: researchers_fk_researcher_postype; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX researchers_fk_researcher_postype ON researchers USING btree (positiontypeid);


--
-- Name: researchers_institution_gist_idx; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX researchers_institution_gist_idx ON researchers USING gist (lower(institution) gist_trgm_ops);


--
-- Name: researchers_institution_idx; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX researchers_institution_idx ON researchers USING btree (institution);


--
-- Name: researchers_institution_low_idx; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX researchers_institution_low_idx ON researchers USING btree (lower(institution));


--
-- Name: researchers_lastname_idx; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX researchers_lastname_idx ON researchers USING btree (lastname);


--
-- Name: researchers_lastname_low_idx; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX researchers_lastname_low_idx ON researchers USING btree (lower(lastname));


--
-- Name: researchers_name_idx; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX researchers_name_idx ON researchers USING btree (name);


--
-- Name: researchers_name_low_idx; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX researchers_name_low_idx ON researchers USING btree (lower(name));


--
-- Name: ssp_kvstore_expire; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX ssp_kvstore_expire ON ssp_kvstore USING btree (_expire);


--
-- Name: ssp_saml_logoutstore_expire; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX ssp_saml_logoutstore_expire ON ssp_saml_logoutstore USING btree (_expire);


--
-- Name: ssp_saml_logoutstore_nameid; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX ssp_saml_logoutstore_nameid ON ssp_saml_logoutstore USING btree (_authsource, _nameid);


--
-- Name: statuses_name_idx; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX statuses_name_idx ON statuses USING btree (name);


--
-- Name: statuses_name_low_idx; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX statuses_name_low_idx ON statuses USING btree (lower((name)::text));


--
-- Name: user_accounts_accountid_idx; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX user_accounts_accountid_idx ON user_accounts USING btree (accountid);


--
-- Name: user_accounts_accountid_idx1; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX user_accounts_accountid_idx1 ON user_accounts USING btree (accountid);


--
-- Name: vos_lower_deleted_idx; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX vos_lower_deleted_idx ON vos USING btree (lower(name), deleted);


--
-- Name: vos_lower_idx; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX vos_lower_idx ON vos USING btree (lower(name));


--
-- Name: vos_lower_idx1; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX vos_lower_idx1 ON vos USING btree (lower(name));


--
-- Name: vos_name_deleted_idx; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX vos_name_deleted_idx ON vos USING btree (name, deleted);


--
-- Name: vos_name_idx; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX vos_name_idx ON vos USING btree (name);


--
-- Name: vos_name_idx1; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX vos_name_idx1 ON vos USING btree (name);


--
-- Name: vos_name_idx2; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX vos_name_idx2 ON vos USING btree (name);


--
-- Name: vos_name_low_idx; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE INDEX vos_name_low_idx ON vos USING btree (lower(name));


--
-- Name: vowide_image_lists_unique_void_state; Type: INDEX; Schema: public; Owner: appdb; Tablespace: 
--

CREATE UNIQUE INDEX vowide_image_lists_unique_void_state ON vowide_image_lists USING btree (void, state) WHERE (state <> 'obsolete'::e_vowide_image_state);


--
-- Name: _RETURN; Type: RULE; Schema: public; Owner: appdb
--

CREATE RULE "_RETURN" AS
    ON SELECT TO vapp_to_xml DO INSTEAD  WITH hypervisors AS (
         WITH x AS (
                 SELECT vmiflavours_2.id,
                    unnest(vmiflavours_2.hypervisors) AS y
                   FROM vmiflavours vmiflavours_2
                )
         SELECT vmiflavours_1.id AS vmiflavourid,
            xmlagg(XMLELEMENT(NAME "virtualization:hypervisor", XMLATTRIBUTES(( SELECT hypervisors_1.id
                   FROM public.hypervisors hypervisors_1
                  WHERE ((hypervisors_1.name)::text = (x.y)::text)) AS id), x.y)) AS hypervisor
           FROM (vmiflavours vmiflavours_1
             JOIN x ON ((x.id = vmiflavours_1.id)))
          GROUP BY vmiflavours_1.id
        )
 SELECT applications.id AS appid,
    vapplications.id AS vappid,
    XMLELEMENT(NAME "virtualization:appliance", XMLATTRIBUTES(vapp_versions.published AS published, vapp_versions.version AS version, vapplications.id AS vappid, applications.id AS appid, vapp_versions.id AS vaversionid, timezone('UTC'::text, (vapp_versions.createdon)::timestamp with time zone) AS createdon, vapp_versions.expireson AS expireson, vapp_versions.status AS status, vapp_versions.enabled AS enabled, vapp_versions.archived AS archived,
        CASE
            WHEN (NOT (vapp_versions.archivedon IS NULL)) THEN timezone('UTC'::text, (vapp_versions.archivedon)::timestamp with time zone)
            ELSE NULL::timestamp without time zone
        END AS archivedon, vapplications.guid AS vappidentifier, vapplications.imglst_private AS "imageListsPrivate"), '
', XMLELEMENT(NAME "virtualization:identifier", vapp_versions.guid), '
', XMLELEMENT(NAME "virtualization:name", vapplications.name), '
', XMLELEMENT(NAME "virtualization:notes", vapp_versions.notes), '
', xmlagg(XMLELEMENT(NAME "virtualization:image", XMLATTRIBUTES(vmiinstances.version AS version, vmiinstances.vmiflavourid AS flavourid, vmis.id AS vmiid, vmiinstances.id AS vmiinstanceid, vmiinstances.enabled AS enabled), '
', XMLELEMENT(NAME "virtualization:vmititle", vmis.name), '
', XMLELEMENT(NAME "virtualization:description", vmis.description), '
', XMLELEMENT(NAME "virtualization:notes", vmis.notes), '
', XMLELEMENT(NAME "virtualization:group", vmis.groupname), '
', XMLELEMENT(NAME "virtualization:os", XMLATTRIBUTES(oses.id AS id), oses.name), '
', XMLELEMENT(NAME "virtualization:osversion", vmiflavours.osversion), '
', XMLELEMENT(NAME "virtualization:arch", XMLATTRIBUTES(archs.id AS id), archs.name), '
', XMLELEMENT(NAME "virtualization:releasetitle", vmiinstances.title), '
', XMLELEMENT(NAME "virtualization:releasenotes", vmiinstances.notes), '
', XMLELEMENT(NAME "virtualization:releasedescription", vmiinstances.description), '
', XMLELEMENT(NAME "virtualization:identifier", vmiinstances.guid), '
', XMLELEMENT(NAME "virtualization:integrity", XMLATTRIBUTES(vmiinstances.integrity_status AS status), vmiinstances.integrity_message), '
', hypervisors.hypervisor, '
',
        CASE
            WHEN (vmiflavours.format IS NULL) THEN NULL::xml
            ELSE XMLELEMENT(NAME "virtualization:format", XMLATTRIBUTES(vmiformats.id AS id), vmiflavours.format)
        END, '
', XMLELEMENT(NAME "virtualization:size", vmiinstances.size), '
', XMLELEMENT(NAME "virtualization:url", vmiinstances.uri), '
', XMLELEMENT(NAME "virtualization:checksum", XMLATTRIBUTES(vmiinstances.checksumfunc AS hash), vmiinstances.checksum), '
', XMLELEMENT(NAME "virtualization:cores", XMLATTRIBUTES(vmiinstances.coreminimum AS minimum, vmiinstances.corerecommend AS recommended)), '
', XMLELEMENT(NAME "virtualization:ram", XMLATTRIBUTES(vmiinstances.ramminimum AS minimum, vmiinstances.ramrecommend AS recommended)), '
', researcher_to_xml(vmiinstances.addedby, 'addedby'::text), '
', XMLELEMENT(NAME "virtualization:addedon", timezone('UTC'::text, (vmiinstances.addedon)::timestamp with time zone)), '
', researcher_to_xml(vmiinstances.lastupdatedby, 'lastupdatedby'::text), '
', XMLELEMENT(NAME "virtualization:lastupdatedon", vmiinstances.lastupdatedon), '
', XMLELEMENT(NAME "virtualization:autointegrity", vmiinstances.autointegrity), '
', XMLELEMENT(NAME "virtualization:ovf", XMLATTRIBUTES(vmiinstances.ovfurl AS url)), '
', vmiinst_cntxscripts_to_xml(vmiinstances.id), '
'))) AS xml
   FROM (((((((((((vmiinstances
     JOIN vmiflavours ON ((vmiflavours.id = vmiinstances.vmiflavourid)))
     JOIN vmis ON ((vmis.id = vmiflavours.vmiid)))
     JOIN vapplications ON ((vapplications.id = vmis.vappid)))
     JOIN applications ON ((applications.id = vapplications.appid)))
     JOIN vapp_versions ON ((vapp_versions.vappid = vapplications.id)))
     JOIN vapplists ON (((vapplists.vappversionid = vapp_versions.id) AND (vapplists.vmiinstanceid = vmiinstances.id))))
     LEFT JOIN archs ON ((archs.id = vmiflavours.archid)))
     LEFT JOIN oses ON ((oses.id = vmiflavours.osid)))
     LEFT JOIN researchers ON ((researchers.id = vmiinstances.addedby)))
     LEFT JOIN hypervisors ON ((hypervisors.vmiflavourid = vmiflavours.id)))
     LEFT JOIN vmiformats ON (((vmiformats.name)::text = vmiflavours.format)))
  GROUP BY applications.id, vapplications.id, vapp_versions.published, vapp_versions.version, applications.guid, vapplications.name, vapp_versions.id, vapp_versions.createdon, vapp_versions.expireson, vapp_versions.status, vapp_versions.enabled, vapp_versions.archived
  ORDER BY vapp_versions.published, vapp_versions.archived, vapp_versions.archivedon DESC;


--
-- Name: _RETURN; Type: RULE; Schema: public; Owner: appdb
--

CREATE RULE "_RETURN" AS
    ON SELECT TO vapps_of_swapps_to_xml DO INSTEAD  SELECT vapps.id,
    XMLELEMENT(NAME "application:application", XMLATTRIBUTES(vapps.id AS id, vapps.cname AS cname, vapps.guid AS guid, vapps.vappversionid AS versionid,
        CASE
            WHEN (vapps.va_version_expireson < now()) THEN true
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
            vaviews.vappversionid,
            vaviews.va_version_expireson,
            vaviews.va_version_archived,
            array_agg(DISTINCT contexts.appid) AS swappids
           FROM (((((contexts
             JOIN context_script_assocs csa ON ((csa.contextid = contexts.id)))
             JOIN contextscripts cs ON ((cs.id = csa.scriptid)))
             JOIN vmiinstance_contextscripts vcs ON ((vcs.contextscriptid = cs.id)))
             JOIN vaviews ON ((vaviews.vmiinstanceid = vcs.vmiinstanceid)))
             JOIN applications vapps_1 ON ((vapps_1.id = vaviews.appid)))
          WHERE (vaviews.va_version_published = true)
          GROUP BY vapps_1.id, vaviews.vappversionid, vaviews.va_version_expireson, vaviews.va_version_archived) vapps;


--
-- Name: r_delete_actor_group_members; Type: RULE; Schema: public; Owner: appdb
--

CREATE RULE r_delete_actor_group_members AS
    ON DELETE TO actor_group_members DO INSTEAD  DELETE FROM __actor_group_members
  WHERE ((__actor_group_members.id = old.id) AND (NOT (old.id IS NULL)))
  RETURNING __actor_group_members.id,
    __actor_group_members.groupid,
    __actor_group_members.actorid,
    __actor_group_members.payload;


--
-- Name: r_delete_app_tags; Type: RULE; Schema: public; Owner: appdb
--

CREATE RULE r_delete_app_tags AS
    ON DELETE TO app_tags DO INSTEAD  DELETE FROM __app_tags
  WHERE ((__app_tags.id = old.id) AND (NOT (old.id IS NULL)))
  RETURNING __app_tags.id,
    __app_tags.appid,
    __app_tags.researcherid,
    __app_tags.tag;


--
-- Name: r_delete_app_vos; Type: RULE; Schema: public; Owner: appdb
--

CREATE RULE r_delete_app_vos AS
    ON DELETE TO app_vos DO INSTEAD  DELETE FROM __app_vos
  WHERE ((__app_vos.appid = old.appid) AND (__app_vos.void = old.void))
  RETURNING __app_vos.appid,
    __app_vos.void;


--
-- Name: r_delete_va_provider_images; Type: RULE; Schema: public; Owner: appdb
--

CREATE RULE r_delete_va_provider_images AS
    ON DELETE TO va_provider_images DO INSTEAD  DELETE FROM __va_provider_images
  WHERE ((__va_provider_images.id = old.id) AND (NOT (old.id IS NULL)))
  RETURNING __va_provider_images.id,
    __va_provider_images.va_provider_id,
    __va_provider_images.vmiinstanceid,
    __va_provider_images.content_type,
    __va_provider_images.va_provider_image_id,
    __va_provider_images.mp_uri,
    __va_provider_images.vowide_vmiinstanceid,
    get_good_vmiinstanceid(__va_provider_images.vmiinstanceid) AS good_vmiinstanceid;


--
-- Name: r_insert_actor_group_members; Type: RULE; Schema: public; Owner: appdb
--

CREATE RULE r_insert_actor_group_members AS
    ON INSERT TO actor_group_members DO INSTEAD  INSERT INTO __actor_group_members (groupid, actorid, payload)
  VALUES (new.groupid, new.actorid, new.payload)
  RETURNING __actor_group_members.id,
    __actor_group_members.groupid,
    __actor_group_members.actorid,
    __actor_group_members.payload;


--
-- Name: r_insert_app_tags; Type: RULE; Schema: public; Owner: appdb
--

CREATE RULE r_insert_app_tags AS
    ON INSERT TO app_tags DO INSTEAD  INSERT INTO __app_tags (appid, researcherid, tag)
  VALUES (new.appid, new.researcherid, new.tag)
  RETURNING __app_tags.id,
    __app_tags.appid,
    __app_tags.researcherid,
    __app_tags.tag;


--
-- Name: r_insert_app_vos; Type: RULE; Schema: public; Owner: appdb
--

CREATE RULE r_insert_app_vos AS
    ON INSERT TO app_vos DO INSTEAD  INSERT INTO __app_vos (appid, void)
  VALUES (new.appid, new.void)
  RETURNING __app_vos.appid,
    __app_vos.void;


--
-- Name: r_insert_va_provider_images; Type: RULE; Schema: public; Owner: appdb
--

CREATE RULE r_insert_va_provider_images AS
    ON INSERT TO va_provider_images DO INSTEAD  INSERT INTO __va_provider_images (va_provider_id, vmiinstanceid, content_type, va_provider_image_id, mp_uri, vowide_vmiinstanceid)
  VALUES (new.va_provider_id, new.vmiinstanceid, new.content_type, new.va_provider_image_id, new.mp_uri, new.vowide_vmiinstanceid)
  RETURNING __va_provider_images.id,
    __va_provider_images.va_provider_id,
    __va_provider_images.vmiinstanceid,
    __va_provider_images.content_type,
    __va_provider_images.va_provider_image_id,
    __va_provider_images.mp_uri,
    __va_provider_images.vowide_vmiinstanceid,
    get_good_vmiinstanceid(__va_provider_images.vmiinstanceid) AS good_vmiinstanceid;


--
-- Name: r_update_actor_group_members; Type: RULE; Schema: public; Owner: appdb
--

CREATE RULE r_update_actor_group_members AS
    ON UPDATE TO actor_group_members DO INSTEAD  UPDATE __actor_group_members SET groupid = new.groupid, actorid = new.actorid, payload = new.payload
  WHERE ((__actor_group_members.id = old.id) AND (NOT (old.id IS NULL)))
  RETURNING __actor_group_members.id,
    __actor_group_members.groupid,
    __actor_group_members.actorid,
    __actor_group_members.payload;


--
-- Name: r_update_app_tags; Type: RULE; Schema: public; Owner: appdb
--

CREATE RULE r_update_app_tags AS
    ON UPDATE TO app_tags DO INSTEAD  UPDATE __app_tags SET appid = new.appid, researcherid = new.researcherid, tag = new.tag
  WHERE ((__app_tags.id = old.id) AND (NOT (old.id IS NULL)))
  RETURNING __app_tags.id,
    __app_tags.appid,
    __app_tags.researcherid,
    __app_tags.tag;


--
-- Name: r_update_app_vos; Type: RULE; Schema: public; Owner: appdb
--

CREATE RULE r_update_app_vos AS
    ON UPDATE TO app_vos DO INSTEAD  UPDATE __app_vos SET appid = new.appid, void = new.void
  WHERE ((__app_vos.appid = old.appid) AND (__app_vos.void = old.void))
  RETURNING __app_vos.appid,
    __app_vos.void;


SET search_path = sci_class, pg_catalog;

--
-- Name: _RETURN; Type: RULE; Schema: sci_class; Owner: appdb
--

CREATE RULE "_RETURN" AS
    ON SELECT TO workingprops DO INSTEAD  WITH t AS (
         SELECT cids.id AS cid,
            cpropids.id AS cpropid,
            cpropids.name AS cpropname,
            getprop(( SELECT cverids.version
                   FROM cverids
                  WHERE (cverids.state = 'stable'::e_version_state)
                  GROUP BY cverids.version
                 HAVING (cverids.createdon = max(cverids.createdon))), cids.id, cpropids.id) AS cpropvalue
           FROM cpropids,
            cids
        )
 SELECT t.cid,
    t.cpropid,
    t.cpropname,
    t.cpropvalue,
    disciplines.id AS disciplineid
   FROM (t
     LEFT JOIN public.disciplines ON ((disciplines.name = ( SELECT t1.cpropvalue
           FROM t t1
          WHERE ((t1.cpropid = 1) AND (t1.cid = t.cid))))));


SET search_path = cache, pg_catalog;

--
-- Name: rtr_filtercache_after; Type: TRIGGER; Schema: cache; Owner: appdb
--

CREATE TRIGGER rtr_filtercache_after AFTER DELETE ON filtercache FOR EACH ROW EXECUTE PROCEDURE trfn_filtercache();


SET search_path = egiops, pg_catalog;

--
-- Name: tr_egiops_vo_contacts_99_refresh_permissions; Type: TRIGGER; Schema: egiops; Owner: appdb
--

CREATE TRIGGER tr_egiops_vo_contacts_99_refresh_permissions AFTER INSERT OR DELETE OR UPDATE ON vo_contacts FOR EACH STATEMENT EXECUTE PROCEDURE public.trfn_refresh_permissions();


--
-- Name: tr_egiops_vo_members_99_refresh_permissions; Type: TRIGGER; Schema: egiops; Owner: appdb
--

CREATE TRIGGER tr_egiops_vo_members_99_refresh_permissions AFTER INSERT OR DELETE OR UPDATE ON vo_members FOR EACH STATEMENT EXECUTE PROCEDURE public.trfn_refresh_permissions();


SET search_path = gocdb, pg_catalog;

--
-- Name: tr_gocdb_sites_99_create_uuid; Type: TRIGGER; Schema: gocdb; Owner: appdb
--

CREATE TRIGGER tr_gocdb_sites_99_create_uuid BEFORE INSERT ON sites FOR EACH ROW EXECUTE PROCEDURE trfn_gocdb_sites_create_uuid();


--
-- Name: tr_gocdb_sites_99_updatefields; Type: TRIGGER; Schema: gocdb; Owner: appdb
--

CREATE TRIGGER tr_gocdb_sites_99_updatefields BEFORE UPDATE ON sites FOR EACH ROW EXECUTE PROCEDURE trfn_gocdb_sites_update_fields();


--
-- Name: tr_gocdb_va_providers_99_refresh_permissions; Type: TRIGGER; Schema: gocdb; Owner: appdb
--

CREATE TRIGGER tr_gocdb_va_providers_99_refresh_permissions AFTER INSERT OR DELETE ON va_providers FOR EACH STATEMENT EXECUTE PROCEDURE public.trfn_refresh_permissions();


SET search_path = perun, pg_catalog;

--
-- Name: tr_perun_vo_contacts_99_refresh_permissions; Type: TRIGGER; Schema: perun; Owner: appdb
--

CREATE TRIGGER tr_perun_vo_contacts_99_refresh_permissions AFTER INSERT OR DELETE OR UPDATE ON vo_contacts FOR EACH STATEMENT EXECUTE PROCEDURE public.trfn_refresh_permissions();


--
-- Name: tr_perun_vo_members_99_refresh_permissions; Type: TRIGGER; Schema: perun; Owner: appdb
--

CREATE TRIGGER tr_perun_vo_members_99_refresh_permissions AFTER INSERT OR DELETE OR UPDATE ON vo_members FOR EACH STATEMENT EXECUTE PROCEDURE public.trfn_refresh_permissions();


SET search_path = public, pg_catalog;

--
-- Name: rtr_10_dataset_location_organizations; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr_10_dataset_location_organizations AFTER INSERT OR DELETE OR UPDATE ON dataset_location_organizations FOR EACH ROW EXECUTE PROCEDURE trfn_dataset_location_organizations();


--
-- Name: rtr_10_dataset_location_organizations_nodupes; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr_10_dataset_location_organizations_nodupes BEFORE INSERT OR UPDATE ON dataset_location_organizations FOR EACH ROW EXECUTE PROCEDURE trfn_dataset_location_organizations_no_dupes();


--
-- Name: rtr_10_dataset_location_sites; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr_10_dataset_location_sites AFTER INSERT OR DELETE OR UPDATE ON dataset_location_sites FOR EACH ROW EXECUTE PROCEDURE trfn_dataset_location_sites();


--
-- Name: rtr_10_dataset_location_sites_nodupes; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr_10_dataset_location_sites_nodupes BEFORE INSERT OR UPDATE ON dataset_location_sites FOR EACH ROW EXECUTE PROCEDURE trfn_dataset_location_sites_no_dupes();


--
-- Name: rtr_10_licenses_before; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr_10_licenses_before BEFORE INSERT OR DELETE OR UPDATE ON licenses FOR EACH ROW EXECUTE PROCEDURE trfn_licenses();


--
-- Name: rtr_10_sync_derived_disciplines_after; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr_10_sync_derived_disciplines_after AFTER INSERT OR UPDATE ON datasets FOR EACH ROW EXECUTE PROCEDURE trfn_sync_derived_dataset_disciplines();


--
-- Name: rtr_10_sync_derived_disciplines_after; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr_10_sync_derived_disciplines_after AFTER INSERT OR DELETE OR UPDATE ON dataset_disciplines FOR EACH ROW EXECUTE PROCEDURE trfn_sync_derived_dataset_disciplines();


--
-- Name: rtr_20_dataset_disciplines_before; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr_20_dataset_disciplines_before BEFORE INSERT OR DELETE OR UPDATE ON dataset_disciplines FOR EACH ROW EXECUTE PROCEDURE trfn_dataset_disciplines();


--
-- Name: rtr__app_archs_cache_delta; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr__app_archs_cache_delta AFTER INSERT OR DELETE OR UPDATE ON app_archs FOR EACH ROW EXECUTE PROCEDURE trfn_app_archs_cache_delta();


--
-- Name: rtr__app_licenses_cache_delta; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr__app_licenses_cache_delta AFTER INSERT OR DELETE OR UPDATE ON app_licenses FOR EACH ROW EXECUTE PROCEDURE trfn_app_licenses_cache_delta();


--
-- Name: rtr__app_middlewares_cache_delta; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr__app_middlewares_cache_delta AFTER INSERT OR DELETE OR UPDATE ON app_middlewares FOR EACH ROW EXECUTE PROCEDURE trfn_app_middlewares_cache_delta();


--
-- Name: rtr__app_oses_cache_delta; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr__app_oses_cache_delta AFTER INSERT OR DELETE OR UPDATE ON app_oses FOR EACH ROW EXECUTE PROCEDURE trfn_app_oses_cache_delta();


--
-- Name: rtr__app_vos_cache_delta; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr__app_vos_cache_delta AFTER INSERT OR DELETE OR UPDATE ON __app_vos FOR EACH ROW EXECUTE PROCEDURE trfn_app_vos_cache_delta();


--
-- Name: rtr__appbookmarks_cache_delta; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr__appbookmarks_cache_delta AFTER INSERT OR DELETE OR UPDATE ON appbookmarks FOR EACH ROW EXECUTE PROCEDURE trfn_appbookmarks_cache_delta();


--
-- Name: rtr__applications_cache_delta; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr__applications_cache_delta AFTER INSERT OR DELETE OR UPDATE ON applications FOR EACH ROW EXECUTE PROCEDURE trfn_applications_cache_delta();


--
-- Name: rtr__appmanualcountries_cache_delta; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr__appmanualcountries_cache_delta AFTER INSERT OR DELETE OR UPDATE ON appmanualcountries FOR EACH ROW EXECUTE PROCEDURE trfn_appmanualcountries_cache_delta();


--
-- Name: rtr__appproglangs_cache_delta; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr__appproglangs_cache_delta AFTER INSERT OR DELETE OR UPDATE ON appproglangs FOR EACH ROW EXECUTE PROCEDURE trfn_appproglangs_cache_delta();


--
-- Name: rtr__archs_cache_delta; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr__archs_cache_delta AFTER INSERT OR DELETE OR UPDATE ON archs FOR EACH ROW EXECUTE PROCEDURE trfn_archs_cache_delta();


--
-- Name: rtr__categories_cache_delta; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr__categories_cache_delta AFTER INSERT OR DELETE OR UPDATE ON categories FOR EACH ROW EXECUTE PROCEDURE trfn_categories_cache_delta();


--
-- Name: rtr__contacts_cache_delta; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr__contacts_cache_delta AFTER INSERT OR DELETE OR UPDATE ON contacts FOR EACH ROW EXECUTE PROCEDURE trfn_contacts_cache_delta();


--
-- Name: rtr__countries_cache_delta; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr__countries_cache_delta AFTER INSERT OR DELETE OR UPDATE ON countries FOR EACH ROW EXECUTE PROCEDURE trfn_countries_cache_delta();


--
-- Name: rtr__disciplines_cache_delta; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr__disciplines_cache_delta AFTER INSERT OR DELETE OR UPDATE ON disciplines FOR EACH ROW EXECUTE PROCEDURE trfn_disciplines_cache_delta();


--
-- Name: rtr__licenses_cache_delta; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr__licenses_cache_delta AFTER INSERT OR DELETE OR UPDATE ON licenses FOR EACH ROW EXECUTE PROCEDURE trfn_licenses_cache_delta();


--
-- Name: rtr__middlewares_cache_delta; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr__middlewares_cache_delta AFTER INSERT OR DELETE OR UPDATE ON middlewares FOR EACH ROW EXECUTE PROCEDURE trfn_middlewares_cache_delta();


--
-- Name: rtr__oses_cache_delta; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr__oses_cache_delta AFTER INSERT OR DELETE OR UPDATE ON oses FOR EACH ROW EXECUTE PROCEDURE trfn_oses_cache_delta();


--
-- Name: rtr__positiontypes_cache_delta; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr__positiontypes_cache_delta AFTER INSERT OR DELETE OR UPDATE ON positiontypes FOR EACH ROW EXECUTE PROCEDURE trfn_positiontypes_cache_delta();


--
-- Name: rtr__proglangs_cache_delta; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr__proglangs_cache_delta AFTER INSERT OR DELETE OR UPDATE ON proglangs FOR EACH ROW EXECUTE PROCEDURE trfn_proglangs_cache_delta();


--
-- Name: rtr__researchers_apps_cache_delta; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr__researchers_apps_cache_delta AFTER INSERT OR DELETE OR UPDATE ON researchers_apps FOR EACH ROW EXECUTE PROCEDURE trfn_researchers_apps_cache_delta();


--
-- Name: rtr__researchers_cache_delta; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr__researchers_cache_delta AFTER INSERT OR DELETE OR UPDATE ON researchers FOR EACH ROW EXECUTE PROCEDURE trfn_researchers_cache_delta();


--
-- Name: rtr__statuses_cache_delta; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr__statuses_cache_delta AFTER INSERT OR DELETE OR UPDATE ON statuses FOR EACH ROW EXECUTE PROCEDURE trfn_statuses_cache_delta();


--
-- Name: rtr__vo_middlewares_cache_delta; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr__vo_middlewares_cache_delta AFTER INSERT OR DELETE OR UPDATE ON vo_middlewares FOR EACH ROW EXECUTE PROCEDURE trfn_vo_middlewares_cache_delta();


--
-- Name: rtr__vos_cache_delta; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr__vos_cache_delta AFTER INSERT OR DELETE OR UPDATE ON vos FOR EACH ROW EXECUTE PROCEDURE trfn_vos_cache_delta();


--
-- Name: rtr_app_api_log_after; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr_app_api_log_after AFTER INSERT OR DELETE OR UPDATE ON app_api_log FOR EACH ROW EXECUTE PROCEDURE trfn_app_api_log();


--
-- Name: rtr_app_api_log_before; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr_app_api_log_before BEFORE INSERT OR DELETE OR UPDATE ON app_api_log FOR EACH ROW EXECUTE PROCEDURE trfn_app_api_log();


--
-- Name: rtr_app_cnames_after; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr_app_cnames_after AFTER INSERT OR DELETE OR UPDATE ON app_cnames FOR EACH ROW EXECUTE PROCEDURE trfn_app_cnames();


--
-- Name: rtr_app_licenses_after; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr_app_licenses_after AFTER INSERT OR DELETE OR UPDATE ON app_licenses FOR EACH ROW EXECUTE PROCEDURE trfn_10_app_licenses();


--
-- Name: rtr_app_mws_before; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr_app_mws_before BEFORE INSERT OR DELETE OR UPDATE ON app_middlewares FOR EACH ROW EXECUTE PROCEDURE trfn_app_mws();


--
-- Name: rtr_app_releases_after; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr_app_releases_after AFTER INSERT OR UPDATE ON app_releases FOR EACH ROW EXECUTE PROCEDURE trfn_app_releases();


--
-- Name: rtr_app_releases_before; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr_app_releases_before BEFORE INSERT OR UPDATE ON app_releases FOR EACH ROW EXECUTE PROCEDURE trfn_app_releases();


--
-- Name: rtr_app_urls_after; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr_app_urls_after AFTER INSERT OR DELETE OR UPDATE ON app_urls FOR EACH ROW EXECUTE PROCEDURE trfn_app_urls();


--
-- Name: rtr_app_urls_before; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr_app_urls_before BEFORE INSERT OR DELETE OR UPDATE ON app_urls FOR EACH ROW EXECUTE PROCEDURE trfn_app_urls();


--
-- Name: rtr_app_vos_before; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr_app_vos_before BEFORE INSERT OR DELETE OR UPDATE ON __app_vos FOR EACH ROW EXECUTE PROCEDURE trfn_app_vos();


--
-- Name: rtr_appcategories_after; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr_appcategories_after AFTER INSERT OR DELETE OR UPDATE ON appcategories FOR EACH ROW EXECUTE PROCEDURE trfn_appcategories();


--
-- Name: rtr_appcategories_before; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr_appcategories_before BEFORE INSERT OR DELETE OR UPDATE ON appcategories FOR EACH ROW EXECUTE PROCEDURE trfn_appcategories();


--
-- Name: rtr_appcategories_primary_entry; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr_appcategories_primary_entry AFTER INSERT OR DELETE OR UPDATE ON appcategories FOR EACH ROW EXECUTE PROCEDURE trfn_appcategories_primary_entry();


--
-- Name: rtr_appdisciplines_after; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr_appdisciplines_after AFTER INSERT OR DELETE OR UPDATE ON appdisciplines FOR EACH ROW EXECUTE PROCEDURE trfn_appdisciplines();


--
-- Name: rtr_appdisciplines_before; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr_appdisciplines_before BEFORE INSERT OR DELETE OR UPDATE ON appdisciplines FOR EACH ROW EXECUTE PROCEDURE trfn_appdisciplines();


--
-- Name: rtr_appdocuments_after; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr_appdocuments_after AFTER INSERT OR DELETE OR UPDATE ON appdocuments FOR EACH ROW EXECUTE PROCEDURE trfn_appdocuments();


--
-- Name: rtr_appdocuments_before; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr_appdocuments_before BEFORE INSERT OR DELETE OR UPDATE ON appdocuments FOR EACH ROW EXECUTE PROCEDURE trfn_appdocuments();


--
-- Name: rtr_applications_after; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr_applications_after AFTER INSERT OR DELETE OR UPDATE ON applications FOR EACH ROW EXECUTE PROCEDURE trfn_applications();


--
-- Name: rtr_applications_before; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr_applications_before BEFORE INSERT OR DELETE OR UPDATE ON applications FOR EACH ROW EXECUTE PROCEDURE trfn_applications();


--
-- Name: rtr_appratings_after; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr_appratings_after AFTER INSERT OR DELETE OR UPDATE ON appratings FOR EACH ROW EXECUTE PROCEDURE trfn_appratings();


--
-- Name: rtr_appratings_before; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr_appratings_before BEFORE INSERT OR DELETE OR UPDATE ON appratings FOR EACH ROW EXECUTE PROCEDURE trfn_appratings();


--
-- Name: rtr_auto_create_app_before; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr_auto_create_app_before BEFORE INSERT ON vapplications FOR EACH ROW EXECUTE PROCEDURE trfn_auto_create_app();


--
-- Name: rtr_config_10_before; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr_config_10_before BEFORE INSERT OR DELETE OR UPDATE ON config FOR EACH ROW EXECUTE PROCEDURE trfn_config_before();


--
-- Name: rtr_contacts_primary_entry; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr_contacts_primary_entry AFTER INSERT OR DELETE OR UPDATE ON contacts FOR EACH ROW EXECUTE PROCEDURE trfn_contacts_primary_entry();


--
-- Name: rtr_context_script_assocs_90_appxmlcache_after; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr_context_script_assocs_90_appxmlcache_after AFTER INSERT OR DELETE OR UPDATE ON context_script_assocs FOR EACH ROW EXECUTE PROCEDURE trfn_context_script_assocs_appxmlcache();


--
-- Name: rtr_dataset_disciplines_after; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr_dataset_disciplines_after AFTER INSERT OR DELETE OR UPDATE ON dataset_disciplines FOR EACH ROW EXECUTE PROCEDURE trfn_dataset_disciplines();


--
-- Name: rtr_extauthors_before; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr_extauthors_before BEFORE INSERT OR DELETE OR UPDATE ON extauthors FOR EACH ROW EXECUTE PROCEDURE trfn_extauthors();


--
-- Name: rtr_faqs_before; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr_faqs_before BEFORE INSERT OR DELETE OR UPDATE ON faqs FOR EACH ROW EXECUTE PROCEDURE trfn_faqs();


--
-- Name: rtr_intauthors_before; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr_intauthors_before BEFORE INSERT OR DELETE OR UPDATE ON intauthors FOR EACH ROW EXECUTE PROCEDURE trfn_intauthors();


--
-- Name: rtr_linksdb; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr_linksdb BEFORE INSERT OR UPDATE ON linksdb FOR EACH ROW EXECUTE PROCEDURE trfn_linksdb();


--
-- Name: rtr_mail_subscriptions_after; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr_mail_subscriptions_after AFTER INSERT ON mail_subscriptions FOR EACH ROW EXECUTE PROCEDURE trfn_mail_subscriptions();


--
-- Name: rtr_middlewares_before; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr_middlewares_before BEFORE INSERT OR DELETE OR UPDATE ON middlewares FOR EACH ROW EXECUTE PROCEDURE trfn_middlewares();


--
-- Name: rtr_organizations_before; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr_organizations_before BEFORE DELETE ON organizations FOR EACH ROW EXECUTE PROCEDURE trfn_organizations();


--
-- Name: rtr_ppl_api_log_after; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr_ppl_api_log_after AFTER INSERT OR DELETE OR UPDATE ON ppl_api_log FOR EACH ROW EXECUTE PROCEDURE trfn_ppl_api_log();


--
-- Name: rtr_ppl_api_log_before; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr_ppl_api_log_before BEFORE INSERT OR DELETE OR UPDATE ON ppl_api_log FOR EACH ROW EXECUTE PROCEDURE trfn_ppl_api_log();


--
-- Name: rtr_projects_before; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr_projects_before BEFORE DELETE ON projects FOR EACH ROW EXECUTE PROCEDURE trfn_projects();


--
-- Name: rtr_researcher_cnames_after; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr_researcher_cnames_after AFTER INSERT OR DELETE OR UPDATE ON researcher_cnames FOR EACH ROW EXECUTE PROCEDURE trfn_researcher_cnames();


--
-- Name: rtr_researchers_after; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr_researchers_after AFTER INSERT OR DELETE OR UPDATE ON researchers FOR EACH ROW EXECUTE PROCEDURE trfn_researchers();


--
-- Name: rtr_researchers_apps_after; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr_researchers_apps_after AFTER INSERT ON researchers_apps FOR EACH ROW EXECUTE PROCEDURE trfn_researchers_apps();


--
-- Name: rtr_researchers_apps_before; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr_researchers_apps_before BEFORE DELETE ON researchers_apps FOR EACH ROW EXECUTE PROCEDURE trfn_researchers_apps();


--
-- Name: rtr_researchers_before; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr_researchers_before BEFORE INSERT OR DELETE OR UPDATE ON researchers FOR EACH ROW EXECUTE PROCEDURE trfn_researchers();


--
-- Name: rtr_userrequests; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr_userrequests BEFORE UPDATE ON userrequests FOR EACH ROW EXECUTE PROCEDURE trfn_userrequests();


--
-- Name: rtr_vapp_versions_10_after; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr_vapp_versions_10_after AFTER INSERT OR UPDATE ON vapp_versions FOR EACH ROW EXECUTE PROCEDURE trfn_vapp_versions();


--
-- Name: rtr_vapp_versions_80_news_after; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr_vapp_versions_80_news_after AFTER INSERT OR DELETE OR UPDATE ON vapp_versions FOR EACH ROW EXECUTE PROCEDURE trfn_vapp_versions_news();


--
-- Name: rtr_vapp_versions_90_appxmlcache_after; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr_vapp_versions_90_appxmlcache_after AFTER INSERT OR DELETE OR UPDATE ON vapp_versions FOR EACH ROW EXECUTE PROCEDURE trfn_vapp_versions_appxmlcache();


--
-- Name: rtr_vapplications_after; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr_vapplications_after AFTER INSERT OR UPDATE ON vapplications FOR EACH ROW EXECUTE PROCEDURE trfn_vapplications();


--
-- Name: rtr_vapplications_before; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr_vapplications_before BEFORE INSERT OR DELETE OR UPDATE ON vapplications FOR EACH ROW EXECUTE PROCEDURE trfn_vapplications();


--
-- Name: rtr_vos_before; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr_vos_before BEFORE INSERT OR DELETE OR UPDATE ON vos FOR EACH ROW EXECUTE PROCEDURE trfn_vos();


--
-- Name: rtr_vowide_image_list_images_09_before; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr_vowide_image_list_images_09_before BEFORE INSERT OR DELETE OR UPDATE ON vowide_image_list_images FOR EACH ROW EXECUTE PROCEDURE trfn_vowide_image_list_images();


--
-- Name: rtr_vowide_image_list_images_10_after; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr_vowide_image_list_images_10_after AFTER INSERT OR DELETE OR UPDATE ON vowide_image_list_images FOR EACH ROW EXECUTE PROCEDURE trfn_vowide_image_list_images();


--
-- Name: rtr_vowide_image_list_images_11_after; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr_vowide_image_list_images_11_after AFTER TRUNCATE ON vowide_image_list_images FOR EACH STATEMENT EXECUTE PROCEDURE trfn_vowide_image_list_images();


--
-- Name: rtr_vowide_image_lists_09_before; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr_vowide_image_lists_09_before BEFORE INSERT OR DELETE OR UPDATE ON vowide_image_lists FOR EACH ROW EXECUTE PROCEDURE trfn_vowide_image_lists();


--
-- Name: rtr_vowide_image_lists_10_after; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER rtr_vowide_image_lists_10_after AFTER INSERT OR DELETE OR UPDATE ON vowide_image_lists FOR EACH ROW EXECUTE PROCEDURE trfn_vowide_image_lists();


--
-- Name: tr___actor_group_members_99_refresh_permissions; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER tr___actor_group_members_99_refresh_permissions AFTER INSERT OR DELETE OR UPDATE ON __actor_group_members FOR EACH STATEMENT EXECUTE PROCEDURE trfn_refresh_permissions();


--
-- Name: tr_actor_groups_99_refresh_permissions; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER tr_actor_groups_99_refresh_permissions AFTER INSERT OR DELETE ON actor_groups FOR EACH STATEMENT EXECUTE PROCEDURE trfn_refresh_permissions();


--
-- Name: tr_applications_99_refresh_permissions; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER tr_applications_99_refresh_permissions AFTER INSERT OR DELETE ON applications FOR EACH STATEMENT EXECUTE PROCEDURE trfn_refresh_permissions();


--
-- Name: tr_appmanualcountries_99_refresh_permissions; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER tr_appmanualcountries_99_refresh_permissions AFTER INSERT OR DELETE OR UPDATE ON appmanualcountries FOR EACH STATEMENT EXECUTE PROCEDURE trfn_refresh_permissions();


--
-- Name: tr_privileges_99_refresh_permissions; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER tr_privileges_99_refresh_permissions AFTER INSERT OR DELETE OR UPDATE ON privileges FOR EACH STATEMENT EXECUTE PROCEDURE trfn_refresh_permissions();


--
-- Name: tr_researchers_99_refresh_permissions; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER tr_researchers_99_refresh_permissions AFTER INSERT OR DELETE ON researchers FOR EACH STATEMENT EXECUTE PROCEDURE trfn_refresh_permissions();


--
-- Name: tr_researchers_apps_90_reset_permissions; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER tr_researchers_apps_90_reset_permissions AFTER DELETE ON researchers_apps FOR EACH ROW EXECUTE PROCEDURE trfn_researchers_apps_reset_permissions();


--
-- Name: tr_researchers_apps_99_refresh_permissions; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER tr_researchers_apps_99_refresh_permissions AFTER INSERT OR DELETE OR UPDATE ON researchers_apps FOR EACH STATEMENT EXECUTE PROCEDURE trfn_refresh_permissions();


--
-- Name: tr_userrequests_99_refresh_permissions; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER tr_userrequests_99_refresh_permissions AFTER INSERT ON userrequests FOR EACH STATEMENT EXECUTE PROCEDURE trfn_refresh_permissions();


--
-- Name: tr_vos_99_refresh_permissions; Type: TRIGGER; Schema: public; Owner: appdb
--

CREATE TRIGGER tr_vos_99_refresh_permissions AFTER INSERT OR DELETE OR TRUNCATE ON vos FOR EACH STATEMENT EXECUTE PROCEDURE trfn_refresh_permissions();


SET search_path = sci_class, pg_catalog;

--
-- Name: rtr_sci_class_10_cprops_delete_orphans; Type: TRIGGER; Schema: sci_class; Owner: appdb
--

CREATE TRIGGER rtr_sci_class_10_cprops_delete_orphans AFTER DELETE ON cprops FOR EACH ROW EXECUTE PROCEDURE trfn_cprops_delete_orphans();


--
-- Name: rtr_sci_class_10_cvers_delete_orphans; Type: TRIGGER; Schema: sci_class; Owner: appdb
--

CREATE TRIGGER rtr_sci_class_10_cvers_delete_orphans AFTER DELETE ON cvers FOR EACH ROW EXECUTE PROCEDURE trfn_cvers_delete_orphans();


SET search_path = cache, pg_catalog;

--
-- Name: appprivsxmlcache_appid_fkey; Type: FK CONSTRAINT; Schema: cache; Owner: appdb
--

ALTER TABLE ONLY appprivsxmlcache
    ADD CONSTRAINT appprivsxmlcache_appid_fkey FOREIGN KEY (appid) REFERENCES public.applications(id);


--
-- Name: appxmlcache_id_fkey; Type: FK CONSTRAINT; Schema: cache; Owner: appdb
--

ALTER TABLE ONLY appxmlcache
    ADD CONSTRAINT appxmlcache_id_fkey FOREIGN KEY (id) REFERENCES public.applications(id);


SET search_path = harvest, pg_catalog;

--
-- Name: fk_projectcontactpersons_contactpersons; Type: FK CONSTRAINT; Schema: harvest; Owner: appdb
--

ALTER TABLE ONLY projectcontactpersons
    ADD CONSTRAINT fk_projectcontactpersons_contactpersons FOREIGN KEY (contactpersonid) REFERENCES contactpersons(id);


--
-- Name: fk_projectcontactpersons_projects; Type: FK CONSTRAINT; Schema: harvest; Owner: appdb
--

ALTER TABLE ONLY projectcontactpersons
    ADD CONSTRAINT fk_projectcontactpersons_projects FOREIGN KEY (contactpersonid) REFERENCES public.projects(id);


--
-- Name: fk_projectparticipants_researchers; Type: FK CONSTRAINT; Schema: harvest; Owner: appdb
--

ALTER TABLE ONLY contactpersons
    ADD CONSTRAINT fk_projectparticipants_researchers FOREIGN KEY (researcherid) REFERENCES public.researchers(id);


--
-- Name: fk_records_additional_record_id_records_id; Type: FK CONSTRAINT; Schema: harvest; Owner: appdb
--

ALTER TABLE ONLY records_additional
    ADD CONSTRAINT fk_records_additional_record_id_records_id FOREIGN KEY (record_id) REFERENCES records(record_id);


--
-- Name: fk_search_objects_archive_id_archives_id; Type: FK CONSTRAINT; Schema: harvest; Owner: appdb
--

ALTER TABLE ONLY search_objects
    ADD CONSTRAINT fk_search_objects_archive_id_archives_id FOREIGN KEY (archive_id) REFERENCES archives(archive_id);


--
-- Name: fk_search_objects_raw_field_id_raw_fields_id; Type: FK CONSTRAINT; Schema: harvest; Owner: appdb
--

ALTER TABLE ONLY search_objects
    ADD CONSTRAINT fk_search_objects_raw_field_id_raw_fields_id FOREIGN KEY (raw_field_id) REFERENCES raw_fields(raw_field_id);


--
-- Name: search_object_keywords_keyword_id_fkey; Type: FK CONSTRAINT; Schema: harvest; Owner: appdb
--

ALTER TABLE ONLY search_object_keywords
    ADD CONSTRAINT search_object_keywords_keyword_id_fkey FOREIGN KEY (keyword_id) REFERENCES search_keyword_list(keyword_id);


--
-- Name: search_object_keywords_object_id_fkey; Type: FK CONSTRAINT; Schema: harvest; Owner: appdb
--

ALTER TABLE ONLY search_object_keywords
    ADD CONSTRAINT search_object_keywords_object_id_fkey FOREIGN KEY (object_id) REFERENCES search_objects(object_id);


--
-- Name: search_objects_record_id_fkey; Type: FK CONSTRAINT; Schema: harvest; Owner: appdb
--

ALTER TABLE ONLY search_objects
    ADD CONSTRAINT search_objects_record_id_fkey FOREIGN KEY (record_id) REFERENCES records(record_id);


SET search_path = oses, pg_catalog;

--
-- Name: any_id_fkey; Type: FK CONSTRAINT; Schema: oses; Owner: appdb
--

ALTER TABLE ONLY "any"
    ADD CONSTRAINT any_id_fkey FOREIGN KEY (id) REFERENCES public.oses(id);


SET search_path = public, pg_catalog;

--
-- Name: __actor_group_members_groupid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY __actor_group_members
    ADD CONSTRAINT __actor_group_members_groupid_fkey FOREIGN KEY (groupid) REFERENCES actor_groups(id);


--
-- Name: __appdomains_appid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY appdomains
    ADD CONSTRAINT __appdomains_appid_fkey FOREIGN KEY (appid) REFERENCES applications(id);


--
-- Name: __appsubdomains_appid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY appsubdomains
    ADD CONSTRAINT __appsubdomains_appid_fkey FOREIGN KEY (appid) REFERENCES applications(id);


--
-- Name: access_token_netfilters_tokenid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY access_token_netfilters
    ADD CONSTRAINT access_token_netfilters_tokenid_fkey FOREIGN KEY (tokenid) REFERENCES access_tokens(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: access_tokens_addedby_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY access_tokens
    ADD CONSTRAINT access_tokens_addedby_fkey FOREIGN KEY (addedby) REFERENCES researchers(id);


--
-- Name: apikey_netfilters_keyid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY apikey_netfilters
    ADD CONSTRAINT apikey_netfilters_keyid_fkey FOREIGN KEY (keyid) REFERENCES apikeys(id);


--
-- Name: apikeys_ownerid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY apikeys
    ADD CONSTRAINT apikeys_ownerid_fkey FOREIGN KEY (ownerid) REFERENCES researchers(id);


--
-- Name: apikeys_sysaccountid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY apikeys
    ADD CONSTRAINT apikeys_sysaccountid_fkey FOREIGN KEY (sysaccountid) REFERENCES researchers(id);


--
-- Name: app_archs_appid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY app_archs
    ADD CONSTRAINT app_archs_appid_fkey FOREIGN KEY (appid) REFERENCES applications(id);


--
-- Name: app_archs_archid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY app_archs
    ADD CONSTRAINT app_archs_archid_fkey FOREIGN KEY (archid) REFERENCES archs(id);


--
-- Name: app_cnames_appid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY app_cnames
    ADD CONSTRAINT app_cnames_appid_fkey FOREIGN KEY (appid) REFERENCES applications(id);


--
-- Name: app_middlewares_ibfk_1; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY app_middlewares
    ADD CONSTRAINT app_middlewares_ibfk_1 FOREIGN KEY (appid) REFERENCES applications(id);


--
-- Name: app_middlewares_ibfk_2; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY app_middlewares
    ADD CONSTRAINT app_middlewares_ibfk_2 FOREIGN KEY (middlewareid) REFERENCES middlewares(id);


--
-- Name: app_oses_appid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY app_oses
    ADD CONSTRAINT app_oses_appid_fkey FOREIGN KEY (appid) REFERENCES applications(id);


--
-- Name: app_oses_osid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY app_oses
    ADD CONSTRAINT app_oses_osid_fkey FOREIGN KEY (osid) REFERENCES oses(id);


--
-- Name: app_releases_appid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY app_releases
    ADD CONSTRAINT app_releases_appid_fkey FOREIGN KEY (appid) REFERENCES applications(id);


--
-- Name: app_releases_manager_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY app_releases
    ADD CONSTRAINT app_releases_manager_fkey FOREIGN KEY (manager) REFERENCES researchers(id);


--
-- Name: app_validation_log_appid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY app_validation_log
    ADD CONSTRAINT app_validation_log_appid_fkey FOREIGN KEY (appid) REFERENCES applications(id);


--
-- Name: appcategories_appid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY appcategories
    ADD CONSTRAINT appcategories_appid_fkey FOREIGN KEY (appid) REFERENCES applications(id);


--
-- Name: appcategories_categoryid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY appcategories
    ADD CONSTRAINT appcategories_categoryid_fkey FOREIGN KEY (categoryid) REFERENCES categories(id);


--
-- Name: appcountries_ibfk_1; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY appmanualcountries
    ADD CONSTRAINT appcountries_ibfk_1 FOREIGN KEY (countryid) REFERENCES countries(id);


--
-- Name: applications_owner_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY applications
    ADD CONSTRAINT applications_owner_fkey FOREIGN KEY (owner) REFERENCES researchers(id);


--
-- Name: appproglangs_appid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY appproglangs
    ADD CONSTRAINT appproglangs_appid_fkey FOREIGN KEY (appid) REFERENCES applications(id);


--
-- Name: appproglangs_proglangid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY appproglangs
    ADD CONSTRAINT appproglangs_proglangid_fkey FOREIGN KEY (proglangid) REFERENCES proglangs(id);


--
-- Name: appratings_appid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY appratings
    ADD CONSTRAINT appratings_appid_fkey FOREIGN KEY (appid) REFERENCES applications(id);


--
-- Name: appratings_submitterid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY appratings
    ADD CONSTRAINT appratings_submitterid_fkey FOREIGN KEY (submitterid) REFERENCES researchers(id);


--
-- Name: categories_parentid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY categories
    ADD CONSTRAINT categories_parentid_fkey FOREIGN KEY (parentid) REFERENCES categories(id);


--
-- Name: category_help_categoryid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY category_help
    ADD CONSTRAINT category_help_categoryid_fkey FOREIGN KEY (categoryid) REFERENCES categories(id);


--
-- Name: contexts_application_id; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY contexts
    ADD CONSTRAINT contexts_application_id FOREIGN KEY (appid) REFERENCES applications(id);


--
-- Name: contexts_researcher_addedby; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY contexts
    ADD CONSTRAINT contexts_researcher_addedby FOREIGN KEY (addedby) REFERENCES researchers(id);


--
-- Name: contexts_researchers_lastupdatedby; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY contexts
    ADD CONSTRAINT contexts_researchers_lastupdatedby FOREIGN KEY (lastupdatedby) REFERENCES researchers(id);


--
-- Name: dataset_disciplines_diciplineid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY dataset_disciplines
    ADD CONSTRAINT dataset_disciplines_diciplineid_fkey FOREIGN KEY (disciplineid) REFERENCES disciplines(id);


--
-- Name: dataset_location_organizations_dataset_location_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY dataset_location_organizations
    ADD CONSTRAINT dataset_location_organizations_dataset_location_id_fkey FOREIGN KEY (dataset_location_id) REFERENCES dataset_locations(id);


--
-- Name: dataset_location_organizations_organizationid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY dataset_location_organizations
    ADD CONSTRAINT dataset_location_organizations_organizationid_fkey FOREIGN KEY (organizationid) REFERENCES organizations(id);


--
-- Name: dataset_location_sites_dataset_location_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY dataset_location_sites
    ADD CONSTRAINT dataset_location_sites_dataset_location_id_fkey FOREIGN KEY (dataset_location_id) REFERENCES dataset_locations(id);


--
-- Name: dataset_location_sites_siteid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY dataset_location_sites
    ADD CONSTRAINT dataset_location_sites_siteid_fkey FOREIGN KEY (siteid) REFERENCES gocdb.sites(pkey);


--
-- Name: dataset_locations_addedby_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY dataset_locations
    ADD CONSTRAINT dataset_locations_addedby_fkey FOREIGN KEY (addedby) REFERENCES researchers(id);


--
-- Name: dataset_locations_connection_type_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY dataset_locations
    ADD CONSTRAINT dataset_locations_connection_type_fkey FOREIGN KEY (connection_type) REFERENCES dataset_conn_types(id);


--
-- Name: dataset_locations_exchange_fmt_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY dataset_locations
    ADD CONSTRAINT dataset_locations_exchange_fmt_fkey FOREIGN KEY (exchange_fmt) REFERENCES dataset_exchange_formats(id);


--
-- Name: dataset_versions_addedby_fkey1; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY dataset_versions
    ADD CONSTRAINT dataset_versions_addedby_fkey1 FOREIGN KEY (addedby) REFERENCES researchers(id);


--
-- Name: dataset_versions_datasetid_fkey1; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY dataset_versions
    ADD CONSTRAINT dataset_versions_datasetid_fkey1 FOREIGN KEY (datasetid) REFERENCES datasets(id);


--
-- Name: dataset_versions_parentid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY dataset_versions
    ADD CONSTRAINT dataset_versions_parentid_fkey FOREIGN KEY (parentid) REFERENCES dataset_versions(id);


--
-- Name: datasets_addedby_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY datasets
    ADD CONSTRAINT datasets_addedby_fkey FOREIGN KEY (addedby) REFERENCES researchers(id);


--
-- Name: datasets_parentid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY datasets
    ADD CONSTRAINT datasets_parentid_fkey FOREIGN KEY (parentid) REFERENCES datasets(id);


--
-- Name: discipline_help_disciplineid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY discipline_help
    ADD CONSTRAINT discipline_help_disciplineid_fkey FOREIGN KEY (disciplineid) REFERENCES disciplines(id);


--
-- Name: disciplines_parentid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY disciplines
    ADD CONSTRAINT disciplines_parentid_fkey FOREIGN KEY (parentid) REFERENCES disciplines(id);


--
-- Name: dissemination_composerid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY dissemination
    ADD CONSTRAINT dissemination_composerid_fkey FOREIGN KEY (composerid) REFERENCES researchers(id);


--
-- Name: faq_history_submitter_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY faq_history
    ADD CONSTRAINT faq_history_submitter_fkey FOREIGN KEY (submitter) REFERENCES researchers(id);


--
-- Name: faqs_submitter_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY faqs
    ADD CONSTRAINT faqs_submitter_fkey FOREIGN KEY (submitter) REFERENCES researchers(id);


--
-- Name: fk_abusereports_submitterid; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY abusereports
    ADD CONSTRAINT fk_abusereports_submitterid FOREIGN KEY (submitterid) REFERENCES researchers(id);


--
-- Name: fk_app_addedby; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY applications
    ADD CONSTRAINT fk_app_addedby FOREIGN KEY (addedby) REFERENCES researchers(id);


--
-- Name: fk_app_api_logger_app; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY app_api_log
    ADD CONSTRAINT fk_app_api_logger_app FOREIGN KEY (appid) REFERENCES applications(id);


--
-- Name: fk_app_api_logger_researcher; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY app_api_log
    ADD CONSTRAINT fk_app_api_logger_researcher FOREIGN KEY (researcherid) REFERENCES researchers(id);


--
-- Name: fk_app_countries_app; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY appmanualcountries
    ADD CONSTRAINT fk_app_countries_app FOREIGN KEY (appid) REFERENCES applications(id);


--
-- Name: fk_app_data; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY app_data
    ADD CONSTRAINT fk_app_data FOREIGN KEY (appid) REFERENCES applications(id);


--
-- Name: fk_app_del_infos_apps; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY app_del_infos
    ADD CONSTRAINT fk_app_del_infos_apps FOREIGN KEY (appid) REFERENCES applications(id);


--
-- Name: fk_app_del_infos_positiontypes; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY app_del_infos
    ADD CONSTRAINT fk_app_del_infos_positiontypes FOREIGN KEY (roleid) REFERENCES positiontypes(id);


--
-- Name: fk_app_del_infos_researchers; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY app_del_infos
    ADD CONSTRAINT fk_app_del_infos_researchers FOREIGN KEY (deletedby) REFERENCES researchers(id);


--
-- Name: fk_app_licenses_apps_appid; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY app_licenses
    ADD CONSTRAINT fk_app_licenses_apps_appid FOREIGN KEY (appid) REFERENCES applications(id);


--
-- Name: fk_app_licenses_apps_licenseid; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY app_licenses
    ADD CONSTRAINT fk_app_licenses_apps_licenseid FOREIGN KEY (licenseid) REFERENCES licenses(id);


--
-- Name: fk_app_mod_infos_apps; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY app_mod_infos
    ADD CONSTRAINT fk_app_mod_infos_apps FOREIGN KEY (appid) REFERENCES applications(id);


--
-- Name: fk_app_mod_infos_researchers; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY app_mod_infos
    ADD CONSTRAINT fk_app_mod_infos_researchers FOREIGN KEY (moddedby) REFERENCES researchers(id);


--
-- Name: fk_app_order_hack_appid; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY app_order_hack
    ADD CONSTRAINT fk_app_order_hack_appid FOREIGN KEY (appid) REFERENCES applications(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: fk_app_status; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY applications
    ADD CONSTRAINT fk_app_status FOREIGN KEY (statusid) REFERENCES statuses(id);


--
-- Name: fk_app_tags_apps; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY __app_tags
    ADD CONSTRAINT fk_app_tags_apps FOREIGN KEY (appid) REFERENCES applications(id);


--
-- Name: fk_app_tags_researchers; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY __app_tags
    ADD CONSTRAINT fk_app_tags_researchers FOREIGN KEY (researcherid) REFERENCES researchers(id);


--
-- Name: fk_app_url; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY app_urls
    ADD CONSTRAINT fk_app_url FOREIGN KEY (appid) REFERENCES applications(id);


--
-- Name: fk_app_vos_app; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY __app_vos
    ADD CONSTRAINT fk_app_vos_app FOREIGN KEY (appid) REFERENCES applications(id);


--
-- Name: fk_app_vos_vo; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY __app_vos
    ADD CONSTRAINT fk_app_vos_vo FOREIGN KEY (void) REFERENCES vos(id);


--
-- Name: fk_appbookmarks_app; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY appbookmarks
    ADD CONSTRAINT fk_appbookmarks_app FOREIGN KEY (appid) REFERENCES applications(id);


--
-- Name: fk_appbookmarks_researcher; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY appbookmarks
    ADD CONSTRAINT fk_appbookmarks_researcher FOREIGN KEY (researcherid) REFERENCES researchers(id);


--
-- Name: fk_appcontact_app_mws; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY appcontact_middlewares
    ADD CONSTRAINT fk_appcontact_app_mws FOREIGN KEY (appmiddlewareid) REFERENCES app_middlewares(id) ON DELETE CASCADE DEFERRABLE;


--
-- Name: fk_appcontact_appvos_void; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY appcontact_vos
    ADD CONSTRAINT fk_appcontact_appvos_void FOREIGN KEY (appid, void) REFERENCES __app_vos(appid, void) ON UPDATE CASCADE ON DELETE CASCADE DEFERRABLE;


--
-- Name: fk_appcontact_mws_conid; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY appcontact_middlewares
    ADD CONSTRAINT fk_appcontact_mws_conid FOREIGN KEY (appid, researcherid) REFERENCES researchers_apps(appid, researcherid) ON UPDATE CASCADE ON DELETE CASCADE DEFERRABLE;


--
-- Name: fk_appcontact_otheritems_conid; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY appcontact_otheritems
    ADD CONSTRAINT fk_appcontact_otheritems_conid FOREIGN KEY (appid, researcherid) REFERENCES researchers_apps(appid, researcherid);


--
-- Name: fk_appcontact_vos_conid; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY appcontact_vos
    ADD CONSTRAINT fk_appcontact_vos_conid FOREIGN KEY (appid, researcherid) REFERENCES researchers_apps(appid, researcherid) ON UPDATE CASCADE ON DELETE CASCADE DEFERRABLE;


--
-- Name: fk_appdoc_app; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY appdocuments
    ADD CONSTRAINT fk_appdoc_app FOREIGN KEY (appid) REFERENCES applications(id);


--
-- Name: fk_appdoc_doctype; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY appdocuments
    ADD CONSTRAINT fk_appdoc_doctype FOREIGN KEY (doctypeid) REFERENCES doctypes(id);


--
-- Name: fk_appdomains_domainid; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY appdomains
    ADD CONSTRAINT fk_appdomains_domainid FOREIGN KEY (domainid) REFERENCES domains(id);


--
-- Name: fk_applogos_applications; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY applogos
    ADD CONSTRAINT fk_applogos_applications FOREIGN KEY (appid) REFERENCES applications(id);


--
-- Name: fk_appmod_app; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY appmodhistories
    ADD CONSTRAINT fk_appmod_app FOREIGN KEY (appid) REFERENCES applications(id);


--
-- Name: fk_appmod_researcher; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY appmodhistories
    ADD CONSTRAINT fk_appmod_researcher FOREIGN KEY (researcherid) REFERENCES researchers(id);


--
-- Name: fk_appsubdomains_subdomainid; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY appsubdomains
    ADD CONSTRAINT fk_appsubdomains_subdomainid FOREIGN KEY (subdomainid) REFERENCES subdomains(id);


--
-- Name: fk_contact_researcher; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY contacts
    ADD CONSTRAINT fk_contact_researcher FOREIGN KEY (researcherid) REFERENCES researchers(id);


--
-- Name: fk_contacts_type; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY contacts
    ADD CONSTRAINT fk_contacts_type FOREIGN KEY (contacttypeid) REFERENCES contacttypes(id);


--
-- Name: fk_context_contexts; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY context_script_assocs
    ADD CONSTRAINT fk_context_contexts FOREIGN KEY (contextid) REFERENCES contexts(id);


--
-- Name: fk_context_contextscripts; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY context_script_assocs
    ADD CONSTRAINT fk_context_contextscripts FOREIGN KEY (scriptid) REFERENCES contextscripts(id);


--
-- Name: fk_context_researcher_addedby; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY context_script_assocs
    ADD CONSTRAINT fk_context_researcher_addedby FOREIGN KEY (addedby) REFERENCES researchers(id);


--
-- Name: fk_contextscripts_contextformats; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY contextscripts
    ADD CONSTRAINT fk_contextscripts_contextformats FOREIGN KEY (formatid) REFERENCES contextformats(id);


--
-- Name: fk_contextscripts_researcher_addedby; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY contextscripts
    ADD CONSTRAINT fk_contextscripts_researcher_addedby FOREIGN KEY (addedby) REFERENCES researchers(id);


--
-- Name: fk_contextscripts_researcher_lastupdatedby; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY contextscripts
    ADD CONSTRAINT fk_contextscripts_researcher_lastupdatedby FOREIGN KEY (lastupdatedby) REFERENCES researchers(id);


--
-- Name: fk_country_region; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY countries
    ADD CONSTRAINT fk_country_region FOREIGN KEY (regionid) REFERENCES regions(id);


--
-- Name: fk_dataset_disciplines_dsid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY dataset_disciplines
    ADD CONSTRAINT fk_dataset_disciplines_dsid_fkey FOREIGN KEY (datasetid) REFERENCES datasets(id);


--
-- Name: fk_dataset_licenses_datasets_dsid; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY dataset_licenses
    ADD CONSTRAINT fk_dataset_licenses_datasets_dsid FOREIGN KEY (datasetid) REFERENCES datasets(id);


--
-- Name: fk_dataset_licenses_datasets_licenseid; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY dataset_licenses
    ADD CONSTRAINT fk_dataset_licenses_datasets_licenseid FOREIGN KEY (licenseid) REFERENCES licenses(id);


--
-- Name: fk_extauthors_doc; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY extauthors
    ADD CONSTRAINT fk_extauthors_doc FOREIGN KEY (docid) REFERENCES appdocuments(id);


--
-- Name: fk_fundings_fundings; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY fundings
    ADD CONSTRAINT fk_fundings_fundings FOREIGN KEY (parentid) REFERENCES fundings(id);


--
-- Name: fk_intauthors_auth; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY intauthors
    ADD CONSTRAINT fk_intauthors_auth FOREIGN KEY (authorid) REFERENCES researchers(id);


--
-- Name: fk_intauthors_doc; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY intauthors
    ADD CONSTRAINT fk_intauthors_doc FOREIGN KEY (docid) REFERENCES appdocuments(id);


--
-- Name: fk_mail_subscriptions_researchers; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY mail_subscriptions
    ADD CONSTRAINT fk_mail_subscriptions_researchers FOREIGN KEY (researcherid) REFERENCES researchers(id);


--
-- Name: fk_messages_receiver; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY messages
    ADD CONSTRAINT fk_messages_receiver FOREIGN KEY (receiverid) REFERENCES researchers(id);


--
-- Name: fk_messages_sender; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY messages
    ADD CONSTRAINT fk_messages_sender FOREIGN KEY (senderid) REFERENCES researchers(id);


--
-- Name: fk_ngi_country; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY ngis
    ADD CONSTRAINT fk_ngi_country FOREIGN KEY (countryid) REFERENCES countries(id);


--
-- Name: fk_organizations_countries; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY organizations
    ADD CONSTRAINT fk_organizations_countries FOREIGN KEY (countryid) REFERENCES countries(id);


--
-- Name: fk_organizations_deletedby; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY organizations
    ADD CONSTRAINT fk_organizations_deletedby FOREIGN KEY (deletedby) REFERENCES researchers(id);


--
-- Name: fk_organizations_researchers; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY organizations
    ADD CONSTRAINT fk_organizations_researchers FOREIGN KEY (addedby) REFERENCES researchers(id);


--
-- Name: fk_organizations_sourceid; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY organizations
    ADD CONSTRAINT fk_organizations_sourceid FOREIGN KEY (sourceid) REFERENCES entitysources(id);


--
-- Name: fk_ppl_api_logger_ppl; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY ppl_api_log
    ADD CONSTRAINT fk_ppl_api_logger_ppl FOREIGN KEY (pplid) REFERENCES researchers(id);


--
-- Name: fk_ppl_api_logger_researcher; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY ppl_api_log
    ADD CONSTRAINT fk_ppl_api_logger_researcher FOREIGN KEY (researcherid) REFERENCES researchers(id);


--
-- Name: fk_priviledges_action; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY privileges
    ADD CONSTRAINT fk_priviledges_action FOREIGN KEY (actionid) REFERENCES actions(id);


--
-- Name: fk_project_contracttype; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY projects
    ADD CONSTRAINT fk_project_contracttype FOREIGN KEY (contracttypeid) REFERENCES contracttypes(id);


--
-- Name: fk_project_deletedby; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY projects
    ADD CONSTRAINT fk_project_deletedby FOREIGN KEY (deletedby) REFERENCES researchers(id);


--
-- Name: fk_project_funding; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY projects
    ADD CONSTRAINT fk_project_funding FOREIGN KEY (fundingid) REFERENCES fundings(id);


--
-- Name: fk_project_researchers; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY projects
    ADD CONSTRAINT fk_project_researchers FOREIGN KEY (addedby) REFERENCES researchers(id);


--
-- Name: fk_project_sourceid; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY projects
    ADD CONSTRAINT fk_project_sourceid FOREIGN KEY (sourceid) REFERENCES entitysources(id);


--
-- Name: fk_relations_relations; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY relations
    ADD CONSTRAINT fk_relations_relations FOREIGN KEY (parentid) REFERENCES relations(id);


--
-- Name: fk_relations_relationtypes; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY relations
    ADD CONSTRAINT fk_relations_relationtypes FOREIGN KEY (reltypeid) REFERENCES relationtypes(id);


--
-- Name: fk_relations_researchers_addedby; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY relations
    ADD CONSTRAINT fk_relations_researchers_addedby FOREIGN KEY (addedby) REFERENCES researchers(id);


--
-- Name: fk_relations_researchers_denyby; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY relations
    ADD CONSTRAINT fk_relations_researchers_denyby FOREIGN KEY (denyby) REFERENCES researchers(id);


--
-- Name: fk_relations_researchers_hiddenby; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY relations
    ADD CONSTRAINT fk_relations_researchers_hiddenby FOREIGN KEY (hiddenby) REFERENCES researchers(id);


--
-- Name: fk_relationtypes_actions; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY relationtypes
    ADD CONSTRAINT fk_relationtypes_actions FOREIGN KEY (actionid) REFERENCES actions(id);


--
-- Name: fk_relationtypes_relationverbs; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY relationtypes
    ADD CONSTRAINT fk_relationtypes_relationverbs FOREIGN KEY (verbid) REFERENCES relationverbs(id);


--
-- Name: fk_researcher_app_app; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY researchers_apps
    ADD CONSTRAINT fk_researcher_app_app FOREIGN KEY (appid) REFERENCES applications(id);


--
-- Name: fk_researcher_app_researcher; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY researchers_apps
    ADD CONSTRAINT fk_researcher_app_researcher FOREIGN KEY (researcherid) REFERENCES researchers(id);


--
-- Name: fk_researcher_country; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY researchers
    ADD CONSTRAINT fk_researcher_country FOREIGN KEY (countryid) REFERENCES countries(id);


--
-- Name: fk_researcher_postype; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY researchers
    ADD CONSTRAINT fk_researcher_postype FOREIGN KEY (positiontypeid) REFERENCES positiontypes(id);


--
-- Name: fk_researcherimages_researchers; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY researcherimages
    ADD CONSTRAINT fk_researcherimages_researchers FOREIGN KEY (researcherid) REFERENCES researchers(id);


--
-- Name: fk_userrequests_state; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY userrequests
    ADD CONSTRAINT fk_userrequests_state FOREIGN KEY (stateid) REFERENCES userrequeststates(id);


--
-- Name: fk_userrequests_type; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY userrequests
    ADD CONSTRAINT fk_userrequests_type FOREIGN KEY (typeid) REFERENCES userrequesttypes(id);


--
-- Name: fk_vmiinstance_contextscripts_contextscripts; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY vmiinstance_contextscripts
    ADD CONSTRAINT fk_vmiinstance_contextscripts_contextscripts FOREIGN KEY (contextscriptid) REFERENCES contextscripts(id);


--
-- Name: fk_vmiinstance_contextscripts_researchers; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY vmiinstance_contextscripts
    ADD CONSTRAINT fk_vmiinstance_contextscripts_researchers FOREIGN KEY (addedby) REFERENCES researchers(id);


--
-- Name: fk_vmiinstance_contextscripts_vmiinstance; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY vmiinstance_contextscripts
    ADD CONSTRAINT fk_vmiinstance_contextscripts_vmiinstance FOREIGN KEY (vmiinstanceid) REFERENCES vmiinstances(id);


--
-- Name: oses_os_family_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY oses
    ADD CONSTRAINT oses_os_family_id_fkey FOREIGN KEY (os_family_id) REFERENCES os_families(id);


--
-- Name: pending_accounts_researcherid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY pending_accounts
    ADD CONSTRAINT pending_accounts_researcherid_fkey FOREIGN KEY (researcherid) REFERENCES researchers(id);


--
-- Name: ppl_del_infos_deletedby_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY ppl_del_infos
    ADD CONSTRAINT ppl_del_infos_deletedby_fkey FOREIGN KEY (deletedby) REFERENCES researchers(id);


--
-- Name: ppl_del_infos_researcherid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY ppl_del_infos
    ADD CONSTRAINT ppl_del_infos_researcherid_fkey FOREIGN KEY (researcherid) REFERENCES researchers(id);


--
-- Name: ppl_del_infos_roleid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY ppl_del_infos
    ADD CONSTRAINT ppl_del_infos_roleid_fkey FOREIGN KEY (roleid) REFERENCES positiontypes(id);


--
-- Name: pplproglangs_proglangid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY pplproglangs
    ADD CONSTRAINT pplproglangs_proglangid_fkey FOREIGN KEY (proglangid) REFERENCES proglangs(id);


--
-- Name: pplproglangs_researcherid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY pplproglangs
    ADD CONSTRAINT pplproglangs_researcherid_fkey FOREIGN KEY (researcherid) REFERENCES researchers(id);


--
-- Name: researcher_cnames_researcherid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY researcher_cnames
    ADD CONSTRAINT researcher_cnames_researcherid_fkey FOREIGN KEY (researcherid) REFERENCES researchers(id);


--
-- Name: researchers_addedby_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY researchers
    ADD CONSTRAINT researchers_addedby_fkey FOREIGN KEY (addedby) REFERENCES researchers(id);


--
-- Name: user_accounts_researcherid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY user_accounts
    ADD CONSTRAINT user_accounts_researcherid_fkey FOREIGN KEY (researcherid) REFERENCES researchers(id);


--
-- Name: user_accounts_state_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY user_accounts
    ADD CONSTRAINT user_accounts_state_fkey FOREIGN KEY (stateid) REFERENCES user_account_states(id);


--
-- Name: user_credentials_researcherid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY user_credentials
    ADD CONSTRAINT user_credentials_researcherid_fkey FOREIGN KEY (researcherid) REFERENCES researchers(id);


--
-- Name: va_provider_endpoints_va_provider_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY va_provider_endpoints
    ADD CONSTRAINT va_provider_endpoints_va_provider_id_fkey FOREIGN KEY (va_provider_id) REFERENCES gocdb.va_providers(pkey) ON DELETE CASCADE;


--
-- Name: va_provider_images_va_provider_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY __va_provider_images
    ADD CONSTRAINT va_provider_images_va_provider_id_fkey FOREIGN KEY (va_provider_id) REFERENCES gocdb.va_providers(pkey) ON DELETE CASCADE;


--
-- Name: va_provider_templates_va_provider_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY va_provider_templates
    ADD CONSTRAINT va_provider_templates_va_provider_id_fkey FOREIGN KEY (va_provider_id) REFERENCES gocdb.va_providers(pkey) ON DELETE CASCADE;


--
-- Name: vapp_versions_vappid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY vapp_versions
    ADD CONSTRAINT vapp_versions_vappid_fkey FOREIGN KEY (vappid) REFERENCES vapplications(id);


--
-- Name: vapplications_appid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY vapplications
    ADD CONSTRAINT vapplications_appid_fkey FOREIGN KEY (appid) REFERENCES applications(id);


--
-- Name: vapplists_vappversionid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY vapplists
    ADD CONSTRAINT vapplists_vappversionid_fkey FOREIGN KEY (vappversionid) REFERENCES vapp_versions(id);


--
-- Name: vapplists_vmiinstanceid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY vapplists
    ADD CONSTRAINT vapplists_vmiinstanceid_fkey FOREIGN KEY (vmiinstanceid) REFERENCES vmiinstances(id);


--
-- Name: vmcaster_requests_appid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY vmcaster_requests
    ADD CONSTRAINT vmcaster_requests_appid_fkey FOREIGN KEY (appid) REFERENCES applications(id);


--
-- Name: vmiflavours_archid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY vmiflavours
    ADD CONSTRAINT vmiflavours_archid_fkey FOREIGN KEY (archid) REFERENCES archs(id);


--
-- Name: vmiflavours_osid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY vmiflavours
    ADD CONSTRAINT vmiflavours_osid_fkey FOREIGN KEY (osid) REFERENCES oses(id);


--
-- Name: vmiflavours_vmiid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY vmiflavours
    ADD CONSTRAINT vmiflavours_vmiid_fkey FOREIGN KEY (vmiid) REFERENCES vmis(id);


--
-- Name: vmiinstances_addedby_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY vmiinstances
    ADD CONSTRAINT vmiinstances_addedby_fkey FOREIGN KEY (addedby) REFERENCES researchers(id);


--
-- Name: vmiinstances_lastupdatedby_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY vmiinstances
    ADD CONSTRAINT vmiinstances_lastupdatedby_fkey FOREIGN KEY (lastupdatedby) REFERENCES researchers(id);


--
-- Name: vmiinstances_vmiflavourid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY vmiinstances
    ADD CONSTRAINT vmiinstances_vmiflavourid_fkey FOREIGN KEY (vmiflavourid) REFERENCES vmiflavours(id);


--
-- Name: vmis_vappid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY vmis
    ADD CONSTRAINT vmis_vappid_fkey FOREIGN KEY (vappid) REFERENCES vapplications(id);


--
-- Name: vo_middlewares_middlewareid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY vo_middlewares
    ADD CONSTRAINT vo_middlewares_middlewareid_fkey FOREIGN KEY (middlewareid) REFERENCES middlewares(id);


--
-- Name: vo_middlewares_void_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY vo_middlewares
    ADD CONSTRAINT vo_middlewares_void_fkey FOREIGN KEY (void) REFERENCES vos(id);


--
-- Name: vo_resources_void_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY vo_resources
    ADD CONSTRAINT vo_resources_void_fkey FOREIGN KEY (void) REFERENCES vos(id);


--
-- Name: vomses_void_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY vomses
    ADD CONSTRAINT vomses_void_fkey FOREIGN KEY (void) REFERENCES vos(id);


--
-- Name: vos_domainid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY vos
    ADD CONSTRAINT vos_domainid_fkey FOREIGN KEY (domainid) REFERENCES domains(id);


--
-- Name: vos_sourceid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY vos
    ADD CONSTRAINT vos_sourceid_fkey FOREIGN KEY (sourceid) REFERENCES vo_sources(id);


--
-- Name: vowide_image_list_images_vapplistid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY vowide_image_list_images
    ADD CONSTRAINT vowide_image_list_images_vapplistid_fkey FOREIGN KEY (vapplistid) REFERENCES vapplists(id);


--
-- Name: vowide_image_list_images_vowide_image_list_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY vowide_image_list_images
    ADD CONSTRAINT vowide_image_list_images_vowide_image_list_id_fkey FOREIGN KEY (vowide_image_list_id) REFERENCES vowide_image_lists(id) ON DELETE CASCADE;


--
-- Name: vowide_image_lists_alteredby_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY vowide_image_lists
    ADD CONSTRAINT vowide_image_lists_alteredby_fkey FOREIGN KEY (alteredby) REFERENCES researchers(id);


--
-- Name: vowide_image_lists_publishedby_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY vowide_image_lists
    ADD CONSTRAINT vowide_image_lists_publishedby_fkey FOREIGN KEY (publishedby) REFERENCES researchers(id);


--
-- Name: vowide_image_lists_void_fkey; Type: FK CONSTRAINT; Schema: public; Owner: appdb
--

ALTER TABLE ONLY vowide_image_lists
    ADD CONSTRAINT vowide_image_lists_void_fkey FOREIGN KEY (void) REFERENCES vos(id);


SET search_path = sci_class, pg_catalog;

--
-- Name: cprops_cid_fkey; Type: FK CONSTRAINT; Schema: sci_class; Owner: appdb
--

ALTER TABLE ONLY cprops
    ADD CONSTRAINT cprops_cid_fkey FOREIGN KEY (cid) REFERENCES cids(id);


--
-- Name: cprops_cpropid_fkey; Type: FK CONSTRAINT; Schema: sci_class; Owner: appdb
--

ALTER TABLE ONLY cprops
    ADD CONSTRAINT cprops_cpropid_fkey FOREIGN KEY (cpropid) REFERENCES cpropids(id);


--
-- Name: cvers_cpropid_fkey; Type: FK CONSTRAINT; Schema: sci_class; Owner: appdb
--

ALTER TABLE ONLY cvers
    ADD CONSTRAINT cvers_cpropid_fkey FOREIGN KEY (cpropid) REFERENCES cprops(id);


--
-- Name: cvers_version_fkey; Type: FK CONSTRAINT; Schema: sci_class; Owner: appdb
--

ALTER TABLE ONLY cvers
    ADD CONSTRAINT cvers_version_fkey FOREIGN KEY (version) REFERENCES cverids(version);


SET search_path = stats, pg_catalog;

--
-- Name: app_cat_stats_categoryid_fkey; Type: FK CONSTRAINT; Schema: stats; Owner: appdb
--

ALTER TABLE ONLY app_cat_stats
    ADD CONSTRAINT app_cat_stats_categoryid_fkey FOREIGN KEY (categoryid) REFERENCES public.categories(id);


--
-- Name: app_disc_stats_disciplineid_fkey; Type: FK CONSTRAINT; Schema: stats; Owner: appdb
--

ALTER TABLE ONLY app_disc_stats
    ADD CONSTRAINT app_disc_stats_disciplineid_fkey FOREIGN KEY (disciplineid) REFERENCES public.disciplines(id);


--
-- Name: app_vo_cat_disc_history_appid_fkey; Type: FK CONSTRAINT; Schema: stats; Owner: appdb
--

ALTER TABLE ONLY app_vo_cat_disc_history
    ADD CONSTRAINT app_vo_cat_disc_history_appid_fkey FOREIGN KEY (appid) REFERENCES public.applications(id);


--
-- Name: app_vo_stats_void_fkey; Type: FK CONSTRAINT; Schema: stats; Owner: appdb
--

ALTER TABLE ONLY app_vo_stats
    ADD CONSTRAINT app_vo_stats_void_fkey FOREIGN KEY (void) REFERENCES public.vos(id);


--
-- Name: vo_disc_stats_disciplineid_fkey; Type: FK CONSTRAINT; Schema: stats; Owner: appdb
--

ALTER TABLE ONLY vo_disc_stats
    ADD CONSTRAINT vo_disc_stats_disciplineid_fkey FOREIGN KEY (disciplineid) REFERENCES public.disciplines(id);


--
-- Name: public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA public FROM postgres;
GRANT ALL ON SCHEMA public TO postgres;
GRANT ALL ON SCHEMA public TO PUBLIC;


SET search_path = public, pg_catalog;

--
-- Name: aggregate_news; Type: ACL; Schema: public; Owner: appdb
--

REVOKE ALL ON TABLE aggregate_news FROM PUBLIC;
REVOKE ALL ON TABLE aggregate_news FROM appdb;
GRANT ALL ON TABLE aggregate_news TO appdb;


--
-- Name: aggregate_news2; Type: ACL; Schema: public; Owner: appdb
--

REVOKE ALL ON TABLE aggregate_news2 FROM PUBLIC;
REVOKE ALL ON TABLE aggregate_news2 FROM appdb;
GRANT ALL ON TABLE aggregate_news2 TO appdb;


--
-- Name: aggregate_news_old; Type: ACL; Schema: public; Owner: appdb
--

REVOKE ALL ON TABLE aggregate_news_old FROM PUBLIC;
REVOKE ALL ON TABLE aggregate_news_old FROM appdb;
GRANT ALL ON TABLE aggregate_news_old TO appdb;


--
-- Name: positiontypes; Type: ACL; Schema: public; Owner: appdb
--

REVOKE ALL ON TABLE positiontypes FROM PUBLIC;
REVOKE ALL ON TABLE positiontypes FROM appdb;
GRANT ALL ON TABLE positiontypes TO appdb;


--
-- Name: linkstatuses; Type: ACL; Schema: public; Owner: appdb
--

REVOKE ALL ON TABLE linkstatuses FROM PUBLIC;
REVOKE ALL ON TABLE linkstatuses FROM appdb;
GRANT ALL ON TABLE linkstatuses TO appdb;


--
-- Name: ppl_api_log; Type: ACL; Schema: public; Owner: appdb
--

REVOKE ALL ON TABLE ppl_api_log FROM PUBLIC;
REVOKE ALL ON TABLE ppl_api_log FROM appdb;
GRANT ALL ON TABLE ppl_api_log TO appdb;


--
-- PostgreSQL database dump complete
--

