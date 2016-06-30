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
Previous version: 8.12.30
New version: 8.12.31
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;
DROP INDEX researchers_apps_fk_researcher_app_app ;
DROP INDEX researchers_apps_fk_researcher_app_researcher;
CREATE INDEX idx_vowide_image_lists_state_pub ON vowide_image_lists(state) WHERE state = 'published'::e_vowide_image_state;
CREATE INDEX idx_applications_any_id ON applications.any(id);
CREATE INDEX idx_licenses_any_id ON licenses.any(id);
CREATE INDEX idx_app_licenses_any_id ON app_licenses.any(id);
CREATE INDEX idx_statuses_any_id ON statuses.any(id);
CREATE INDEX idx_categories_any_id ON categories.any(id);
CREATE INDEX idx_disciplines_any_id ON disciplines.any(id);
CREATE INDEX idx_countries_any_id ON countries.any(id);
CREATE INDEX idx_app_middlewares_any_id ON app_middlewares.any(id);
CREATE INDEX idx_middlewares_any_id ON middlewares.any(id);
CREATE INDEX idx_researchers_any_id ON researchers.any(id);
CREATE INDEX idx_contacts_any_id ON contacts.any(id);
CREATE INDEX idx_positiontypes_any_id ON positiontypes.any(id);

ALTER VIEW appautocountries RENAME TO __appautocountries;
CREATE MATERIALIZED VIEW appautocountries AS SELECT * FROM __appautocountries;
ALTER MATERIALIZED VIEW appautocountries OWNER TO appdb;

CREATE UNIQUE INDEX idx_appautocountries_pk ON appautocountries(countryid, id, positiontypeid);
CREATE INDEX idx_appautocountries_id ON appautocountries(id);
CREATE INDEX idx_appautocountries_countryid ON appautocountries(countryid);
CREATE INDEX idx_appautocountries_positiontypeid ON appautocountries(positiontypeid);

ALTER VIEW appcountries RENAME TO __appcountries;
CREATE MATERIALIZED VIEW appcountries AS SELECT * FROM __appcountries;
ALTER MATERIALIZED VIEW appcountries OWNER TO appdb;

CREATE UNIQUE INDEX idx_appcountries_pk ON appcountries(id, appid, inherited);
CREATE INDEX idx_appcountries_id ON appcountries(id);
CREATE INDEX idx_appcountries_appid ON appcountries(appid);
CREATE INDEX idx_appcountries_regionid ON appcountries(regionid);
CREATE INDEX idx_appcountries_inherited ON appcountries(inherited);

CREATE OR REPLACE FUNCTION trfn_refresh_appcountries() 
RETURNS TRIGGER
AS
$$
BEGIN
	REFRESH MATERIALIZED VIEW CONCURRENTLY appautocountries;
	REFRESH MATERIALIZED VIEW CONCURRENTLY appcountries;
	IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN RETURN NEW; ELSE RETURN OLD; END IF;
END;
$$ LANGUAGE plpgsql VOLATILE;
ALTER FUNCTION trfn_refresh_appcountries() OWNER TO appdb;

DROP TRIGGER IF EXISTS tr_applications_99_refresh_appcountries ON applications;
CREATE TRIGGER tr_applications_99_refresh_appcountries
    AFTER INSERT OR UPDATE OR DELETE
    ON applications
    FOR EACH STATEMENT
    EXECUTE PROCEDURE trfn_refresh_appcountries(); 
DROP TRIGGER IF EXISTS tr_researchers_apps_99_refresh_appcountries ON researchers_apps;
CREATE TRIGGER tr_researchers_apps_99_refresh_appcountries
    AFTER INSERT OR UPDATE OR DELETE
    ON researchers_apps
    FOR EACH STATEMENT
    EXECUTE PROCEDURE trfn_refresh_appcountries(); 
DROP TRIGGER IF EXISTS tr_researchers_99_refresh_appcountries ON researchers;
CREATE TRIGGER tr_researchers_99_refresh_appcountries
    AFTER INSERT OR UPDATE OR DELETE
    ON researchers
    FOR EACH STATEMENT
    EXECUTE PROCEDURE trfn_refresh_appcountries(); 
DROP TRIGGER IF EXISTS tr_countries_99_refresh_appcountries ON countries;
CREATE TRIGGER tr_countries_99_refresh_appcountries
    AFTER INSERT OR UPDATE OR DELETE
    ON countries
    FOR EACH STATEMENT
    EXECUTE PROCEDURE trfn_refresh_appcountries(); 
DROP TRIGGER IF EXISTS tr_positiontypes_99_refresh_appcountries ON positiontypes;
CREATE TRIGGER tr_positiontypes_99_refresh_appcountries
    AFTER INSERT OR UPDATE OR DELETE
    ON positiontypes
    FOR EACH STATEMENT
    EXECUTE PROCEDURE trfn_refresh_appcountries(); 
DROP TRIGGER IF EXISTS tr_countries_99_refresh_appcountries ON countries;
CREATE TRIGGER tr_countries_99_refresh_appcountries
    AFTER INSERT OR UPDATE OR DELETE
    ON countries
    FOR EACH STATEMENT
    EXECUTE PROCEDURE trfn_refresh_appcountries(); 
DROP TRIGGER IF EXISTS tr_appmanualcountries_99_refresh_appcountries ON appmanualcountries;
CREATE TRIGGER tr_appmanualcountries_99_refresh_appcountries
    AFTER INSERT OR UPDATE OR DELETE
    ON appmanualcountries
    FOR EACH STATEMENT
    EXECUTE PROCEDURE trfn_refresh_appcountries(); 

DROP VIEW hypervisors CASCADE;
CREATE MATERIALIZED VIEW hypervisors AS
 SELECT e.enumsortorder AS id,
    e.enumlabel AS name,
    e.enumlabel::e_hypervisors AS value
   FROM pg_enum e
     JOIN pg_type t ON e.enumtypid = t.oid
  WHERE t.typname = 'e_hypervisors'::name;

CREATE VIEW vapp_to_xml AS
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
 SELECT applications.id AS appid,
    vapplications.id AS vappid,
    XMLELEMENT(NAME "virtualization:appliance", XMLATTRIBUTES(vapp_versions.published AS published, vapp_versions.version AS version, vapplications.id AS vappid, applications.id AS appid, vapp_versions.id AS vaversionid, timezone('UTC'::text, vapp_versions.createdon::timestamp with time zone) AS createdon, vapp_versions.expireson AS expireson, vapp_versions.status AS status, vapp_versions.enabled AS enabled, vapp_versions.archived AS archived,
        CASE
            WHEN NOT vapp_versions.archivedon IS NULL THEN timezone('UTC'::text, vapp_versions.archivedon::timestamp with time zone)
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
            WHEN vmiflavours.format IS NULL THEN NULL::xml
            ELSE XMLELEMENT(NAME "virtualization:format", XMLATTRIBUTES(vmiformats.id AS id), vmiflavours.format)
        END, '
', XMLELEMENT(NAME "virtualization:size", vmiinstances.size), '
', XMLELEMENT(NAME "virtualization:url", vmiinstances.uri), '
', XMLELEMENT(NAME "virtualization:checksum", XMLATTRIBUTES(vmiinstances.checksumfunc AS hash), vmiinstances.checksum), '
', XMLELEMENT(NAME "virtualization:cores", XMLATTRIBUTES(vmiinstances.coreminimum AS minimum, vmiinstances.corerecommend AS recommended)), '
', XMLELEMENT(NAME "virtualization:ram", XMLATTRIBUTES(vmiinstances.ramminimum AS minimum, vmiinstances.ramrecommend AS recommended)), '
', researcher_to_xml(vmiinstances.addedby, 'addedby'::text), '
', XMLELEMENT(NAME "virtualization:addedon", timezone('UTC'::text, vmiinstances.addedon::timestamp with time zone)), '
', researcher_to_xml(vmiinstances.lastupdatedby, 'lastupdatedby'::text), '
', XMLELEMENT(NAME "virtualization:lastupdatedon", vmiinstances.lastupdatedon), '
', XMLELEMENT(NAME "virtualization:autointegrity", vmiinstances.autointegrity), '
', XMLELEMENT(NAME "virtualization:ovf", XMLATTRIBUTES(vmiinstances.ovfurl AS url)), '
', vmiinst_cntxscripts_to_xml(vmiinstances.id), '
'))) AS xml
   FROM vmiinstances
     JOIN vmiflavours ON vmiflavours.id = vmiinstances.vmiflavourid
     JOIN vmis ON vmis.id = vmiflavours.vmiid
     JOIN vapplications ON vapplications.id = vmis.vappid
     JOIN applications ON applications.id = vapplications.appid
     JOIN vapp_versions ON vapp_versions.vappid = vapplications.id
     JOIN vapplists ON vapplists.vappversionid = vapp_versions.id AND vapplists.vmiinstanceid = vmiinstances.id
     LEFT JOIN archs ON archs.id = vmiflavours.archid
     LEFT JOIN oses ON oses.id = vmiflavours.osid
     LEFT JOIN researchers ON researchers.id = vmiinstances.addedby
     LEFT JOIN hypervisors ON hypervisors.vmiflavourid = vmiflavours.id
     LEFT JOIN vmiformats ON vmiformats.name::text = vmiflavours.format
  GROUP BY applications.id, vapplications.id, vapp_versions.published, vapp_versions.version, applications.guid, vapplications.name, vapp_versions.id, vapp_versions.createdon, vapp_versions.expireson, vapp_versions.status, vapp_versions.enabled, vapp_versions.archived
  ORDER BY vapp_versions.published, vapp_versions.archived, vapp_versions.archivedon DESC;
ALTER VIEW vapp_to_xml OWNER TO appdb;

CREATE VIEW vmiflavor_hypervisor_xml AS 
 WITH x AS (
         SELECT vmiflavours_2.id,
            unnest(vmiflavours_2.hypervisors) AS y
           FROM vmiflavours vmiflavours_2
        )
 SELECT vmiflavours_1.id AS vmiflavourid,
    xmlagg(XMLELEMENT(NAME "virtualization:hypervisor", XMLATTRIBUTES(( SELECT hypervisors_1.id
           FROM hypervisors hypervisors_1
          WHERE hypervisors_1.name::text = x.y::text) AS id), x.y)) AS hypervisor
   FROM vmiflavours vmiflavours_1
     JOIN x ON x.id = vmiflavours_1.id
  GROUP BY vmiflavours_1.id;
ALTER VIEW vmiflavor_hypervisor_xml OWNER TO appdb;

CREATE MATERIALIZED VIEW site_service_images_xml AS 
 SELECT siteimages.va_provider_id,
    xmlagg(siteimages.x) AS xmlagg
   FROM ( SELECT __va_providers.id AS va_provider_id,
            XMLELEMENT(NAME "siteservice:image", XMLATTRIBUTES(__vaviews.vappversionid AS versionid, __vaviews.va_version_archived AS archived, __vaviews.va_version_enabled AS enabled, __vaviews.va_version_expireson AS expireson,
                CASE
                    WHEN __vaviews.va_version_expireson <= now() THEN true
                    ELSE false
                END AS isexpired, __vaviews.imglst_private AS private, __vaviews.vmiinstanceid AS id, __vaviews.vmiinstance_guid AS identifier, __vaviews.vmiinstance_version AS version, good_vmiinstanceid(va_provider_images.*) AS goodid), vmiflavor_hypervisor_xml.hypervisor::text::xml, XMLELEMENT(NAME "virtualization:os", XMLATTRIBUTES(oses.id AS id, __vaviews.osversion AS version, oses.os_family_id AS family_id), oses.name), XMLELEMENT(NAME "virtualization:arch", XMLATTRIBUTES(archs.id AS id), archs.name), XMLELEMENT(NAME "virtualization:format", __vaviews.format), XMLELEMENT(NAME "virtualization:url", XMLATTRIBUTES(
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
                  WHERE config.var = 'ui-host'::text))) || '/store/vm/image/'::text) || __vaviews.vmiinstance_guid::text) || ':'::text) || good_vmiinstanceid(va_provider_images.*)::text) || '/'::text), array_to_string(array_agg(DISTINCT site_service_imageocciids_to_xml(va_provider_images.va_provider_id, va_provider_images.vmiinstanceid, va_provider_images.vowide_vmiinstanceid)::text), ''::text)::xml, XMLELEMENT(NAME "application:application", XMLATTRIBUTES(__vaviews.appid AS id, __vaviews.appcname AS cname, __vaviews.imglst_private AS imagelistsprivate, applications.deleted AS deleted, applications.moderated AS moderated), XMLELEMENT(NAME "application:name", __vaviews.appname)), vmiinst_cntxscripts_to_xml(__vaviews.vmiinstanceid)) AS x
           FROM __va_providers
             JOIN va_provider_images va_provider_images ON va_provider_images.va_provider_id = __va_providers.id
             JOIN __vaviews __vaviews ON __vaviews.vmiinstanceid = va_provider_images.vmiinstanceid
             JOIN applications ON applications.id = __vaviews.appid
             LEFT JOIN vmiflavor_hypervisor_xml ON vmiflavor_hypervisor_xml.vmiflavourid = __vaviews.vmiflavourid
             LEFT JOIN archs ON archs.id = __vaviews.archid
             LEFT JOIN oses ON oses.id = __vaviews.osid
             LEFT JOIN vmiformats ON vmiformats.name::text = __vaviews.format
          WHERE __vaviews.va_version_published
          GROUP BY __va_providers.id, __vaviews.vappversionid, __vaviews.va_version_archived, __vaviews.va_version_enabled, __vaviews.va_version_expireson, __vaviews.imglst_private, __vaviews.vmiinstanceid, __vaviews.vmiinstance_guid, __vaviews.vmiinstance_version, good_vmiinstanceid(va_provider_images.*), vmiflavor_hypervisor_xml.hypervisor::text, oses.id, archs.id, __vaviews.osversion, __vaviews.format, __vaviews.uri, __vaviews.size, __vaviews.appid, __vaviews.appcname, __vaviews.appname, applications.deleted, applications.moderated) siteimages
  GROUP BY siteimages.va_provider_id;
ALTER MATERIALIZED VIEW site_service_images_xml OWNER TO appdb;

CREATE INDEX idx_hypervisors_id ON hypervisors(id);
CREATE INDEX idx_hypervisors_name ON hypervisors(name);
CREATE INDEX idx_hypervisors_name_low ON hypervisors(lower(name));
CREATE INDEX idx_hypervisors_value ON hypervisors(value);

DROP VIEW app_tags;
DROP VIEW app_vos;

CREATE VIEW v_app_vos AS 
 SELECT DISTINCT appid, void FROM (
 SELECT DISTINCT vaviews.appid,
    vowide_image_lists.void
   FROM vowide_image_list_images
     JOIN vowide_image_lists ON vowide_image_list_images.vowide_image_list_id = vowide_image_lists.id
     JOIN __vaviews AS vaviews ON vaviews.vapplistid = vowide_image_list_images.vapplistid
  WHERE vowide_image_lists.state = 'published'::e_vowide_image_state
UNION
 SELECT DISTINCT applications.id AS appid,
    vowide_image_lists.void
   FROM vowide_image_list_images
     JOIN vowide_image_lists ON vowide_image_list_images.vowide_image_list_id = vowide_image_lists.id
     JOIN __vaviews AS vaviews ON vaviews.vapplistid = vowide_image_list_images.vapplistid
     JOIN vmiinstance_contextscripts vcs ON vcs.vmiinstanceid = vaviews.vmiinstanceid
     JOIN contextscripts cs ON cs.id = vcs.contextscriptid
     JOIN context_script_assocs ON context_script_assocs.scriptid = vcs.contextscriptid
     JOIN contexts ON contexts.id = context_script_assocs.contextid
     JOIN applications ON applications.id = contexts.appid
  WHERE vowide_image_lists.state = 'published'::e_vowide_image_state AND applications.metatype = 2
UNION
 SELECT __app_vos.appid,
    __app_vos.void
   FROM __app_vos
) AS t;
COMMENT ON VIEW v_app_vos IS 'Shortcut view for re-populating app_vos table in trigger function. Must select from "__vaviews" instead of the materialized view "vaviews" to make sure data is up-to-date';
ALTER VIEW v_app_vos
  OWNER TO appdb;

CREATE TABLE app_vos AS SELECT DISTINCT appid, void FROM v_app_vos;
ALTER TABLE app_vos
  OWNER TO appdb;
COMMENT ON TABLE app_vos IS 'This table is populated by triggers from the "v_app_views" view (manual materialized view in order to support rules)';

CREATE UNIQUE INDEX idx_app_vos_appid ON app_vos(appid, void);
CREATE INDEX idx_app_vos_void ON app_vos(void);

 CREATE OR REPLACE VIEW app_tags AS 
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
           FROM applications
             JOIN v_app_vos AS app_vos ON app_vos.appid = applications.id
             JOIN vos ON vos.id = app_vos.void
          WHERE NOT vos.name IS NULL AND NOT vos.deleted
        UNION
         SELECT DISTINCT NULL::integer AS int4,
            applications.id,
            NULL::integer AS int4,
            appcountries.name
           FROM applications
             JOIN appcountries ON appcountries.appid = applications.id
          WHERE NOT appcountries.name IS NULL
        UNION
         SELECT DISTINCT NULL::integer AS int4,
            applications.id,
            NULL::integer AS int4,
                CASE
                    WHEN middlewares.id = 5 THEN app_middlewares.comment
                    ELSE middlewares.name
                END AS name
           FROM applications
             JOIN app_middlewares ON app_middlewares.appid = applications.id
             JOIN middlewares ON middlewares.id = app_middlewares.middlewareid
          WHERE NOT
                CASE
                    WHEN middlewares.id = 5 THEN app_middlewares.comment
                    ELSE middlewares.name
                END IS NULL) t;

ALTER TABLE app_tags
  OWNER TO appdb;

CREATE OR REPLACE RULE r_delete_app_tags AS
    ON DELETE TO app_tags DO INSTEAD  DELETE FROM __app_tags
  WHERE __app_tags.id = old.id AND NOT old.id IS NULL
  RETURNING __app_tags.id,
    __app_tags.appid,
    __app_tags.researcherid,
    __app_tags.tag;

CREATE OR REPLACE RULE r_insert_app_tags AS
    ON INSERT TO app_tags DO INSTEAD  INSERT INTO __app_tags (appid, researcherid, tag)
  VALUES (new.appid, new.researcherid, new.tag)
  RETURNING __app_tags.id,
    __app_tags.appid,
    __app_tags.researcherid,
    __app_tags.tag;

CREATE OR REPLACE RULE r_update_app_tags AS
    ON UPDATE TO app_tags DO INSTEAD  UPDATE __app_tags SET appid = new.appid, researcherid = new.researcherid, tag = new.tag
  WHERE __app_tags.id = old.id AND NOT old.id IS NULL
  RETURNING __app_tags.id,
    __app_tags.appid,
    __app_tags.researcherid,
    __app_tags.tag;

CREATE OR REPLACE FUNCTION trfn_refresh_app_vos()
RETURNS TRIGGER
AS
$$
BEGIN
	TRUNCATE TABLE app_vos;
	INSERT INTO app_vos SELECT DISTINCT appid, void FROM v_app_vos;
	IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN RETURN NEW; ELSE RETURN OLD; END IF;
END;
$$ LANGUAGE plpgsql VOLATILE;
ALTER FUNCTION trfn_refresh_app_vos() OWNER TO appdb;

DROP TRIGGER IF EXISTS tr_vowide_image_list_images_99_refresh_app_vos ON vowide_image_list_images;
CREATE TRIGGER tr_vowide_image_list_images_99_refresh_app_vos
    AFTER INSERT OR UPDATE OR DELETE
    ON vowide_image_list_images
    FOR EACH STATEMENT
    EXECUTE PROCEDURE trfn_refresh_app_vos(); 
DROP TRIGGER IF EXISTS tr_vowide_image_lists_99_refresh_app_vos ON vowide_image_lists;
CREATE TRIGGER tr_vowide_image_lists_99_refresh_app_vos
    AFTER INSERT OR UPDATE OR DELETE
    ON vowide_image_lists
    FOR EACH STATEMENT
    EXECUTE PROCEDURE trfn_refresh_app_vos(); 
DROP TRIGGER IF EXISTS tr_vmiinstance_contextscripts_99_refresh_app_vos ON vmiinstance_contextscripts;
CREATE TRIGGER tr_vmiinstance_contextscripts_99_refresh_app_vos
    AFTER INSERT OR UPDATE OR DELETE
    ON vmiinstance_contextscripts
    FOR EACH STATEMENT
    EXECUTE PROCEDURE trfn_refresh_app_vos(); 
DROP TRIGGER IF EXISTS tr_contextscripts_99_refresh_app_vos ON contextscripts;
CREATE TRIGGER tr_contextscripts_99_refresh_app_vos
    AFTER INSERT OR UPDATE OR DELETE
    ON contextscripts
    FOR EACH STATEMENT
    EXECUTE PROCEDURE trfn_refresh_app_vos(); 
DROP TRIGGER IF EXISTS tr_context_script_assocs_99_refresh_app_vos ON context_script_assocs;
CREATE TRIGGER tr_context_script_assocs_99_refresh_app_vos
    AFTER INSERT OR UPDATE OR DELETE
    ON context_script_assocs
    FOR EACH STATEMENT
    EXECUTE PROCEDURE trfn_refresh_app_vos(); 
DROP TRIGGER IF EXISTS tr_contexts_99_refresh_app_vos ON contexts;
CREATE TRIGGER tr_contexts_99_refresh_app_vos
    AFTER INSERT OR UPDATE OR DELETE
    ON contexts
    FOR EACH STATEMENT
    EXECUTE PROCEDURE trfn_refresh_app_vos(); 

DROP TRIGGER IF EXISTS tr__app_vos_99_refresh_app_vos ON __app_vos;
CREATE TRIGGER tr___app_vos_99_refresh_app_vos
    AFTER INSERT OR UPDATE OR DELETE
    ON __app_vos
    FOR EACH STATEMENT
    EXECUTE PROCEDURE trfn_refresh_app_vos(); 

DROP TRIGGER IF EXISTS tr_applications_99_refresh_app_vos ON applications;
CREATE TRIGGER tr_applications_99_refresh_app_vos
    AFTER INSERT OR UPDATE OR DELETE
    ON applications
    FOR EACH STATEMENT
    EXECUTE PROCEDURE trfn_refresh_app_vos(); 
DROP TRIGGER IF EXISTS tr_vmiinstances_99_refresh_app_vos ON vmiinstances;
CREATE TRIGGER tr_vmiinstances_99_refresh_app_vos
    AFTER INSERT OR UPDATE OR DELETE
    ON vmiinstances
    FOR EACH STATEMENT
    EXECUTE PROCEDURE trfn_refresh_app_vos(); 
DROP TRIGGER IF EXISTS tr_vmiflavours_99_refresh_app_vos ON vmiflavours;
CREATE TRIGGER tr_vmiflavours_99_refresh_app_vos
    AFTER INSERT OR UPDATE OR DELETE
    ON vmiflavours
    FOR EACH STATEMENT
    EXECUTE PROCEDURE trfn_refresh_app_vos(); 
DROP TRIGGER IF EXISTS tr_vmis_99_refresh_app_vos ON vmis;
CREATE TRIGGER tr_vmis_99_refresh_app_vos
    AFTER INSERT OR UPDATE OR DELETE
    ON vmis
    FOR EACH STATEMENT
    EXECUTE PROCEDURE trfn_refresh_app_vos(); 
DROP TRIGGER IF EXISTS tr_vapplications_99_refresh_app_vos ON vapplications;
CREATE TRIGGER tr_vapplications_99_refresh_app_vos
    AFTER INSERT OR UPDATE OR DELETE
    ON vapplications
    FOR EACH STATEMENT
    EXECUTE PROCEDURE trfn_refresh_app_vos(); 
DROP TRIGGER IF EXISTS tr_vapp_versions_99_refresh_app_vos ON vapp_versions;
CREATE TRIGGER tr_vapp_versions_99_refresh_app_vos
    AFTER INSERT OR UPDATE OR DELETE
    ON vapp_versions
    FOR EACH STATEMENT
    EXECUTE PROCEDURE trfn_refresh_app_vos(); 
DROP TRIGGER IF EXISTS tr_app_order_hack_99_refresh_app_vos ON app_order_hack;
CREATE TRIGGER tr_app_order_hack_99_refresh_app_vos
    AFTER INSERT OR UPDATE OR DELETE
    ON app_order_hack
    FOR EACH STATEMENT
    EXECUTE PROCEDURE trfn_refresh_app_vos();


CREATE OR REPLACE FUNCTION public.delete_app(_appid integer)
 RETURNS boolean
 LANGUAGE plpgsql
AS $function$
  BEGIN
      BEGIN
          DELETE FROM applications.any WHERE id = $1;
          DELETE FROM vapplications WHERE appid = $1;
          DELETE FROM app_cnames WHERE appid = $1;
          DELETE FROM app_validation_log WHERE appid = $1;
          DELETE FROM "privileges" WHERE object = (SELECT guid FROM applications WHERE id = $1);
          DELETE FROM __app_vos WHERE appid = $1;
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
          DELETE FROM __app_tags WHERE appid = $1;
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
  $function$;

-- Function: count_app_matches(text, text, boolean)

-- DROP FUNCTION count_app_matches(text, text, boolean);

CREATE OR REPLACE FUNCTION count_app_matches(
    itemname text,
    cachetable text,
    private boolean DEFAULT false)
  RETURNS SETOF record AS
$BODY$
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
LEFT JOIN hypervisors ON hypervisors.value = ANY(vmiflavours.hypervisors)';
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
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION count_app_matches(text, text, boolean)
  OWNER TO appdb;
COMMENT ON FUNCTION count_app_matches(text, text, boolean) IS 'not to be called directly; used by app_logistics function';

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 12, 31, E'Performance improvements'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=12 AND revision=31);

COMMIT;
