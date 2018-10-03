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
Previous version: 8.22.6
New version: 8.22.7
Author: wvkarag@kadath.priv.iasa.gr
*/

START TRANSACTION;

CREATE OR REPLACE FUNCTION public.validate_app_name(
    text,
    integer DEFAULT NULL::integer)
  RETURNS text AS
$BODY$
DECLARE
        p TEXT;
        err TEXT;
        reason TEXT;
        exids INT[];
        exnames TEXT[];
	excnames TEXT[];
	exmetatypes INT[];
BEGIN
        -- check min length
        IF (LENGTH($1) < 3) OR (LENGTH($1) > 50) THEN
                err := 'Invalid length';
		RETURN '{"valid": false, "error": ' || to_json(err) || ', "reason": {"min": 3, "max": 50}}';
        END IF;

        -- check validity
        IF NOT $1 ~ '^[A-Za-z0-9 *.+,&!#@=_^(){}\[\]-]+$' THEN
                err := 'Invalid character';
                RETURN '{"valid": false, "error": ' || to_json(err) || ', "reason": ' || to_json('*.+,&!#@=_^(){}[]-'::text) || '}';
        END IF;

        -- check similarity
        SELECT array_agg(id ORDER BY id), array_agg(name ORDER BY id), array_agg(cname ORDER BY id), array_agg(metatype ORDER BY id) FROM app_name_available($1)
		INTO exids, exnames, excnames, exmetatypes;
        IF ARRAY_LENGTH(exids, 1) > 0 THEN
                IF ($2 IS NULL) OR (NOT $2 = ANY(exids)) THEN
                        err := 'Invalid name';
                        RETURN '{"valid": false, "error": ' || to_json(err) || ', "reason": {"ids":' || to_json(exids) || ', "names": ' || to_json(exnames) ||  ', "cnames": ' || to_json(excnames) || ', "metatypes": ' || to_json(exmetatypes) || '}}';
                END IF;
        END IF;

        IF EXISTS (SELECT 1 FROM applications WHERE (name ILIKE '%' || $1 || '%') AND (NOT deleted) AND (($2 IS NULL) OR ((NOT $2 IS NULL) AND (id <> $2)))) THEN
		SELECT array_agg(id ORDER BY id), array_agg(name ORDER BY id), array_agg(metatype ORDER BY id) FROM applications WHERE (name ILIKE '%' || $1 || '%') AND (NOT deleted) AND (($2 IS NULL) OR ((NOT $2 IS NULL) AND (id <> $2)))
			INTO exids, exnames, exmetatypes;
                RETURN '{"valid": true, "warning": true, "reason": {"ids":' || to_json(exids) || ', "names": ' || to_json(exnames) || ', "metatypes": ' || to_json(exmetatypes) || '}}';
        END IF;

	SELECT validate_app_cname($1, $2) INTO p;
        IF NOT p IS NULL THEN
                err := 'Invalid cname';
                RETURN '{"valid": false, "error": ' || to_json(err) || ', "reason": ' || to_json(p) || '}';
        END IF;

        RETURN '{"valid": true}';
END;
$BODY$
  LANGUAGE plpgsql STABLE
  COST 100;
ALTER FUNCTION public.validate_app_name(text, integer)
  OWNER TO appdb;

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 22, 7, E'Minor additions to JSON returned by validate_app_name function'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=22 AND revision=7);

COMMIT;	
