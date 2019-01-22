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
    Document   : poapackage.xsl
    Created on : March 5, 2013, 3:02 PM
    Author     : nakos
    Description:
        Purpose of transformation follows.
-->

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0"
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
	<xsl:output method="xml"/>
	<xsl:strip-space elements="*" />
	<xsl:template match="*">
		<xsl:copy>
			<xsl:copy-of select="@*"/>
			<xsl:apply-templates />
		</xsl:copy>
	</xsl:template>

	<xsl:template match="//MetaPoaReleasePackage" >
		<xsl:element name="poapackage">
			<xsl:attribute name="id">
				<xsl:value-of select="current()/id"/>
			</xsl:attribute>
			<xsl:attribute name="poareleaseid">
				<xsl:value-of select="current()/poaId"/>
			</xsl:attribute>
			<xsl:attribute name="name">
				<xsl:value-of select="current()/pkgName"/>
			</xsl:attribute>
			<xsl:attribute name="version">
				<xsl:value-of select="current()/pkgVersion"/>
			</xsl:attribute>
			<xsl:attribute name="arch">
				<xsl:value-of select="current()/pkgArch"/>
			</xsl:attribute>
			<xsl:attribute name="type">
				<xsl:value-of select="current()/pkgType"/>
			</xsl:attribute>
			<xsl:attribute name="level">
				<xsl:value-of select="current()/pkgLevel"/>
			</xsl:attribute>
			<xsl:element name="release">
				<xsl:value-of select="current()/pkgRelease" />
			</xsl:element>
			<xsl:element name="description">
				<xsl:value-of select="current()/pkgDescription" disable-output-escaping="no" />
			</xsl:element>
			<xsl:element name="filename">
				<xsl:value-of select="current()/pkgFilename" />
			</xsl:element>
			<xsl:element name="md5sum">
				<xsl:value-of select="current()/pkgMd5Sum" />
			</xsl:element>
			<xsl:element name="sha1sum">
				<xsl:value-of select="current()/pkgSha1Sum" />
			</xsl:element>
			<xsl:element name="url">
				<xsl:value-of select="current()/pkgUrl" />
			</xsl:element>
			<xsl:element name="size">
				<xsl:value-of select="current()/pkgSize" />
			</xsl:element>
			<xsl:element name="versionindex">
				<xsl:value-of select="current()/pkgVersionIndex" />
			</xsl:element>
		</xsl:element>
	</xsl:template>
	
</xsl:stylesheet>
