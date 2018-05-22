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
xmlns:application="http://appdb.egi.eu/api/1.0/application"
xmlns:vo="http://appdb.egi.eu/api/1.0/vo"
xmlns:privilege="http://appdb.egi.eu/api/1.0/privilege"
xmlns:person="http://appdb.egi.eu/api/1.0/person"
xmlns:people="http://appdb.egi.eu/api/1.0/people">    
	<xsl:output method="xml"/>
	<xsl:strip-space elements="*" />

	<xsl:template match="//vo:vo">
		<xsl:apply-templates />
	</xsl:template>
	<xsl:template match="//privilege:group[@id=-4 or @id=-7 or @id=-11 or @id=-12 or @id=-13]/text()">
		<xsl:apply-templates />
	</xsl:template>
	<xsl:template match="//privilege:group[@id=-4 or @id=-7 or @id=-11 or @id=-12 or @id=-13]">
		<xsl:apply-templates />
	</xsl:template>
	<xsl:template match="//person:contact/text()">
		<xsl:apply-templates />
	</xsl:template>
	<xsl:template match="//person:contact">
		<xsl:apply-templates />
	</xsl:template>

	<xsl:template match="*">
		<xsl:copy>
			<xsl:copy-of select="@*"/>
			<xsl:apply-templates />
		</xsl:copy>
	</xsl:template>
    <xsl:include href="person.xsl" />
</xsl:stylesheet>
