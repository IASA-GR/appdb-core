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
	Document   : swsocial_export_csv.xsl
	Created on : September 25, 2013, 5:24 PM
	Author     : nakos
	Description:
		Purpose of transformation follows.
-->

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
	<xsl:output method="text"/>
	<xsl:strip-space elements="*"/>
    
	
	<xsl:template match="//shares">
		<xsl:text>"Id","Name","Moderated","facebook","twitter","linkedin","googleplus","Url"&#x0A;</xsl:text>
		<xsl:apply-templates select="*"/>
	</xsl:template>
	
	<xsl:template match="//software">
		<xsl:text>"</xsl:text>
		<xsl:value-of select="./id" />
		<xsl:text>",</xsl:text>
		<xsl:text>"</xsl:text>
		<xsl:value-of select="translate(normalize-space(./name),'&quot;','`')" />
		<xsl:text>",</xsl:text>
		<xsl:text>"</xsl:text>
		<xsl:value-of select="translate(normalize-space(./moderated),'&quot;','`')" />
		<xsl:text>",</xsl:text>
		<xsl:text>"</xsl:text>
		<xsl:value-of select="./count/fb" />
		<xsl:text>",</xsl:text>
		<xsl:text>"</xsl:text>
		<xsl:value-of select="./count/tw" />
		<xsl:text>",</xsl:text>
		<xsl:text>"</xsl:text>
		<xsl:value-of select="./count/in" />
		<xsl:text>",</xsl:text>
		<xsl:text>"</xsl:text>
		<xsl:value-of select="./count/gp" />
		<xsl:text>",</xsl:text>
		<xsl:text>"</xsl:text>
		<xsl:value-of select="translate(normalize-space(./url),'&quot;','`')" />
		<xsl:text>"</xsl:text>
		<xsl:text>&#x0A;</xsl:text>
    </xsl:template>
</xsl:stylesheet>
