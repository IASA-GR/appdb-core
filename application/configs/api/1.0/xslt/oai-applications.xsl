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
xmlns:application="http://appdb.egi.eu/api/1.0/application"
xmlns:appdb="http://appdb.egi.eu/api/1.0/appdb"
xmlns:person="http://appdb.egi.eu/api/1.0/person"
xmlns:vo="http://appdb.egi.eu/api/1.0/vo"
xmlns:regional="http://appdb.egi.eu/api/1.0/regional"
xmlns:discipline="http://appdb.egi.eu/api/1.0/discipline"
xmlns:date="http://exslt.org/dates-and-times"
extension-element-prefixes="date">
	<xsl:output method="xml"/>
    <xsl:strip-space elements="*" />
	    <xsl:template match="*">
			<xsl:copy>
				<xsl:copy-of select="@*"/>
			<xsl:apply-templates />
		</xsl:copy>
	</xsl:template>
	<xsl:template match="//appdb:appdb">
		<xsl:choose>
			<xsl:when test="@error">
				<error>
					<xsl:choose>
						<xsl:when test="@errornum = 2">
							<xsl:attribute name="code">idDoesNotExist</xsl:attribute>
							<xsl:value-of select="@error" />
						</xsl:when>
						<xsl:when test="@errornum = 4">
							<xsl:attribute name="code">badVerb</xsl:attribute>
							<xsl:value-of select="@error" />
						</xsl:when>
						<xsl:when test="@errornum = 5">
							<xsl:attribute name="code">badArgument</xsl:attribute>
							<xsl:value-of select="@error" />
						</xsl:when>
						<xsl:otherwise>
							<xsl:attribute name="code">apiError</xsl:attribute>
							<xsl:value-of select="@error" />
						</xsl:otherwise>
					</xsl:choose>
				</error>
			</xsl:when>
			<xsl:otherwise>
				<xsl:element name="record">
					<xsl:apply-templates select="*" />
				</xsl:element>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	<xsl:template match="//application:application">
		<xsl:element name="header">
			<identifier>
				<xsl:value-of select="concat('http://appdb.egu.ei/applications/',//node()/@id)" />
			</identifier>
			<datestamp>
				<xsl:value-of select="//node()/application:lastUpdated"/>
			</datestamp>
		</xsl:element>
		<xsl:element name="metadata">
			<oai_dc:dc 
				xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/" 
				xmlns:dc="http://purl.org/dc/elements/1.1/" 
				xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
				xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/oai_dc/ http://www.openarchives.org/OAI/2.0/oai_dc.xsd">
				<dc:title>
					<xsl:value-of select="//node()/application:name" />
				</dc:title>
				<dc:creator>
					<xsl:value-of select="concat(//node()/application:addedby/person:firstname, ' ', //node()/application:addedby/person:lastname)" />
<!--					<xsl:value-of select="concat('http://appdb.egi.eu/people/', //node()/application:addedby/@id)" /> -->
				</dc:creator>
				<xsl:if test="//node()/application:addedby/@id != //node()/application:owner/@id">
					<dc:creator>
						<xsl:value-of select="concat(//node()/application:addedby/person:firstname, ' ', //node()/application:addedby/person:lastname)" />
<!--						<xsl:value-of select="concat('http://appdb.egi.eu/people/', //node()/application:owner/@id)" /> -->
					</dc:creator>
				</xsl:if>
				<xsl:for-each select="//node()/application:contact">
					<dc:contributor>
						<xsl:value-of select="concat(person:firstname, ' ', person:lastname)" />
<!--						<xsl:value-of select="concat('http://appdb.egi.eu/people/', @id)" /> -->
					</dc:contributor>
				</xsl:for-each>
				<xsl:for-each select="//node()/discipline:discipline">
					<dc:subject>
						<xsl:value-of select="node()" />
					</dc:subject>
				</xsl:for-each>
				<xsl:for-each select="//node()/discipline:subdiscipline">
					<dc:subject>
						<xsl:value-of select="node()" />
					</dc:subject>
				</xsl:for-each>
				<dc:description>
					<xsl:value-of select="//node()/application:description" />
				</dc:description>
				<dc:description>
					<xsl:value-of select="//node()/application:abstract" />
				</dc:description>
				<dc:date>
					<xsl:value-of select="//node()/application:addedOn" />
				</dc:date>
				<xsl:for-each select="//node()/application:category">
					<dc:type>
						<xsl:value-of select="node()" />
					</dc:type>
				</xsl:for-each>
				<xsl:for-each select="//node()/application:subcategory">
					<dc:type>
						<xsl:value-of select="node()" />
					</dc:type>
				</xsl:for-each>
				<dc:identifier>
					<xsl:value-of select="concat('http://appdb.egu.ei/applications/',//node()/@id)" />
				</dc:identifier>
			</oai_dc:dc>
		</xsl:element>
	</xsl:template>
</xsl:stylesheet>
