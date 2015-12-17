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
xmlns:person="http://appdb.egi.eu/api/1.0/person"
xmlns:dissemination="http://appdb.egi.eu/api/1.0/dissemination"
xmlns:application="http://appdb.egi.eu/api/1.0/application">
    <xsl:template match="//person:gender">
        <xsl:if test=". = ''">
            <xsl:element name="person:gender">
                <xsl:attribute name="xsi:nil">
                    <xsl:value-of select="'true'"/>
                </xsl:attribute>
                <xsl:apply-templates/>
            </xsl:element>
        </xsl:if>
        <xsl:if test=". != ''">
            <xsl:element name="person:gender">
				<xsl:copy-of select="@*" />
                <xsl:apply-templates/>
            </xsl:element>
        </xsl:if>
    </xsl:template>
	<xsl:template match="//person:person">
	    <xsl:if test="@metatype='owner'">
		   <application:owner>
				<xsl:copy-of select="@id|@xsi:nil|@nodissemination|@deleted|@guid|@cname" />
				<xsl:apply-templates/>
			</application:owner>
		</xsl:if>
	    <xsl:if test="@metatype='actor'">
		   <application:addedby>
				<xsl:copy-of select="@id|@xsi:nil|@nodissemination|@deleted|@guid|@cname" />
				<xsl:apply-templates/>
			</application:addedby>
		</xsl:if>
		<xsl:if test="@metatype='contact'">
			<application:contact>
				<xsl:copy-of select="@id|@xsi:nil|@nodissemination|@deleted|@guid|@cname" />
				<xsl:apply-templates/>
			</application:contact>
		</xsl:if>		
		<xsl:if test="@metatype='moderator'">
			<application:moderator>
				<xsl:copy-of select="@id|@xsi:nil|@nodissemination|@deleted|@guid|@cname" />
				<xsl:apply-templates/>
			</application:moderator>
		</xsl:if>		
		<xsl:if test="@metatype='composer'">
			<dissemination:composer>
				<xsl:copy-of select="@id|@xsi:nil|@nodissemination|@deleted|@guid|@cname" />
				<xsl:apply-templates/>
			</dissemination:composer>
		</xsl:if>		
		<xsl:if test="@metatype='recipient'">
			<dissemination:recipient>
				<xsl:copy-of select="@id|@xsi:nil|@nodissemination|@deleted|@guid|@cname" />
				<xsl:apply-templates/>
			</dissemination:recipient>
		</xsl:if>		
		<xsl:if test="@metatype='deleter'">
			<application:deleter>
				<xsl:copy-of select="@id|@xsi:nil|@nodissemination|@deleted|@guid|@cname" />
				<xsl:apply-templates/>
			</application:deleter>
		</xsl:if>		
		<xsl:if test="@metatype='deleter2'">
			<person:deleter>
				<xsl:copy-of select="@id|@xsi:nil|@nodissemination|@deleted|@guid|@cname" />
				<xsl:apply-templates/>
			</person:deleter>
		</xsl:if>		
		<xsl:if test="@metatype='' or not(@metatype)">
			<person:person>
				<xsl:copy-of select="@id|@xsi:nil|@nodissemination|@deleted|@guid|@cname" />
				<xsl:apply-templates/>
			</person:person>
		</xsl:if>		
	</xsl:template>
</xsl:stylesheet>
