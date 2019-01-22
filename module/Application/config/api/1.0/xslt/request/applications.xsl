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
    <xsl:template match="discipline">
         <xsl:element name="discipline.id">
             <xsl:value-of select="." />
         </xsl:element>
     </xsl:template>
     <xsl:template match="subdiscipline">
         <xsl:element name="subdiscipline.id">
             <xsl:value-of select="." />
         </xsl:element>
     </xsl:template>
     <xsl:template match="status">
         <xsl:element name="statusid">
             <xsl:value-of select="." />
         </xsl:element>
     </xsl:template>
     <xsl:template match="region">
         <xsl:element name="regionid" >
             <xsl:value-of select="." />
         </xsl:element>
     </xsl:template>
     <xsl:template match="country">
         <xsl:element name="country.id">
             <xsl:value-of select="." />
         </xsl:element>
     </xsl:template>
     <xsl:template match="middleware">
         <xsl:element name="middleware.id">
             <xsl:value-of select="." />
         </xsl:element>
     </xsl:template>
     <xsl:template match="addedon">
         <xsl:element name="dateadded" >
             <xsl:value-of select="." />
         </xsl:element>
     </xsl:template>
     <xsl:template match="owner">
         <xsl:element name="addedby" >
                <xsl:value-of select="." />
         </xsl:element>
     </xsl:template>
     <xsl:template match="vo">
        <xsl:element name="vo.id">
            <xsl:value-of select="." />
        </xsl:element>
     </xsl:template>
     <xsl:template match="tool">
         <xsl:element name="tool">
             <xsl:choose>
                 <xsl:when test="string(.)='true'" >true</xsl:when>
                 <xsl:when test="string(.)='false'">false</xsl:when>
                 <xsl:when test=". >0" >true</xsl:when>
                 <xsl:otherwise>false</xsl:otherwise>
             </xsl:choose>
         </xsl:element>
     </xsl:template>
     <xsl:template match="flt">
        <xsl:element name="flt">
            <xsl:value-of select="." />
        </xsl:element>
     </xsl:template>
     <xsl:template match="fuzzySearch">
        <xsl:element name="fuzzySearch">
            <xsl:value-of select="." />
        </xsl:element>
     </xsl:template>
</xsl:stylesheet>
