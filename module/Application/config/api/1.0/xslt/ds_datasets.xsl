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
	Document   : ds_datasets.xsl
	Created on : February 17, 2015, 6:55 PM
	Author     : nakos
	Description:
		Purpose of transformation follows.
-->

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
				xmlns:appdb="http://appdb.egi.eu/api/1.0/appdb" 
				xmlns:dataset="http://appdb.egi.eu/api/1.0/dataset" 
				xmlns:person="http://appdb.egi.eu/api/1.0/person"
				version="1.0">
	<xsl:output method="xml" indent="yes" />
	<xsl:strip-space elements="*" />
	 <xsl:template match="node()|@*">
		<xsl:copy>
		  <xsl:apply-templates select="node()|@*"/>
		</xsl:copy>
	</xsl:template>
	<xsl:template match="person:person[@metatype]">
		<xsl:element name="dataset:{@metatype}">
			<xsl:apply-templates select="@*[name()!='metatype']|node()"/>
		</xsl:element>
	</xsl:template>
	
	
</xsl:stylesheet>
