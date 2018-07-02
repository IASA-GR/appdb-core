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
Previous version: 8.20.2
New version: 8.20.3
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

CREATE OR REPLACE FUNCTION public.filteritems(
    fltstr text,
    m_from text,
    m_where text,
    itemtype text)
  RETURNS text AS
$BODY$
DECLARE h TEXT;
DECLARE t TEXT;
DECLARE rank TEXT;
DECLARE _rank TEXT;
DECLARE cols TEXT[];
DECLARE cachecount BIGINT;
BEGIN
	IF fltstr IS NULL THEN fltstr = ''; END IF;
	IF m_from IS NULL THEN m_from = ''; END IF;
	IF m_where IS NULL THEN m_where = ''; END IF;
	fltstr := TRIM(fltstr);
	m_from := TRIM(m_from);
	m_where := TRIM(m_where);
	IF itemtype IS NULL THEN itemtype = ''; END IF;
	IF itemtype = 'rankedapps' THEN
		t := 'applications';
		-- rank := 'rankapp(' || t || '.*, ''' || fltstr || ''') as rank';
		rank := 'rankapp';
		cols := (SELECT array_agg(column_name::text ORDER BY ordinal_position) FROM INFORMATION_SCHEMA.columns WHERE table_name = 'applications' AND table_schema = 'public');
	ELSIF itemtype = 'rankedppl' THEN
		t := 'researchers';
		-- rank := 'rankppl(' || t || '.*, ''' || fltstr || ''') as rank';
		rank := 'rankppl';
		cols := (SELECT array_agg(column_name::text ORDER BY ordinal_position) FROM INFORMATION_SCHEMA.columns WHERE table_name = 'researchers' AND table_schema = 'public');
	ELSIF itemtype = 'rankedvos' THEN
		t := 'vos';
		-- rank := 'rankvo(' || t || '.*, ''' || fltstr || ''') as rank';
		rank := 'rankvo';
		cols := (SELECT array_agg(column_name::text ORDER BY ordinal_position) FROM INFORMATION_SCHEMA.columns WHERE table_name = 'vos' AND table_schema = 'public');
	ELSIF itemtype = 'rankedsites' THEN
		t := 'sites';
		-- rank := 'ranksite(' || t || '.*, ''' || fltstr || ''') as rank';
		rank := 'ranksite';
		cols := (SELECT array_agg(column_name::text ORDER BY ordinal_position) FROM INFORMATION_SCHEMA.columns WHERE table_name = '__sites' AND table_schema = 'public');
	END IF;
	_rank := '0 as rank';
	h := MD5(m_from || ' ' || m_where);
	IF m_where = 'WHERE ()' THEN m_where = ''; END IF;
	IF EXISTS (SELECT * FROM config WHERE var = 'disable_filtercache' AND data::BOOLEAN IS TRUE) THEN
		DELETE FROM cache.filtercache WHERE hash = h;
	END IF;
	cachecount := 0;
	BEGIN
		EXECUTE 'SELECT COUNT(*) FROM cache.filtercache_' || h INTO cachecount;
	EXCEPTION
		WHEN OTHERS THEN
	END;
	IF (NOT EXISTS (SELECT hash FROM cache.filtercache WHERE hash = h AND invalid IS FALSE)) OR (cachecount = 0) THEN
		EXECUTE 'DROP TABLE IF EXISTS cache.filtercache_' || h || '; ' ||
			'CREATE TABLE cache.filtercache_' || h || ' AS SELECT DISTINCT ON (' || t || '.id) ' || t || '.*, ' || _rank || ' ' || m_from || ' ' || m_where || '; ' ||
			'UPDATE cache.filtercache_' || h || ' SET rank = ' || rank || '((' || array_to_string(cols,' ,') || '), ''' || REPLACE(fltstr,'''','''''') || ''')';
		IF NOT EXISTS (SELECT hash FROM cache.filtercache WHERE hash = h) THEN
			INSERT INTO cache.filtercache (hash, m_from, m_where, fltstr) SELECT h, m_from, m_where, fltstr WHERE NOT EXISTS (SELECT hash FROM cache.filtercache AS c WHERE c.hash = h);
		ELSE
			UPDATE cache.filtercache SET usecount = usecount+1, invalid = FALSE WHERE hash = h;
		END IF;
	ELSE
		UPDATE cache.filtercache SET usecount = usecount+1 WHERE hash = h;
	END IF;
	RETURN h;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION public.filteritems(text, text, text, text)
  OWNER TO appdb;

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 20, 3, E'Avoid cache table race conditions in filteritems function'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=20 AND revision=3);

COMMIT;	
