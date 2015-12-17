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
    Document   : apps.xsl
    Created on : January 27, 2011, 10:56 AM
    Author     : nakos
-->
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0"
xmlns:application="http://appdb.egi.eu/api/1.0/application"
xmlns:vo="http://appdb.egi.eu/api/1.0/vo">
   <!-- Remove elements -->
    <xsl:template match="//id"/>
    <xsl:template match="//domainID"/>
    <xsl:template match="//subdomainID"/>
    <xsl:template match="//statusID"/>
    <xsl:template match="//tool"/>
    <xsl:template match="//groupID" />
    <xsl:template match="//respect" />
    <xsl:template match="//guid" />
    <xsl:template match="//hasDocs" />
   <!-- <xsl:template match="//addedBy" />-->
   <!-- Apply Templates -->
    <xsl:template match="//Application | //AppView">
        <xsl:element name="application:application">
            <xsl:attribute name="id">
                <xsl:value-of select="./id" />
            </xsl:attribute>
            <xsl:attribute name="tool">
                <xsl:value-of select="./tool" />
            </xsl:attribute>
            <xsl:apply-templates/>
        </xsl:element >
    </xsl:template>
     <xsl:template match="//Application/name">
        <xsl:element name="application:name">
            <xsl:value-of select="." />
        </xsl:element>
    </xsl:template>
    <xsl:template match="//Application/description">
        <xsl:element name="application:description">
            <xsl:value-of select="." />
        </xsl:element>
    </xsl:template>
    <xsl:template match="//Application/abstract">
        <xsl:element name="application:abstract">
            <xsl:value-of select="." />
        </xsl:element>
    </xsl:template>
    <xsl:template match="//Domain">
        <xsl:element name="application:discipline">
            <xsl:attribute name="id">
                <xsl:value-of select="./id" />
            </xsl:attribute>
            <xsl:value-of select="./name" />
        </xsl:element>
    </xsl:template>
    <xsl:template match="//Subdomain">
        <xsl:element name="application:subdiscipline">
            <xsl:attribute name="id">
                <xsl:value-of select="./id" />
            </xsl:attribute>
            <xsl:value-of select="./name" />
        </xsl:element>
    </xsl:template>
    <xsl:template match="//Status">
        <xsl:element name="application:status">
            <xsl:attribute name="id">
                <xsl:value-of select="./id" />
            </xsl:attribute>
            <xsl:value-of select="./name" />
        </xsl:element>
    </xsl:template>
    <xsl:template match="//Middleware | //AppGroup">
        <xsl:element name="application:middleware">
            <xsl:attribute name="id">
                <xsl:value-of select="./ID" />
            </xsl:attribute>
            <xsl:value-of select="./name" />
        </xsl:element>
    </xsl:template>
    <xsl:template match="//Application/VO">
        <xsl:element name="vo:vo">
            <xsl:attribute name="id">
                <xsl:value-of select="./id" />
            </xsl:attribute>
            <xsl:value-of select="./name" />
        </xsl:element>
    </xsl:template>
    <xsl:template match="//Application/dateAdded">
        <xsl:element name="application:addedOn">
            <xsl:value-of select="translate(.,' ','T')" />
        </xsl:element>
    </xsl:template>
    <xsl:template match="//Application/AppUrl">
        <xsl:element name="application:url">
            <xsl:attribute name = "type" >
                <xsl:value-of select=".//description" />
            </xsl:attribute>
            <xsl:value-of select=".//url" />
        </xsl:element>
    </xsl:template>
    <xsl:template match="//Application/permalink" >
        <xsl:element name="application:permalink">
            <xsl:value-of select="." />
        </xsl:element>
    </xsl:template>
    <xsl:template match="//Application/logo" >
        <xsl:element name="application:logo">
            <xsl:value-of select="." />
        </xsl:element>
    </xsl:template>
</xsl:stylesheet>
