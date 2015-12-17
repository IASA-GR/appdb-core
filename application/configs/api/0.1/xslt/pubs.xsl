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
    Document   : publications.xsl
    Created on : January 27, 2011, 4:11 PM
    Author     : nakos
-->
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0"
xmlns:publication="http://appdb.egi.eu/api/1.0/publication" >
    <!-- Remove elements -->
    <xsl:template match="//id"/>
    <xsl:template match="//appID"/>
    <xsl:template match="//DocType" />
    <xsl:template match="//mainAuthor" />
    <xsl:template match="//docID" />
    <xsl:template match="//authorID" />
    <xsl:template match="//fullName" />
    <xsl:template match="//main" />
    <xsl:template match="ID" />
    <xsl:template match="//AppDocument/Application" />
    <!--Apply templates -->
    <xsl:template match="//AppDocument">
        <xsl:element name="publication:publication">
            <xsl:attribute name="id">
                <xsl:value-of select="./id" />
            </xsl:attribute>
            <xsl:apply-templates/>
        </xsl:element>
    </xsl:template>
    <xsl:template match="//AppDocument/title">
        <xsl:element name="publication:title">
            <xsl:value-of select="." />
        </xsl:element>
    </xsl:template>
    <xsl:template match="//AppDocument/url" >
        <xsl:element name="publication:url">
            <xsl:value-of select="." />
        </xsl:element>
    </xsl:template>
    <xsl:template match="//AppDocument/docTypeID">
        <xsl:element name="publication:type">
            <xsl:value-of select="//DocType//description" />
        </xsl:element>
    </xsl:template>
    <xsl:template match="//AppDocument/conference" >
        <xsl:element name="publication:conference">
            <xsl:value-of select="." />
        </xsl:element>
    </xsl:template>
    <xsl:template match="//AppDocument/proceedings" >
        <xsl:element name="publication:proceedings">
            <xsl:value-of select="." />
        </xsl:element>
    </xsl:template>
    <xsl:template match="//AppDocument/volume" >
        <xsl:element name="publication:volume">
            <xsl:value-of select="." />
        </xsl:element>
    </xsl:template>
    <xsl:template match="//AppDocument/pageStart">
        <xsl:element name="publication:startPage">
            <xsl:value-of select="." />
        </xsl:element>
    </xsl:template>
    <xsl:template match="//AppDocument/pageEnd">
        <xsl:element name="publication:endPage" >
            <xsl:value-of select="." />
        </xsl:element>
    </xsl:template>
    <xsl:template match="//AppDocument/year">
        <xsl:element name="publication:year" >
            <xsl:value-of select="." />
        </xsl:element>
    </xsl:template>
    <xsl:template match="//AppDocument/isbn" >
        <xsl:element name="publication:isbn">
            <xsl:value-of select="." />
        </xsl:element>
    </xsl:template>
    <xsl:template match="//AppDocument/publisher">
        <xsl:element name="publication:publisher" >
            <xsl:value-of select="." />
        </xsl:element>
    </xsl:template>
    <xsl:template match="//AppDocument/Author">
        <xsl:element name="publication:author">
            <xsl:if test="main">
                <xsl:attribute name="main">
                    <xsl:if test="main &gt; 0">true</xsl:if>
                    <xsl:if test="main &lt; 1">false</xsl:if>
                </xsl:attribute>
            </xsl:if>
            <!-- if authorID is present in data then the author type
                is "internal" otherwise "external" -->
            <xsl:if test="authorID" >
                <xsl:attribute name="type">internal</xsl:attribute>
                <xsl:apply-templates />
            </xsl:if>
            <xsl:if test="not(authorID)">
                <xsl:attribute name="type">external</xsl:attribute>
                <xsl:element name="publication:person">
                    <xsl:value-of select="//AppDocument/Author/fullName" />
                </xsl:element>
            </xsl:if>
        </xsl:element>
    </xsl:template>
</xsl:stylesheet>