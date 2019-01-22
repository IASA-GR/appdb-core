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

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0"
  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
  xmlns:appdb="http://appdb.egi.eu/api/1.0/appdb"
  xmlns:virtualization="http://appdb.egi.eu/api/1.0/virtualization"
  xmlns:person="http://appdb.egi.eu/api/1.0/person">
	<xsl:output method="xml"/>
	<xsl:strip-space elements="*" />
	
	<xsl:template match="@*|node()">
		<xsl:copy>
			<!--<xsl:copy-of select="@*" />-->
			<xsl:apply-templates select="@*|node()"/>
        
			<!-- <xsl:apply-templates/>-->
		</xsl:copy>
	</xsl:template>
	
	<xsl:template match="//virtualization:image">
		<xsl:copy>
            <xsl:attribute name="protected">true</xsl:attribute>
            <xsl:copy-of select="@*" />
            <xsl:apply-templates />
        </xsl:copy>
	</xsl:template>
	<xsl:template match="//virtualization:image/virtualization:checksum">
		<xsl:element name="virtualization:checksum">
			<xsl:attribute name="protected">
				<xsl:value-of select="'true'" />
			</xsl:attribute>
			<xsl:attribute name="hash">
				<xsl:value-of select="@hash" />
			</xsl:attribute>
		</xsl:element>		
		<xsl:apply-templates/>
	</xsl:template>
	<xsl:template match="//virtualization:image/virtualization:size">
		<xsl:element name="virtualization:size">
			<xsl:attribute name="protected">
				<xsl:value-of select="'true'" />
			</xsl:attribute>
		</xsl:element>		
		<xsl:apply-templates/>
	</xsl:template>
	<xsl:template match="//virtualization:image/virtualization:url">
		<xsl:element name="virtualization:url">
			<xsl:attribute name="protected">
				<xsl:value-of select="'true'" />
			</xsl:attribute>
		</xsl:element>		
		<xsl:apply-templates/>
	</xsl:template>
	<xsl:template match="//virtualization:image/virtualization:ovf">
		<xsl:element name="virtualization:ovf">
			<xsl:attribute name="protected">
				<xsl:value-of select="'true'" />
			</xsl:attribute>
		</xsl:element>		
		<xsl:apply-templates/>
	</xsl:template>
</xsl:stylesheet>
