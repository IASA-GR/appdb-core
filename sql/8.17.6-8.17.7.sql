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
Previous version: 8.17.6
New version: 8.17.7
Author: wvkarag@lovecraft.priv.iasa.gr
*/

CREATE OR REPLACE FUNCTION xsltproc(xslt TEXT, x XML) RETURNS XML AS
$$
return `(cat << EOF
$_[1]
EOF
) | xsltproc $_[0] -`;
$$
LANGUAGE plperlu STRICT;
ALTER FUNCTION xsltproc(text, xml) OWNER TO appdb;

INSERT INTO config (var, data) VALUES (
	'datacite_xslt',
	'/usr/local/src/appdb/git/portal/application/configs/api/1.0/xslt/datacite.xsl'
);

CREATE OR REPLACE FUNCTION openaire(applications) RETURNS XML AS
$$
	SELECT xsltproc(
		COALESCE((SELECT data FROM config WHERE var = 'datacite_xslt'), '/var/www/html/appdb/application/configs/api/1.0/xslt/datacite.xsl'),
		('<appdb:appdb xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:appdb="http://appdb.egi.eu/api/1.0/appdb" xmlns:application="http://appdb.egi.eu/api/1.0/application" xmlns:discipline="http://appdb.egi.eu/api/1.0/discipline" xmlns:category="http://appdb.egi.eu/api/1.0/category" xmlns:dissemination="http://appdb.egi.eu/api/1.0/dissemination" xmlns:filter="http://appdb.egi.eu/api/1.0/filter" xmlns:history="http://appdb.egi.eu/api/1.0/history" xmlns:logistics="http://appdb.egi.eu/api/1.0/logistics" xmlns:resource="http://appdb.egi.eu/api/1.0/resource" xmlns:middleware="http://appdb.egi.eu/api/1.0/middleware" xmlns:person="http://appdb.egi.eu/api/1.0/person" xmlns:permission="http://appdb.egi.eu/api/1.0/permission" xmlns:privilege="http://appdb.egi.eu/api/1.0/privilege" xmlns:publication="http://appdb.egi.eu/api/1.0/publication" xmlns:rating="http://appdb.egi.eu/api/1.0/rating" xmlns:ratingreport="http://appdb.egi.eu/api/1.0/ratingreport" xmlns:regional="http://appdb.egi.eu/api/1.0/regional" xmlns:user="http://appdb.egi.eu/api/1.0/user" xmlns:vo="http://appdb.egi.eu/api/1.0/vo" xmlns:virtualization="http://appdb.egi.eu/api/1.0/virtualization" xmlns:contextualization="http://appdb.egi.eu/api/1.0/contextualization" xmlns:license="http://appdb.egi.eu/api/1.0/license" xmlns:provider="http://appdb.egi.eu/api/1.0/provider" xmlns:provider_template="http://appdb.egi.eu/api/1.0/provider_template" xmlns:classification="http://appdb.egi.eu/api/1.0/classification" xmlns:site="http://appdb.egi.eu/api/1.0/site" xmlns:siteservice="http://appdb.egi.eu/api/1.0/site" xmlns:entity="http://appdb.egi.eu/api/1.0/entity" xmlns:organization="http://appdb.egi.eu/api/1.0/organization" xmlns:project="http://appdb.egi.eu/api/1.0/project" xmlns:dataset="http://appdb.egi.eu/api/1.0/dataset" datatype="application" type="entry" host="appdb-wvk.priv.iasa.gr" apihost="appdbpi-wvk.priv.iasa.gr" cacheState="0" permsState="0" requestedOn="1525089043.263" deliveredOn="1525089044.183" processingTime="0.921" version="1.0">' ||
			app_to_xml($1.id) || '</appdb:appdb>')::XML
	);
$$
LANGUAGE sql STRICT;
ALTER FUNCTION openaire(applications) OWNER TO appdb;

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 17, 7, E'Add xsltproc Perl function and OpenAIRE-compliant app to XML function'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=17 AND revision=7);
