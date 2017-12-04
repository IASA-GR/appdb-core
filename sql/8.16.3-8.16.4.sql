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
Previous version: 8.16.3
New version: 8.16.4
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

CREATE OR REPLACE FUNCTION expiresin(vapp_versions) RETURNS TEXT AS
$$
SELECT
	CASE WHEN $1.expireson > NOW() THEN
		(SELECT ARRAY_TO_STRING(ARRAY[y,m,d], ', ') AS expiresin FROM (
			SELECT
				CASE WHEN y > 0 THEN y::text || CASE WHEN y > 1 THEN ' years' ELSE ' year' END END AS y,
				CASE WHEN m > 0 THEN m::text || CASE WHEN m > 1 THEN ' months' ELSE ' month' END END AS m,
				CASE WHEN d > 0 THEN d::text || CASE WHEN d > 1 THEN ' days' ELSE ' day' END END AS d
			FROM (
				SELECT -- round to months, unless interval < 1 month
					y,
					CASE WHEN (m >= 1 AND d >= 15) THEN m+1 ELSE m END AS m,
					CASE WHEN m = 0 THEN d ELSE 0 END AS d
				FROM (
					SELECT
						-- average years worth in days to 365.4
						-- average month's worth in days based on 4 years length, including a leap year
						FLOOR(EXTRACT('days' FROM $1.expireson - NOW()) / 365.25) AS y,
						FLOOR((EXTRACT('days' FROM $1.expireson - NOW())::NUMERIC % 365.25) / 30.4375) AS m,
						ROUND((EXTRACT('days' FROM $1.expireson - NOW())::NUMERIC % 365.25)::NUMERIC % 30.4375) AS d
				) AS t
			) AS tt
		) AS ttt
		)
	ELSE
		'expired'
	END
$$ LANGUAGE SQL STABLE;

CREATE OR REPLACE FUNCTION public.app_to_json(mid integer)
  RETURNS text AS
$BODY$
SELECT '{"application": {' ||
	'"id": ' || to_json(applications.id) || ', ' ||
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

CREATE OR REPLACE FUNCTION public.vapp_version_to_json(mid integer)
  RETURNS text AS
$BODY$
SELECT '{"vapp_version": {' ||
  '"id": ' || to_json(vapp_versions.id) || ', ' ||
  '"version": ' || to_json(vapp_versions.version) || ', ' ||
  '"guid": ' || to_json(vapp_versions.guid) || ', ' ||
  '"notes": ' || COALESCE(to_json(vapp_versions.notes::text), 'null') || ', ' ||
  '"vappid": ' || to_json(vapp_versions.vappid) || ', ' ||
  '"published": ' || to_json(vapp_versions.published) || ', ' ||
  '"createdon": ' || to_json(vapp_versions.createdon) || ', ' ||
  '"expireson": ' || COALESCE(to_json(vapp_versions.expireson), 'null') || ', ' ||
  '"expiresin": ' || COALESCE(to_json(vapp_versions.expiresin), 'null') || ', ' ||
  '"enabled": ' || to_json(vapp_versions.enabled) || ', ' ||
  '"archived": ' || to_json(vapp_versions.archived) || ', ' ||
  '"status": ' || to_json(vapp_versions.status) || ', ' ||
  '"archivedon": ' || COALESCE(to_json(vapp_versions.archivedon), 'null') || ', ' ||
  REGEXP_REPLACE(REGEXP_REPLACE(app_to_json((SELECT appid FROM vapplications WHERE id = (SELECT vappid FROM vapp_versions WHERE id = $1))), '^{', ''), '}$', '')::text ||
'}}'::text
FROM vapp_versions WHERE id = $1;
$BODY$
  LANGUAGE sql STABLE
  COST 100;
ALTER FUNCTION public.vapp_version_to_json(integer)
  OWNER TO appdb;

CREATE OR REPLACE VIEW vapp_to_xml AS
 SELECT applications.id AS appid,
    vapplications.id AS vappid,
    XMLELEMENT(NAME "virtualization:appliance", XMLATTRIBUTES(vapp_versions.published AS published, vapp_versions.publishedon AS publishedon, vapp_versions.version AS version, vapplications.id AS vappid, applications.id AS appid, vapp_versions.id AS vaversionid, timezone('UTC'::text, vapp_versions.createdon::timestamp with time zone) AS createdon, vapp_versions.expireson AS expireson, vapp_versions.expiresin AS expiresin, vapp_versions.status AS status, vapp_versions.enabled AS enabled, vapp_versions.enabledon AS enabledon, vapp_versions.archived AS archived,
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
  GROUP BY tpublishedby.institution, tpublishedby.positiontypeid, tpublishedby.lastname, tpublishedby.cname, tpublishedby.firstname, tenabledby.institution, tenabledby.positiontypeid, tenabledby.lastname, tenabledby.cname, tenabledby.firstname, applications.id, vapplications.id, vapp_versions.published, vapp_versions.publishedby, vapp_versions.publishedon, vapp_versions.enabledby, vapp_versions.enabledon, vapp_versions.version, applications.guid, vapplications.name, vapp_versions.id, vapp_versions.createdon, vapp_versions.expireson, vapp_versions.expiresin, vapp_versions.status, vapp_versions.enabled, vapp_versions.archived
  ORDER BY vapp_versions.published, vapp_versions.archived, vapp_versions.archivedon DESC;

UPDATE vapp_versions SET expireson = NOW() + '1 year'::INTERVAL WHERE (expireson > NOW()) AND (expireson - NOW() > '365 days'::INTERVAL);
ALTER TABLE vapp_versions ALTER COLUMN expireson SET NOT NULL;
ALTER TABLE public.vapp_versions ADD CONSTRAINT chk_expireson CHECK ((expireson::timestamp with time zone - now()) <= '365 days'::INTERVAL);

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 16, 4, E'Add computed column expiresin to vapp_versions. Limit vapp_versions expiration to 1y max.'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=16 AND revision=4);

COMMIT;
