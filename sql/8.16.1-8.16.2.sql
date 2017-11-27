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
Previous version: 8.16.1
New version: 8.16.2
Author: wvkarag@lovecraft.priv.iasa.gr
*/

-- Function: public.dataset_location_to_xml(integer[])

-- DROP FUNCTION public.dataset_location_to_xml(integer[]);

START TRANSACTION;

CREATE OR REPLACE FUNCTION public.dataset_location_to_xml(mid integer[])
  RETURNS SETOF xml AS
$BODY$
SELECT
    XMLELEMENT(
        name "dataset:location",
        XMLATTRIBUTES(
            dataset_locations.id,
            dataset_locations.dataset_version_id AS datasetversionid,
            (SELECT datasetid FROM dataset_versions WHERE id = dataset_locations.dataset_version_id) AS datasetid,
            dataset_locations.addedon,
            dataset_locations.is_master AS master,
            dataset_locations.is_public AS public
        ),
        XMLELEMENT(
            name "dataset:addedby",
            XMLATTRIBUTES(
                dataset_locations.addedby AS id,
                (SELECT cname FROM researchers WHERE id = dataset_locations.addedby) AS cname
            ),
            (SELECT name FROM researchers WHERE id = dataset_locations.addedby)
        ),
        XMLELEMENT(
            name "dataset:uri",
            dataset_locations.uri
        ),
        XMLELEMENT(
            name "dataset:interface",
            XMLATTRIBUTES(connection_type AS id),
            dataset_conn_types.name
        ),
        XMLELEMENT(
            name "dataset:exchange_format",
            XMLATTRIBUTES(
                exchange_fmt AS id,
                dataset_exchange_formats.shortname AS alias
            ),
            dataset_exchange_formats.name
        ),
        XMLELEMENT(
            name "dataset:notes",
            dataset_locations.notes
        ),
	CASE WHEN NOT organizationid IS NULL THEN
	ARRAY_TO_STRING(ARRAY_AGG(DISTINCT
		XMLELEMENT(
			name "dataset:organization",
			XMLATTRIBUTES(
				organizations.id AS id,
				organizations.shortname,
				organizations.name,
				organizations.sourceid
			),
			XMLELEMENT(
				name "organization:url",
				XMLATTRIBUTES('website' AS type ),
				organizations.websiteurl
			),
			country_to_xml(organizations.countryid)
		)::text
	),'')::xml END,
	(
	SELECT ARRAY_TO_STRING(ARRAY_AGG(DISTINCT s.x::text),'')::xml FROM (
		SELECT site_to_xml(sites.guid::TEXT) as x FROM sites
		INNER JOIN dataset_location_sites on dataset_location_sites.siteid = sites.id
		WHERE dataset_location_sites.dataset_location_id=dataset_locations.id
	) AS s )
    )
FROM
    dataset_locations
INNER JOIN dataset_conn_types ON dataset_conn_types.id = dataset_locations.connection_type
INNER JOIN dataset_exchange_formats ON dataset_exchange_formats.id = exchange_fmt
LEFT OUTER JOIN organizations ON organizations.id = ANY(dataset_locations.organizationid)
WHERE dataset_locations.id = ANY(mid)
GROUP BY
	dataset_locations.id,
	dataset_conn_types.name,
	dataset_exchange_formats.shortname,
	dataset_exchange_formats.name
$BODY$
  LANGUAGE sql STABLE
  COST 100
  ROWS 1000;
ALTER FUNCTION public.dataset_location_to_xml(integer[])
  OWNER TO appdb;

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 16, 2, E'Fix regression error in dataset_location_to_xml function'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=16 AND revision=2);

COMMIT;
