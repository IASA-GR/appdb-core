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
Previous version: 8.16.0
New version: 8.16.1
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

CREATE OR REPLACE FUNCTION vacreators_to_xml() RETURNS SETOF XML AS
$$
WITH conf AS (
	SELECT 'https://' || data AS uihost FROM config WHERE var = 'ui-host'
)
SELECT
	XMLELEMENT(
		name "user",
		XMLATTRIBUTES(
			r.id
		),
		XMLELEMENT(
			name "firstname",
			r.firstname
		),
		XMLELEMENT(
			name "lastname",
			r.lastname
		),
		XMLELEMENT(
			name "profile_url",
			COALESCE((SELECT uihost FROM conf), 'https://appdb.egi.eu') || '/store/person/' || r.cname
		),
		ARRAY_TO_STRING(ARRAY_AGG(DISTINCT
			XMLELEMENT(
				name "epuid",
				ua.accountid
			)::text
		),'')::XML,
		ARRAY_TO_STRING(ARRAY_AGG(DISTINCT
			XMLELEMENT(
				name "email",
				contacts.data
			)::text
		),'')::XML,
		ARRAY_TO_STRING(ARRAY_AGG(DISTINCT
			XMLELEMENT(
				name "image",
				XMLATTRIBUTES(
					vmiinstances.id AS id,
					COALESCE((SELECT uihost FROM conf), 'https://appdb.egi.eu') || '/store/vm/image/' || vmiinstances.guid::text || ':' || vmiinstances.id::text || '/' AS base_mpuri,
					COALESCE((SELECT uihost FROM conf), 'https://appdb.egi.eu') || '/store/vm/image/' || vmiinstances.guid::text || ':' || vmiinstances.id::text || '/xml?strict' AS meta,
					CASE
						WHEN r.id = vmiinstances.addedby THEN 'owner'
						ELSE 'contact'
					END AS user_role
				),
				vos.xml
			)::text
		),'')::XML
	)
FROM vowide_image_lists
INNER JOIN vowide_image_list_images ON vowide_image_list_images.vowide_image_list_id = vowide_image_lists.id
INNER JOIN vapplists ON vowide_image_list_images.vapplistid = vapplists.id
INNER JOIN vmiinstances ON vmiinstances.id = vapplists.vmiinstanceid
INNER JOIN vapp_versions ON vapp_versions.id = vapplists.vappversionid
INNER JOIN vapplications ON vapplications.id = vapp_versions.vappid
INNER JOIN applications ON applications.id = vapplications.appid
INNER JOIN researchers_apps ON researchers_apps.appid = applications.id
INNER JOIN researchers AS r ON (r.id = vmiinstances.addedby) OR (r.id = applications.addedby) OR (r.id = applications.owner) OR (r.id = researchers_apps.researcherid)
LEFT OUTER JOIN user_accounts AS ua ON ua.researcherid = r.id
LEFT OUTER JOIN contacts ON contacts.researcherid = r.id
INNER JOIN (
SELECT
	rrid AS rid,
	vid AS vid,
	XMLAGG(
			XMLELEMENT(
				name "vo",
				XMLATTRIBUTES(
					vos2.void AS id,
					vos2.name,
					'/store/vo/image/' || vowiguid::text || ':' || vowiid::text || '/' AS mpuri,
					'/store/vo/image/' || vowiguid::text || ':' || vowiid::text || '/xml?strict' AS meta
				)
			)
	) AS xml
	FROM (
		SELECT DISTINCT ON (vos.id, rr.id, vmiinstances2.id) rr.id AS rrid, vmiinstances2.id AS vid, vos.id AS void, vos.name, vowide_image_list_images2.guid AS vowiguid, vowide_image_list_images2.id AS vowiid
		FROM vowide_image_lists AS vowide_image_lists2
		INNER JOIN vowide_image_list_images AS vowide_image_list_images2 ON vowide_image_list_images2.vowide_image_list_id = vowide_image_lists2.id
		INNER JOIN vapplists AS vapplists2 ON vowide_image_list_images2.vapplistid = vapplists2.id
		INNER JOIN vmiinstances AS vmiinstances2 ON vmiinstances2.id = vapplists2.vmiinstanceid
		INNER JOIN vapp_versions AS vapp_versions2 ON vapp_versions2.id = vapplists2.vappversionid
		INNER JOIN vapplications AS vapplications2 ON vapplications2.id = vapp_versions2.vappid
		INNER JOIN applications AS applications2 ON applications2.id = vapplications2.appid
		INNER JOIN researchers_apps AS researchers_apps2 ON researchers_apps2.appid = applications2.id
		INNER JOIN researchers AS rr ON (rr.id = vmiinstances2.addedby) OR (rr.id = applications2.addedby) OR (rr.id = applications2.owner) OR (rr.id = researchers_apps2.researcherid)
		INNER JOIN vos ON vos.id = vowide_image_lists2.void
		WHERE
			(NOT vos.deleted)
		AND
			vowide_image_lists2.state = 'published'

		ORDER BY vos.id
	) AS vos2
	GROUP BY rrid, vid

) AS vos ON vos.rid = r.id AND vos.vid = vmiinstances.id
WHERE
	vowide_image_lists.state = 'published'
AND
	contacts.contacttypeid = 7
AND
	ua.stateid = 1
AND
ua.account_type = 'egi-aai'
GROUP BY
	r.id
ORDER BY r.id
$$ LANGUAGE SQL STABLE;
ALTER FUNCTION vacreators_to_xml() OWNER TO appdb;

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 16, 1, E'Add vacreators_to_xml() function'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=16 AND revision=1);

COMMIT;
