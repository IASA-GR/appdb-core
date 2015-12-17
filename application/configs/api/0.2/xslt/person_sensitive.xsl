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
    Document   : people.xsl
    Created on : June 24, 2011, 10:40 AM
    Author     : wvkarageorgos
-->
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0"
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xmlns:appdb="http://appdb.egi.eu/api/0.2/appdb"
xmlns:person="http://appdb.egi.eu/api/0.2/person"
xmlns:application="http://appdb.egi.eu/api/0.2/application">
	<xsl:output method="xml"/>
	<xsl:template match="*">
		<xsl:copy>
			<xsl:copy-of select="@*" />
			<xsl:apply-templates/>
		</xsl:copy>
	</xsl:template>
	<xsl:template match="//person:contact">
		<xsl:choose>
		<xsl:when test="@type='e-mail'">
			<xsl:copy>
				<xsl:copy-of select="@*" />
				<xsl:attribute name="protected">
					<xsl:value-of select="'true'"/>
				</xsl:attribute>
                <xsl:value-of select="concat('/texttoimage/personcontact?id=',@id)"/>
			</xsl:copy>
		</xsl:when>
		<xsl:otherwise>
		<xsl:copy>
			<xsl:copy-of select="@*" />
			<xsl:apply-templates/>
		</xsl:copy>
		</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
</xsl:stylesheet>
