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
    Document   : target.xsl
    Created on : February 22, 2013, 6:47 PM
    Author     : nakos
    Description:
        Purpose of transformation follows.
-->

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0"
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
	<xsl:output method="xml"/>
	<xsl:strip-space elements="*" />
	<xsl:template match="*">
		<xsl:copy>
			<xsl:copy-of select="@*" />
			<xsl:apply-templates />
		</xsl:copy>
	</xsl:template>
	
	<xsl:template name="tokenize">
		<xsl:param name="text" select="." />
		<xsl:param name="separator" select="';'" />
		<xsl:choose>
            <xsl:when test="not(contains($text, $separator))">
                <xsl:element name='artifact'>
					<xsl:attribute name="type">
						<xsl:value-of select="normalize-space($text)"/>
					</xsl:attribute>
                </xsl:element>
            </xsl:when>
            <xsl:otherwise>
                <xsl:element name='artifact'>
					<xsl:attribute name="type">
						<xsl:value-of select="normalize-space(substring-before($text, $separator))"/>
					</xsl:attribute>
                </xsl:element>
                <xsl:call-template name="tokenize">
                    <xsl:with-param name="text" select="substring-after($text, $separator)"/>
                </xsl:call-template>
            </xsl:otherwise>
        </xsl:choose>
	</xsl:template>
	
	<xsl:template name="target" match="//CommRepoAllowedPlatformCombination">
		<xsl:element name="target">
			<xsl:attribute name="id">
				<xsl:value-of select="current()/id"/>
			</xsl:attribute>
			<xsl:attribute name="incrementalsupport">
				<xsl:if test="incRelSupport[text()='yes']">true</xsl:if>
				<xsl:if test="not(incRelSupport[text()='yes'])">false</xsl:if>
			</xsl:attribute>
			<xsl:attribute name="cansupport">
				<xsl:value-of select="current()/canSupport" />
			</xsl:attribute>
			<xsl:element name="os">
				<xsl:attribute name="id">
					<xsl:value-of select="current()/CommRepoOs/id"/>
				</xsl:attribute>
				<xsl:attribute name="name">
					<xsl:value-of select="current()/CommRepoOs/name"/>
				</xsl:attribute>
				<xsl:attribute name="displayname">
					<xsl:value-of select="current()/CommRepoOs/displayName"/>
				</xsl:attribute>
				<xsl:attribute name="flavor">
					<xsl:value-of select="current()/CommRepoOs/flavor"/>
				</xsl:attribute>
				<xsl:attribute name="displayflavor">
					<xsl:value-of select="current()/CommRepoOs/displayFlavor"/>
				</xsl:attribute>
				<xsl:attribute name="acronym">
					<xsl:value-of select="current()/CommRepoOs/acronym"/>
				</xsl:attribute>
				<xsl:call-template name="tokenize">
					<xsl:with-param name="text" select="current()/CommRepoOs/artifactType" />
				</xsl:call-template>
			</xsl:element>
			<xsl:element name="arch">
				<xsl:attribute name="id">
					<xsl:value-of select="current()/CommRepoArch/id" />
				</xsl:attribute>
				<xsl:attribute name="name">
					<xsl:value-of select="current()/CommRepoArch/name" />
				</xsl:attribute>
				<xsl:attribute name="displayname">
					<xsl:if test="not(current()/CommRepoArch/label[.=''])">
						<xsl:value-of select="current()/CommRepoArch/label" />
					</xsl:if>
					<xsl:if test="current()/CommRepoArch/label[.='']">
						<xsl:value-of select="current()/CommRepoArch/name" />
					</xsl:if>
				</xsl:attribute>
			</xsl:element>
			<xsl:element name="deploymethod">
				<xsl:attribute name="id">
					<xsl:value-of select="current()/CommRepoDmethod/id" />
				</xsl:attribute>
				<xsl:attribute name="name">
					<xsl:value-of select="current()/CommRepoDmethod/name" />
				</xsl:attribute>
				<xsl:attribute name="label">
					<xsl:if test="not(current()/CommRepoDmethod/label[.=''])">
						<xsl:value-of select="current()/CommRepoDmethod/label" />
					</xsl:if>
					<xsl:if test="current()/CommRepoDmethod/label[.='']">
						<xsl:value-of select="current()/CommRepoDmethod/name" />
					</xsl:if>
				</xsl:attribute>
			</xsl:element>
		</xsl:element>
	</xsl:template>	
</xsl:stylesheet>