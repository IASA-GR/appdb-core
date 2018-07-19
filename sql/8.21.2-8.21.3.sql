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
Previous version: 8.21.2
New version: 8.21.3
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

CREATE TABLE IF NOT EXISTS t_personname (
	firstname TEXT,
	lastname TEXT
);

CREATE TABLE IF NOT EXISTS t_citation (
	appid INT REFERENCES applications(id),
	authors t_personname[],
	publisher TEXT
);


CREATE OR REPLACE FUNCTION cite(applications) RETURNS t_citation AS
$$
	SELECT (
		$1.id,
		ARRAY_AGG((firstname,
		lastname)::t_personname),
		'EGI Applications Database'
	)::t_citation
	FROM researchers_apps ra
	INNER JOIN researchers r ON r.id = ra.researcherid
	WHERE ra.appid = $1.id
$$ LANGUAGE SQL;
ALTER FUNCTION cite(applications) OWNER TO appdb;

CREATE OR REPLACE FUNCTION bibtex(t_citation) RETURNS TEXT AS
$$
	SELECT
		'@misc{' || (SELECT a.cname FROM applications a WHERE id = $1.appid) || E',\n' ||
		'  publisher={' || $1.publisher || E'},\n' ||
		'  author={' || ARRAY_TO_STRING(ARRAY_AGG(auth.lastname || ', ' || auth.firstname), ' and ') || E'}\n' ||
		'}'
	FROM UNNEST($1.authors) AS auth
$$ LANGUAGE SQL;
ALTER FUNCTION bibtex(t_citation) OWNER TO appdb;

CREATE OR REPLACE FUNCTION latex(t_citation) RETURNS TEXT AS
$$
	SELECT
		'%\cite{' || (SELECT a.cname FROM applications a WHERE id = $1.appid) || E'}\n' ||
		'\bibitem{' || (SELECT a.cname FROM applications a WHERE id = $1.appid) || E'}\n' ||
		ARRAY_TO_STRING(ARRAY_AGG(XTRIM(REGEXP_REPLACE(auth.firstname, '([[:space:]]|^)(.)\w*', '\2.~', 'g')) || auth.lastname), ' and ') || E',\n' ||
		'%``' || (SELECT a.name FROM applications a WHERE id = $1.appid) || ',''''' || E'\n' ||
		$1.publisher || '.'
	FROM UNNEST($1.authors) AS auth
$$ LANGUAGE SQL;
ALTER FUNCTION bibtex(t_citation) OWNER TO appdb;


INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 21, 3, E'Added types and functions to create bibtex / latex citing code for applications'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=21 AND revision=3);

COMMIT;
