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
    Document   : app_for_export.xsl
    Created on : August 05, 2011, 15:56
    Author     : wvkarag
-->
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0"
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xmlns:application="http://appdb.egi.eu/api/0.2/application"
xmlns:appdb="http://appdb.egi.eu/api/0.2/appdb"
xmlns:publication="http://appdb.egi.eu/api/0.2/publication"
xmlns:person="http://appdb.egi.eu/api/0.2/person"
xmlns:user="http://appdb.egi.eu/api/0.2/user"
xmlns:vo="http://appdb.egi.eu/api/0.2/vo"
xmlns:regional="http://appdb.egi.eu/api/0.2/regional">
	<xsl:output method="xml"/>
	<xsl:template match="/">
    	 <xsl:apply-templates select="@*|node()" />
	</xsl:template>
	<xsl:template match="@*|node()">
    	<xsl:copy>
			<xsl:apply-templates select="@*|node()"/>
		</xsl:copy>
	</xsl:template>
	<xsl:template match="appdb:appdb/@*" />
	<xsl:template match="appdb:appdb">
		<xsl:element name="people">
			<xsl:apply-templates select="@*|node()"/>
		</xsl:element>
	</xsl:template>
	<xsl:template match="person:person/@*" />
	<xsl:template match="person:contact" />
	<xsl:template match="application:application" />
	<xsl:template match="person:lastUpdated" />
	<xsl:template match="regional:provider" />
	<xsl:template match="user:permissions" />
	<xsl:template match="person:privileges" />
	<xsl:template match="publication:publication" />
	<xsl:template match="person:image" />
	<xsl:template match="person:person">
		<xsl:element name="person">
			<xsl:apply-templates select="@*|node()"/>
			<xsl:element name="applications">
				<xsl:for-each select="./application:application">
					<xsl:sort select="node()" />
					<xsl:element name="application">
						<xsl:choose>
							<xsl:when test="./@owner">
								<xsl:attribute name="owner" select="./@owner"/>
							</xsl:when>
						</xsl:choose>
						<xsl:value-of select="./application:name"/>
					</xsl:element>
				</xsl:for-each>
			</xsl:element>
			<xsl:element name="contacts">
				<xsl:for-each select="./person:contact">
					<xsl:sort select="node()" />
					<xsl:element name="contact">
						<!--<xsl:attribute name="type" select="@type"/>
						<xsl:value-of select="."/> -->
						<xsl:apply-templates select="@*|node()"/>
					</xsl:element>
				</xsl:for-each>
			</xsl:element>
		</xsl:element>
	</xsl:template>
	<xsl:template match="person:firstname">
		<xsl:element name="firstname">
			<xsl:apply-templates select="@*|node()"/>
		</xsl:element>
	</xsl:template>
	<xsl:template match="person:lastname">
		<xsl:element name="lastname">
			<xsl:apply-templates select="@*|node()"/>
		</xsl:element>
	</xsl:template>
	<xsl:template match="person:role">
		<xsl:element name="role">
			<xsl:value-of select="./@type"/>
		</xsl:element>
	</xsl:template>
	<xsl:template match="regional:country/@*" />
	<xsl:template match="regional:country">
		<xsl:element name="country">
			<xsl:apply-templates select="@*|node()"/>
		</xsl:element>
	</xsl:template>
	<xsl:template match="person:registeredOn">
		<xsl:element name="registered">
			<xsl:apply-templates select="@*|node()"/>
		</xsl:element>
	</xsl:template>
	<xsl:template match="person:institute">
		<xsl:element name="institution">
			<xsl:apply-templates select="@*|node()"/>
		</xsl:element>
	</xsl:template>
	<xsl:template match="person:permalink">
		<xsl:element name="permalink">
			<xsl:apply-templates select="@*|node()"/>
		</xsl:element>
	</xsl:template>
</xsl:stylesheet>
