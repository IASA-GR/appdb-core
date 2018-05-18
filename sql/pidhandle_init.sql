------------ INITIAL IMPORT SCRIPT ---------------

START TRANSACTION;

INSERT INTO pidhandles (entryid, suffix, entrytype, result, url)

SELECT * FROM (
	SELECT id, guid, 'software'::e_entity, 0,
		'http://' || (SELECT data FROM config WHERE var = 'ui-host') || '/store/software/' || cname AS url
	FROM applications
	WHERE guid::TEXT NOT IN (SELECT suffix FROM pidhandles) AND NOT moderated AND NOT deleted AND metatype = 0
	-- LIMIT 10
) AS t1
UNION

SELECT * FROM (
	SELECT a.id, a.guid, 'vappliance'::e_entity, 0,
		'http://' || (SELECT data FROM config WHERE var = 'ui-host') || '/store/vappliance/' || a.cname
	FROM vapplications AS v
	INNER JOIN applications AS a ON a.id = v.appid
	WHERE a.guid::TEXT NOT IN (SELECT suffix FROM pidhandles) AND NOT moderated AND NOT deleted AND metatype = 1
	-- LIMIT 10
) AS t2
UNION

SELECT * FROM (
	SELECT id, guid, 'software_release'::e_entity, 0,
		'http://' || (SELECT data FROM config WHERE var = 'ui-host') || '/store/software/' || (SELECT cname FROM applications a WHERE a.id = appid) || '/releases/' || series || '/' || release
	FROM app_releases 
	WHERE state = 2 AND appid NOT IN (SELECT id FROM applications WHERE deleted OR moderated)
	-- LIMIT 10
) AS t3
UNION

SELECT * FROM (
	SELECT id, uguid, 'vappliance_version'::e_entity, 0,
		'http://' || (SELECT data FROM config WHERE var = 'ui-host') || '/store/vappliance/' || (SELECT cname FROM applications WHERE id = (SELECT appid FROM vapplications WHERE id = vappid)) || '/vaversion/' || 
			CASE WHEN published AND NOT archived THEN
				'latest'
			ELSE
				'previous/' || id::TEXT
			END
	FROM vapp_versions
	WHERE ((published AND NOT archived) OR (published AND archived)) AND (SELECT appid FROM vapplications WHERE id = vappid) NOT IN (SELECT id FROM applications WHERE deleted OR moderated) 
	AND vappid <> 25 -- exclude monitoring VA
	-- LIMIT 10
) AS t4
;

COMMIT;
