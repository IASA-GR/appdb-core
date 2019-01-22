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
xmlns:person="http://appdb.egi.eu/api/0.2/person"
xmlns:publication="http://appdb.egi.eu/api/0.2/publication"
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
		<xsl:element name="applications">
			<xsl:apply-templates select="@*|node()"/>
		</xsl:element>
	</xsl:template>
	<xsl:template match="application:application/@*" />
	<xsl:template match="publication:publication" />
	<xsl:template match="regional:country" />
	<xsl:template match="application:middleware" />
	<xsl:template match="application:url" />
	<xsl:template match="application:permalink" />
	<xsl:template match="user:permissions" />
	<xsl:template match="application:logo" />
	<xsl:template match="application:contact" />
	<xsl:template match="application:tag" />
	<xsl:template match="application:moderatedOn" />
	<xsl:template match="application:moderationReason" />
	<xsl:template match="application:moderator" />
	<xsl:template match="application:deletedOn" />
	<xsl:template match="application:deleter" />
	<xsl:template match="application:discipline" />
	<xsl:template match="application:subdiscipline" />
	<xsl:template match="vo:vo" />
	<xsl:template match="application:application">
		<xsl:element name="application">
			<xsl:apply-templates select="@*|node()"/>
			<xsl:element name="tool">
				<xsl:value-of select="./@tool" />
			</xsl:element>
			<xsl:element name="middlewares">
			<xsl:for-each select="./application:middleware">
				<xsl:sort select="node()" />
				<xsl:element name="middleware">
					<xsl:apply-templates />
				</xsl:element>
			</xsl:for-each>
			</xsl:element>
			<xsl:element name="vos">
			<xsl:for-each select="./vo:vo">
				<xsl:sort select="node()" />
				<xsl:element name="vo">
					<xsl:value-of select="./@name"/>
				</xsl:element>
			</xsl:for-each>
			</xsl:element>
			<xsl:element name="disciplines">
			<xsl:for-each select="./application:discipline">
				<xsl:sort select="node()" />
				<xsl:element name="discipline">
					<xsl:apply-templates />
				</xsl:element>
			</xsl:for-each>
			</xsl:element>
			<xsl:element name="subdisciplines">
			<xsl:for-each select="./application:subdiscipline">
				<xsl:sort select="node()" />
				<xsl:element name="subdiscipline">
					<xsl:apply-templates />
				</xsl:element>
			</xsl:for-each>
			</xsl:element>
			<xsl:element name="countries">
			<xsl:for-each select="./regional:country">
				<xsl:sort select="node()" />
				<xsl:element name="country">
					<xsl:apply-templates />
				</xsl:element>
			</xsl:for-each>
			</xsl:element>
			<xsl:element name="urls">
			<xsl:for-each select="./application:url">
				<xsl:sort select="@*|node()" />
				<xsl:element name="url">
					<xsl:attribute name="type">
						<xsl:value-of select="./@type" />
					</xsl:attribute>
					<xsl:apply-templates />
				</xsl:element>
			</xsl:for-each>
			</xsl:element>
			<xsl:element name="researchers">
			<xsl:for-each select="./application:contact">
				<xsl:sort select="node()" />
				<xsl:variable name="cont1" select="concat('[',./person:contact[@type='e-mail' and not(@protected='true')][1],']')" />
				<xsl:element name="researcher">
					<xsl:if test="$cont1='[]'">
						<xsl:value-of select="concat(./person:firstname,' ',./person:lastname)"/>
					</xsl:if>
					<xsl:if test="not($cont1='[]')">
						<xsl:value-of select="concat(./person:firstname,' ',./person:lastname,' [',./person:contact[@type='e-mail' and not(@protected='true')][1],']')"/>
					</xsl:if>
				</xsl:element>
			</xsl:for-each>
			</xsl:element>
		</xsl:element>
	</xsl:template>
	<xsl:template match="application:name">
		<xsl:element name="name">
			<xsl:apply-templates select="@*|node()"/>
		</xsl:element>
	</xsl:template>
	<xsl:template match="application:description">
		<xsl:element name="description">
			<xsl:apply-templates select="@*|node()"/>
		</xsl:element>
	</xsl:template>
	<xsl:template match="application:abstract">
		<xsl:element name="abstract">
			<xsl:apply-templates select="@*|node()"/>
		</xsl:element>
	</xsl:template>
	<xsl:template match="application:status/@*" />
	<xsl:template match="application:status">
		<xsl:element name="status">
			<xsl:apply-templates select="@*|node()"/>
		</xsl:element>
	</xsl:template>
<!--	<xsl:template match="application:discipline/@*" />
	<xsl:template match="application:discipline">
		<xsl:element name="discipline">
			<xsl:apply-templates select="@*|node()"/>
		</xsl:element>
	</xsl:template> -->
<!--	<xsl:template match="application:subdiscipline/@*" />
	<xsl:template match="application:subdiscipline">
		<xsl:element name="subdiscipline">
			<xsl:apply-templates select="@*|node()"/>
		</xsl:element>
	</xsl:template> -->
	<xsl:template match="application:owner/@*" />
	<xsl:template match="application:owner">
		<xsl:element name="addedBy">
			<xsl:value-of select="concat(./person:firstname,' ',./person:lastname)"/>
		</xsl:element>
	</xsl:template>
	<xsl:template match="application:addedOn">
		<xsl:element name="dateAdded">
			<xsl:apply-templates select="@*|node()"/>
		</xsl:element>
	</xsl:template>
	<xsl:template match="application:lastUpdated" />
	<xsl:template match="regional:provider" />
</xsl:stylesheet>
