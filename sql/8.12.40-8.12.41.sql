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
Previous version: 8.12.40
New version: 8.12.41
Author: wvkarag@kadath.priv.iasa.gr
*/
START TRANSACTION;

CREATE OR REPLACE FUNCTION clean_old_fedcloud_images(keep INT DEFAULT 200) 
RETURNS INT
AS
$$
BEGIN
DELETE FROM vapplists WHERE vmiinstanceid IN (
	SELECT id FROM vmiinstances WHERE vmiflavourid IN (
		SELECT id FROM vmiflavours WHERE vmiid IN (
			SELECT id FROM vmis WHERE vappid = 25 AND id NOT IN (
				SELECT id FROM vmis WHERE vappid = 25 ORDER BY id DESC LIMIT $1
			)
		)
	) AND id NOT IN (
		SELECT vowide_vmiinstanceid FROM va_provider_images WHERE NOT vowide_vmiinstanceid IS NULL
		UNION
		SELECT vmiinstanceid FROM va_provider_images
	) AND id NOT IN (
		SELECT vmiinstanceid FROM vmiinstance_contextscripts
	)
) AND id NOT IN (
	SELECT vapplistid FROM vowide_image_list_images
);

DELETE FROM vmiinstances WHERE vmiflavourid IN (
	SELECT id FROM vmiflavours WHERE vmiid IN (
		SELECT id FROM vmis WHERE vappid = 25  AND id NOT IN (
			SELECT id FROM vmis WHERE vappid = 25 ORDER BY id DESC LIMIT $1
		)
	)
) AND id NOT IN (
		SELECT vowide_vmiinstanceid FROM va_provider_images WHERE NOT vowide_vmiinstanceid IS NULL
		UNION
		SELECT vmiinstanceid FROM va_provider_images
) AND id NOT IN (
	SELECT vmiinstanceid FROM vapplists WHERE id IN (
		SELECT vapplistid FROM vowide_image_list_images
	)
) AND id NOT IN (
	SELECT vmiinstanceid FROM vmiinstance_contextscripts
);

DELETE FROM vmiflavours WHERE vmiid IN (
	SELECT id FROM vmis WHERE vappid = 25  AND id NOT IN (
		SELECT id FROM vmis WHERE vappid = 25 ORDER BY id DESC LIMIT $1
	) AND NOT EXISTS (
		SELECT * FROM vmiinstances WHERE vmiflavourid = vmiflavours.id
	)
);

DELETE FROM vmis WHERE vappid = 25 AND id NOT IN (
	SELECT id FROM vmis WHERE vappid = 25 ORDER BY id DESC LIMIT $1
) AND NOT EXISTS (
	SELECT * FROM vmiflavours WHERE vmiid = vmis.id
);

RETURN (SELECT COUNT(*) FROM vmis WHERE vappid = 25);
END;
$$ LANGUAGE plpgsql;
ALTER FUNCTION clean_old_fedcloud_images(INT) OWNER TO appdb;

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 12, 41, E'Added function to cleanold fedcloud tesing VA images'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=12 AND revision=41);

COMMIT;
