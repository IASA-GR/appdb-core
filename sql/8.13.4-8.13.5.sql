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
Previous version: 8.13.4
New version: 8.13.5
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

CREATE OR REPLACE FUNCTION public.publish_vowide_image_list(
    _void integer,
    _userid integer)
  RETURNS integer AS
$BODY$
DECLARE listid INT;
BEGIN
	listid := (SELECT id FROM vowide_image_lists WHERE void = $1 AND state = 'draft');
	IF NOT listid IS NULL THEN
		UPDATE vowide_image_lists SET state = 'obsolete' WHERE void = $1 AND state = 'published';
		DELETE FROM vowide_image_list_images WHERE vowide_image_list_id = listid AND vapplistid IN (
			SELECT vapplistid 
			FROM vaviews 
			INNER JOIN applications ON applications.id = vaviews.appid
			WHERE applications.deleted -- OR applications.moderated
		);
		UPDATE vowide_image_lists SET state = 'published', published_on = NOW(), publishedby = $2 WHERE id = listid;
		NOTIFY clean_cache;
		REFRESH MATERIALIZED VIEW vaviews;
		RETURN listid;
	ELSE 
		RETURN NULL;
	END IF;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION public.publish_vowide_image_list(integer, integer)
  OWNER TO appdb;
COMMENT ON FUNCTION public.publish_vowide_image_list(integer, integer) IS 'Sets currently published list to "obsolete" state, and promotes the draft version to "published". Returns the id of the published image list or NULL if no draft version exists';

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 13, 5, E'Refresh vaviews materialized view after publishing a VO-wide image list'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=13 AND revision=5);

COMMIT;
