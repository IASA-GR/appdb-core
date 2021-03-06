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
Previous version: 8.15.2
New version: 8.15.3
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

ALTER TABLE vmiinstances ADD COLUMN default_access TEXT NOT NULL DEFAULT 'None';

CREATE OR REPLACE VIEW public.__vaviews AS
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
    applications.cname AS appcname,
    vmiinstances.min_acc,
    vmiinstances.rec_acc,
    contextfmts(vmiinstances.*) AS contextfmts,
    vapp_versions.enabledon AS va_version_enabledon,
    vapp_versions.enabledby AS va_version_enabledby,
    vapp_versions.publishedon AS va_version_publishedon,
    vapp_versions.publishedby AS va_version_publishedby,
    vmiinstances.default_access
   FROM vapplists
     JOIN vmiinstances ON vmiinstances.id = vapplists.vmiinstanceid
     JOIN vmiflavours ON vmiflavours.id = vmiinstances.vmiflavourid
     JOIN vmis ON vmis.id = vmiflavours.vmiid
     JOIN vapplications ON vapplications.id = vmis.vappid
     JOIN vapp_versions ON vapp_versions.id = vapplists.vappversionid
     JOIN applications ON applications.id = vapplications.appid
     LEFT JOIN app_order_hack ON app_order_hack.appid = applications.id
  WHERE app_order_hack.appid IS NULL;

ALTER TABLE public.__vaviews
  OWNER TO appdb;

-- Materialized View: public.vaviews

DROP MATERIALIZED VIEW public.vaviews;

CREATE MATERIALIZED VIEW public.vaviews AS
 SELECT __vaviews.vapplistid,
    __vaviews.vappversionid,
    __vaviews.vmiinstanceid,
    __vaviews.size,
    __vaviews.uri,
    __vaviews.vmiinstance_version,
    __vaviews.checksum,
    __vaviews.checksumfunc,
    __vaviews.vmiinstance_notes,
    __vaviews.vmiinstance_guid,
    __vaviews.vmiinstance_addedon,
    __vaviews.vmiinstance_addedby,
    __vaviews.vmiflavourid,
    __vaviews.autointegrity,
    __vaviews.coreminimum,
    __vaviews.ramminimum,
    __vaviews.vmiinstance_lastupdatedby,
    __vaviews.vmiinstance_lastupdatedon,
    __vaviews.vmiinstance_description,
    __vaviews.vmiinstance_title,
    __vaviews.integrity_status,
    __vaviews.integrity_message,
    __vaviews.ramrecommend,
    __vaviews.corerecommend,
    __vaviews.accessinfo,
    __vaviews.vmiinstance_enabled,
    __vaviews.initialsize,
    __vaviews.initialchecksum,
    __vaviews.ovfurl,
    __vaviews.vmiid,
    __vaviews.hypervisors,
    __vaviews.archid,
    __vaviews.osid,
    __vaviews.osversion,
    __vaviews.format,
    __vaviews.vmi_name,
    __vaviews.vmi_description,
    __vaviews.vmi_guid,
    __vaviews.va_id,
    __vaviews.vmi_notes,
    __vaviews.groupname,
    __vaviews.va_name,
    __vaviews.appid,
    __vaviews.va_guid,
    __vaviews.imglst_private,
    __vaviews.va_version,
    __vaviews.va_version_guid,
    __vaviews.va_version_notes,
    __vaviews.va_version_published,
    __vaviews.va_version_createdon,
    __vaviews.va_version_expireson,
    __vaviews.va_version_enabled,
    __vaviews.va_version_archived,
    __vaviews.va_version_status,
    __vaviews.va_version_archivedon,
    __vaviews.submissionid,
    __vaviews.va_version_isexternal,
    __vaviews.appname,
    __vaviews.appcname,
    __vaviews.min_acc,
    __vaviews.rec_acc,
    __vaviews.contextfmts,
    __vaviews.va_version_enabledon,
    __vaviews.va_version_enabledby,
    __vaviews.va_version_publishedon,
    __vaviews.va_version_publishedby,
    __vaviews.default_access
   FROM __vaviews
WITH DATA;

ALTER TABLE public.vaviews
  OWNER TO appdb;

-- Index: public.idx_vaviews_appid

-- DROP INDEX public.idx_vaviews_appid;

CREATE INDEX idx_vaviews_appid
  ON public.vaviews
  USING btree
  (appid);

-- Index: public.idx_vaviews_archid

-- DROP INDEX public.idx_vaviews_archid;

CREATE INDEX idx_vaviews_archid
  ON public.vaviews
  USING btree
  (archid);

-- Index: public.idx_vaviews_checksum

-- DROP INDEX public.idx_vaviews_checksum;

CREATE INDEX idx_vaviews_checksum
  ON public.vaviews
  USING btree
  (checksum COLLATE pg_catalog."default");

-- Index: public.idx_vaviews_checksum_textops

-- DROP INDEX public.idx_vaviews_checksum_textops;

CREATE INDEX idx_vaviews_checksum_textops
  ON public.vaviews
  USING btree
  (checksum COLLATE pg_catalog."default" text_pattern_ops);

-- Index: public.idx_vaviews_checksum_trgmops

-- DROP INDEX public.idx_vaviews_checksum_trgmops;

CREATE INDEX idx_vaviews_checksum_trgmops
  ON public.vaviews
  USING gin
  (checksum COLLATE pg_catalog."default" gin_trgm_ops);

-- Index: public.idx_vaviews_format

-- DROP INDEX public.idx_vaviews_format;

CREATE INDEX idx_vaviews_format
  ON public.vaviews
  USING btree
  (format COLLATE pg_catalog."default");

-- Index: public.idx_vaviews_format_textops

-- DROP INDEX public.idx_vaviews_format_textops;

CREATE INDEX idx_vaviews_format_textops
  ON public.vaviews
  USING btree
  (format COLLATE pg_catalog."default" text_pattern_ops);

-- Index: public.idx_vaviews_format_trgmops

-- DROP INDEX public.idx_vaviews_format_trgmops;

CREATE INDEX idx_vaviews_format_trgmops
  ON public.vaviews
  USING gin
  (format COLLATE pg_catalog."default" gin_trgm_ops);

-- Index: public.idx_vaviews_imglst_private

-- DROP INDEX public.idx_vaviews_imglst_private;

CREATE INDEX idx_vaviews_imglst_private
  ON public.vaviews
  USING btree
  (imglst_private);

-- Index: public.idx_vaviews_osid

-- DROP INDEX public.idx_vaviews_osid;

CREATE INDEX idx_vaviews_osid
  ON public.vaviews
  USING btree
  (osid);

-- Index: public.idx_vaviews_osversion

-- DROP INDEX public.idx_vaviews_osversion;

CREATE INDEX idx_vaviews_osversion
  ON public.vaviews
  USING btree
  (osversion COLLATE pg_catalog."default");

-- Index: public.idx_vaviews_osversion_textops

-- DROP INDEX public.idx_vaviews_osversion_textops;

CREATE INDEX idx_vaviews_osversion_textops
  ON public.vaviews
  USING btree
  (osversion COLLATE pg_catalog."default" text_pattern_ops);

-- Index: public.idx_vaviews_osversion_trgmops

-- DROP INDEX public.idx_vaviews_osversion_trgmops;

CREATE INDEX idx_vaviews_osversion_trgmops
  ON public.vaviews
  USING gin
  (osversion COLLATE pg_catalog."default" gin_trgm_ops);

-- Index: public.idx_vaviews_submissionid

-- DROP INDEX public.idx_vaviews_submissionid;

CREATE INDEX idx_vaviews_submissionid
  ON public.vaviews
  USING btree
  (submissionid);

-- Index: public.idx_vaviews_unique

-- DROP INDEX public.idx_vaviews_unique;

CREATE UNIQUE INDEX idx_vaviews_unique
  ON public.vaviews
  USING btree
  (vapplistid, vmiinstanceid, vmiflavourid, vmiid, va_id, vappversionid, appid);

-- Index: public.idx_vaviews_va_guid

-- DROP INDEX public.idx_vaviews_va_guid;

CREATE INDEX idx_vaviews_va_guid
  ON public.vaviews
  USING btree
  (va_guid);

-- Index: public.idx_vaviews_va_id

-- DROP INDEX public.idx_vaviews_va_id;

CREATE INDEX idx_vaviews_va_id
  ON public.vaviews
  USING btree
  (va_id);

-- Index: public.idx_vaviews_va_version

-- DROP INDEX public.idx_vaviews_va_version;

CREATE INDEX idx_vaviews_va_version
  ON public.vaviews
  USING btree
  (va_version COLLATE pg_catalog."default");

-- Index: public.idx_vaviews_va_version_archived

-- DROP INDEX public.idx_vaviews_va_version_archived;

CREATE INDEX idx_vaviews_va_version_archived
  ON public.vaviews
  USING btree
  (va_version_archived);

-- Index: public.idx_vaviews_va_version_enabled

-- DROP INDEX public.idx_vaviews_va_version_enabled;

CREATE INDEX idx_vaviews_va_version_enabled
  ON public.vaviews
  USING btree
  (va_version_enabled);

-- Index: public.idx_vaviews_va_version_guid

-- DROP INDEX public.idx_vaviews_va_version_guid;

CREATE INDEX idx_vaviews_va_version_guid
  ON public.vaviews
  USING btree
  (va_version_guid);

-- Index: public.idx_vaviews_va_version_isexternal

-- DROP INDEX public.idx_vaviews_va_version_isexternal;

CREATE INDEX idx_vaviews_va_version_isexternal
  ON public.vaviews
  USING btree
  (va_version_isexternal);

-- Index: public.idx_vaviews_va_version_published

-- DROP INDEX public.idx_vaviews_va_version_published;

CREATE INDEX idx_vaviews_va_version_published
  ON public.vaviews
  USING btree
  (va_version_published);

-- Index: public.idx_vaviews_va_version_status

-- DROP INDEX public.idx_vaviews_va_version_status;

CREATE INDEX idx_vaviews_va_version_status
  ON public.vaviews
  USING btree
  (va_version_status COLLATE pg_catalog."default");

-- Index: public.idx_vaviews_va_version_status_textops

-- DROP INDEX public.idx_vaviews_va_version_status_textops;

CREATE INDEX idx_vaviews_va_version_status_textops
  ON public.vaviews
  USING btree
  (va_version_status COLLATE pg_catalog."default" text_pattern_ops);

-- Index: public.idx_vaviews_va_version_status_trgmops

-- DROP INDEX public.idx_vaviews_va_version_status_trgmops;

CREATE INDEX idx_vaviews_va_version_status_trgmops
  ON public.vaviews
  USING gin
  (va_version_status COLLATE pg_catalog."default" gin_trgm_ops);

-- Index: public.idx_vaviews_va_version_textops

-- DROP INDEX public.idx_vaviews_va_version_textops;

CREATE INDEX idx_vaviews_va_version_textops
  ON public.vaviews
  USING btree
  (va_version COLLATE pg_catalog."default" text_pattern_ops);

-- Index: public.idx_vaviews_va_version_trgmops

-- DROP INDEX public.idx_vaviews_va_version_trgmops;

CREATE INDEX idx_vaviews_va_version_trgmops
  ON public.vaviews
  USING gin
  (va_version COLLATE pg_catalog."default" gin_trgm_ops);

-- Index: public.idx_vaviews_vapplistid

-- DROP INDEX public.idx_vaviews_vapplistid;

CREATE INDEX idx_vaviews_vapplistid
  ON public.vaviews
  USING btree
  (vapplistid);

-- Index: public.idx_vaviews_vappversionid

-- DROP INDEX public.idx_vaviews_vappversionid;

CREATE INDEX idx_vaviews_vappversionid
  ON public.vaviews
  USING btree
  (vappversionid);

-- Index: public.idx_vaviews_vmi_guid

-- DROP INDEX public.idx_vaviews_vmi_guid;

CREATE INDEX idx_vaviews_vmi_guid
  ON public.vaviews
  USING btree
  (vmi_guid);

-- Index: public.idx_vaviews_vmiflavourid

-- DROP INDEX public.idx_vaviews_vmiflavourid;

CREATE INDEX idx_vaviews_vmiflavourid
  ON public.vaviews
  USING btree
  (vmiflavourid);

-- Index: public.idx_vaviews_vmiid

-- DROP INDEX public.idx_vaviews_vmiid;

CREATE INDEX idx_vaviews_vmiid
  ON public.vaviews
  USING btree
  (vmiid);

-- Index: public.idx_vaviews_vmiinstance_addedby

-- DROP INDEX public.idx_vaviews_vmiinstance_addedby;

CREATE INDEX idx_vaviews_vmiinstance_addedby
  ON public.vaviews
  USING btree
  (vmiinstance_addedby);

-- Index: public.idx_vaviews_vmiinstance_enabled

-- DROP INDEX public.idx_vaviews_vmiinstance_enabled;

CREATE INDEX idx_vaviews_vmiinstance_enabled
  ON public.vaviews
  USING btree
  (vmiinstance_enabled);

-- Index: public.idx_vaviews_vmiinstance_guid

-- DROP INDEX public.idx_vaviews_vmiinstance_guid;

CREATE INDEX idx_vaviews_vmiinstance_guid
  ON public.vaviews
  USING btree
  (vmiinstance_guid);

-- Index: public.idx_vaviews_vmiinstance_lastupdatedby

-- DROP INDEX public.idx_vaviews_vmiinstance_lastupdatedby;

CREATE INDEX idx_vaviews_vmiinstance_lastupdatedby
  ON public.vaviews
  USING btree
  (vmiinstance_lastupdatedby);

-- Index: public.idx_vaviews_vmiinstance_version

-- DROP INDEX public.idx_vaviews_vmiinstance_version;

CREATE INDEX idx_vaviews_vmiinstance_version
  ON public.vaviews
  USING btree
  (vmiinstance_version COLLATE pg_catalog."default");

-- Index: public.idx_vaviews_vmiinstance_version_textops

-- DROP INDEX public.idx_vaviews_vmiinstance_version_textops;

CREATE INDEX idx_vaviews_vmiinstance_version_textops
  ON public.vaviews
  USING btree
  (vmiinstance_version COLLATE pg_catalog."default" text_pattern_ops);

-- Index: public.idx_vaviews_vmiinstance_version_trgmops

-- DROP INDEX public.idx_vaviews_vmiinstance_version_trgmops;

CREATE INDEX idx_vaviews_vmiinstance_version_trgmops
  ON public.vaviews
  USING gin
  (vmiinstance_version COLLATE pg_catalog."default" gin_trgm_ops);

-- Index: public.idx_vaviews_vmiinstanceid

-- DROP INDEX public.idx_vaviews_vmiinstanceid;

CREATE INDEX idx_vaviews_vmiinstanceid
  ON public.vaviews
  USING btree
  (vmiinstanceid);

-- View: public.vapp_to_xml

-- DROP VIEW public.vapp_to_xml;

CREATE OR REPLACE VIEW public.vapp_to_xml AS
 SELECT applications.id AS appid,
    vapplications.id AS vappid,
    XMLELEMENT(NAME "virtualization:appliance", XMLATTRIBUTES(vapp_versions.published AS published, vapp_versions.publishedon AS publishedon, vapp_versions.version AS version, vapplications.id AS vappid, applications.id AS appid, vapp_versions.id AS vaversionid, timezone('UTC'::text, vapp_versions.createdon::timestamp with time zone) AS createdon, vapp_versions.expireson AS expireson, vapp_versions.status AS status, vapp_versions.enabled AS enabled, vapp_versions.enabledon AS enabledon, vapp_versions.archived AS archived,
        CASE
            WHEN NOT vapp_versions.archivedon IS NULL THEN timezone('UTC'::text, vapp_versions.archivedon::timestamp with time zone)
            ELSE NULL::timestamp without time zone
        END AS archivedon, vapplications.guid AS vappidentifier, vapplications.imglst_private AS "imageListsPrivate"), '
',
        CASE
            WHEN NOT vapp_versions.publishedby IS NULL THEN XMLELEMENT(NAME "person:publishedby", XMLATTRIBUTES(vapp_versions.publishedby AS id, tpublishedby.cname AS cname), XMLELEMENT(NAME "person:firstname", tpublishedby.firstname), XMLELEMENT(NAME "person:lastname", tpublishedby.lastname), XMLELEMENT(NAME "person:institute", tpublishedby.institution), XMLELEMENT(NAME "person:role", XMLATTRIBUTES(tpublishedby.positiontypeid AS id, ( SELECT positiontypes.description
               FROM positiontypes
              WHERE positiontypes.id = tpublishedby.positiontypeid) AS type)))
            ELSE NULL::xml
        END, '
',
        CASE
            WHEN NOT vapp_versions.enabledby IS NULL THEN XMLELEMENT(NAME "person:enabledby", XMLATTRIBUTES(vapp_versions.enabledby AS id, tenabledby.cname AS cname), XMLELEMENT(NAME "person:firstname", tenabledby.firstname), XMLELEMENT(NAME "person:lastname", tenabledby.lastname), XMLELEMENT(NAME "person:institute", tenabledby.institution), XMLELEMENT(NAME "person:role", XMLATTRIBUTES(tenabledby.positiontypeid AS id, ( SELECT positiontypes.description
               FROM positiontypes
              WHERE positiontypes.id = tenabledby.positiontypeid) AS type)))
            ELSE NULL::xml
        END, '
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
', '
', XMLELEMENT(NAME "person:addedby", XMLATTRIBUTES(vmiinstances.addedby AS id, taddedby.cname AS cname), XMLELEMENT(NAME "person:firstname", taddedby.firstname), XMLELEMENT(NAME "person:lastname", taddedby.lastname), XMLELEMENT(NAME "person:institute", taddedby.institution), XMLELEMENT(NAME "person:role", XMLATTRIBUTES(taddedby.positiontypeid AS id, ( SELECT positiontypes.description
           FROM positiontypes
          WHERE positiontypes.id = taddedby.positiontypeid) AS type))), '
', XMLELEMENT(NAME "virtualization:addedon", timezone('UTC'::text, vmiinstances.addedon::timestamp with time zone)), '
', '
',
        CASE
            WHEN tlastupdatedby.id IS NULL THEN NULL::xml
            ELSE XMLELEMENT(NAME "person:lastupdatedby", XMLATTRIBUTES(vmiinstances.lastupdatedby AS id, tlastupdatedby.cname AS cname), XMLELEMENT(NAME "person:firstname", tlastupdatedby.firstname), XMLELEMENT(NAME "person:lastname", tlastupdatedby.lastname), XMLELEMENT(NAME "person:institute", tlastupdatedby.institution), XMLELEMENT(NAME "person:role", XMLATTRIBUTES(tlastupdatedby.positiontypeid AS id, ( SELECT positiontypes.description
               FROM positiontypes
              WHERE positiontypes.id = tlastupdatedby.positiontypeid) AS type)))
        END, '
',
        CASE
            WHEN vmiinstances.lastupdatedon IS NULL THEN NULL::xml
            ELSE XMLELEMENT(NAME "virtualization:lastupdatedon", vmiinstances.lastupdatedon)
        END, '
', XMLELEMENT(NAME "virtualization:autointegrity", vmiinstances.autointegrity), '
', XMLELEMENT(NAME "virtualization:ovf", XMLATTRIBUTES(vmiinstances.ovfurl AS url)), '
', XMLELEMENT(NAME "virtualization:defaultaccess", vmiinstances.default_access), '
',
        CASE
            WHEN vmiinstances.rec_acc_type IS DISTINCT FROM NULL::e_acc_type OR vmiinstances.min_acc IS DISTINCT FROM NULL::integer OR vmiinstances.rec_acc IS DISTINCT FROM NULL::integer THEN XMLELEMENT(NAME "virtualization:accelerators", XMLATTRIBUTES(vmiinstances.rec_acc_type AS type, vmiinstances.min_acc AS minimum, vmiinstances.rec_acc AS recommended))
            ELSE NULL::xml
        END, '
', vmi_nt.x, '
', contextfmtsxml(vmiinstances.*), '
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
     LEFT JOIN researchers taddedby ON taddedby.id = vmiinstances.addedby
     LEFT JOIN researchers tpublishedby ON tpublishedby.id = vapp_versions.publishedby
     LEFT JOIN researchers tenabledby ON tenabledby.id = vapp_versions.enabledby
     LEFT JOIN researchers tlastupdatedby ON tlastupdatedby.id = vmiinstances.lastupdatedby
     LEFT JOIN vmiflavor_hypervisor_xml hypervisors ON hypervisors.vmiflavourid = vmiflavours.id
     LEFT JOIN vmiformats ON vmiformats.name::text = vmiflavours.format
     LEFT JOIN ( SELECT tt.vmiinstanceid,
            xmlagg(XMLELEMENT(NAME "virtualization:network_traffic", XMLATTRIBUTES(tt.flow AS direction, tt.net_protocols AS protocols, tt.ip_range AS ip_range, tt.ports AS port_range))) AS x
           FROM ( SELECT DISTINCT t.vmiinstanceid,
                    array_to_string(flow(t.*), ' '::text) AS flow,
                    array_to_string(net_protocols(t.*), ' '::text) AS net_protocols,
                    t.ip_range,
                    t.ports
                   FROM vmi_net_traffic t) tt
          GROUP BY tt.vmiinstanceid) vmi_nt ON vmi_nt.vmiinstanceid = vmiinstances.id
  GROUP BY tpublishedby.institution, tpublishedby.positiontypeid, tpublishedby.lastname, tpublishedby.cname, tpublishedby.firstname, tenabledby.institution, tenabledby.positiontypeid, tenabledby.lastname, tenabledby.cname, tenabledby.firstname, applications.id, vapplications.id, vapp_versions.published, vapp_versions.publishedby, vapp_versions.publishedon, vapp_versions.enabledby, vapp_versions.enabledon, vapp_versions.version, applications.guid, vapplications.name, vapp_versions.id, vapp_versions.createdon, vapp_versions.expireson, vapp_versions.status, vapp_versions.enabled, vapp_versions.archived
  ORDER BY vapp_versions.published, vapp_versions.archived, vapp_versions.archivedon DESC;

ALTER TABLE public.vapp_to_xml
  OWNER TO appdb;

INSERT INTO vmi_supported_context_fmt (vmiinstanceid, fmtid) SELECT id, 1 FROM vmiinstances WHERE NOT EXISTS (SELECT * FROM vmi_supported_context_fmt WHERE vmiinstanceid = vmiinstances.id);

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 15, 3, E'Added default_access column to vmiinstances table'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=15 AND revision=3);

COMMIT;	
