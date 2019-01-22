<?xml version="1.0"?>
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
    Document   : productrelease.xsl
    Created on : February 21, 2013, 11:45 AM
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
			<xsl:apply-templates />
		</xsl:copy>
	</xsl:template>
	
	<xsl:include href="poarelease.xsl" />
	<xsl:include href="contact.xsl" />
	
<!--	<xsl:template match="//VMetaProductRepoAreaContact">
		<xsl:element name="MetaContact">
			<xsl:copy-of select="node()"/>
		</xsl:element>
    </xsl:template>
	-->
	<xsl:template match="//MetaProductRelease">
		<xsl:element name="productrelease">
			<xsl:attribute name="id">
				<xsl:value-of select="current()/id"/>
			</xsl:attribute>
			<xsl:attribute name="parentid">
				<xsl:value-of select="current()/parentId"/>
			</xsl:attribute>
			<xsl:attribute name="priority">
				<xsl:value-of select="current()/priority"/>
			</xsl:attribute>
			<xsl:attribute name="deleted">
				<xsl:if test="deleted[text()='Y']">true</xsl:if>
				<xsl:if test="deleted[text()='1']">true</xsl:if>
				<xsl:if test="deleted[text()='N']">false</xsl:if>
				<xsl:if test="deleted[text()='0']">false</xsl:if>
			</xsl:attribute>
			<xsl:attribute name="priority">
				<xsl:value-of select="current()/priority"/>
			</xsl:attribute>
			<xsl:element name="state">
				<xsl:attribute name="id">
					<xsl:value-of select="current()/CommRepoState/id"/>
				</xsl:attribute>
				<xsl:attribute name="name">
					<xsl:value-of select="current()/CommRepoState/name"/>
				</xsl:attribute>
				<xsl:attribute name="repositoryId">
					<xsl:value-of select="current()/CommRepoState/repositoryId"/>
				</xsl:attribute>
			</xsl:element>
			<xsl:element name="displayversion">
				<xsl:value-of select="current()/displayVersion" />
			</xsl:element>
			<xsl:element name="repositoryarea">
				<xsl:attribute name="id">
					<xsl:value-of select="current()/MetaProductRepoArea/id" />
				</xsl:attribute>
				<xsl:attribute name="name">
					<xsl:value-of select="current()/MetaProductRepoArea/name" />
				</xsl:attribute>
			</xsl:element>
			<xsl:element name="releasenotes">
				<xsl:value-of select="current()/releaseNotes" />
			</xsl:element>
			<xsl:element name="installationnotes">
				<xsl:value-of select="current()/installationNotes" />
			</xsl:element>
			<xsl:element name="description">
				<xsl:value-of select="current()/description" />
			</xsl:element>
			<xsl:element name="changelog">
				<xsl:value-of select="current()/changeLog" />
			</xsl:element>
			<xsl:element name="additionaldetails">
				<xsl:value-of select="current()/additionalDetails" />
			</xsl:element>
			<xsl:element name="knownissues">
				<xsl:value-of select="current()/knownIssues" />
			</xsl:element>
			<xsl:element name="created">
				<xsl:if test="not(current()/timestampInserted/text()='0000-00-00 00:00:00')">
					<xsl:value-of select="current()/timestampInserted" />	
				</xsl:if>
			</xsl:element>
			<xsl:element name="lastupdate">
				<xsl:if test="not(current()/timestampLastUpdated/text()='0000-00-00 00:00:00')">
					<xsl:value-of select="current()/timestampLastUpdated" />	
				</xsl:if>
			</xsl:element>
			<xsl:element name="statechanged">
				<xsl:if test="not(current()/timestampLastStateChange/text()='0000-00-00 00:00:00')">
					<xsl:value-of select="current()/timestampLastStateChange" />	
				</xsl:if>
			</xsl:element>
			<xsl:element name="releasedate">
				<xsl:if test="not(current()/timestampReleaseDate/text()='0000-00-00 00:00:00')">
					<xsl:value-of select="current()/timestampReleaseDate" />	
				</xsl:if>
			</xsl:element>
			<!--<xsl:element name="lastproductionbuild">
				<xsl:if test="not(current()/timestampLastProductionBuild/text()='0000-00-00 00:00:00')">
					<xsl:value-of select="current()/timestampLastProductionBuild" />	
				</xsl:if>
			</xsl:element>-->
			<xsl:element name="lastproductionbuild">
				<xsl:if test="not(current()/utclastproductiondate/text()='')">
					<xsl:attribute name='backendtime'>
						<xsl:value-of select='current()/utcservertime'/>
					</xsl:attribute>
					<xsl:attribute name="productiontime">
						<xsl:value-of select="current()/utclastproductiondate" />
					</xsl:attribute>
				</xsl:if>
			</xsl:element>
			<xsl:apply-templates select="current()/CommRepoAllowedPlatformCombination" />
			<xsl:apply-templates select="current()/MetaPoaRelease" />
			<xsl:apply-templates select="current()/MetaContact" />
			<xsl:if test="not(current()/MetaProductRelease/id = current()/parentId)" >
				<xsl:apply-templates select="current()/MetaProductRelease" />
			</xsl:if>
		</xsl:element>
	</xsl:template>
</xsl:stylesheet>
