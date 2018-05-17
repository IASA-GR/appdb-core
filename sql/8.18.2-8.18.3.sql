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
Previous version: 8.18.2
New version: 8.18.3
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

CREATE OR REPLACE FUNCTION public.diff(TEXT, TEXT)
  RETURNS text AS
$BODY$
use Text::Diff;
my ($act) = @_;
return diff(\$_[0],\$_[1], { STYLE => "Unified" });
$BODY$
  LANGUAGE plperl STABLE
  COST 100;
ALTER FUNCTION public.diff(TEXT, TEXT)
  OWNER TO appdb;

CREATE OR REPLACE FUNCTION appdb_xmlns() RETURNS TEXT AS
$$
SELECT
	array_to_string(array_agg('xmlns:' || x || '="http://appdb.egi.eu/api/1.0/' || x ||'"'), ' ') AS xmlns
FROM UNNEST(ARRAY[
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
]) AS x
$$ LANGUAGE sql IMMUTABLE;
ALTER FUNCTION appdb_xmlns() OWNER TO appdb;

CREATE OR REPLACE FUNCTION public.__openaire(applications)
  RETURNS xml AS
$BODY$
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
                        -- UPDATE cache.appxmlcache SET openairexml = x::XML WHERE id = $1.id;
--                END IF;
                RETURN x;
        END IF;
END;
$BODY$
  LANGUAGE plpgsql STABLE
  COST 100;
ALTER FUNCTION public.__openaire(applications)
  OWNER TO appdb;

CREATE OR REPLACE FUNCTION public.__oaidc(applications)
  RETURNS xml AS
$BODY$
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
                                        /*xsltproc(
                                                COALESCE((SELECT data FROM config WHERE var = 'oaidc_xslt'), '/var/www/html/appdb/application/configs/api/1.0/xslt/oai_dc.xsl'),
                                                ('<appdb:appdb xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" ' ||
                                                E'xmlns:appdb="http://appdb.egi.eu/api/1.0/appdb" xmlns:application="http://appdb.egi.eu/api/1.0/application" xmlns:discipline="http://appdb.egi.eu/api/1.0/discipline" xmlns:category="http://appdb.egi.eu/api/1.0/category" xmlns:dissemination="http://appdb.egi.eu/api/1.0/dissemination" xmlns:filter="http://appdb.egi.eu/api/1.0/filter" xmlns:history="http://appdb.egi.eu/api/1.0/history" xmlns:logistics="http://appdb.egi.eu/api/1.0/logistics" xmlns:resource="http://appdb.egi.eu/api/1.0/resource" xmlns:middleware="http://appdb.egi.eu/api/1.0/middleware" xmlns:person="http://appdb.egi.eu/api/1.0/person" xmlns:permission="http://appdb.egi.eu/api/1.0/permission" xmlns:privilege="http://appdb.egi.eu/api/1.0/privilege" xmlns:publication="http://appdb.egi.eu/api/1.0/publication" xmlns:rating="http://appdb.egi.eu/api/1.0/rating" xmlns:ratingreport="http://appdb.egi.eu/api/1.0/ratingreport" xmlns:regional="http://appdb.egi.eu/api/1.0/regional" xmlns:user="http://appdb.egi.eu/api/1.0/user" xmlns:vo="http://appdb.egi.eu/api/1.0/vo" xmlns:virtualization="http://appdb.egi.eu/api/1.0/virtualization" xmlns:contextualization="http://appdb.egi.eu/api/1.0/contextualization" xmlns:license="http://appdb.egi.eu/api/1.0/license" xmlns:provider="http://appdb.egi.eu/api/1.0/provider" xmlns:provider_template="http://appdb.egi.eu/api/1.0/provider_template" xmlns:classification="http://appdb.egi.eu/api/1.0/classification" xmlns:site="http://appdb.egi.eu/api/1.0/site" xmlns:siteservice="http://appdb.egi.eu/api/1.0/site" xmlns:entity="http://appdb.egi.eu/api/1.0/entity" xmlns:organization="http://appdb.egi.eu/api/1.0/organization" xmlns:project="http://appdb.egi.eu/api/1.0/project" xmlns:dataset="http://appdb.egi.eu/api/1.0/dataset" ' ||
                                                E'datatype="application" type="entry" host="' || (SELECT data FROM config WHERE var = 'ui-host') || '" apihost="' || (SELECT data FROM config WHERE var = 'api-host') ||
                                                '" cacheState="0" permsState="' || (SELECT data FROM config WHERE var = 'permissions_cache_dirty') || '" requestedOn="###REQUESTED_ON###" deliveredOn="###DELIVERED_ON###" processingTime="###PROCESSING_TIME###" version="1.0">' ||
                                                app_to_xml_ext($1.id) || '</appdb:appdb>')::XML
                                        )::TEXT,*/
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
                        -- UPDATE cache.appxmlcache SET openairexml = x::XML WHERE id = $1.id;
--                END IF;
                RETURN x;
        END IF;
END;
$BODY$
  LANGUAGE plpgsql STABLE
  COST 100;
ALTER FUNCTION public.__oaidc(applications)
  OWNER TO appdb;

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 18, 3, E'Use plxslt instead of xsltproc inside OAI-PMH functions'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=18 AND revision=3);

COMMIT;
