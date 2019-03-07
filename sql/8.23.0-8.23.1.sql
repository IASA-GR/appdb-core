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
Previous version: 8.23.0
New version: 8.23.1
Author: wvkarag@lovecraft.priv.iasa.gr
*/

CREATE OR REPLACE FUNCTION endorsables_to_xml()
RETURNS SETOF XML
LANGUAGE SQL
STABLE
AS $$
	SELECT
	  XMLELEMENT(
	    name "endorsable:endorsable",
	    XMLATTRIBUTES(
	      'vapplianceVersion' AS "kind",
	      -- '' || (SELECT data FROM pidurl) || versionPID.suffix AS "pid",
	      'http://hdl.handle.net/' || vapp_versions.pidhandle as "pid",
	      vaviews.va_version AS "name",
	      vaviews.va_version AS "cname",
	      -- applications.cname || '/' || vaviews.va_version AS "cname",
	      vaviews.va_version_publishedon AS "createdOn"
	    ),
	    xmlelement(
	      name "endorsable:signatureType",
	      vaviews.checksumfunc
	    ),
	    xmlelement(
	      name "endorsable:signature",
	      vaviews.checksum
	    ),
	    -- (SELECT XMLAGG(dataxml::xml) FROM VOImages WHERE VOImages.vapplistid = vaviews.vapplistid),
	    array_to_string(array_agg(DISTINCT voimages.dataxml), '')::xml,
	    -- (SELECT XMLAGG(dataxml::xml) FROM siteVOImages WHERE siteVOImages.vappversionid = vaviews.vappversionid),
	    array_to_string(array_agg(DISTINCT sitevoimages.dataxml), '')::xml,
	    XMLELEMENT(
	      name "endorsable:parent",
	      xmlattributes(
	        'vappliance' AS "kind",
	        applications.id AS "id",
	        -- (SELECT data FROM pidurl) || appPID.suffix AS "pid",
	        'http://hdl.handle.net/' || applications.pidhandle AS "pid",
	        applications.name AS "name",
	        applications.cname AS "cname"
	      ),
	      xmlelement(
	        name "endorsable:description",
	        applications.description
	      ),
	      xmlelement(
	        name "endorsable:url",
	        -- 'http://' || (SELECT data FROM uihost) || '/store/vappliance/' || applications.cname
	        applications.curl
	      ),
	      xmlelement(
	        name "endorsable:dataUrl",
	        -- 'http://' || (SELECT data FROM apihost) || '/rest/1.0/applications/' || applications.id
	        applications.dataurl
	      ),
	      xmlelement(
	        name "endorsable:imageUrl",
	        -- 'http://' || (SELECT data FROM uihost) || '/apps/getlogo?id=' || applications.id
	        applications.logourl
	      ),
	      xmlelement(
	        name "endorsable:attribute",
	        xmlattributes(
	          'deleted' AS "name",
	          applications.deleted AS "value"
	        )
	      ),
	      xmlelement(
	        name "endorsable:attribute",
	        xmlattributes(
	         'moderated' AS "name",
	         applications.moderated AS "value"
	        )
	      )
	    ),
	    xmlelement(
	      name "endorsable:meta",
	      xmlelement(
	        name "endorsable:source",
	        xmlattributes(
	          (SELECT data FROM config WHERE var = 'ui-host') AS "name",
	          vaviews.va_version_guid AS "guid",
	          vaviews.vappversionid AS "id",
	          -- 'http://' || (SELECT data FROM config WHERE var = 'ui-host') || '/store/vappliance/' || applications.cname || '/vaversion/' || CASE WHEN vaviews.va_version_archived THEN 'prev/' ELSE '' END || vaviews.vappversionid AS "href",
	          vapp_versions.curl AS "href",
	          NOW() AS "harvestedOn"
	        )
	      )
	    ),
	    xmlelement(
	      name "endorsable:attribute",
	      xmlattributes(
	        'id' AS "name",
	        vaviews.vappversionid AS "value"
	      )
	     ),
	    xmlelement(
	      name "endorsable:attribute",
	      xmlattributes(
	        'guid' AS "name",
	        vaviews.va_version_guid AS "value"
	      )
	    ),
	    xmlelement(
	      name "endorsable:attribute",
	      xmlattributes(
	        'published' AS "name",
	        vaviews.va_version_published AS "value",
	        vaviews.va_version_publishedon AS "since"
	      )
	    ),
	    xmlelement(
	      name "endorsable:attribute",
	      xmlattributes(
	        'archived' AS "name",
	        vaviews.va_version_archived AS "value",
	        vaviews.va_version_archivedon AS "since"
	      )
	    ),
	    xmlelement(
	      name "endorsable:attribute",
	      xmlattributes(
	        'expired' AS "name",
	        CASE WHEN vaviews.va_version_expireson < NOW() THEN TRUE ELSE FALSE END AS "value",
	        CASE WHEN vaviews.va_version_expireson < NOW() THEN vaviews.va_version_expireson ELSE NULL END AS "since"
	      )
	    ),
	    xmlelement(
	      name "endorsable:attribute",
	      xmlattributes(
	        'expiresOn' AS "name",
	        vaviews.va_version_expireson AS "value"
	      )
	    ),
	    xmlelement(
	      name "endorsable:attribute",
	      xmlattributes(
	        'obsolete' AS "name",
	        vaviews.va_version_archived AS "value",
	        vaviews.va_version_archivedon AS "since"
	      )
	    ),
	    xmlelement(
	      name "endorsable:attribute",
	      xmlattributes(
	        'enabled' AS "name",
	        vaviews.va_version_enabled AS "value"
	      )
	    ),
	    xmlelement(
	      name "endorsable:attribute",
	      xmlattributes(
          'isprivate' AS "name",
          vaviews.imglst_private AS "value"
        )
      ),
      CASE WHEN NOT vaviews.imglst_private THEN
        xmlelement(
          name "endorsable:attribute",
          xmlattributes(
            'size' AS "name",
            vaviews."size" AS "value"
          )
        )
      ELSE NULL::xml END,
      CASE WHEN NOT vaviews.imglst_private THEN
        xmlelement(
          name "endorsable:attribute",
          xmlattributes(
            'location' AS "name",
            vaviews.uri AS "value"
          )
        )
      ELSE NULL::xml END,
      xmlelement(
        name "endorsable:attribute",
        xmlattributes(
	        'publisherId' AS "name",
	        vappversion_publishers.id AS "value"
	      )
	    ),
	    xmlelement(
	      name "endorsable:attribute",
	      xmlattributes(
	        'publisherName' AS "name",
	        vappversion_publishers.firstname || ' ' || vappversion_publishers.lastname AS "value"
	      )
	    ),
	    xmlelement(
	      name "endorsable:attribute",
	      xmlattributes(
	        'publisherCName' AS "name",
	        vappversion_publishers.cname AS "value"
	      )
	    ),
	    xmlelement(
	      name "endorsable:attribute",
	      xmlattributes(
	        'publisherUrl' AS "name",
	        vappversion_publishers.curl AS "value"
	      )
	    )
	) AS dataxml
	FROM applications
	INNER JOIN vaviews on vaviews.appid = applications.id
	INNER JOIN vapp_versions ON vapp_versions.id = vaviews.vappversionid
	INNER JOIN  researchers AS vappversion_publishers on vappversion_publishers.id = vaviews.va_version_publishedby
	-- LEFT OUTER JOIN pidhandles AS appPID on appPID.entryid = applications.id AND appPID.entrytype = 'vappliance'
	-- LEFT OUTER JOIN pidhandles AS versionPID on versionPID.entryid = vaviews.vappversionid AND versionPID.entrytype = 'vappliance_version'
	LEFT OUTER JOIN (
		SELECT
			DISTINCT
			vos.id,
			volistimages.vapplistid,
			xmlelement(
	    		name "endorsable:referrer",
	    		xmlattributes(
	      			'vo' AS "type",
	      			vos."name" AS "name",
	      			vowide_image_lists.published_on AS "since"
	    		)
	  		)::text AS dataxml
		FROM vos
	  	INNER JOIN vowide_image_lists ON vowide_image_lists.void = vos.id AND vowide_image_lists.state = 'published'
	  	INNER JOIN vowide_image_list_images AS volistimages ON volistimages.vowide_image_list_id = vowide_image_lists.id
	  	ORDER BY vos.id, volistimages.vapplistid
	) AS voimages ON voimages.vapplistid = vaviews.vapplistid
	LEFT OUTER JOIN (
		SELECT
			DISTINCT -- (va_providers.sitename, vaviews.vappversionid),
			va_providers.sitename,
			vaviews.vappversionid,
			xmlelement(
	    		name "endorsable:referrer",
	      		xmlattributes(
	        		'site' AS "type",
	        		va_providers.sitename AS "name",
	        		'vo' AS "refSourceType",
	        		vos."name" AS "refSource",
	        		CASE vowide_image_lists.state WHEN 'obsolete' THEN 'obsolete' else 'ok' END AS "refSourceValidity"
	      		)
	    	)::text AS dataxml
		 FROM va_providers
		 INNER JOIN va_provider_images ON va_provider_images.va_provider_id = va_providers.id AND va_provider_images.content_type = 'vo'
		 INNER JOIN vowide_image_list_images ON vowide_image_list_images.id =va_provider_images.vowide_vmiinstanceid
		 INNER JOIN vowide_image_lists ON vowide_image_lists.id = vowide_image_list_images.vowide_image_list_id AND vowide_image_lists.state <> 'draft'
		 INNER JOIN vos ON vos.id = vowide_image_lists."void"
		 INNER JOIN vaviews ON vaviews.vapplistid = vowide_image_list_images.vapplistid
		 -- GROUP BY va_providers.sitename, vaviews.vappversionid, vowide_image_lists.state, vos."name"
		 ORDER BY va_providers.sitename, vaviews.vappversionid
	) AS sitevoimages ON sitevoimages.vappversionid = vaviews.vappversionid
	WHERE vaviews.va_version_published AND (NOT vaviews.imglst_private) AND (NOT vaviews.va_version_publishedon IS NULL) AND vaviews.va_version_expireson > NOW()
	GROUP BY vaviews.va_version, vaviews.vappversionid,
	-- versionpid.suffix,
	applications.id,
	applications.name,
	applications.cname,
	applications.description,
	-- apppid.suffix,
	vaviews.vapplistid,
	vaviews.va_version_published,
	vaviews.va_version_publishedon,
	vaviews.va_version_archived,
	vaviews.va_version_archivedon,
	vaviews.va_version_expireson,
	vappversion_publishers.id,
	vaviews.va_version_enabled,
	vaviews.checksumfunc,
	vaviews.checksum,
	vaviews.va_version_guid,
	vaviews.vappversionid,
  	vaviews.imglst_private,
  	vaviews."size",
  	vaviews.uri,
	vapp_versions.*
	ORDER BY applications.id, vaviews.va_version_publishedon
$$;
ALTER FUNCTION endorsables_to_xml() OWNER TO appdb;

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 23, 1, E'Updated endorsables_to_xml function'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=23 AND revision=1);
