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
xmlns:vo="http://appdb.egi.eu/api/0.2/vo">    
	<xsl:output omit-xml-declaration="yes" method="xml" indent="yes"/>
    <xsl:template match="/">
         <xsl:apply-templates select="@*|node()" />
    </xsl:template>
    <xsl:template match="@*|node()">
        <xsl:copy>
            <xsl:apply-templates select="@*|node()"/>
        </xsl:copy>
    </xsl:template>
    <xsl:template match="IDCard">
        <xsl:element name="vo:vo">
            <xsl:attribute name="id">###PUT_VO_ID_HERE###</xsl:attribute>
            <xsl:attribute name="name">
                <xsl:value-of select="./@Name" />
            </xsl:attribute>
            <xsl:attribute name="discipline">
                <xsl:value-of select="./Discipline" />
            </xsl:attribute>
            <xsl:element name="vo:scope" >
                <xsl:value-of select="./Scope" />
            </xsl:element>
            <xsl:element name="vo:validatedOn" >
                <xsl:value-of select="./ValidationDate" />
            </xsl:element>
            <xsl:element name="vo:url" >
                <xsl:attribute name="type">enrollment</xsl:attribute>
                <xsl:value-of select="./EnrollmentUrl" />
            </xsl:element>
            <xsl:element name="vo:url" >
                <xsl:attribute name="type">homepage</xsl:attribute>
                <xsl:value-of select="./HomepageUrl" />
            </xsl:element>
            <xsl:apply-templates select="node()" />
            <xsl:element name="vo:aup" >
                <xsl:attribute name="type">
                    <xsl:value-of select="./AUP/@type" />
                </xsl:attribute>
                <xsl:value-of select="./AUP" />
            </xsl:element>
            <xsl:apply-templates select="node()" />
            <xsl:element name="vo:description" >
                <xsl:value-of select="./Description" />
            </xsl:element>
            <xsl:element name="vo:resource">
                <xsl:attribute name="type">RAM per i386 core</xsl:attribute>
                <xsl:value-of select="./Ressources/RAM_per_i386_Core" />
            </xsl:element>
            <xsl:element name="vo:resource">
                <xsl:attribute name="type">RAM per x86_64 core</xsl:attribute>
                <xsl:value-of select="./Ressources/RAM_per_x86_64_Core" />
            </xsl:element>
            <xsl:element name="vo:resource">
                <xsl:attribute name="type">job scratch space</xsl:attribute>
                <xsl:value-of select="./Ressources/JobScratchSpace" />
            </xsl:element>
            <xsl:element name="vo:resource">
                <xsl:attribute name="type">job max CPU time</xsl:attribute>
                <xsl:value-of select="./Ressources/JobMaxCPUTime" />
            </xsl:element>
            <xsl:element name="vo:resource">
                <xsl:attribute name="type">job max wall clock time</xsl:attribute>
                <xsl:value-of select="./Ressources/JobMaxWallClockTime" />
            </xsl:element>
            <xsl:for-each select="./Contacts/Individuals/Contact">
                <xsl:element name="vo:contact">
                    <xsl:attribute name="role">
                        <xsl:value-of select="./Role" />
                    </xsl:attribute>
                    <xsl:value-of select="./Name" />
                </xsl:element>
            </xsl:for-each>
            <xsl:if test="./Middlewares/@ARC=1">
                <xsl:element name="vo:middleware">
                <xsl:attribute name="id">2</xsl:attribute>ARC</xsl:element>
            </xsl:if>
            <xsl:if test="./Middlewares/@gLite=1">
                <xsl:element name="vo:middleware"><xsl:attribute name="id">1</xsl:attribute>gLite</xsl:element>
            </xsl:if>
            <xsl:if test="./Middlewares/@UNICORE=1">
                <xsl:element name="vo:middleware"><xsl:attribute name="id">3</xsl:attribute>UNICORE</xsl:element>
            </xsl:if>
            <xsl:if test="./Middlewares/@GLOBUS=1">
                <xsl:element name="vo:middleware"><xsl:attribute name="id">4</xsl:attribute>Globus</xsl:element>
            </xsl:if>
        </xsl:element>
    </xsl:template>
    <xsl:template match="*" />
</xsl:stylesheet>
