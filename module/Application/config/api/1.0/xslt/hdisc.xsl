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

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://appdb.egi.eu/api/1.0/appdb" version="1.0"
	xmlns:discipline="http://appdb.egi.eu/api/1.0/discipline"
	xmlns:appdb="http://appdb.egi.eu/api/1.0/appdb"
	>
	<!--	<xsl:include href="applications.xsl" /> -->
    <xsl:output indent="yes"/>

    <xsl:key name="sons" match="discipline:discipline" use="@parentid"/>
	<xsl:param name="maxLevels">10</xsl:param>

	<xsl:template match="//discipline:discipline[not(@parentid)]">
		<xsl:copy>
			<xsl:attribute name="id"><xsl:value-of select="@id"/></xsl:attribute>
			<xsl:attribute name="parentid">0</xsl:attribute>
			<xsl:apply-templates select="node()"/>
		</xsl:copy>
	</xsl:template>
 
	<xsl:template match="@* | node()">
		<xsl:copy>
			<xsl:apply-templates select="@* | node()"/>
		</xsl:copy>
	</xsl:template>

    <xsl:template match="appdb:appdb">
        <appdb:appdb>
			<xsl:apply-templates select="key('sons', 0)"/>
			<xsl:apply-templates select="@*" />
			<xsl:apply-templates select="//discipline:discipline[not(@parentid)]" />
        </appdb:appdb>
    </xsl:template>

	<xsl:template match="//discipline:discipline">
		<xsl:param name="level">1</xsl:param>
        <xsl:if test="$level &lt;= $maxLevels">
			<discipline:discipline>
					<xsl:attribute name="id"><xsl:value-of select="@id"/></xsl:attribute>
					<xsl:attribute name="name"><xsl:value-of select="."/></xsl:attribute>
                    <xsl:apply-templates select="key('sons', @id)">
                        <xsl:with-param name="level" select="$level + 1"/>
                    </xsl:apply-templates>
            </discipline:discipline>
        </xsl:if>
	</xsl:template>
</xsl:stylesheet>
