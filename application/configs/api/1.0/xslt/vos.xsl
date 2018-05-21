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
    Document   : applications.xsl
    Created on : January 30, 2011, 6:06 PM
    Author     : nakos
-->
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0"
xmlns:vo="http://appdb.egi.eu/api/1.0/vo">
	<xsl:output method="xml"/>
	<xsl:strip-space elements="*" />
	<xsl:template match="//vo:contact">
			<xsl:apply-templates />
	</xsl:template>
	<xsl:template match="//vo:voms">
		<xsl:copy>
			<xsl:apply-templates select="@*"/>
		</xsl:copy>
	</xsl:template>
	<xsl:template match="@*|node()">
		<xsl:copy>
			<xsl:apply-templates select="@*|node()"/>
		</xsl:copy>
	</xsl:template>
	<xsl:template match="vo:imagelist">
		<xsl:element name="vo:imagelist">
			<xsl:if test="./vo:image[@state='obsolete' or @state='deleted']">
				<xsl:attribute name="outdated">
					<xsl:text>true</xsl:text>
				</xsl:attribute>
			</xsl:if>
			<xsl:copy-of select="@*"/>
			<xsl:apply-templates />
		</xsl:element>
	</xsl:template>
	<xsl:template match="@discipline">
		<xsl:attribute name="discipline">
			<xsl:choose>
				<xsl:when test=". = 'Multidisciplinary VOs'">
					<xsl:text>Multidisciplinary</xsl:text>
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="." />
				</xsl:otherwise>
			</xsl:choose>
		</xsl:attribute>
</xsl:template>
</xsl:stylesheet>
