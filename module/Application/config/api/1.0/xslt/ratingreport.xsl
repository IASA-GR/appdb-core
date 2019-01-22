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
        xmlns:ratingreport="http://appdb.egi.eu/api.0.2/ratingreport"
	xmlns:php="http://php.net/xsl">
	<xsl:output method="xml"/>
	<xsl:strip-space elements="*" />
	
        <xsl:template match="//ratings">
            <xsl:element name="application:ratingreport">
                <xsl:attribute name="applicationid">
                    <xsl:value-of select="//@applicationid" />
                </xsl:attribute>
                <xsl:attribute name="type">
                    <xsl:value-of select="//@type" />
                </xsl:attribute>
                <xsl:attribute name="average">
                    <xsl:value-of select="//@average" />
                </xsl:attribute>
                <xsl:attribute name="total">
                    <xsl:value-of select="//@total" />
                </xsl:attribute>
                <xsl:apply-templates />
            </xsl:element >
        </xsl:template>
        <xsl:template match="//rating">
            <xsl:element name="ratingreport:rating">
                <xsl:attribute name="value">
                    <xsl:value-of select="./@value" />
                </xsl:attribute>
                <xsl:attribute name="votes">
                    <xsl:value-of select="./@votes" />
                </xsl:attribute>
            </xsl:element>
        </xsl:template>
</xsl:stylesheet>