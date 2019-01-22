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
    Document   : contact.xsl
    Created on : March 12, 2013, 5:23 PM
    Author     : nakos
    Description:
        Purpose of transformation follows.
-->

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
	<xsl:output method="html"/>

    <!-- TODO customize transformation rules 
         syntax recommendation http://www.w3.org/TR/xslt 
    -->
	<xsl:template match="//MetaContact|//VMetaProductRepoAreaContact">
		<xsl:element name="contact">
			<xsl:if test="current()/id">
				<xsl:attribute name="id">
					<xsl:value-of select="current()/id"/>
				</xsl:attribute>
			</xsl:if>
			<xsl:if test="current()/assocId">
				<xsl:attribute name="associatedid">
					<xsl:value-of select="current()/assocId" />
				</xsl:attribute>
			</xsl:if>
			<xsl:if test="current()/assocEntity">
				<xsl:attribute name="associatedtype">
					<xsl:value-of select="current()/assocEntity" />
				</xsl:attribute>
			</xsl:if>
			<xsl:attribute name="externalid">
				<xsl:value-of select="current()/externalId" />
			</xsl:attribute>
			<xsl:element name="contacttype">
				<xsl:attribute name="id">
					<xsl:value-of select="current()/MetaContactType/id" />
				</xsl:attribute>
				<xsl:attribute name="name">
					<xsl:value-of select="current()/MetaContactType/name" />
				</xsl:attribute>
			</xsl:element>
			<xsl:element name="firstname">
				<xsl:value-of select="current()/firstname" />
			</xsl:element>
			<xsl:element name="lastname">
				<xsl:value-of select="current()/lastname" />
			</xsl:element>
			<xsl:element name="email">
				<xsl:if test='/repository/@userid'>
					<xsl:value-of select="current()/email" />
				</xsl:if>
				<xsl:if test='not(/repository/@userid)'>
					<xsl:if test='boolean(/repository/@content="repositoryarea")'>
						<xsl:value-of select="concat('https://',//repository/@host,'/repository/contacts/contactimage?id=',current()/id)"/>
					</xsl:if>
					<xsl:if test='not(boolean(/repository/@content="repositoryarea"))'>
						<xsl:value-of select="concat('https://',//repository/@host,'/repository/contacts/contactimage?id=',current()/id)"/>
					</xsl:if>
				</xsl:if>
			</xsl:element>
		</xsl:element>
	</xsl:template>

</xsl:stylesheet>
