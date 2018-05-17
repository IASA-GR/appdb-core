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
Previous version: 8.18.8
New version: 8.18.9
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

CREATE OR REPLACE FUNCTION oai_dc_xslt(x xml) RETURNS XML AS
$$
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
                                <dc:creator>
                                <!--                            <dc:contributor> -->
                                        <xsl:value-of select="concat(./person:person[@metatype='actor']/person:lastname, ', ', ./person:person[@metatype='actor']/person:firstname)" />
                                <!--                            </dc:contributor> -->
                                </dc:creator>
                        </xsl:if>

                        <xsl:if test="not(./person:person[@metatype='actor']/@id=./person:person[@metatype='owner']/@id)">
                                <xsl:if test="not(./person:person[@metatype='owner']/@id=./person:person[@metatype='contact']/@id)">
                                        <!--                                    <dc:contributor> -->
                                        <dc:creator>
                                                <xsl:value-of select="concat(./person:person[@metatype='owner']/person:lastname, ', ', ./person:person[@metatype='owner']/person:firstname)" />
                                        <!--                                    </dc:contributor> -->
                                        </dc:creator>
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

                        <xsl:choose>
                                <xsl:when test="./application:vappliance[@imageListsPrivate='true']">
                                        <dc:rights>Restricted Access</dc:rights>
                                </xsl:when>
                                <xsl:otherwise>
                                        <dc:rights>Open Access</dc:rights>
                                </xsl:otherwise>
                        </xsl:choose>

                        <dc:description>
                                <xsl:value-of select="./application:description" />
                        </dc:description>
                        <dc:description>
                                <xsl:value-of select="./application:abstract" />
                        </dc:description>
                </oai_dc:dc>
        </xsl:template>

</xsl:stylesheet>
$$ LANGUAGE xslt STABLE;
ALTER FUNCTION oai_dc_xslt(xml) OWNER TO appdb;

CREATE OR REPLACE FUNCTION oai_datacite_xslt(x xml) RETURNS XML AS
$$
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
        xmlns:entity="http://appdb.egi.eu/api/1.0/entity"
        xmlns:organization="http://appdb.egi.eu/api/1.0/organization"
        xmlns:php="http://php.net/xsl"
        exclude-result-prefixes="appdb application virtualization php person license">
        <xsl:output method="xml"/>
        <xsl:strip-space elements="*" />
        <xsl:template match="//application:application">
                <resource xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://datacite.org/schema/kernel-4" xsi:schemaLocation="http://datacite.org/schema/kernel-4 http://schema.datacite.org/meta/kernel-4/metadata.xsd">
                        <identifier identifierType="Handle">
                                <xsl:value-of select="@handle" />
                        </identifier>

                        <creators>
                                <xsl:for-each select="./person:person[@metatype='contact']">
                                        <creator>
                                                <creatorName>
                                                        <xsl:value-of select="concat(./person:lastname, ', ',./person:firstname)" />
                                                </creatorName>
                                                <givenName>
                                                        <xsl:value-of select="./person:firstname" />
                                                </givenName>
                                                <familyName>
                                                        <xsl:value-of select="./person:lastname" />
                                                </familyName>
                                                <affiliation>
                                                        <xsl:value-of select="./person:institute" />
                                                </affiliation>
                                        </creator>
                                </xsl:for-each>
                        </creators>

                        <contributors>
                                 <!-- addedby -->
                                <xsl:if test="not(./person:person[@metatype='actor']/@id=./person:person[@metatype='contact']/@id)">
                                        <contributor contributorType="ContactPerson">
                                                <contributorName>
                                                        <xsl:value-of select="concat(./person:person[@metatype='actor']/person:lastname, ', ',./person:person[@metatype='actor']/person:firstname)" />
                                                </contributorName>
                                                <givenName>
                                                        <xsl:value-of select="./person:person[@metatype='actor']/person:firstname" />
                                                </givenName>
                                                <familyName>
                                                        <xsl:value-of select="./person:person[@metatype='actor']/person:lastname" />
                                                </familyName>
                                                <affiliation>
                                                        <xsl:value-of select="./person:person[@metatype='actor']/person:institute" />
                                                </affiliation>
                                        </contributor>
                                </xsl:if>

                                <!-- owner -->
                                <xsl:if test="not(./person:person[@metatype='owner']/@id=./person:person[@metatype='contact']/@id)">
                                        <xsl:if test="not(./person:person[@metatype='actor']/@id=./person:person[@metatype='owner']/@id)">
                                                <contributor contributorType="ContactPerson">
                                                <contributorName>
                                                        <xsl:value-of select="concat(./person:person[@metatype='owner']/person:lastname, ', ',./person:person[@metatype='owner']/person:firstname)" />
                                                </contributorName>
                                                <givenName>
                                                        <xsl:value-of select="./person:person[@metatype='owner']/person:firstname" />
                                                </givenName>
                                                <familyName>
                                                        <xsl:value-of select="./person:person[@metatype='owner']/person:lastname" />
                                                </familyName>
                                                <affiliation>
                                                        <xsl:value-of select="./person:person[@metatype='owner']/person:institute" />
                                                </affiliation>
                                                </contributor>
                                        </xsl:if>
                                </xsl:if>
                        </contributors>

                        <titles>
                                <title>
                                        <xsl:value-of select="./application:name" />
                                </title>
                        </titles>

                        <publisher>EGI Applications Database</publisher>
                        <!--
                        <xsl:if test="./entity:relation[@verbname='developer' and @reversed='true']/entity:entity[@type='organization']/organization:organization">
                                <publisher>
                                        <xsl:value-of select="./entity:relation[@verbname='developer' and @reversed='true']/entity:entity[@type='organization']/organization:organization/@name" />
                                </publisher>
                        </xsl:if>
                        -->
                        <resourceType resourceTypeGeneral="Software" />

                        <subjects>
                                <subject>
                                        <xsl:value-of select="./application:category[@primary='true']/text()" />
                                </subject>
                                <subject>
                                        <xsl:value-of select="./application:category[@primary='false']/text()" />
                                </subject>
                        </subjects>

                        <dates>
                                <date dateType="Issued">
                                        <xsl:value-of select="./application:addedOn/text()" />
                                </date>
                        </dates>

                        <languages>
                                <language>en</language>
                        </languages>

                        <alternateIdentifiers>
                                <alternateIdentifier alternateIdentifierType="URL">
                                        <xsl:value-of select="./application:permalink/text()" />
                                </alternateIdentifier>
                                <alternateIdentifier alternateIdentifierType="URL">
                                        <xsl:value-of select="concat('https://', //appdb:appdb/@host, '/store/software/', ./@cname)" />
                                </alternateIdentifier>
                                <xsl:if test="./application:url[@type='Website']">
                                        <alternateIdentifier alternateIdentifierType = "LandingPage">
                                                <xsl:value-of select="./application:url[@type='Website']/text()" />
                                        </alternateIdentifier>
                                </xsl:if>
                                <xsl:if test="./application:url[@type='Downlod']">
                                        <alternateIdentifier alternateIdentifierType = "DistributionLocation">
                                                <xsl:value-of select="./application:url[@type='Download']/text()" />
                                        </alternateIdentifier>
                                </xsl:if>
                        </alternateIdentifiers>

                        <xsl:if test="./application:url[@type='Documentation']">
                                <relatedIdentifiers>
                                        <relatedIdentifier relatedIdentifierType="URL" relationType="IsDocumentedBy">
                                                <xsl:value-of select="./application:url[@type='Documentation']/text()" />
                                        </relatedIdentifier>
                                </relatedIdentifiers>
                        </xsl:if>

                        <xsl:if test="./application:language">
                                <formats>
                                        <format>
                                                <xsl:value-of select="./application:language/text()" />
                                        </format>
                                </formats>
                        </xsl:if>

                        <rightsList>
                                <xsl:choose>
                                        <xsl:when test="./application:vappliance[@imageListsPrivate='true']">
                                                <rights rightsURI="http://purl.org/coar/access_right/c_16ec">Restricted Access</rights>
                                        </xsl:when>
                                        <xsl:otherwise>
                                                <rights rightsURI="http://purl.org/coar/access_right/c_abf2">Open Access</rights>
                                        </xsl:otherwise>
                                </xsl:choose>
                                <xsl:if test="./application:license">
                                        <xsl:element name="rights">
                                                <xsl:attribute name="rightsURI">
                                                        <xsl:value-of select="./application:license/license:url" />
                                                </xsl:attribute>
                                                <xsl:value-of select="./application:license/license:title" />
                                        </xsl:element>
                                </xsl:if>
                        </rightsList>

                        <descriptions>
                                <description descriptionType="Abstract">
                                        <xsl:value-of select="concat(./application:description, '&#xA;&#xA;', ./application:abstract)" />
                                </description>
                        </descriptions>

                </resource>
        </xsl:template>

</xsl:stylesheet>
$$ LANGUAGE xslt STABLE;
ALTER FUNCTION oai_datacite_xslt(xml) OWNER TO appdb;

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 18, 9, E'Update OAI-DC OAI-Datacite XSLTs'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=18 AND revision=9);

COMMIT;	
