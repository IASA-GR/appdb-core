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
Previous version: 8.18.7
New version: 8.18.8
Author: wvkarag@lovecraft.priv.iasa.gr
*/

-- Function: public.oai_dc_xslt(xml)

-- DROP FUNCTION public.oai_dc_xslt(xml);

CREATE OR REPLACE FUNCTION public.oai_dc_xslt(x xml)
  RETURNS xml AS
$BODY$
<?xml version="1.0" encoding="UTF-8"?>
<!--
/**
 * Copyright (C) 2015 IASA - Institute of Accelerating Systems and Applications (http://www.iasa.gr)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
-->

<!--
    Document   : datacite.xml
    Created on : April 30, 2018, 6:06 PM
    Author     : wvkarag
-->
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0"
        xmlns:appdb="http://appdb.egi.eu/api/1.0/appdb"
        xmlns:application="http://appdb.egi.eu/api/1.0/application"
        xmlns:person="http://appdb.egi.eu/api/1.0/person"
        xmlns:virtualization="http://appdb.egi.eu/api/1.0/virtualization"
        xmlns:license="http://appdb.egi.eu/api/1.0/license"
        xmlns:php="http://php.net/xsl"
        exclude-result-prefixes="appdb application virtualization php person license">
        <xsl:output method="xml"/>
        <xsl:strip-space elements="*" />
        <xsl:template match="//application:application">
                <oai_dc:dc xmlns:dc="http://purl.org/dc/elements/1.1/"
           xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/"
           xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
           xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/oai_dc/ http://www.openarchives.org/OAI/2.0/oai_dc.xsd">
                        <dc:title>
                                <xsl:value-of select="./application:name" />
                        </dc:title>

                        <dc:identifier>
                                <xsl:value-of select="@handle" />
                        </dc:identifier>

                        <xsl:for-each select="./person:person[@metatype='contact']">
                                <dc:creator>
                                        <xsl:value-of select="concat(./person:lastname, ', ', ./person:firstname)" />
                                </dc:creator>
                        </xsl:for-each>

                        <xsl:if test="not(./person:person[@metatype='actor']/@id=./person:person[@metatype='contact']/@id)">
                                <dc:contributor>
                                        <xsl:value-of select="concat(./person:person[@metatype='actor']/person:lastname, ', ', ./person:person[@metatype='actor']/person:firstname)" />
                                </dc:contributor>
                        </xsl:if>

                        <xsl:if test="not(./person:person[@metatype='actor']/@id=./person:person[@metatype='owner']/@id)">
				<xsl:if test="not(./person:person[@metatype='owner']/@id=./person:person[@metatype='contact']/@id)">
					<dc:contributor>
						<xsl:value-of select="concat(./person:person[@metatype='owner']/person:lastname, ', ', ./person:person[@metatype='owner']/person:firstname)" />
					</dc:contributor>
				</xsl:if>
			</xsl:if>

                        <dc:publisher>EGI Applications Database</dc:publisher>

                        <dc:subject>
                                <xsl:value-of select="./application:category[@primary='true']/text()" />
                        </dc:subject>
                        <xsl:if test="./application:category[@primary='false']">
                                <dc:subject>
                                        <xsl:value-of select="./application:category[@primary='false']/text()" />
                                </dc:subject>
                        </xsl:if>

                        <dc:date>
                                <xsl:value-of select="./application:addedOn/text()" />
                        </dc:date>

                        <dc:language>en</dc:language>

                        <xsl:if test="./application:language">
                                <dc:format>
                                        <xsl:value-of select="./application:language/text()" />
                                </dc:format>
                        </xsl:if>

                        <dc:rights>Open Access</dc:rights>

                        <dc:description>
                                <xsl:value-of select="./application:description" />
                        </dc:description>
                        <dc:description>
                                <xsl:value-of select="./application:abstract" />
                        </dc:description>
                </oai_dc:dc>
        </xsl:template>

</xsl:stylesheet>
$BODY$
  LANGUAGE xslt STABLE
  COST 100;
ALTER FUNCTION public.oai_dc_xslt(xml)
  OWNER TO appdb;

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 18, 8, E'Update oai_dc_xslt function'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=18 AND revision=8);
