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
    Document   : repoarea.xsl
    Created on : March 6, 2013, 5:57 PM
    Author     : nakos
    Description:
        Purpose of transformation follows.
-->

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0"
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
	<xsl:include href="productrelease.xsl"/>
	<xsl:output method="xml"/>
	<xsl:strip-space elements="*" />
	<xsl:template match="*">
		<xsl:copy>
			<xsl:copy-of select="@*" />
			<xsl:apply-templates />
		</xsl:copy>
	</xsl:template>
	<xsl:include href="contact.xsl" />
	<xsl:template match="//MetaProductRepoArea" >
		<xsl:element name="repositoryarea">
			<xsl:attribute name="id">
				<xsl:value-of select="id"/>
			</xsl:attribute>
			<xsl:attribute name="swid">
				<xsl:value-of select="swId"/>
			</xsl:attribute>
			<xsl:attribute name="name">
				<xsl:value-of select="name"/>
			</xsl:attribute>
			<xsl:element name="swname">
				<xsl:value-of select="swName" />
			</xsl:element>
			<xsl:element name="description">
				<xsl:value-of select="description" />
			</xsl:element>
			<xsl:element name="installationnotes">
				<xsl:value-of select="installationNotes" />
			</xsl:element>
			<xsl:element name="additionaldetails">
				<xsl:value-of select="additionalDetails" />
			</xsl:element>
			<xsl:element name="knownissues">
				<xsl:value-of select="knownIssues" />
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
			<xsl:apply-templates select="current()/MetaProductRelease" />
			<xsl:apply-templates select="current()/VMetaProductRepoAreaContact" />
			<xsl:apply-templates select="current()/MetaContact" />
		</xsl:element>
		
	</xsl:template>

</xsl:stylesheet>
