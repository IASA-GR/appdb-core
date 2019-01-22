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
    Document   : vmc2appdb.xsl
    Created on : September 3, 2013, 12:50 PM
    Author     : nakos
    Description:
        Transform XML from vmcaster to a valid virtual appliance API XML
-->

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0"
  xmlns:appdb="http://appdb.egi.eu/api/1.0/appdb"
  xmlns:virtualization="http://appdb.egi.eu/api/1.0/virtualization"
  xmlns="http://www.w3.org/2001/XMLSchema" 
  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
	<xsl:output method="xml" indent="yes" omit-xml-declaration="yes"/>
	<xsl:strip-space elements="*" />
	
	<!--
		Will be used for identifying groups of image instances based on comments.
		This is a subject to change if the vmcaster will include "group" element.
	-->
	<xsl:key name="groupname" match="/vmc2appdb/hv_imagelist/hv_images/hv_image/ad_group" use="." />
	
	<xsl:template match="/">
		<xsl:copy>
			<xsl:copy-of select="@*"/>
			<xsl:apply-templates  />
		</xsl:copy>
	</xsl:template>
	
	<xsl:template match="vmc2appdb">
		<xsl:element name="virtualization:appliance" ><!--- [START]: Vitrual appliance -->
			<xsl:attribute name="appid">
				<xsl:value-of select="/vmc2appdb/hv_imagelist/ad_appid" />
			</xsl:attribute>
			<xsl:for-each select="//vmc2appdb/hv_imagelist">
				<xsl:element name="virtualization:instance"><!--- [START]: INSTANCE -->
					<xsl:attribute name="version">
						<xsl:value-of select="hv_version" />
					</xsl:attribute>
					<xsl:if test="//vmc2appdb/hv_imagelist/@published">
						<xsl:attribute name="published">
							<xsl:value-of select="//vmc2appdb/hv_imagelist/@published" />
						</xsl:attribute>
					</xsl:if>
					<xsl:if test="//vmc2appdb/hv_imagelist/@archived">
						<xsl:attribute name="archived">
							<xsl:value-of select="//vmc2appdb/hv_imagelist/@archived" />
						</xsl:attribute>
					</xsl:if>
					<xsl:if test="//vmc2appdb/hv_imagelist/@enabled">
						<xsl:attribute name="enabled">
							<xsl:value-of select="//vmc2appdb/hv_imagelist/@enabled" />
						</xsl:attribute>
					</xsl:if>
					<xsl:if test="//vmc2appdb/hv_imagelist/dc_date_created">
						<xsl:attribute name="createdon">
							<xsl:value-of select="//vmc2appdb/hv_imagelist/dc_date_created" />
						</xsl:attribute>
					</xsl:if>
					<xsl:if test="//vmc2appdb/hv_imagelist/dc_date_expires">
						<xsl:attribute name="expireson">
							<xsl:value-of select="//vmc2appdb/hv_imagelist/dc_date_expires" />
						</xsl:attribute>
					</xsl:if>
					<xsl:if test="//vmc2appdb/hv_imagelist/ad_submissionid">
						<xsl:attribute name="submissionid">
							<xsl:value-of select="//vmc2appdb/hv_imagelist/ad_submissionid" />
						</xsl:attribute>
					</xsl:if>
					<xsl:element name="virtualization:identifier" >
						<xsl:value-of select="dc_identifier" />
					</xsl:element>
					<xsl:element name="virtualization:notes">
						<xsl:value-of select="dc_description" />
					</xsl:element>
					<xsl:for-each select="/vmc2appdb/hv_imagelist/hv_images/hv_image/ad_group[generate-id()= generate-id(key('groupname',.)[1])]">
						<xsl:variable name="currentGroup" select="."/>
						<xsl:element name="virtualization:image"><!--- [START]: IMAGE -->
							<xsl:element name="virtualization:group" ><xsl:value-of select="."/></xsl:element>
							<xsl:element name="virtualization:notes">
								<xsl:value-of select="//vmc2appdb/hv_imagelist/dc_title"></xsl:value-of>
							</xsl:element>
							<xsl:for-each select="key('groupname', $currentGroup)/..">
								<xsl:element name="virtualization:instance"><!--- [START] IMAGE INSTANCE -->
									<xsl:attribute name="version">
										<xsl:value-of select="hv_version" />
									</xsl:attribute>
									<xsl:attribute name="integrity">
										<xsl:text>false</xsl:text>
									</xsl:attribute>
									<xsl:element name="virtualization:identifier">
										<xsl:value-of select="dc_identifier" />
									</xsl:element>
									<xsl:element name="virtualization:title">
										<xsl:value-of select="dc_title" />
									</xsl:element>
									<xsl:element name="virtualization:description">
										<xsl:value-of select="dc_description" />
									</xsl:element>
									<xsl:element name="virtualization:notes">
										<xsl:value-of select="sl_comments"/>
									</xsl:element>
									<xsl:element name="virtualization:url">
										<xsl:value-of select="hv_uri" />
									</xsl:element>
									<xsl:element name="virtualization:checksum" >
										<xsl:attribute name="hash">
											<xsl:text>sha512</xsl:text>
										</xsl:attribute>
										<xsl:value-of select="sl_checksum_sha512" />
									</xsl:element>
									<xsl:element name="virtualization:size" >
										<xsl:value-of select="hv_size" />
									</xsl:element>
									<xsl:if test="hv_format">
										<xsl:element name="virtualization:format">
											<xsl:value-of select="hv_format" />
										</xsl:element>
									</xsl:if>
									<xsl:if test="sl_arch">
										<xsl:element name="virtualization:arch">
											<xsl:value-of select="sl_arch" />
										</xsl:element>
									</xsl:if>
									<xsl:element name="virtualization:os">
										<xsl:attribute name="version">
											<xsl:value-of select="sl_osversion" />
										</xsl:attribute>
										<xsl:attribute name="familyid">
											<xsl:value-of select="sl_os" />
										</xsl:attribute>
										<xsl:value-of select="sl_osname" />
									</xsl:element>
									<!--<xsl:element name="virtualization:os">
										<xsl:attribute name="version">
											<xsl:value-of select="sl_osversion" />
										</xsl:attribute>
											<xsl:value-of select="sl_os" />
									</xsl:element> -->
									<xsl:element name="virtualization:hypervisor">
										<xsl:value-of select="hv_hypervisor" />
									</xsl:element>
									<xsl:element name="virtualization:cores">
										<xsl:if test="hv_core_minimum">
											<xsl:attribute name="minimum">
												<xsl:value-of select="hv_core_minimum" />
											</xsl:attribute>
										</xsl:if>
										<xsl:if test="ad_core_recommended">
										<xsl:attribute name="recommended">
											<xsl:value-of select="ad_core_recommended" />
										</xsl:attribute>
										</xsl:if>
									</xsl:element>							
									<xsl:element name="virtualization:ram">
										<xsl:if test="hv_ram_minimum">
											<xsl:attribute name="minimum">
												<xsl:value-of select="hv_ram_minimum" />
											</xsl:attribute>
										</xsl:if>
										<xsl:if test="ad_ram_recommended">
											<xsl:attribute name="recommended">
												<xsl:value-of select="ad_ram_recommended" />
											</xsl:attribute>
										</xsl:if>
									</xsl:element>
								</xsl:element><!--- [END] IMAGE INSTANCE -->
							</xsl:for-each>
						</xsl:element><!--- [END] IMAGE -->
					</xsl:for-each>
				</xsl:element><!--- [END]: INSTANCE -->
			</xsl:for-each>
		</xsl:element><!--- [END]: Vitrual appliance -->
	</xsl:template>
	

</xsl:stylesheet>
