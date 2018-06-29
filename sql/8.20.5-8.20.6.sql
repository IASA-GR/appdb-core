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
Previous version: 8.20.5
New version: 8.20.6
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

DROP VIEW IF EXISTS app_popularities;

CREATE MATERIALIZED VIEW app_popularities AS
SELECT appid, ROUND(count_pct, 2) AS popularity FROM (
	SELECT
		appid,
		count,
		100 * COUNT/(SUM(COUNT) OVER ())::NUMERIC AS count_pct
	FROM (
		SELECT appid, COUNT(1)
		FROM app_api_log
		GROUP BY appid
	) AS t
) AS tt
WITH DATA;
ALTER MATERIALIZED VIEW app_popularities OWNER TO appdb;
CREATE UNIQUE INDEX idx_app_popularities_appid ON app_popularities(appid);

CREATE OR REPLACE FUNCTION app_to_xml_ext(mid integer, muserid integer DEFAULT NULL::integer) RETURNS XML AS
$$
SELECT
	XMLELEMENT(
		NAME "application:application",
		XMLATTRIBUTES(
			applications.id as id,
			applications.pidhandle AS "handle",
			applications.tool as tool,
			applications.rating as rating,
			applications.ratingcount as "ratingCount",
			app_popularities.popularity AS "popularity",
			(SELECT vappliance_site_count(applications.id) ) as "sitecount",
			applications.cname as "cname",
			applications.metatype,
			CASE WHEN metatype = 1 THEN
				(SELECT format FROM vaviews WHERE appid = $1 LIMIT 1)
			ELSE
				NULL
			END AS "vaformat",
			applications.guid as guid,
			CASE WHEN applications.metatype = 2 THEN (
				SELECT COUNT(context_script_assocs.scriptid)
				FROM context_script_assocs
				INNER JOIN contexts ON contexts.id = context_script_assocs.contextid
				WHERE contexts.appid = applications.id
			) ELSE
				(SELECT relcount FROM app_release_count WHERE appid = applications.id)
			END AS relcount,
			hitcounts.count as hitcount,
			CASE WHEN metatype = 1 THEN (
				SELECT COUNT(DISTINCT(va_provider_images.va_provider_id))
				FROM applications
				INNER JOIN vaviews ON vaviews.appid = applications.id
				INNER JOIN va_provider_images ON va_provider_images.vmiinstanceid = vaviews.vmiinstanceid AND vaviews.vmiinstanceid = va_provider_images.good_vmiinstanceid
				WHERE applications.id = $1
			) ELSE
				0
			END AS vaprovidercount,
			CASE WHEN applications.metatype = 2 THEN (
				SELECT COUNT(DISTINCT(va_provider_images.va_provider_id))
				FROM contexts
				INNER JOIN context_script_assocs ON context_script_assocs.contextid = contexts.id
				INNER JOIN contextscripts AS cs ON cs.id = context_script_assocs.scriptid
				INNER JOIN vmiinstance_contextscripts AS vcs ON vcs.contextscriptid = cs.id
				INNER JOIN vaviews ON vaviews.vmiinstanceid = vcs.vmiinstanceid
				INNER JOIN va_provider_images ON va_provider_images.vmiinstanceid = vaviews.vmiinstanceid AND vaviews.vmiinstanceid = va_provider_images.good_vmiinstanceid
				INNER JOIN applications AS apps ON apps.id = vaviews.appid
				WHERE apps.metatype = 1 AND contexts.appid = $1
			) ELSE
				0
			END AS swprovidercount,
			applications.tagpolicy as "tagPolicy",
			lastupdated BETWEEN NOW() - (SELECT data FROM config WHERE var='app_validation_period' LIMIT 1)::INTERVAL AND NOW() as "validated",
			CASE WHEN applications.moderated IS TRUE THEN 'true' END as "moderated",
			CASE WHEN applications.deleted IS TRUE THEN 'true' END as "deleted",
			CASE WHEN (NOT $2 IS NULL) THEN
				EXISTS (SELECT 1 FROM appbookmarks WHERE appid = applications.id AND researcherid = $2)
			END  as "bookmarked"
		), -- END APPLICATION:APPLICATION XML ATTRIBUTES
		XMLELEMENT(NAME "application:name", applications.name),
		XMLELEMENT(NAME "application:description", applications.description),
		XMLELEMENT(NAME "application:abstract", applications.abstract),
		XMLELEMENT(NAME "application:addedOn", applications.dateadded),
		XMLELEMENT(NAME "application:lastUpdated", applications.lastupdated),
		owners."owner",
		actors."actor",
		category_to_xml(applications.categoryid, applications.id),
		disciplines.discipline,
		status_to_xml(statuses.id),
		-- CASE WHEN 34 = ANY(applications.categoryid) THEN
		--      va_vos.vo
		-- ELSE
			vos.vo,
		-- END, E
		countries.country,
		people.person,
		urls.url,
		docs.doc,
		middlewares.mw,
		XMLELEMENT(name "application:permalink",'http://'||(SELECT data FROM config WHERE var='ui-host')||'/?p='||encode(CAST('/apps/details?id='||applications.id::text AS bytea),'base64')), E'\n\t',
		CASE WHEN NOT applogos.logo IS NULL THEN
			XMLELEMENT(name "application:logo",'http://'||(SELECT data FROM config WHERE var='ui-host')||'/apps/getlogo?id='||applications.id::text)
		END,
		CASE WHEN applications.moderated AND (NOT $2 IS NULL) /*AND ((SELECT positiontypeid FROM researchers AS moderators WHERE moderators.id = $2) IN (5,7))*/ THEN (
			XMLELEMENT(name "application:moderatedOn",app_mod_infos.moddedon)::text || XMLELEMENT(name "application:moderationReason",app_mod_infos.modreason)::text || researcher_to_xml(app_mod_infos.moddedby, 'moderator')::text
		)::xml END,
		CASE WHEN applications.deleted AND (NOT $2 IS NULL) /*AND ((SELECT positiontypeid FROM researchers AS deleters WHERE deleters.id = $2) IN (5,7))*/ THEN (
			XMLELEMENT(name "application:deletedOn",app_del_infos.deletedon)::text || researcher_to_xml(app_del_infos.deletedby, 'deleter')::text
		)::xml END,
		-- CASE WHEN applications.categoryid[1] = 34 THEN (
		CASE WHEN metatype = 1 THEN (
			SELECT
			XMLELEMENT(
				name "application:vappliance",
				XMLATTRIBUTES(
					vapplications.id AS "id",
					vapplications.appid AS "appid",
					vapplications.guid AS "identifier",
					vapplications.name AS "name",
					vapplications.imglst_private AS "imageListsPrivate"
				)
			)::text
			FROM vapplications WHERE vapplications.appid = applications.id
		)::xml END,
		app_licenses_to_xml($1),
		tags.tag,
		proglangs.proglang,
		archs.arch,
		oses.os,
		appcds.xml,
		-- CASE WHEN NOT $2 IS NULL THEN
		--      CASE WHEN EXISTS(
		--              SELECT *
		--              FROM permissions
		--              WHERE (object = applications.guid OR object IS NULL) AND (actor = (SELECT guid FROM researchers WHERE id = $2)) AND (actionid IN (1,2))
		--      ) THEN
		--              targetprivs.privs
		--      END
		-- END, E'\n\t',
		target_relations.xml,
		subject_relations.xml,
		CASE WHEN NOT $2 IS NULL THEN
			privs_to_xml(applications.guid, (SELECT guid FROM researchers WHERE id = $2))
		END
	) AS application
FROM applications
LEFT OUTER JOIN (SELECT appid, xmlagg(discipline_to_xml(disciplineid)) AS discipline FROM appdisciplines WHERE appid = $1 GROUP BY appid) AS disciplines ON disciplines.appid = applications.id
LEFT OUTER JOIN (SELECT id, xmlagg(researcher_to_xml("owner", 'owner')) AS "owner" FROM applications WHERE id = $1 GROUP BY id) AS owners ON owners.id = applications.id
LEFT OUTER JOIN (SELECT id, xmlagg(researcher_to_xml(addedby, 'actor')) AS "actor" FROM applications WHERE id = $1 GROUP BY id) AS actors ON actors.id = applications.id
LEFT OUTER JOIN
	(SELECT appid, xmlagg(vo_to_xml(void)) AS vo FROM app_vos INNER JOIN vos ON vos.id = app_vos.void WHERE vos.deleted IS FALSE AND appid = $1 GROUP BY appid)
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
LEFT OUTER JOIN (SELECT appid, xmlagg(country_to_xml(id, appid)) AS country FROM appcountries WHERE appid = $1 GROUP BY appid) AS countries ON countries.appid = applications.id
INNER JOIN statuses ON statuses.id = applications.statusid
LEFT OUTER JOIN (SELECT appid, xmlagg(appmiddleware_to_xml(id)) AS mw FROM app_middlewares WHERE appid = $1 GROUP BY appid) AS middlewares ON middlewares.appid = applications.id
LEFT OUTER JOIN (SELECT appid, xmlagg(researcher_to_xml(researcherid, 'contact',appid)) AS person FROM researchers_apps INNER JOIN researchers ON researchers.id = researchers_apps.researcherid WHERE researchers.deleted IS FALSE AND appid = $1 GROUP BY appid) AS people ON people.appid = applications.id
LEFT OUTER JOIN (SELECT appid, xmlagg(xmlelement(name "application:url", xmlattributes(id as id, description as type, title as title), url)) AS url FROM app_urls WHERE appid = $1 GROUP BY appid) AS urls ON urls.appid = applications.id
LEFT OUTER JOIN (SELECT appid, xmlagg(appdocument_to_xml(id)) AS doc FROM appdocuments WHERE appid = $1 GROUP BY appid) AS docs ON docs.appid = applications.id
LEFT OUTER JOIN (SELECT appid, xmlagg(xmlelement(name "application:tag", xmlattributes(CASE WHEN researcherid ISNULL THEN 0 ELSE researcherid END as "submitterID"),tag)) as tag FROM app_tags WHERE appid = $1 GROUP BY appid) as tags ON tags.appid = applications.id
LEFT OUTER JOIN app_mod_infos ON app_mod_infos.appid = applications.id
LEFT OUTER JOIN app_del_infos ON app_del_infos.appid = applications.id
LEFT OUTER JOIN hitcounts ON hitcounts.appid = applications.id
LEFT OUTER JOIN (SELECT appid, xmlagg(xmlelement(name "application:language", xmlattributes(proglangid as id), (SELECT proglangs.name FROM proglangs WHERE id = proglangid))) as proglang FROM appproglangs WHERE appid = $1 GROUP BY appid) as proglangs ON proglangs.appid = applications.id
LEFT OUTER JOIN (SELECT appid, xmlagg(xmlelement(name "application:arch", xmlattributes(archid as id),(SELECT archs.name FROM archs WHERE id = archid))) as arch FROM app_archs WHERE appid = $1 GROUP BY appid) as archs ON archs.appid = applications.id
LEFT OUTER JOIN (SELECT appid, xmlagg(xmlelement(name "application:os", xmlattributes(osid as id), (SELECT oses.name FROM oses WHERE id = osid))) as os FROM app_oses WHERE appid = $1 GROUP BY appid) as oses ON oses.appid = applications.id
LEFT OUTER JOIN applogos ON applogos.appid = applications.id
/* CONTINUOUS DELIVERY -- START */
LEFT OUTER JOIN (
	SELECT
		cds.app_id AS id,
		XMLELEMENT(
			name "application:cd",
			XMLATTRIBUTES(
				cds.id,
				cds.paused,
				cds.enabled,
				CASE
					WHEN EXISTS (SELECT 1 FROM cd_instances WHERE cd_instances.cd_id = cds.id AND cd_instances.state = 'running'::text) THEN
						'running'
					ELSE
						'idle'
				END AS state,
	--                        cds.url,
				cds.default_actor_id AS defaultactorid
			),
			CASE WHEN EXISTS (SELECT 1 FROM cd_instances WHERE cd_instances.cd_id = cds.id AND cd_instances.state = 'running'::text) THEN
				XMLAGG(
					XMLELEMENT(
						name "application:cdinstance",
						XMLATTRIBUTES(
							cd_instances.id,
							cd_instances.state,
							cd_instances.started_on AS startedon,
							cd_instances.progress_max AS stepcount,
							cd_instances.progress_val AS stepcomplete,
							cd_instances.trigger_by_id AS triggeredbyid
						),
						XMLELEMENT(
							name "application:cdinstancetrigger",
							XMLATTRIBUTES(
								cd_trigger_types.id,
								cd_trigger_types.name
							)
						)
					)
				)
			ELSE
				NULL::XML
			END
		) AS xml
	FROM applications a
	LEFT JOIN cds ON cds.app_id = a.id
	LEFT JOIN cd_instances cd_instances ON cd_instances.cd_id = cds.id AND cd_instances.state = 'running'::text
	LEFT JOIN cd_trigger_types ON cd_trigger_types.id = cd_instances.trigger_type
	WHERE a.id = $1
	GROUP BY cds.id
	-- LIMIT 1 -- FIXME: properly aggregate rows when and if multiple CD types are supported. (LIMIT used to prevent cardinality errors)
) AS appcds ON appcds.id = applications.id
/* CONTINUOUS DELIVERY -- END */

-- LEFT OUTER JOIN (
--      SELECT xmlagg(x) AS privs, id FROM (SELECT target_privs_to_xml(applications.guid, $2) AS x, applications.id FROM applications) AS t GROUP BY t.id
-- ) AS targetprivs ON applications.id = targetprivs.id
LEFT OUTER JOIN app_popularities ON app_popularities.appid = applications.id
LEFT OUTER JOIN (SELECT $1 AS appid, xmlagg(x) as "xml" FROM target_relations_to_xml((SELECT guid FROM applications WHERE id = $1)) as x) AS target_relations ON target_relations.appid = applications.id
LEFT OUTER JOIN (SELECT $1 AS appid, xmlagg(x) as "xml" FROM subject_relations_to_xml((SELECT guid FROM applications WHERE id = $1)) as x) AS subject_relations ON subject_relations.appid = applications.id
WHERE applications.id = $1;
$$ LANGUAGE SQL STABLE;

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 20, 6, E'app_to_xml_ext performance improvements'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=20 AND revision=6);

COMMIT;

