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
Previous version: 8.12.25
New version: 8.12.26
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

DROP MATERIALIZED VIEW site_services_xml;
DROP MATERIALIZED VIEW site_service_images_xml;
DROP VIEW va_provider_images;
ALTER TABLE __va_provider_images RENAME TO va_provider_images;

CREATE OR REPLACE FUNCTION good_vmiinstanceid(va_provider_images)
  RETURNS integer AS
$BODY$
	SELECT CASE WHEN goodid IS NULL THEN $1.vmiinstanceid ELSE goodid END FROM (
			SELECT max(t1.id) as goodid FROM vmiinstances AS t1
			INNER JOIN vmiinstances AS t2 ON t1.checksum = t2.checksum AND t1.guid = t2.guid AND t2.id = $1.vmiinstanceid
			INNER JOIN vapplists ON t1.id = vapplists.vmiinstanceid
			INNER JOIN vapp_versions ON vapplists.vappversionid = vapp_versions.id 
			WHERE vapp_versions.published
	) AS t
$BODY$
  LANGUAGE sql STABLE
  COST 100;
ALTER FUNCTION good_vmiinstanceid(va_provider_images)
  OWNER TO appdb;


CREATE MATERIALIZED VIEW site_services_xml
AS 
 SELECT va_providers.sitename,
    XMLELEMENT(NAME "site:service", XMLATTRIBUTES('occi' AS type, va_providers.id AS id, va_providers.hostname AS host, count(DISTINCT va_provider_images.good_vmiinstanceid) AS instances, va_providers.beta AS beta, va_providers.in_production AS in_production), xmlagg(XMLELEMENT(NAME "siteservice:image", XMLATTRIBUTES(va_provider_images.vmiinstanceid AS id, va_provider_images.good_vmiinstanceid AS goodid)))) AS x
   FROM va_providers
     LEFT JOIN va_provider_images ON va_provider_images.va_provider_id = va_providers.id AND (va_provider_images.vmiinstanceid IN ( SELECT vaviews.vmiinstanceid
           FROM vaviews))
  GROUP BY va_providers.id, va_providers.hostname, va_providers.beta, va_providers.in_production, va_providers.sitename
WITH DATA;

ALTER TABLE site_services_xml
  OWNER TO appdb;

CREATE INDEX idx_site_services_xml_sitename
  ON site_services_xml
  USING btree
  (sitename COLLATE pg_catalog."default");

CREATE MATERIALIZED VIEW site_service_images_xml AS 
 SELECT siteimages.va_provider_id,
    xmlagg(siteimages.x) AS xmlagg
   FROM ( SELECT va_providers.id AS va_provider_id,
            XMLELEMENT(NAME "siteservice:image", XMLATTRIBUTES(__vaviews.vappversionid AS versionid, __vaviews.va_version_archived AS archived, __vaviews.va_version_enabled AS enabled, __vaviews.va_version_expireson AS expireson,
                CASE
                    WHEN __vaviews.va_version_expireson <= now() THEN true
                    ELSE false
                END AS isexpired, __vaviews.imglst_private AS private, __vaviews.vmiinstanceid AS id, __vaviews.vmiinstance_guid AS identifier, __vaviews.vmiinstance_version AS version, va_provider_images.good_vmiinstanceid AS goodid), vmiflavor_hypervisor_xml.hypervisor::text::xml, XMLELEMENT(NAME "virtualization:os", XMLATTRIBUTES(oses.id AS id, __vaviews.osversion AS version, oses.os_family_id AS family_id), oses.name), XMLELEMENT(NAME "virtualization:arch", XMLATTRIBUTES(archs.id AS id), archs.name), XMLELEMENT(NAME "virtualization:format", __vaviews.format), XMLELEMENT(NAME "virtualization:url", XMLATTRIBUTES(
                CASE
                    WHEN __vaviews.imglst_private = true THEN 'true'::text
                    ELSE NULL::text
                END AS protected),
                CASE
                    WHEN __vaviews.imglst_private = false THEN __vaviews.uri
                    ELSE NULL::text
                END), XMLELEMENT(NAME "virtualization:size", XMLATTRIBUTES(
                CASE
                    WHEN __vaviews.imglst_private = true THEN 'true'::text
                    ELSE NULL::text
                END AS protected),
                CASE
                    WHEN __vaviews.imglst_private = false THEN __vaviews.size
                    ELSE NULL::bigint
                END), XMLELEMENT(NAME "siteservice:mpuri", ((((('//'::text || (( SELECT config.data
                   FROM config
                  WHERE config.var = 'ui-host'::text))) || '/store/vm/image/'::text) || __vaviews.vmiinstance_guid::text) || ':'::text) || va_provider_images.good_vmiinstanceid::text) || '/'::text), array_to_string(array_agg(DISTINCT site_service_imageocciids_to_xml(va_provider_images.va_provider_id, va_provider_images.vmiinstanceid, va_provider_images.vowide_vmiinstanceid)::text), ''::text)::xml, XMLELEMENT(NAME "application:application", XMLATTRIBUTES(__vaviews.appid AS id, __vaviews.appcname AS cname, __vaviews.imglst_private AS imagelistsprivate, applications.deleted AS deleted, applications.moderated AS moderated), XMLELEMENT(NAME "application:name", __vaviews.appname)), vmiinst_cntxscripts_to_xml(__vaviews.vmiinstanceid)) AS x
           FROM va_providers
             JOIN va_provider_images va_provider_images ON va_provider_images.va_provider_id = va_providers.id
             JOIN vaviews __vaviews ON __vaviews.vmiinstanceid = va_provider_images.vmiinstanceid
             JOIN applications ON applications.id = __vaviews.appid
             LEFT JOIN vmiflavor_hypervisor_xml ON vmiflavor_hypervisor_xml.vmiflavourid = __vaviews.vmiflavourid
             LEFT JOIN archs ON archs.id = __vaviews.archid
             LEFT JOIN oses ON oses.id = __vaviews.osid
             LEFT JOIN vmiformats ON vmiformats.name::text = __vaviews.format
          WHERE __vaviews.va_version_published
          GROUP BY va_providers.id, __vaviews.vappversionid, __vaviews.va_version_archived, __vaviews.va_version_enabled, __vaviews.va_version_expireson, __vaviews.imglst_private, __vaviews.vmiinstanceid, __vaviews.vmiinstance_guid, __vaviews.vmiinstance_version, va_provider_images.good_vmiinstanceid, vmiflavor_hypervisor_xml.hypervisor::text, oses.id, archs.id, __vaviews.osversion, __vaviews.format, __vaviews.uri, __vaviews.size, __vaviews.appid, __vaviews.appcname, __vaviews.appname, applications.deleted, applications.moderated) siteimages
  GROUP BY siteimages.va_provider_id
WITH DATA;

ALTER TABLE site_service_images_xml
  OWNER TO appdb;

-- Function: app_to_xml_ext(integer, integer)

-- DROP FUNCTION app_to_xml_ext(integer, integer);

CREATE OR REPLACE FUNCTION app_to_xml_ext(
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
ALTER FUNCTION app_to_xml_ext(integer, integer)
  OWNER TO appdb;

CREATE OR REPLACE FUNCTION vapp_image_providers_to_xml(_appid integer)
  RETURNS SETOF xml AS
$BODY$
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
					vos.name as voname,
					va_provider_images.vmiinstanceid as vmiinstanceid
				)
			)::text
		),'')::xml
)
FROM 
	applications
	INNER JOIN vaviews ON vaviews.appid = applications.id
	INNER JOIN va_provider_images AS va_provider_images ON va_provider_images.vmiinstanceid = vaviews.vmiinstanceid
	LEFT OUTER JOIN hypervisors ON hypervisors.vmiflavourid = vaviews.vmiflavourid
	LEFT OUTER JOIN archs ON archs.id = vaviews.archid
	LEFT OUTER JOIN oses ON oses.id = vaviews.osid
	LEFT OUTER JOIN vmiformats ON vmiformats.name::text = vaviews.format
	LEFT OUTER JOIN app_vos ON app_vos.appid = applications.id
	LEFT OUTER JOIN vowide_image_list_images ON vowide_image_list_images.id = va_provider_images.vowide_vmiinstanceid
	LEFT OUTER JOIN vowide_image_lists ON vowide_image_lists.id = vowide_image_list_images.vowide_image_list_id AND (vowide_image_lists.state::text = 'published' OR vowide_image_lists.state::text = 'obsolete')
	LEFT OUTER JOIN vos ON vos.id = vowide_image_lists.void
WHERE  
	vaviews.vmiinstanceid = va_provider_images.good_vmiinstanceid AND
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
	vmiformats.id;
$BODY$
  LANGUAGE sql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION vapp_image_providers_to_xml(integer)
  OWNER TO appdb;

CREATE OR REPLACE FUNCTION vappliance_site_count(appid integer) RETURNS integer
    LANGUAGE sql
    AS $_$
SELECT COUNT(p.sitename)::INTEGER FROM (
        SELECT vp.sitename  FROM
        va_providers AS vp
        INNER JOIN va_provider_images AS vi ON vi.va_provider_id = vp.id
        INNER JOIN vaviews AS vv ON vv.vmiinstanceid = vi.vmiinstanceid
        WHERE vv.appid = $1 AND vv.va_version_published = true AND vv.va_version_enabled = true AND vv.vmiinstanceid = vi.good_vmiinstanceid
        GROUP BY vp.sitename
) AS p
$_$;

-- Function: count_site_matches(text, text, boolean)

-- DROP FUNCTION count_site_matches(text, text, boolean);

CREATE OR REPLACE FUNCTION count_site_matches(
    itemname text,
    cachetable text,
    private boolean DEFAULT false)
  RETURNS SETOF record AS
$BODY$
DECLARE q TEXT;
DECLARE allitems INT;
BEGIN
	IF itemname = 'country' THEN
		q := 'SELECT countries.name::TEXT AS count_text, COUNT(DISTINCT sites.id) AS count, countries.id AS count_id FROM ' || cachetable || ' AS sites LEFT JOIN countries ON countries.id = sites.countryid';
	ELSIF itemname = 'discipline' THEN
		q := 'SELECT disciplines.name::TEXT AS count_text, COUNT(DISTINCT sites.id) AS count, disciplines.id AS count_id FROM ' || cachetable || ' AS sites LEFT JOIN va_providers ON va_providers.sitename = sites.name
		LEFT JOIN va_provider_images AS va_provider_images ON va_provider_images.va_provider_id = va_providers.id
		LEFT JOIN vaviews ON vaviews.vmiinstanceid = va_provider_images.vmiinstanceid
		LEFT JOIN applications ON applications.id = vaviews.appid
		LEFT JOIN appdisciplines ON appdisciplines.appid = applications.id
		LEFT JOIN disciplines ON disciplines.id = appdisciplines.disciplineid';
		-- q := 'SELECT disciplines.name::TEXT AS count_text, COUNT(DISTINCT sites.id) AS count, disciplines.id AS count_id FROM ' || cachetable || ' AS sites' || CASE WHEN NOT private THEN ' LEFT JOIN app_vos ON app_vos.appid = applications.id LEFT JOIN vos ON vos.id = app_vos.void AND vos.deleted IS FALSE' ELSE '' END || ' LEFT JOIN appdisciplines ON appdisciplines.appid = applications.id LEFT JOIN disciplines ON disciplines.id = appdisciplines.disciplineid' || CASE WHEN NOT private THEN ' OR disciplines.id = vos.domainid' ELSE '' END;
	ELSIF itemname = 'category' THEN
		q := 'SELECT categories.name::TEXT AS count_text, COUNT(DISTINCT sites.id) AS count, categories.id AS count_id
		FROM ' || cachetable || ' AS sites LEFT JOIN va_providers ON va_providers.sitename = sites.name LEFT JOIN va_provider_images AS va_provider_images ON va_provider_images.va_provider_id = va_providers.id
		LEFT JOIN vaviews ON vaviews.vmiinstanceid = va_provider_images.vmiinstanceid LEFT JOIN applications ON applications.id = vaviews.appid LEFT JOIN categories ON categories.id = ANY(applications.categoryid)';
		--q := 'SELECT categories.name::TEXT AS count_text, COUNT(DISTINCT applications.id) AS count, categories.id AS count_id FROM ' || cachetable || ' AS applications LEFT JOIN categories ON categories.id = ANY(applications.categoryid)';
	ELSIF itemname = 'arch' THEN
		q := 'SELECT archs.name::TEXT AS count_text, COUNT(DISTINCT sites.id) AS count, archs.id AS count_id FROM ' || cachetable || ' AS sites LEFT JOIN va_providers ON va_providers.sitename = sites.name
		LEFT JOIN va_provider_images AS va_provider_images ON va_provider_images.va_provider_id = va_providers.id
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
		LEFT JOIN va_provider_images AS va_provider_images ON va_provider_images.va_provider_id = va_providers.id
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
		LEFT JOIN va_provider_images AS va_provider_images ON va_provider_images.va_provider_id = va_providers.id
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
		LEFT JOIN va_provider_images AS va_provider_images ON va_provider_images.va_provider_id = va_providers.id
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
		LEFT JOIN va_provider_images AS va_provider_images ON va_provider_images.va_provider_id = va_providers.id AND va_provider_images.vowide_vmiinstanceid IS NOT NULL
		LEFT JOIN vowide_image_list_images ON vowide_image_list_images.ID = va_provider_images.vowide_vmiinstanceid and vowide_image_list_images.state = ''up-to-date''::e_vowide_image_state
		LEFT JOIN vowide_image_lists ON vowide_image_lists.id = vowide_image_list_images.vowide_image_list_id AND vowide_image_list_images.state <> ''draft''::e_vowide_image_state
		LEFT JOIN vos ON vos.id = vowide_image_lists.void AND vos.deleted IS FALSE';
	ELSIF itemname = 'middleware' THEN
		q := 'SELECT middlewares.name::TEXT AS count_text, COUNT(DISTINCT sites.id) AS count, middlewares.id AS count_id FROM ' || cachetable || ' AS sites
		LEFT JOIN va_providers ON va_providers.sitename = sites.name
		LEFT JOIN va_provider_images AS va_provider_images ON va_provider_images.va_provider_id = va_providers.id
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
		LEFT JOIN va_provider_images AS va_provider_images ON va_provider_images.va_provider_id = va_providers.id
		LEFT JOIN vaviews ON vaviews.vmiinstanceid = va_provider_images.vmiinstanceid';
	ELSE
		RAISE NOTICE 'Unknown site property requested for logistics counting: %', itemname;
		RETURN;
	END IF;
	RETURN QUERY EXECUTE 'SELECT count_text, count, count_id::text FROM (' || q || ' GROUP BY count_text, count_id) AS t WHERE NOT count_text IS NULL';
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION count_site_matches(text, text, boolean)
  OWNER TO appdb;
COMMENT ON FUNCTION count_site_matches(text, text, boolean) IS 'not to be called directly; used by site_logistics function';

CREATE OR REPLACE FUNCTION good_vmiinstanceid(va_provider_images)
  RETURNS integer AS
$BODY$
	SELECT CASE WHEN goodid IS NULL THEN $1.vmiinstanceid ELSE goodid END FROM (
			SELECT max(t1.id) as goodid FROM vmiinstances AS t1
			INNER JOIN vmiinstances AS t2 ON t1.checksum = t2.checksum AND t1.guid = t2.guid AND t2.id = $1.vmiinstanceid
			INNER JOIN vapplists ON t1.id = vapplists.vmiinstanceid
			INNER JOIN vapp_versions ON vapplists.vappversionid = vapp_versions.id 
			WHERE vapp_versions.published
	) AS t
$BODY$
  LANGUAGE sql STABLE
  COST 100;
ALTER FUNCTION good_vmiinstanceid(va_provider_images)
  OWNER TO appdb;

ALTER TABLE app_order_hack ADD CONSTRAINT pk_app_order_hack PRIMARY KEY (appid);
ALTER TABLE va_provider_images ADD CONSTRAINT fk_va_provider_images_vmiinstances_1 FOREIGN KEY (vmiinstanceid) REFERENCES vmiinstances(id);
CREATE INDEX idx_va_provider_images_vmiinstanceid ON va_provider_images(vmiinstanceid);
CREATE INDEX idx_va_provider_images_vowide_vmiinstanceid ON va_provider_images(vowide_vmiinstanceid);
CREATE INDEX idx_vapplications_appid ON vapplications(appid);
CREATE INDEX idx_vapplists_vappversionid ON vapplists(vappversionid);
CREATE INDEX idx_vapplists_vmiinstanceid ON vapplists(vmiinstanceid);
CREATE INDEX idx_context_script_assocs_contextid ON context_script_assocs(contextid);
CREATE INDEX idx_context_script_assocs_scriptid ON context_script_assocs(scriptid);
CREATE INDEX idx_vowide_image_list_images_listid ON vowide_image_list_images(vowide_image_list_id);
CREATE INDEX idx_vowide_image_list_images_vapplistid ON vowide_image_list_images(vapplistid);
CREATE INDEX idx_vowide_image_lists_void ON vowide_image_lists(void);
CREATE INDEX idx_vowide_image_lists_state ON vowide_image_lists(state);
CREATE INDEX idx_vowide_image_lists_state_pub_or_obs ON vowide_image_lists(state) WHERE state = 'published' OR state = 'obsolete';
CREATE INDEX idx_vmiinstance_contextscripts_vmiinstanceid ON vmiinstance_contextscripts(vmiinstanceid);
CREATE INDEX idx_vmiinstance_contextscripts_contextscriptid ON vmiinstance_contextscripts(contextscriptid);
CREATE INDEX idx_vmiinstances_checksum ON vmiinstances(checksum);
CREATE INDEX idx_vmiinstances_guid ON vmiinstances(guid);
CREATE INDEX idx_applications_metatype ON applications(metatype);

CREATE OR REPLACE FUNCTION swapp_image_providers_to_xml(_appid integer)
  RETURNS SETOF xml AS
$BODY$
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
	INNER JOIN va_provider_images ON va_provider_images.good_vmiinstanceid = vcs.vmiinstanceid
	INNER JOIN vaviews ON vaviews.vmiinstanceid = vcs.vmiinstanceid
	INNER JOIN applications ON applications.id = vaviews.appid
	LEFT OUTER JOIN vmiflavor_hypervisor_xml AS hypervisors ON hypervisors.vmiflavourid = vaviews.vmiflavourid
	LEFT OUTER JOIN archs ON archs.id = vaviews.archid
	LEFT OUTER JOIN oses ON oses.id = vaviews.osid
	-- LEFT OUTER JOIN vmiformats ON vmiformats.name = vaviews.format
	LEFT OUTER JOIN app_vos ON app_vos.appid = applications.id
	LEFT OUTER JOIN vowide_image_list_images ON vowide_image_list_images.id = va_provider_images.vowide_vmiinstanceid
	LEFT OUTER JOIN vowide_image_lists ON vowide_image_lists.id = vowide_image_list_images.vowide_image_list_id AND (vowide_image_lists.state = 'published' OR vowide_image_lists.state = 'obsolete')
WHERE  
	vaviews.va_version_published 
	-- AND contexts.appid = $1
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
	-- vmiformats.id,
	app_vos.appid
$BODY$
  LANGUAGE sql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION swapp_image_providers_to_xml(integer)
  OWNER TO appdb;

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 12, 26, E'Performance improvements'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=12 AND revision=26);

COMMIT;
