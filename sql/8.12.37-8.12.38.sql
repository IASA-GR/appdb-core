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
Previous version: 8.12.37
New version: 8.12.38
Author: wvkarag@lovecraft.priv.iasa.gr
*/

CREATE OR REPLACE FUNCTION public.update_vowide_image_list(_void integer, _vappversionid integer DEFAULT NULL::integer, _userid integer DEFAULT NULL::integer)
 RETURNS void
 LANGUAGE plpgsql
AS $function$
DECLARE a1 int[];
DECLARE old_vappverid int;
BEGIN
	IF NOT $2 IS NULL THEN
		$2 = (SELECT DISTINCT vappversionid
		FROM vowide_image_list_images
		INNER JOIN vapplists ON vapplists.id = vowide_image_list_images.vapplistid
		WHERE vowide_image_list_images.vowide_image_list_id = (SELECT id FROM vowide_image_lists WHERE void = $1 AND state = 'draft')
		AND vappversionid IN (SELECT vappversionid FROM __vaviews WHERE __vaviews.va_id IN (SELECT va_id FROM __vaviews WHERE vappversionid = $2)) AND vappversionid <> $2);
	END IF;
	BEGIN	
		SELECT array_agg(vapplists.id)
		FROM vapplists 
		WHERE
			vapplists.vappversionid IN (
				SELECT id FROM vapp_versions WHERE vappid IN (
					SELECT va_id FROM __vaviews WHERE vapplistid IN (
						SELECT vapplistid FROM vowide_image_list_images 
						WHERE vowide_image_list_id = (SELECT id FROM vowide_image_lists WHERE void = $1 AND state = 'draft')
						AND state <> 'up-to-date'
					) AND CASE WHEN $2 IS NULL THEN TRUE ELSE va_id IN (SELECT va_id FROM __vaviews AS vav WHERE vav.vappversionid = $2) END
				)
				AND published AND NOT archived AND enabled
			)
		INTO a1;

		RAISE NOTICE '%', a1;

		RAISE NOTICE '%', (SELECT array_agg(vapplistid) FROM vowide_image_list_images
		WHERE vowide_image_list_id = (SELECT id FROM vowide_image_lists WHERE void = $1 AND state = 'draft')
		AND CASE 
			WHEN NOT $2 IS NULL THEN 
				vapplistid IN (SELECT id FROM vapplists INNER JOIN __vaviews ON __vaviews.vappversionid = $2)
			ELSE 
				TRUE
		END
		AND state <> 'up-to-date');

		DELETE FROM vowide_image_list_images
		WHERE vowide_image_list_id = (SELECT id FROM vowide_image_lists WHERE void = $1 AND state = 'draft')
		AND CASE 
			WHEN NOT $2 IS NULL THEN 
				vapplistid IN (SELECT id FROM vapplists INNER JOIN __vaviews ON __vaviews.vappversionid = vapplists.vappversionid AND __vaviews.vappversionid = $2)
			ELSE 
				TRUE
		END
		AND state <> 'up-to-date';
		
		INSERT INTO vowide_image_list_images (vowide_image_list_id, vapplistid) 
		SELECT DISTINCT (SELECT id FROM vowide_image_lists WHERE void = $1 AND state = 'draft'), id 
		FROM UNNEST(a1) AS id
		EXCEPT SELECT vowide_image_list_id, vapplistid FROM vowide_image_list_images;

		IF NOT $3 IS NULL THEN
			UPDATE vowide_image_lists SET alteredby = $3 WHERE id = (SELECT id FROM vowide_image_lists WHERE void = $1 AND state = 'draft');
		END IF;
	END;
END;
$function$;

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 12, 38, E'Use live instead of materialized version of vaviews when updating vowide image lists'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=12 AND revision=38);
