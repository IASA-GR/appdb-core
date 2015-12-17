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
xmlns:appdb="http://appdb.egi.eu/api/1.0/appdb"
xmlns:history="http://appdb.egi.eu/api/1.0/history">
	<xsl:strip-space elements="*"/>
    <xsl:template match="appdb:appdb">
        <xsl:copy>
			<xsl:copy-of select="@*"/>
            <xsl:apply-templates>
                <xsl:sort select="@timestamp" order="descending"/>
            </xsl:apply-templates>
        </xsl:copy>
    </xsl:template>
    <xsl:template match="*">
        <xsl:copy>
			<xsl:copy-of select="@*"/>
            <xsl:apply-templates/>
        </xsl:copy>
    </xsl:template>
    <xsl:include href="applications.xsl" />
</xsl:stylesheet>
