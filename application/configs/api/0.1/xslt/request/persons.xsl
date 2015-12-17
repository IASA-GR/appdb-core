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
    Created on : January 31, 2011, 10:48 AM
    Author     : nakos
-->
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
    <xsl:output method="xml"/>
    <xsl:template match="*">
        <xsl:copy>
            <xsl:apply-templates />
        </xsl:copy>
    </xsl:template>
    <xsl:template match="firstname">
        <xsl:element name="firstName">
            <xsl:value-of select="." />
        </xsl:element>
    </xsl:template>
    <xsl:template match="lastname">
        <xsl:element name="lastName">
            <xsl:value-of select="." />
        </xsl:element>
    </xsl:template>
    <xsl:template match="registeredon">
        <xsl:element name="dateInclusion">
            <xsl:value-of select="." />
        </xsl:element>
    </xsl:template>
    <xsl:template match="institute">
        <xsl:element name="institution">
            <xsl:value-of select="." />
        </xsl:element>
    </xsl:template>
    <xsl:template match="country">
        <xsl:element name="countryID" >
            <xsl:value-of select="." />
        </xsl:element>
    </xsl:template>
    <xsl:template match="region">
        <xsl:element name="regionID" >
            <xsl:value-of select="." />
        </xsl:element>
    </xsl:template>
    <xsl:template match="role">
        <xsl:element name="positionTypeID" >
            <xsl:value-of select="." />
        </xsl:element>
    </xsl:template>
    <xsl:template match="rolevalidated">
        <xsl:element name="roleVerified">
            <xsl:choose>
                 <xsl:when test="string(.)='true'" >1</xsl:when>
                 <xsl:when test="string(.)='false'">0</xsl:when>
                 <xsl:when test=". >0" >1</xsl:when>
                 <xsl:otherwise>0</xsl:otherwise>
             </xsl:choose>
        </xsl:element>
    </xsl:template>
    <xsl:template match="documents" >
        <xsl:element name="docCount">
            <xsl:value-of select="." />
        </xsl:element>
    </xsl:template>
</xsl:stylesheet>
