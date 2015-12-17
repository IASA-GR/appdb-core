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
    Document   : poarelease.xsl
    Created on : March 5, 2013, 3:01 PM
    Author     : nakos
    Description:
        Purpose of transformation follows.
-->

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0"
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
	<xsl:include href="poapackage.xsl" />
	<xsl:include href="target.xsl" />
	
	<xsl:output method="xml"/>
	<xsl:strip-space elements="*" />
	<xsl:template match="*">
		<xsl:copy>
			<xsl:copy-of select="@*"/>
			<xsl:apply-templates />
		</xsl:copy>
	</xsl:template>
	
	<xsl:template name="poarelease" match="//MetaPoaRelease">
		<xsl:element name="poarelease">
			<xsl:attribute name="id">
				<xsl:value-of select="current()/id"/>
			</xsl:attribute>
			<xsl:attribute name="releaseid">
				<xsl:value-of select="current()/productReleaseId"/>
			</xsl:attribute>
			<xsl:attribute name="deleted">
				<xsl:if test="deleted[text()='Y']">true</xsl:if>
				<xsl:if test="deleted[text()='1']">true</xsl:if>
				<xsl:if test="deleted[text()='N']">false</xsl:if>
				<xsl:if test="deleted[text()='0']">false</xsl:if>
			</xsl:attribute>
			<xsl:attribute name="created">
				<xsl:value-of select="current()/timestampInserted" />
			</xsl:attribute>
			<xsl:element name="lastupdated">
				<xsl:value-of select="current()/timestampLastUpdated" />
			</xsl:element>
			<xsl:element name="statelastchanged">
				<xsl:value-of select="current()/timestampLastStateChange" />
			</xsl:element>
			<xsl:element name="releasenotes">
				<xsl:value-of select="current()/releaseNotes"/>
			</xsl:element>
			<xsl:element name="changelog">
				<xsl:value-of select="current()/changeLog" />
			</xsl:element>
			<xsl:element name="repositoryurl">
				<xsl:value-of select="current()/repositoryURL"/>
			</xsl:element>
			<xsl:element name="repositoryinfo">
				<xsl:element name="repositoryurl">
					<xsl:if test="not(current()/repositoryInfo/repositoryUrl/text()='Content is not available')">
						<xsl:value-of select="current()/repositoryInfo/repositoryUrl" />
					</xsl:if>
				</xsl:element>
				<xsl:element name="repositoryfileurl">
					<xsl:if test="not(current()/repositoryInfo/repositoryFileUrl/text()='Content is not available')">	
						<xsl:value-of select="current()/repositoryInfo/repositoryFileUrl" />
					</xsl:if>
				</xsl:element>
				<xsl:element name="repositoryfilecontents">
					<xsl:if test="not(current()/repositoryInfo/repositoryFileContents/text()='Content is not available')">
						<xsl:value-of select="current()/repositoryInfo/repositoryFileContents" />
					</xsl:if>
				</xsl:element>
			</xsl:element>
			<xsl:apply-templates select="current()/CommRepoAllowedPlatformCombination" />
			<xsl:apply-templates select="current()/MetaPoaReleasePackage" />
		</xsl:element>
	</xsl:template>
	
</xsl:stylesheet>