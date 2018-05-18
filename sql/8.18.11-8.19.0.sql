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
Previous version: 8.18.11
New version: 8.19.0
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

CREATE TABLE public.pidhandles
(
  id bigint NOT NULL DEFAULT nextval('pidhandles_id_seq'::regclass),
  url text NOT NULL,
  extras jsonb,
  suffix text,
  result integer NOT NULL DEFAULT 0,
  createdon timestamp without time zone,
  lastupdated timestamp without time zone,
  deletedon timestamp without time zone,
  entrytype e_entity NOT NULL,
  entryid integer NOT NULL,
  prefix text,
  CONSTRAINT pidhandles_pkey PRIMARY KEY (id),
  CONSTRAINT pidhandles_url_key UNIQUE (url),
  ADD CONSTRAINT pidhandles_suffix_key UNIQUE (suffix)
)
WITH (
  OIDS=FALSE
);
ALTER TABLE public.pidhandles
  OWNER TO appdb;

CREATE INDEX idx_pidhandles_entryid ON pidhandles (entryid);
CREATE INDEX idx_pidhandles_entrytype ON pidhandles (entrytype);
CREATE INDEX idx_pidhandles_suffix ON pidhandles (suffix);
CREATE INDEX idx_pidhandles_result ON pidhandles (result);

-- DROP TABLE IF EXISTS pidhandlelog;

CREATE TYPE mintaction AS ENUM ('register', 'update', 'delete');
CREATE TYPE mintstate AS ENUM ('pending', 'success', 'successverified', 'failed');

CREATE TABLE pidhandlelog(
id BIGSERIAL NOT NULL PRIMARY KEY,
url TEXT NOT NULL,
extras TEXT,
suffix TEXT,
action mintaction NOT NULL, -- 0 = insert, 1 = update, 2 = delete
result minstate NOT NULL DEFAULT 'pending'::mintstate, -- 0 = pending, 1 = success, 2 = successverified, 3 = failed
tstamp TIMESTAMP,
entrytype e_entity NOT NULL,
entryid INT NOT NULL
);
ALTER TABLE public.pidhandlelog
  OWNER TO appdb;

CREATE OR REPLACE FUNCTION public.htree_text(tbl text, padding character DEFAULT ' '::bpchar, padding_count integer DEFAULT 2, indicator text DEFAULT '>'::text)
 RETURNS TABLE(id integer, name text, parentid integer, lvl integer)
 LANGUAGE plpgsql
AS $function$
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
		CASE WHEN COALESCE(lvl.cname, '''') = '''' THEN '''' ELSE lvl.cname || LPAD(''' || indicator || ''', ' || padding_count::text || ', ''' || padding || ''') || RPAD('''', ' || (padding_count - 1)::text || ', ''' || padding || ''') END || name,
                parentid,
                CASE WHEN o IS NULL THEN CASE COALESCE(ord, 0) WHEN 0 THEN ''Z'' ELSE ord::text END || '' '' || name ELSE o || ''_'' || name END
        FROM lvl, ' || tbl || ' WHERE NOT ' || tbl || '.parentid IS DISTINCT FROM cid
)
SELECT cid,cname,pid,l FROM lvl
WHERE l>0
ORDER BY o';
END;
$function$;

CREATE OR REPLACE FUNCTION public.handle_extras(applications)
 RETURNS JSONB
 LANGUAGE sql
 STABLE
AS $function$
SELECT (
	'{' || 
        '"name": ' || COALESCE(to_json($1.name::text), 'null') || ', ' || 
        '"cname": ' || COALESCE(to_json(COALESCE($1.cname::text, normalize_cname($1.name))), 'null') || ', ' || 
        '"description": ' || COALESCE(to_json($1.description::text), 'null') || ', ' || 
        '"discipline": ' || COALESCE(to_json(CASE array_to_string(array_agg(DISTINCT ddd.name), ', ') WHEN '' THEN NULL ELSE array_to_string(array_agg(DISTINCT ddd.name), ', ') END ), 'null') || ', ' ||
        '"category":  ' || COALESCE(to_json(CASE array_to_string(array_agg(DISTINCT ccc.name), ', ') WHEN '' THEN NULL ELSE array_to_string(array_agg(DISTINCT ccc.name), ', ') END), 'null') ||
	'}')::jsonb
FROM (SELECT $1) AS applications 
LEFT OUTER JOIN htree_text('disciplines') AS ddd ON ddd.id = ANY($1.disciplineid) AND (ddd.id NOT IN (SELECT parentid FROM htree_text('disciplines') AS d2 WHERE d2.id = ANY($1.disciplineid) AND NOT parentid IS NULL))
LEFT OUTER JOIN htree_text('categories') AS ccc ON ccc.id = ANY($1.categoryid) AND (ccc.id NOT IN (SELECT parentid FROM htree_text('categories') AS c2 WHERE c2.id = ANY($1.categoryid) AND NOT parentid IS NULL))
GROUP BY $1.id, $1.name, $1.cname, $1.description;
$function$;
ALTER FUNCTION handle_extras(applications) OWNER TO appdb;

CREATE OR REPLACE FUNCTION public.trfn_pidhandles()
  RETURNS trigger AS
$BODY$
DECLARE res mintstate;
DECLARE act mintaction;
DECLARE ver BOOLEAN;
BEGIN
	IF TG_WHEN = 'AFTER' THEN
		IF TG_OP = 'DELETE' THEN
			res := 'success'::mintstate;
			act := 'delete'::mintaction;
			ver := TRUE;
			RAISE NOTICE 'act: %, res:%, ver: %', act, res, ver;
			INSERT INTO pidhandlelog (url, suffix, action, result, tstamp, entrytype, entryid)
				VALUES (OLD.url, OLD.suffix, 
				act,
				res,
				NOW(),
				OLD.entrytype, OLD.entryid 			
			);
		ELSIF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN
			IF (NEW.result & 1)::BOOLEAN AND (NEW.result & 16)::BOOLEAN THEN -- registered + needs verification
				res := 'success'::mintstate;
			ELSIF (NEW.result = 1) THEN -- registed
				res := 'successverified'::mintstate;
			ELSIF (NEW.result & 2)::BOOLEAN THEN -- error occured
				res := 'failed'::mintstate;
			ELSE
				res := 'pending'::mintstate;
			END IF;

			IF ((NEW.result & 8)::boolean) THEN -- needs deletion
				act := 'delete'::mintaction;
			ELSIF (TG_OP = 'INSERT' AND (NEW.result & 4)::boolean) OR (TG_OP = 'UPDATE' AND ((OLD.result & 4)::boolean OR (NEW.result & 4)::boolean)) THEN -- needs update OR needed update
				act := 'update'::mintaction;
			ELSE
				act := 'register'::mintaction;
			END IF;

			IF (res = 'pending'::mintstate) THEN
				ver := NULL;
			ELSE
				IF ((NEW.result & 16)::boolean) THEN
					ver := FALSE;
				ELSE
					ver := TRUE;
				END IF;
			END IF;

			RAISE NOTICE 'act: %, res:%, ver: %', act, res, ver;
			INSERT INTO pidhandlelog (url, suffix, action, result, tstamp, entrytype, entryid)
				VALUES (NEW.url, NEW.suffix, 
				act,
				res,
				NOW(),
				NEW.entrytype, NEW.entryid 			
			);
		END IF;
		RETURN NULL;
	ELSE
		IF TG_OP = 'INSERT' THEN
			RETURN NEW;
		ELSIF TG_OP = 'UPDATE' THEN
			IF (NEW.result & 1)::boolean AND OLD.createdon IS NULL THEN
				NEW.createdon = NOW();
			ELSIF (NEW.result & 1)::boolean AND (OLD.result & 4)::boolean THEN
				NEW.lastupdated := NOW();
			END IF;
			RETURN NEW;
		ELSE
			RETURN OLD;
		END IF;
	END IF;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION public.trfn_pidhandles()
  OWNER TO appdb;

CREATE TRIGGER rtr_10_pidhandles_before
  BEFORE UPDATE
  ON public.pidhandles
  FOR EACH ROW
  EXECUTE PROCEDURE public.trfn_pidhandles();

CREATE TRIGGER rtr_10_pidhandles_after
  AFTER INSERT OR UPDATE OR DELETE
  ON public.pidhandles  
  FOR EACH ROW
  EXECUTE PROCEDURE public.trfn_pidhandles();

CREATE OR REPLACE FUNCTION trfn_applications_pidhandle() RETURNS TRIGGER
AS
$$
DECLARE relcurs CURSOR (m_appid INT) FOR SELECT guid, series, release FROM app_releases WHERE state = 2 AND appid = m_appid; -- only fetch releases in production state
DECLARE vavcurs CURSOR (m_appid INT) FOR SELECT guid, id, published, archived FROM vapp_versions WHERE vappid IN (SELECT id FROM vapplications WHERE appid = m_appid);
DECLARE vav_suffix TEXT;
BEGIN
	IF  TG_WHEN = 'AFTER' THEN
		IF TG_OP = 'INSERT' THEN
			IF NEW.cname IS NULL THEN 
				NEW.cname = normalize_cname(NEW.name); 
			END IF;
			INSERT INTO pidhandles (url, suffix, entrytype, entryid, extras) VALUES (
				CASE NEW.metatype 
					WHEN 0 THEN
						'http://' || (SELECT data FROM config WHERE var = 'ui-host') || '/store/software/' || NEW.cname
					WHEN 1 THEN
						'http://' || (SELECT data FROM config WHERE var = 'ui-host') || '/store/vappliance/' || NEW.cname
				END,
				NEW.guid,
				CASE NEW.metatype 
					WHEN 0 THEN
						'software'::e_entity
					WHEN 1 THEN
						'vappliance'::e_entity
				END,
				NEW.id,
				(NEW::applications).handle_extras
			);
		ELSIF TG_OP = 'UPDATE' THEN
			IF NEW.deleted OR NEW.moderated THEN
				UPDATE pidhandles SET result = result | 8 WHERE suffix = NEW.guid::TEXT;
			ELSE
				IF NEW.cname IS NULL THEN 
					NEW.cname = NEW.guid; 
				END IF;
				IF (NEW::applications).handle_extras IS DISTINCT FROM (OLD::applications).handle_extras THEN
					UPDATE pidhandles SET 
						result = CASE 
							WHEN (result & 1)::BOOLEAN THEN result | 4 -- update::mintaction
							ELSE 0 -- mark it as unregistered
						END,
						url = CASE NEW.metatype 
							WHEN 0 THEN
								'http://' || (SELECT data FROM config WHERE var = 'ui-host') || '/store/software/' || NEW.cname
							WHEN 1 THEN
								'http://' || (SELECT data FROM config WHERE var = 'ui-host') || '/store/vappliance/' || NEW.cname									
						END,
						extras = (NEW::applications).handle_extras
					WHERE suffix = NEW.guid::TEXT;

					IF NEW.cname IS DISTINCT FROM OLD.cname THEN -- also update URLs for sw releases / va versions due to cname change
						IF NEW.metatype = 0 THEN
							FOR rel IN relcurs(NEW.id) LOOP						
								UPDATE pidhandles SET
									result = result | 4,
									url = 'http://' || (SELECT data FROM config WHERE var = 'ui-host') || '/store/software/' || NEW.cname || '/releases/' || rel.series || '/' || rel.release
								WHERE suffix = rel.guid;
							END LOOP;
						ELSIF NEW.metatype = 1 THEN
							FOR vav IN vavcurs(NEW.id) LOOP
								IF vav.published AND NOT vav.archived THEN
									vav_suffix = 'latest';
								ELSIF vav.published AND vav.archived THEN
									vav_suffix = 'previous/' || (vav.id)::TEXT;
								ELSE
									vav_suffix = NULL;
								END IF;
								IF NOT vav_suffix IS NULL THEN
									UPDATE pidhandles SET
										result = result | 4,
										url = 'http://' || (SELECT data FROM config WHERE var = 'ui-host') || '/store/vappliance/' || NEW.cname || '/vaversion/' || vav_suffix
									WHERE suffix = vav.guid;
								END IF;
							END LOOP;
						END IF;
					END IF;
				END IF;
			END IF;
		ELSIF TG_OP = 'DELETE' THEN
			UPDATE pidhandles SET result = result | 8 WHERE suffix = OLD.guid::TEXT;
		END IF;
		RETURN NULL;
	END IF;
END;
$$ LANGUAGE plpgsql;
ALTER FUNCTION trfn_applications_pidhandle() OWNER TO appdb;

CREATE TRIGGER rtr_99_applications_pidhandle
  AFTER INSERT OR UPDATE OR DELETE
  ON applications
  FOR EACH ROW EXECUTE PROCEDURE trfn_applications_pidhandle();

------------

ALTER TABLE app_releases ADD COLUMN guid UUID NOT NULL DEFAULT uuid_generate_v4();
ALTER TYPE e_entity ADD VALUE 'software_release';

CREATE OR REPLACE FUNCTION trfn_app_releases_pidhandle() RETURNS TRIGGER
AS
$$
BEGIN
	IF  TG_WHEN = 'AFTER' THEN
		IF TG_OP = 'INSERT' THEN
			IF NEW.state = 2 THEN -- production, ignore other states (1: unverified, 3: candidate)
				INSERT INTO pidhandles (url, suffix, entrytype, entryid) VALUES (
					'http://' || (SELECT data FROM config WHERE var = 'ui-host') || '/store/software/' || (SELECT cname FROM applications WHERE id = NEW.appid) || '/releases/' || NEW.series || '/' || NEW.release,
					NEW.guid,
					'software_release'::e_entity,
					NEW.id
				);
			END IF;
		ELSIF TG_OP = 'UPDATE' THEN
			IF (NEW.state, NEW.release, NEW.series) IS DISTINCT FROM (OLD.state, OLD.release, OLD.series) THEN
				IF NEW.state = 2 THEN
					UPDATE pidhandles SET
						url = 'http://' || (SELECT data FROM config WHERE var = 'ui-host') || '/store/software/' || (SELECT cname FROM applications WHERE id = NEW.appid) || '/releases/' || NEW.series || '/' || NEW.release,
						result = result | 4
					WHERE suffix = NEW.guid::TEXT;
				ELSE -- mark record for handle deletion if not in production anymore
					UPDATE pidhandles SET result = result | 8 WHERE suffix = NEW.guid::TEXT;
				END IF;
			END IF;
		ELSIF TG_OP = 'DELETE' THEN
			UPDATE pidhandles SET result = result | 8 WHERE suffix = OLD.guid::TEXT;
		END IF;
		RETURN NULL;
	END IF;
END;
$$ LANGUAGE plpgsql;
ALTER FUNCTION trfn_app_releases_pidhandle() OWNER TO appdb;

CREATE TRIGGER rtr_99_app_releases_pidhandle
  AFTER INSERT OR UPDATE OR DELETE
  ON app_releases
  FOR EACH ROW EXECUTE PROCEDURE trfn_app_releases_pidhandle();

-------------

ALTER TYPE e_entity ADD VALUE 'vappliance_version';

CREATE OR REPLACE FUNCTION trfn_vapp_versions_pidhandle() RETURNS TRIGGER
AS
$$
DECLARE vav_suffix TEXT;
BEGIN
	IF  TG_WHEN = 'AFTER' THEN
		IF TG_OP = 'INSERT' THEN
			IF NEW.puſblished AND NOT NEW.archived THEN 
				vav_suffix = 'latest';
			ELSIF NEW.published AND NEW.archived THEN 
				vav_suffix = 'previous/' || (NEW.id)::TEXT;
			-- ELSE 
				-- IGNORE all other cases
			END IF;
			INSERT INTO pidhandles (url, suffix, entrytype, entryid) VALUES (
				'http://' || (SELECT data FROM config WHERE var = 'ui-host') || '/store/vappliance/' || (SELECT cname FROM applications WHERE id = (SELECT appid FROM vapplications WHERE id = NEW.vappid)) || '/vaversion/' || vav_suffix,
				NEW.guid,
				'vappliance_version'::e_entity,
				NEW.id
			);
		ELSIF TG_OP = 'UPDATE' THEN
			IF NEW.published AND NOT NEW.archived THEN 
				vav_suffix = 'latest';
			ELSIF NEW.published AND NEW.archived THEN 
				vav_suffix = 'previous/' || (NEW.id)::TEXT;
			-- ELSE 
				-- IGNORE all other cases
			END IF;
			UPDATE pidhandles SET 
				url = 'http://' || (SELECT data FROM config WHERE var = 'ui-host') || '/store/vappliance/' || (SELECT cname FROM applications WHERE id = (SELECT appid FROM vapplications WHERE id = NEW.vappid)) || '/vaversion/' || vav_suffix,
				result = result | 4 
			WHERE suffix = NEW.guid::TEXT;
		ELSIF TG_OP = 'DELETE' THEN
			UPDATE pidhandles SET result = result | 8 WHERE suffix = OLD.guid::TEXT;
		END IF;
		RETURN NULL;
	END IF;
END;
$$ LANGUAGE plpgsql;
ALTER FUNCTION trfn_vapp_versions_pidhandle() OWNER TO appdb;

CREATE TRIGGER rtr_99_vapp_versions_pidhandle
  AFTER INSERT OR UPDATE OR DELETE
  ON vapp_versions
  FOR EACH ROW EXECUTE PROCEDURE trfn_vapp_versions_pidhandle();

---------------
INSERT INTO config (var, data) VALUES ('handleprefix', '21.T12995');

CREATE OR REPLACE FUNCTION public.pidhandle(applications)
 RETURNS text
 LANGUAGE sql
AS $function$
SELECT 
	COALESCE((SELECT data FROM config WHERE var = 'handleprefix'), '') || '/' || suffix 
FROM pidhandles 
WHERE 
	(entrytype = 'software') AND 
	(entryid = $1.id) AND 
	((result & 1)::BOOLEAN) AND -- marked as registered
	(NOT ((result & 8)::BOOLEAN)) -- not marked as to-be-deleted
$function$;

----------------------

-- Function: public.__app_to_xml(integer[])

-- DROP FUNCTION public.__app_to_xml(integer[]);

CREATE OR REPLACE FUNCTION public.__app_to_xml(m_id integer[])
  RETURNS SETOF xml AS
$BODY$
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
applications.pidhandle,
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
apps.pidhandle AS "handle",
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
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION public.__app_to_xml(integer[])
  OWNER TO appdb;

CREATE OR REPLACE FUNCTION public.app_to_xml_list(ids integer[])
  RETURNS SETOF xml AS
$BODY$
SELECT 
XMLELEMENT(
name "application:application",
XMLATTRIBUTES(
applications.id AS id, applications.pidhandle AS handle,applications.rating, applications.ratingcount AS "ratingCount",
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

CREATE OR REPLACE FUNCTION public.app_to_xml_ext(
    mid integer,
    muserid integer DEFAULT NULL::integer)
  RETURNS xml AS
$BODY$
WITH target_relations AS(
	SELECT $1 as id, xmlagg(x) as "xml" FROM target_relations_to_xml((SELECT guid FROM applications WHERE id = $1)) as x
),
subject_relations AS (
	SELECT $1 as id, xmlagg(x) as "xml" FROM subject_relations_to_xml((SELECT guid FROM applications WHERE id = $1)) as x
) SELECT xmlelement(name "application:application", xmlattributes(
applications.id as id,
applications.pidhandle as handle,
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
INNER JOIN va_provider_images ON va_provider_images.vmiinstanceid = vaviews.vmiinstanceid AND vaviews.vmiinstanceid = va_provider_images.good_vmiinstanceid
WHERE applications.id = $1) AS vaprovidercount,
CASE WHEN applications.metatype = 2 THEN (SELECT COUNT(DISTINCT(va_provider_images.va_provider_id)) FROM contexts
INNER JOIN context_script_assocs ON context_script_assocs.contextid = contexts.id
INNER JOIN contextscripts AS cs ON cs.id = context_script_assocs.scriptid
INNER JOIN vmiinstance_contextscripts AS vcs ON vcs.contextscriptid = cs.id
INNER JOIN vaviews ON vaviews.vmiinstanceid = vcs.vmiinstanceid
INNER JOIN va_provider_images ON va_provider_images.vmiinstanceid = vaviews.vmiinstanceid AND vaviews.vmiinstanceid = va_provider_images.good_vmiinstanceid
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
$BODY$
  LANGUAGE sql VOLATILE
  COST 100;
ALTER FUNCTION public.app_to_xml_ext(integer, integer)
  OWNER TO appdb;

CREATE OR REPLACE FUNCTION public.app_to_json(mid integer)
  RETURNS text AS
$BODY$
SELECT '{"application": {' ||
	'"id": ' || to_json(applications.id) || ', ' ||
	'"handle": ' || to_json(applications.pidhandle) || ', ' ||
	'"name": ' || to_json(applications.name::text) || ', ' || 
	'"cname": ' || to_json(applications.cname::text) || ', ' || 
	'"description": ' || COALESCE(to_json(applications.description::text), 'null') ||', ' || 
	'"rating": ' || COALESCE(to_json(applications.rating), 'null') || ', ' ||
	'"tool": '|| to_json(applications.tool) || ', ' || 
	'"discipline": [' || array_to_string(array_agg(DISTINCT '{' || 
		'"id": ' || to_json(disciplines.id) || ', ' || 
		'"name": ' || to_json(disciplines.name::text) || '}'),',') || '], ' || 
	'"category": [' || array_to_string(array_agg(DISTINCT '{' || 
		'"id": '|| to_json(categories.id) || ', ' || 
		'"name": ' || to_json(categories.name::text) || ', ' || 
		'"isPrimary": ' || to_json(appcategories.isprimary) || ', ' || 
		'"parentid": ' || COALESCE(to_json(categories.parentid::text), 'null') || '}'),',') || ']}}'
FROM public.applications
LEFT OUTER JOIN public.disciplines ON public.disciplines.id = ANY(public.applications.disciplineid)
LEFT OUTER JOIN public.appcategories ON public.appcategories.categoryid = ANY(public.applications.categoryid) AND public.appcategories.appid = $1
LEFT OUTER JOIN public.categories ON public.categories.id = public.appcategories.categoryid
WHERE public.applications.id = $1
GROUP BY public.applications.id, public.applications.name, public.applications.description, public.applications.rating, public.applications.tool;
$BODY$
  LANGUAGE sql STABLE
  COST 100;
ALTER FUNCTION public.app_to_json(integer)
  OWNER TO appdb;

ALTER TABLE vapp_versions ADD COLUMN uguid uuid NOT NULL DEFAULT uuid_generate_v4();

CREATE OR REPLACE VIEW pidhandle_latest_states AS
SELECT 
	entrytype, 
	entryid, 
	last_action_id,
	last_action,
	last_action_result,
	parent_action_id
FROM (
	SELECT 
		r,
		id AS last_action_id,
		entrytype, 
		entryid, 
		action AS last_action,
		result AS last_action_result,
		lead(id) OVER (PARTITION BY suffix ORDER BY tstamp DESC) AS parent_action_id
	FROM (
		SELECT * FROM (
			SELECT 
				ROW_NUMBER() OVER (PARTITION BY suffix ORDER BY tstamp DESC) AS r, 
				id,
				suffix,
				entrytype, 
				entryid, 
				tstamp,
				action,
				result
			FROM pidhandlelog
		) AS t
		WHERE (t.r = 1) OR (t.r > 1 AND t.action = 'register' AND t.result IN ('successverified', 'success'))
	) AS tt
) AS ttt
WHERE ttt.r = 1
ORDER BY entrytype, entryid;
ALTER VIEW pidhandle_latest_states OWNER TO appdb;

INSERT INTO version (major,minor,revision,notes)
        SELECT 8, 19, 0, E'Add support for Handle PIDs'
        WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=19 AND revision=0);

COMMIT;
