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
Previous version: 8.14.5
New version: 8.14.6
Author: root@appdb.marie.hellasgrid.gr
*/

DROP INDEX IF EXISTS idx_vaviews_unique;
CREATE UNIQUE INDEX idx_vaviews_unique ON vaviews (vapplistid, vmiinstanceid, vmiflavourid, vmiid, va_id, vappversionid, appid);

CREATE OR REPLACE FUNCTION public.trfn_refresh_vaviews()
 RETURNS trigger
 LANGUAGE plpgsql
AS $function$
BEGIN
        REFRESH MATERIALIZED VIEW CONCURRENTLY vaviews;
        IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN RETURN NEW; ELSE RETURN OLD; END IF;
END;
$function$;
ALTER FUNCTION trfn_refresh_vaviews OWNER TO appdb;

CREATE OR REPLACE FUNCTION CRC32(text_string text)
 RETURNS bigint
 LANGUAGE plpgsql
 IMMUTABLE
AS $function$
DECLARE
    tmp bigint;
    i int;
    j int;
    byte_length int;
    binary_string bytea;
BEGIN
    IF text_string = '' THEN
        RETURN 0;
    END IF;

    i = 0;
    tmp = 4294967295;
    byte_length = bit_length(text_string) / 8;
    binary_string = decode(replace(text_string, E'\\\\', E'\\\\\\\\'), 'escape');
    LOOP
        tmp = (tmp # get_byte(binary_string, i))::bigint;
        i = i + 1;
        j = 0;
        LOOP
            tmp = ((tmp >> 1) # (3988292384 * (tmp & 1)))::bigint;
            j = j + 1;
            IF j >= 8 THEN
                EXIT;
            END IF;
        END LOOP;
        IF i >= byte_length THEN
            EXIT;
        END IF;
    END LOOP;
    RETURN (tmp # 4294967295);
END
$function$
ALTER FUNCTION CRC32(text) OWNER TO appdb;

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 14, 6, E'Added CRC32 function. Added unique index to vaviews materialized view'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=14 AND revision=6);
