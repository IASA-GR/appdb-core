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
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0"
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xmlns:application="http://appdb.egi.eu/api/0.2/application"
xmlns:appdb="http://appdb.egi.eu/api/0.2/appdb"
xmlns:person="http://appdb.egi.eu/api/0.2/person"
xmlns:vo="http://appdb.egi.eu/api/0.2/vo"
xmlns:regional="http://appdb.egi.eu/api/0.2/regional">
	<xsl:output method="text"/>
	<xsl:strip-space elements="*"/>
    
	<xsl:template match="//people">
		<xsl:text>"Firstname","Lastname","Gender","Registered","Institution","Country","Role","Permalink","Applications","Contacts"&#x0A;</xsl:text>
		<xsl:apply-templates />
	</xsl:template>

	<xsl:template match="//person">
        <xsl:apply-templates select="*" />
        <xsl:text>&#x0A;</xsl:text>
    </xsl:template>

    <xsl:template match="//person/*/*">
        <xsl:if test="position() = 1">
	    <xsl:text>"</xsl:text>
        </xsl:if>
        <xsl:choose>
            <xsl:when test="count(child::*) > 0">
                <xsl:apply-templates select="*" />
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="translate(normalize-space(.),'&quot;','`')"/>
            </xsl:otherwise>
        </xsl:choose>
        <xsl:if test="position() = last()">
	    <xsl:text>"</xsl:text>
        </xsl:if>
        <xsl:if test="position() != last()">
            <xsl:text>, </xsl:text>
        </xsl:if>
    </xsl:template>

    <xsl:template match="//person/*">
        <xsl:choose>
            <xsl:when test="count(child::*) > 0">
                <xsl:apply-templates select="*" />
            </xsl:when>
            <xsl:otherwise>
                <xsl:text>"</xsl:text>
                <xsl:value-of select="translate(normalize-space(.),'&quot;','`')"/>
                <xsl:text>"</xsl:text>
            </xsl:otherwise>
        </xsl:choose>
        <xsl:if test="position() != last()">
            <xsl:text>,</xsl:text>
        </xsl:if>
    </xsl:template>

</xsl:stylesheet>
