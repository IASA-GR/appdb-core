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
Previous version: 8.13.7
New version: 8.13.8
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

CREATE OR REPLACE FUNCTION public.app_target_privs_to_xml(
    _appid integer,
    _userid integer)
  RETURNS SETOF xml AS
$BODY$
BEGIN
RETURN QUERY SELECT target_privs_to_xml(applications.guid, $2, app_actions()) FROM applications WHERE id = $1;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION public.app_target_privs_to_xml(integer, integer)
  OWNER TO appdb;

CREATE OR REPLACE FUNCTION request_permissions_refresh() RETURNS void                                                                                                                      
LANGUAGE plpgsql                                                                                                                                                            
AS $$                                                                                                                                                                       
BEGIN                                                                                                                                                                           
	NOTIFY invalidate_cache, 'permissions';                                                                                                                                     
END;                                                                                                                                                                            
$$;

DROP FUNCTION public.grant_privilege(integer, uuid, uuid, integer);
DROP FUNCTION public.revoke_privilege(integer, uuid, uuid, integer);

CREATE OR REPLACE FUNCTION public.grant_privilege(
    _actionid integer,
    _actor uuid,
    _target uuid,
    editorid integer,
    dryrun boolean DEFAULT FALSE)
  RETURNS boolean AS
$BODY$
DECLARE g1 INT[];
DECLARE g2 INT[];
DECLARE g3 INT[];
BEGIN
	IF EXISTS (SELECT * FROM permissions WHERE actionid = 1 AND (actor = (SELECT guid FROM researchers WHERE id = editorid)) AND ((object = _target) OR (object IS NULL))) THEN
		-- editor has grant/revoke access
		-- g2: groups of the person who is editing the privilege
		g2 := (
			SELECT array_agg(groupid)
			FROM actor_group_members
			WHERE actorid = (SELECT guid FROM researchers WHERE id = editorid)
			AND CASE
				WHEN groupid IN (-3, -6, -9) THEN
					CASE WHEN (SELECT type FROM targets WHERE guid = _target) = 'app' THEN
						payload IN (SELECT id::text FROM appcountries WHERE appid = (SELECT id FROM applications WHERE guid = _target))
					ELSE
						FALSE
					END
				ELSE
					TRUE
			END
		);
		-- g3: groups of the actor who will receive the privilege
		g3 := (
			SELECT array_agg(groupid)
			FROM actor_group_members
			WHERE actorid = _actor
			AND CASE
				WHEN groupid IN (-3, -6, -9) THEN
					CASE WHEN (SELECT type FROM targets WHERE guid = _target) = 'app' THEN
						payload IN (SELECT id::text FROM appcountries WHERE appid = (SELECT id FROM applications WHERE guid = _target))
					ELSE
						FALSE
					END
				ELSE
					TRUE
			END
		);
		IF NOT EXISTS (SELECT * FROM permissions WHERE actionid = _actionid AND actor = _actor AND ((object = _target) OR (object IS NULL))) THEN
			IF NOT EXISTS (SELECT * FROM privileges WHERE actionid = _actionid AND actor = _actor AND ((object = _target) OR (object IS NULL)) AND revoked) THEN
				IF (-1 = ANY(g3) OR -2 = ANY(g3)) AND NOT (-1 = ANY(g2)) THEN
					-- only admins may alter privileges of other admins and of managers
					RETURN FALSE;
				ELSIF (-3 = ANY(g3)) AND NOT (-1 = ANY(g2) OR -2 = ANY(g2)) THEN
					-- only admins and managers may alter privileges of NILs
					RETURN FALSE;
				ELSE
					IF NOT dryrun THEN
						INSERT INTO privileges (actionid, object, actor, revoked, addedby) SELECT _actionid, _target, _actor, FALSE, editorid WHERE NOT EXISTS (SELECT * FROM permissions WHERE actionid = _actionid AND actor = _actor AND ((object = _target) OR (object IS NULL)));
					END IF;
					RETURN TRUE;
				END IF;
			ELSE
				-- privilege has been revoked. See if it can be un-revoked
				-- g1: groups of the person that added the privilege
				g1 := (
					SELECT array_agg(groupid) 
					FROM privileges 
					INNER JOIN actor_group_members AS agm ON agm.actorid = (SELECT guid FROM researchers WHERE id = privileges.addedby) 
					WHERE 
						revoked AND actionid = _actionid AND actor = _actor AND ((object = _target) OR (object IS NULL))
					AND CASE
						WHEN groupid IN (-3, -6, -9) THEN
							CASE WHEN (SELECT type FROM targets WHERE guid = _target) = 'app' THEN
								payload IN (SELECT id::text FROM appcountries WHERE appid = (SELECT id FROM applications WHERE guid = _target))
							ELSE
								FALSE
							END
						ELSE
							TRUE
					END
				);
				CASE
					WHEN (-1 = ANY(g1)) AND NOT (-1 = ANY(g2) OR -9 = ANY(g2)) THEN
						RETURN FALSE;
					WHEN (-2 = ANY(g1)) AND NOT (-1 = ANY(g2) OR -9 = ANY(g2)) THEN
						RETURN FALSE;
					WHEN -3 = ANY(g1) AND NOT (-2 = ANY(g2) OR -1 = ANY(g2) OR -9 = ANY(g2)) THEN
						RETURN FALSE;
					ELSE
						IF 
							_actionid = ANY(app_actions()) AND 
							_actor IN (
								SELECT guid FROM researchers WHERE id IN (
									SELECT addedby FROM applications WHERE guid = _target 
									UNION 
									SELECT owner FROM applications WHERE guid = _target
								)
							) AND NOT (-3 = ANY(g2) OR -2 = ANY(g2) OR -1 = ANY(g2))					
						THEN
							-- only admins, managers, and NILs can alter s/w owner/submitter privileges 
							RETURN FALSE;
						ELSIF (-1 = ANY(g3) OR -2 = ANY(g3)) AND NOT (-1 = ANY(g2)) THEN
							-- only admins may alter privileges of other admins and of managers
							RETURN FALSE;
						ELSIF (-3 = ANY(g3)) AND NOT (-1 = ANY(g2) OR -2 = ANY(g2)) THEN
							-- only admins and managers may alter privileges of NILs
							RETURN FALSE;
						ELSE
							IF (NOT _target IS NULL) AND (EXISTS (SELECT * FROM privileges WHERE actionid = _actionid AND actor = _actor AND object IS NULL)) AND (NOT EXISTS (SELECT * FROM privileges WHERE actionid = _actionid AND actor = _actor AND object = _target)) THEN
								-- cannot grant target-specific priv for when priv has been revoked for all targets
								RETURN FALSE;
							ELSE
								IF NOT dryrun THEN
									DELETE FROM privileges WHERE revoked AND actionid = _actionid AND actor = _actor AND ((object = _target) OR (object IS NULL));
								END IF;
								RETURN TRUE;
							END IF;
						END IF;
				END CASE;
			END IF;
		ELSE
			RETURN TRUE;
		END IF;
	ELSE
		-- editor does not have grant/revoke access 
		RETURN FALSE;
	END IF;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION public.grant_privilege(integer, uuid, uuid, integer, boolean)
  OWNER TO appdb;
    
CREATE OR REPLACE FUNCTION public.revoke_privilege(
    _actionid integer,
    _actor uuid,
    _target uuid,
    editorid integer,
    dryrun boolean DEFAULT FALSE)
  RETURNS boolean AS
$BODY$
DECLARE g1 INT[];
DECLARE g2 INT[];
DECLARE g3 INT[];
DECLARE sysonly BOOLEAN;
BEGIN
	IF editorid::text = (SELECT id FROM actors WHERE type = 'ppl' AND guid = _actor)::text THEN 
		-- do not alter own privs
		RETURN FALSE;
	END IF;
	IF EXISTS (SELECT * FROM permissions WHERE actionid = 1 AND (actor = (SELECT guid FROM researchers WHERE id = editorid)) AND ((object = _target) OR (object IS NULL))) THEN
		-- editor has grant/revoke access 	
		g2 := (
			SELECT array_agg(groupid)
			FROM actor_group_members
			WHERE actorid = (SELECT guid FROM researchers WHERE id = editorid)
			AND CASE
				WHEN ((SELECT type FROM targets WHERE guid = _target) = 'app') AND (groupid = -3) THEN
					payload IN (SELECT id::text FROM appcountries WHERE appid = (SELECT id FROM applications WHERE guid = _target))
				ELSE
					TRUE
			END
		);
		-- g3: groups of the actor who will receive the privilege
		g3 := (
			SELECT array_agg(groupid)
			FROM actor_group_members
			WHERE actorid = _actor
			AND CASE
				WHEN groupid IN (-3, -6, -9) THEN
					CASE WHEN (SELECT type FROM targets WHERE guid = _target) = 'app' THEN
						payload IN (SELECT id::text FROM appcountries WHERE appid = (SELECT id FROM applications WHERE guid = _target))
					ELSE
						FALSE
					END
				ELSE
					TRUE
			END
		);		
		IF EXISTS (SELECT * FROM privileges WHERE actionid = _actionid AND actor = _actor AND ((object = _target) OR (object IS NULL)) AND (NOT revoked)) THEN
			-- there exists an explicit privilege. See if it can be overridden
			sysonly := FALSE;
			-- g1: groups of the person that added the privilege
			g1 := (
				SELECT array_agg(groupid) 
				FROM privileges 
				INNER JOIN actor_group_members AS agm ON agm.actorid = (SELECT guid FROM researchers WHERE id = privileges.addedby) 
				WHERE 
					(NOT revoked) AND actionid = _actionid AND actor = _actor AND ((object = _target) OR (object IS NULL))
				AND CASE
					WHEN groupid IN (-3, -6, -9) THEN
						CASE WHEN (SELECT type FROM targets WHERE guid = _target) = 'app' THEN
							payload IN (SELECT id::text FROM appcountries WHERE appid = (SELECT id FROM applications WHERE guid = _target))
						ELSE
							FALSE
						END
					ELSE
						TRUE
				END
			);
		ELSIF EXISTS (SELECT * FROM permissions WHERE actionid = _actionid AND actor = _actor AND ((object = _target) OR (object IS NULL))) THEN
			sysonly := TRUE;
			-- there exist only system-generated privileges. See if it they can be overridden
			g1:= g3;
		ELSE 
			-- priv not granted or already revoked. do nothing
			RETURN true;
		END IF;		
		CASE 
			WHEN (-1 = ANY(g1)) AND NOT (-1 = ANY(g2) OR -9 = ANY(g2)) THEN
				RETURN FALSE;
			WHEN -2 = ANY(g1) AND NOT (-1 = ANY(g2) OR -9 = ANY(g2)) THEN
				RETURN FALSE;
			WHEN -3 = ANY(g1) AND NOT (-2 = ANY(g2) OR -1 = ANY(g2) OR -9 = ANY(g2)) THEN
				RETURN FALSE;
			ELSE
				IF 
					_actionid = ANY(app_actions()) AND 
					_actor IN (
						SELECT guid FROM researchers WHERE id IN (
							SELECT addedby FROM applications WHERE guid = _target 
							UNION 
							SELECT owner FROM applications WHERE guid = _target
						)
					) AND NOT (-3 = ANY(g2) OR -2 = ANY(g2) OR -1 = ANY(g2))					
				THEN
					-- only admins, managers, and NILs can alter s/w owner/submitter privileges 
					RETURN FALSE;
				ELSE
					IF (-1 = ANY(g3) OR -2 = ANY(g3)) AND NOT (-1 = ANY(g2)) THEN
						-- only admins may alter privileges of other admins and of managers
						RETURN FALSE;
					ELSIF (-3 = ANY(g3)) AND NOT (-1 = ANY(g2) OR -2 = ANY(g2)) THEN
						-- only admins and managers may alter privileges of NILs
						RETURN FALSE;
					ELSE
						IF NOT dryrun THEN
							IF sysonly THEN
								INSERT INTO privileges (actionid, object, actor, revoked, addedby) VALUES (_actionid, _target, _actor, TRUE, editorid);
							ELSE
								IF (NOT _target IS NULL) AND EXISTS (SELECT * FROM privileges WHERE actionid = _actionid AND actor = _actor AND object IS NULL) THEN
									INSERT INTO privileges (actionid, object, actor, revoked, addedby) VALUES (_actionid, _target, _actor, TRUE, editorid);
								ELSE
									DELETE FROM privileges WHERE actionid = _actionid AND object = _target AND actor = _actor AND (NOT revoked);
								END IF;
							END IF;
						END IF;
						RETURN TRUE;
					END IF;
				END IF;
		END CASE;
	ELSE
		-- editor does not have grant/revoke access 
		RETURN FALSE;
	END IF;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION public.revoke_privilege(integer, uuid, uuid, integer, boolean)
  OWNER TO appdb;

CREATE OR REPLACE FUNCTION public.can_grant_priv(
    _actionid integer,
    _actor uuid,
    _target uuid,
    editorid integer)
  RETURNS boolean AS
$BODY$
	SELECT grant_privilege($1, $2, $3, $4, TRUE);
$BODY$
  LANGUAGE sql STABLE STRICT
  COST 100;
ALTER FUNCTION public.can_grant_priv(integer, uuid, uuid, integer)
  OWNER TO appdb;

CREATE OR REPLACE FUNCTION public.can_revoke_priv(
    _actionid integer,
    _actor uuid,
    _target uuid,
    editorid integer)
  RETURNS boolean AS
$BODY$
	SELECT revoke_privilege($1, $2, $3, $4, TRUE);
$BODY$
  LANGUAGE sql STABLE STRICT
  COST 100;
ALTER FUNCTION public.can_revoke_priv(integer, uuid, uuid, integer)
  OWNER TO appdb;

CREATE OR REPLACE FUNCTION public.target_privs_to_xml(
    target_guid uuid,
    userid integer,
    actionlist integer[] DEFAULT NULL::integer[])
  RETURNS SETOF xml AS
$BODY$
DECLARE materialize BOOLEAN;
BEGIN
	RETURN QUERY
	WITH permissions AS (
		SELECT 
			-- DISTINCT ON ("system", actor, actionid)
			array_to_string(array_agg(array_to_string(ids,',')), ',') AS rules,
			MAX("system"::int)::boolean AS "system", 
			MAX((object IS NULL)::int)::boolean AS "global",
			actor, 
			actionid, 
			'granted' AS state,
			NULL::int AS revoked_by
		FROM permissions
		WHERE 
			(object = target_guid OR object IS NULL)
			AND CASE 
				WHEN NOT $3 IS NULL THEN
					actionid = ANY(actionlist)  
				ELSE
					TRUE
			END                 
		GROUP BY actor, actionid
		UNION
		SELECT 
			NULL,
			NULL,
			NULL,
			actors.guid,
			actions.id,
			'notgranted',
			NULL
		FROM actions
		CROSS JOIN actors
		WHERE NOT EXISTS (
			SELECT * FROM permissions WHERE actor = actors.guid AND (object = target_guid OR object IS NULL) AND actionid = actions.id
		) AND actors.guid IN (SELECT actor FROM __permissions WHERE actionid = ANY(actionlist) AND (object = target_guid OR object IS NULL))
		AND actions.id = ANY(actionlist)
		AND NOT EXISTS (
			SELECT * FROM privileges WHERE actor = actors.guid AND (object = target_guid OR object IS NULL) AND actionid = actions.id AND revoked
		)
		UNION
		SELECT
			NULL,
			FALSE,
			MAX((object IS NULL)::int)::boolean,
			actor,
			actionid,
			'revoked' AS state,
			(array_agg(addedby ORDER BY object NULLS FIRST))[1] AS addedby
		FROM privileges
		WHERE 
			revoked 
			AND (object = target_guid OR object IS NULL)
			AND actionid = ANY(actionlist)
		GROUP BY actor, actionid
	)
	SELECT
		xmlelement(
			name "privilege:actor",
			xmlattributes(
				actors.guid AS "suid",
				actors.type AS "type",
				actors.name AS "name",
				actors.cname as "cname",
				actors.id AS "id"
			),
			xmlagg(
				xmlelement(
					name "privilege:action",
					xmlattributes(
						rules,
						"system",
						"global",
						CASE 
							WHEN NOT $2 IS NULL AND state = 'granted' THEN 
								can_revoke_priv(actions.id, actors.guid, $1, $2)
							WHEN NOT $2 IS NULL AND (state = 'notgranted' OR state = 'revoked') THEN 
								can_grant_priv(actions.id, actors.guid, $1, $2) 
						END AS "canModify",
						actions.id AS "id",
						state,
						revoked_by AS "revokedBy"
						
					), actions.description
				) ORDER BY actions.id
			)
		)
	FROM permissions
	INNER JOIN actions ON actions.id = actionid
	INNER JOIN actors ON actors.guid = actor
	WHERE 
		NOT COALESCE(actors.hidden, FALSE)
	AND
		CASE WHEN (SELECT COUNT(*) FROM permissions AS pp WHERE pp.actor = permissions.actor AND pp.actionid = permissions.actionid GROUP BY actionid, actor) > 1 THEN
			NOT EXISTS (SELECT * FROM permissions AS pp WHERE pp.actionid = permissions.actionid AND pp.actor = permissions.actor AND pp.global IS TRUE)
		ELSE
			TRUE
		END 
	GROUP BY 
		actors.guid,
		actors.type,
		actors.name,
		actors.cname,
		actors.id
	ORDER BY actors.id;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION public.target_privs_to_xml(uuid, integer, integer[])
  OWNER TO appdb;

TRUNCATE TABLE cache.appprivsxmlcache;
UPDATE config SET data = '0' WHERE var = 'permissions_cache_dirty';

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 13, 8, E'Do not use cache for application privileges (obsolete). Minor performance improvements for application privileges'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=13 AND revision=8);

COMMIT;


