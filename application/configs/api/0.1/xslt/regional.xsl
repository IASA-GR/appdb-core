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
    Document   : regional.xsl
    Created on : January 27, 2011, 6:48 PM
    Author     : nakos
-->
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0"
 xmlns:regional="http://appdb.egi.eu/api/1.0/regional">
    <!-- Remove elements -->
    <xsl:template match="//countryID" />
    <xsl:template match="//Country" />
    <xsl:template match="//regionID" />
    <xsl:template match="//Region" />
    <!-- Apply Templates -->
    <xsl:template match="//AppCountry | //Country | //Rearcher/Country">
        <xsl:element name="regional:country">
            <xsl:attribute name="id">
                <xsl:value-of select=".//id" />
            </xsl:attribute>
            <xsl:attribute name="isocode">
                <xsl:value-of select=".//ISOcode" />
            </xsl:attribute>
            <xsl:value-of select=".//name" />
        </xsl:element>
    </xsl:template>
    <xsl:template match="//regionID">
        <xsl:element name="regional:region">
            <xsl:attribute name="id">
                <xsl:value-of select="." />
            </xsl:attribute>
        </xsl:element>
    </xsl:template>
    <xsl:template match="//Region">
        <xsl:element name="regional:region">
            <xsl:attribute name="id">
                <xsl:value-of select=".//id" />
            </xsl:attribute>
            <xsl:value-of select=".//name" />
        </xsl:element>
    </xsl:template>
    
</xsl:stylesheet>
