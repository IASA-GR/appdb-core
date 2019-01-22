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
    Created on : January 27, 2011, 10:40 AM
    Author     : nakos
-->
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0"
xmlns:person="http://appdb.egi.eu/api/1.0/person"
xmlns:application="http://appdb.egi.eu/api/1.0/application">
    <!-- Remove elements -->
    <xsl:template match="//id"/>
    <xsl:template match="//PplView/name | //Researcher/name | //AppDocument/Author/Researcher/name" />
    <xsl:template match="//docCount"/>
    <xsl:template match="//hasDocs" />
    <xsl:template match="/appdb/name" />
    <xsl:template match="//PositionType" />
    <xsl:template match="//positionTypeID" />
    <xsl:template match="//roleVerified" />
    <xsl:template match="//DN" />
    <xsl:template match="//guid" />
    <xsl:template match="//roleVerificationPass" />
    <xsl:template match="//username" />
    <xsl:template match="//addedBy" />
    <xsl:template match="//PplView/permalink | //Researcher/permalink" />
    <xsl:template match="//Researcher/Application" />
    <!-- Add id and docsCount alements as attributes to element person-->
    <xsl:template match="//PplView | //Researcher | //AppDocument/Author/Researcher">
        <xsl:element name="person:person">
            <xsl:attribute name="id">
                <xsl:value-of select="id" />
            </xsl:attribute>
            
            <xsl:apply-templates/>
        </xsl:element>
    </xsl:template>
   <!-- In case the caller is apps.xslt -->
    <xsl:template match="//Application/Researcher">
        <xsl:element name="application:owner">
            <xsl:attribute name="id">
                <xsl:value-of select="id" />
            </xsl:attribute>
            <xsl:apply-templates/>
        </xsl:element>
    </xsl:template>
    <xsl:template match="//Application/SciCon">
        <xsl:element name="application:contact">
            <xsl:attribute name="id">
                <xsl:value-of select="id" />
            </xsl:attribute>
            <xsl:apply-templates/>
        </xsl:element>
    </xsl:template>
    <!-- Case sensitive correction and renaming -->
    <xsl:template match="//firstName">
        <xsl:element name="person:firstname">
            <xsl:apply-templates/>
        </xsl:element>
    </xsl:template>
    <xsl:template match="//lastName">
        <xsl:element name="person:lastname">
            <xsl:apply-templates/>
        </xsl:element>
    </xsl:template>
    <xsl:template match="//institution">
        <xsl:element name="person:institute">
            <xsl:apply-templates/>
        </xsl:element>
    </xsl:template>
    <xsl:template match="//PositionType">
        <xsl:element name="person:role">
            <xsl:attribute name="id">
                <xsl:value-of select="id" />
            </xsl:attribute>
            <xsl:attribute name="type">
                <xsl:value-of select="description" />
            </xsl:attribute>
            <xsl:attribute name="validated">
                <xsl:choose>
                <xsl:when test="normalize-space(../roleVerified)!=''">
                    <xsl:value-of select="../roleVerified" />
                </xsl:when>
                <xsl:otherwise>0</xsl:otherwise>
            </xsl:choose>
            </xsl:attribute>
        </xsl:element>
    </xsl:template>
    <xsl:template match="//dateInclusion">
        <xsl:element name="person:registeredOn" >
            <xsl:value-of select="." />
        </xsl:element>
    </xsl:template>
    <xsl:template match="//PplView/permalink | //Researcher/permalink | //SciCon/permalink" >
        <xsl:element name="person:permalink" >
            <xsl:value-of select="." />
        </xsl:element>
    </xsl:template>
    <!--Because there might be many contacts with the same type, the xslt will produce many copies
    of the same node. In order to avoid it , using 'key' elements for distinction is a necessity -->
    <xsl:key name="uniqueContact" match="//Contact/ContactType" use="description" />
    <xsl:template match="//Contact" >
        <xsl:for-each select="ContactType" >
            <xsl:if test="generate-id(.) = generate-id(key('uniqueContact', description)[1])">
                <xsl:element name="person:contact">
                    <xsl:attribute name="type">
                        <xsl:value-of select="description" />
                    </xsl:attribute>
                </xsl:element>
            </xsl:if>
        </xsl:for-each>
    </xsl:template>
    <xsl:template match="//PplView/image | //Researcher/image | //SciCon/image">
        <xsl:element name="person:image">
            <xsl:value-of select="." />
        </xsl:element>
    </xsl:template>
</xsl:stylesheet>
