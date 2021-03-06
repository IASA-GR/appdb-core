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
Previous version: 8.12.34
New version: 8.12.35
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

CREATE INDEX idx_vapp_versions_archivedon ON vapp_versions(archivedon);
CREATE OR REPLACE VIEW public.vapp_to_xml AS
 /*WITH hypervisors AS (
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
        )*/
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
', /*researcher_to_xml(vmiinstances.addedby, 'addedby'::text),*/ '
', XMLELEMENT(NAME "person:addedby", XMLATTRIBUTES(
   		vmiinstances.addedby AS id, 
    	(SELECT cname FROM researchers WHERE id = vmiinstances.addedby) AS cname
    ),
        (SELECT name FROM researchers WHERE id = vmiinstances.addedby)
   ), '
', XMLELEMENT(NAME "virtualization:addedon", timezone('UTC'::text, vmiinstances.addedon::timestamp with time zone)), '
', /*researcher_to_xml(vmiinstances.lastupdatedby, 'lastupdatedby'::text),*/ '
', XMLELEMENT(NAME "person:lastupdatedby", XMLATTRIBUTES(
   		vmiinstances.lastupdatedby AS id, 
    	(SELECT cname FROM researchers WHERE id = vmiinstances.lastupdatedby) AS name),
        (SELECT name FROM researchers WHERE id = vmiinstances.lastupdatedby)
   ), '          
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
--     LEFT JOIN hypervisors ON hypervisors.vmiflavourid = vmiflavours.id
     LEFT JOIN vmiflavor_hypervisor_xml AS hypervisors ON hypervisors.vmiflavourid = vmiflavours.id
     LEFT JOIN vmiformats ON vmiformats.name::text = vmiflavours.format
  GROUP BY applications.id, vapplications.id, vapp_versions.published, vapp_versions.version, applications.guid, vapplications.name, vapp_versions.id, vapp_versions.createdon, vapp_versions.expireson, vapp_versions.status, vapp_versions.enabled, vapp_versions.archived
  ORDER BY vapp_versions.published, vapp_versions.archived, vapp_versions.archivedon DESC;

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 12, 35, E'Faster vapp_to_xml view'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=12 AND revision=35);

COMMIT;
