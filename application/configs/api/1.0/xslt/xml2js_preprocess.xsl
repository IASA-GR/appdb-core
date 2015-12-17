<?xml version="1.0"?>
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

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
  <xsl:output method="xml"/>
  <xsl:strip-space elements="*"/>


  <xsl:template match="*">
	  <xsl:choose>
		  <xsl:when test="name() = 'appdb:appdb'">
			  <xsl:if test="name() = 'appdb:appdb'">
					<xsl:element name="appdb">
						<xsl:element name="meta">
							 <xsl:for-each select="@*">
							  <xsl:element name="{local-name()}">
								<xsl:value-of select="."/>
							  </xsl:element>
							</xsl:for-each>
						</xsl:element>
						<xsl:for-each select="./*">
						 <xsl:element name="{local-name()}">
							<xsl:for-each select="@*">
							  <xsl:element name="{local-name()}">
								<xsl:value-of select="."/>
							  </xsl:element>
							</xsl:for-each>
							<xsl:if test="text() and @*">
								<xsl:choose>
									<xsl:when test="@*">
										<xsl:element name="value">
											<xsl:value-of select="text()"></xsl:value-of>
										</xsl:element>
										<xsl:text></xsl:text>
									</xsl:when>
									<xsl:otherwise>
										<xsl:value-of select="text()"></xsl:value-of>
									</xsl:otherwise>
								</xsl:choose>
							</xsl:if>
							<xsl:apply-templates select="*"/>
						  </xsl:element>
						</xsl:for-each>
					</xsl:element>
				</xsl:if>
		  </xsl:when>
		  <xsl:otherwise>
			  <xsl:element name="{local-name()}">
				<xsl:for-each select="@*">
				  <xsl:element name="{local-name()}">
					<xsl:value-of select="."/>
				  </xsl:element>
				</xsl:for-each>
				<xsl:if test="text()">
					<xsl:choose>
						<xsl:when test="@*">
							<xsl:element name="value">
								<xsl:value-of select="text()"></xsl:value-of>
							</xsl:element>
							<xsl:text></xsl:text>
						</xsl:when>
						<xsl:otherwise>
							<xsl:value-of select="."></xsl:value-of>
						</xsl:otherwise>
					</xsl:choose>
				</xsl:if>
				<xsl:apply-templates select="*"/>
			  </xsl:element>
		  </xsl:otherwise>
	  </xsl:choose>
	  
    
  </xsl:template>

</xsl:stylesheet>
