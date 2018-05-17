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
Previous version: 8.18.0
New version: 8.18.1
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

CREATE OR REPLACE VIEW vaviewsall AS
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
     JOIN applications ON applications.id = vapplications.appid;
ALTER VIEW vaviewsall OWNER TO appdb;

CREATE OR REPLACE VIEW __vaviews AS
SELECT vaviewsall.* FROM vaviewsall
LEFT JOIN app_order_hack ON app_order_hack.appid = vaviewsall.appid
WHERE app_order_hack.appid IS NULL;

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 18, 1, E'Added view vaviewsall and re-based __vaviews on it'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=18 AND revision=1);

COMMIT;	
