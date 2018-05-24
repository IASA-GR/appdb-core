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
    Document   : apiroutes.xsl
    Created on : June 11, 2013, 16:45 PM
    Author     : wvkarag
-->
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0"
	xmlns:appdb="http://appdb.egi.eu/api/1.0/appdb"
	xmlns:resource="http://appdb.egi.eu/api/1.0/resource">
	<xsl:output method="xml"/>
	<xsl:strip-space elements="*" />
	<xsl:template match="//routes">
		<xsl:element name="routes">
		<xsl:apply-templates match="//route"/>
		</xsl:element>
	</xsl:template>
	<xsl:template match="route[@hidden = 'true']">
	</xsl:template>
	<xsl:template match="route[@type='rest']">
		<xsl:element name="appdb:resource">
			<xsl:attribute name="uri">
				<xsl:value-of select="@url" />
			</xsl:attribute>
			<xsl:for-each select="current()/param">
				<xsl:element name="resource:parameter">
					<xsl:attribute name="name">
						<xsl:value-of select="current()/@name" />
					</xsl:attribute>
					<xsl:attribute name="format">
						<xsl:value-of select="current()/@fmt" />
					</xsl:attribute>
				</xsl:element>
			</xsl:for-each>
			<xsl:for-each select="current()/comment">
				<xsl:element name="resource:comment">
					<xsl:value-of select="." />
				</xsl:element>
			</xsl:for-each>
		</xsl:element>
	</xsl:template>
</xsl:stylesheet>
