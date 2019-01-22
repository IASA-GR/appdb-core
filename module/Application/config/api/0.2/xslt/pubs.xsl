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
    <xsl:template match="//publication/Application" />
    <!--Apply templates -->
    <xsl:template match="//publication">
        <xsl:element name="publication:publication">
            <xsl:attribute name="id">
                <xsl:value-of select="./@id" />
            </xsl:attribute>
            <xsl:apply-templates/>
        </xsl:element>
    </xsl:template>
    <xsl:template match="//publication/title">
        <xsl:element name="publication:title">
            <xsl:value-of select="." />
        </xsl:element>
    </xsl:template>
    <xsl:template match="//publication/url" >
        <xsl:element name="publication:url">
            <xsl:value-of select="." />
        </xsl:element>
    </xsl:template>
    <xsl:template match="//publication/type">
        <xsl:element name="publication:type">
			<xsl:value-of select="." />
        </xsl:element>
    </xsl:template>
    <xsl:template match="//publication/conference" >
        <xsl:element name="publication:conference">
            <xsl:value-of select="." />
        </xsl:element>
    </xsl:template>
    <xsl:template match="//publication/proceedings" >
        <xsl:element name="publication:proceedings">
            <xsl:value-of select="." />
        </xsl:element>
    </xsl:template>
    <xsl:template match="//publication/volume" >
        <xsl:element name="publication:volume">
            <xsl:value-of select="." />
        </xsl:element>
    </xsl:template>
    <xsl:template match="//publication/startpage">
        <xsl:element name="publication:startPage">
            <xsl:value-of select="." />
        </xsl:element>
    </xsl:template>
    <xsl:template match="//publication/endpage">
        <xsl:element name="publication:endPage" >
            <xsl:value-of select="." />
        </xsl:element>
    </xsl:template>
    <xsl:template match="//publication/year">
        <xsl:element name="publication:year" >
            <xsl:value-of select="." />
        </xsl:element>
    </xsl:template>
    <xsl:template match="//publication/isbn" >
        <xsl:element name="publication:isbn">
            <xsl:value-of select="." />
        </xsl:element>
    </xsl:template>
    <xsl:template match="//publication/publisher">
        <xsl:element name="publication:publisher" >
            <xsl:value-of select="." />
        </xsl:element>
    </xsl:template>
    <xsl:template match="//publication/author">
        <xsl:element name="publication:author">
            <xsl:if test="@main">
                <xsl:attribute name="main">
					<xsl:value-of select="./@main"/>
                </xsl:attribute>
            </xsl:if>
			<xsl:attribute name="type">
				<xsl:value-of select="./@type" />
			</xsl:attribute>
        </xsl:element>
    </xsl:template>
</xsl:stylesheet>
