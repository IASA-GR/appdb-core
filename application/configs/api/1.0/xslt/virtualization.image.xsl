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
	Document   : virtualization.image.xsl
	Created on : October 22, 2013, 6:31 PM
	Author     : nakos
	Description:
		Purpose of transformation follows.
-->

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0"
  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
  xmlns:appdb="http://appdb.egi.eu/api/1.0/appdb"
  xmlns:virtualization="http://appdb.egi.eu/api/1.0/virtualization"
  xmlns:person="http://appdb.egi.eu/api/1.0/person" 
  xmlns:site="http://appdb.egi.eu/api/1.0/site"
  xmlns:siteservice="http://appdb.egi.eu/api/1.0/site" 
  xmlns:vo="http://appdb.egi.eu/api/1.0/vo">
	<xsl:output method="xml" indent="yes" />
	<xsl:strip-space elements="*" />
	<xsl:template match="appdb:appdb">
		<xsl:copy>
			<xsl:apply-templates />
		</xsl:copy>
	</xsl:template>
	<xsl:template match="//vmiinstance">
		<xsl:element name="virtualization:image">
			<xsl:attribute name="id"><xsl:value-of select="./id" /></xsl:attribute>
			<xsl:if test='./baseid'>
				<xsl:attribute name="baseid"><xsl:value-of select="./baseid" /></xsl:attribute>
			</xsl:if>
			<xsl:if test='./requested_baseid'>
				<xsl:attribute name="requested_baseid"><xsl:value-of select="./requested_baseid" /></xsl:attribute>
			</xsl:if>
			<xsl:if test='./requested_id'>
				<xsl:attribute name="requested_id"><xsl:value-of select="./requested_id" /></xsl:attribute>
			</xsl:if>
			<xsl:attribute name="version"><xsl:value-of select="./version" /></xsl:attribute>
			<xsl:attribute name="published"><xsl:value-of select="./published" /></xsl:attribute>
			<xsl:attribute name="archived"><xsl:value-of select="./archived" /></xsl:attribute>
			<xsl:element name="virtualization:application">
				<xsl:attribute name="id"><xsl:value-of select="./application/id"/></xsl:attribute>
				<xsl:attribute name="cname"><xsl:value-of select="./application/cname"/></xsl:attribute>
				<xsl:value-of select="./application/name"/>
			</xsl:element>
			<xsl:element name="virtualization:appliance">
				<xsl:attribute name="version"><xsl:value-of select="./vappliance/version"/></xsl:attribute>
				<xsl:attribute name="archived"><xsl:value-of select="./vappliance/archivedon"/></xsl:attribute>
				<xsl:attribute name="createdon"><xsl:value-of select="./vappliance/createdon"/></xsl:attribute>
				<xsl:attribute name="expireson"><xsl:value-of select="./vappliance/expireson"/></xsl:attribute>
				<xsl:attribute name="expiresin"><xsl:value-of select="./vappliance/expiresin"/></xsl:attribute>
			</xsl:element>
			<xsl:element name="virtualization:identifier"><xsl:value-of select="./identifier"/></xsl:element>
			<xsl:if test='./baseidentifier'>
				<xsl:element name="virtualization:baseidentifier"><xsl:value-of select="./baseidentifier"/></xsl:element>
			</xsl:if>
			<xsl:element name="virtualization:size"><xsl:value-of select="./size"/></xsl:element>
			<xsl:element name="virtualization:url"><xsl:value-of select="./url"/></xsl:element>
			<xsl:element name="virtualization:checksum">
				<xsl:attribute name="hash"><xsl:value-of select="./checksum/hash"/></xsl:attribute>
				<xsl:value-of select="./checksum/value"/>
			</xsl:element>
			<xsl:element name="virtualization:arch">
				<xsl:attribute name="id"><xsl:value-of select="./arch/id" /></xsl:attribute>
				<xsl:value-of select="./arch/name" />
			</xsl:element>
			<xsl:element name="virtualization:os">
				<xsl:attribute name="id"><xsl:value-of select="./os/id" /></xsl:attribute>
				<xsl:attribute name="version"><xsl:value-of select="./os/version" /></xsl:attribute>
				<xsl:value-of select="./os/family" />
			</xsl:element>
			<xsl:element name="virtualization:format">
				<xsl:value-of select="./format" />
			</xsl:element>
			<xsl:element name="virtualization:hypervisor">
				<xsl:attribute name="id"></xsl:attribute>
				<xsl:value-of select="./hypervisor" />
			</xsl:element>
			<xsl:element name="virtualization:title"><xsl:value-of select="./title" /></xsl:element>
			<xsl:element name="virtualization:notes"><xsl:value-of select="./notes" /></xsl:element>
			<xsl:element name="virtualization:description"><xsl:value-of select="./description" /></xsl:element>
			
			<xsl:element name="virtualization:cores">
				<xsl:attribute name="minimum"><xsl:value-of select="./cores/minimum"/></xsl:attribute>
				<xsl:attribute name="recommended"><xsl:value-of select="./cores/recommended"/></xsl:attribute>
			</xsl:element>
			<xsl:element name="virtualization:ram">
				<xsl:attribute name="minimum"><xsl:value-of select="./ram/minimum"/></xsl:attribute>
				<xsl:attribute name="recommended"><xsl:value-of select="./ram/recommended"/></xsl:attribute>
			</xsl:element>
                        <xsl:if test='./accelerators/type'>
                            <xsl:element name="virtualization:accelerators">
                                <xsl:attribute name='minimum'>
                                    <xsl:value-of select='./accelerators/minimum'></xsl:value-of>
                                </xsl:attribute>
                                <xsl:attribute name='recommended'>
                                    <xsl:value-of select='./accelerators/recommended'></xsl:value-of>
                                </xsl:attribute>
                                <xsl:attribute name='type'>
                                    <xsl:value-of select='./accelerators/type'></xsl:value-of>
                                </xsl:attribute>
                            </xsl:element>
                        </xsl:if>
                        <xsl:if test='./network_traffic'>
                            <xsl:for-each select="./network_traffic/*">
                                <xsl:element name="virtualization:network_traffic">
                                    <xsl:attribute name='direction'>
                                        <xsl:value-of select='./direction'></xsl:value-of>
                                    </xsl:attribute>
                                    <xsl:attribute name='protocols'>
                                        <xsl:value-of select='./protocols'></xsl:value-of>
                                    </xsl:attribute>
                                    <xsl:attribute name='ip_range'>
                                        <xsl:value-of select='./ip_range'></xsl:value-of>
                                    </xsl:attribute>
                                    <xsl:attribute name='port_range'>
                                        <xsl:value-of select='./port_range'></xsl:value-of>
                                    </xsl:attribute>
                                </xsl:element>
                            </xsl:for-each>
                        </xsl:if>
                        <xsl:element name="virtualization:defaultaccess">
                            <xsl:value-of select="./defaultaccess"></xsl:value-of>
                        </xsl:element>
                        <xsl:if test="./contextformat">
                            <xsl:for-each select="./contextformat/*">
                                <xsl:element name="virtualization:contextformat">
                                    <xsl:attribute name="id"><xsl:value-of select="./id" /></xsl:attribute>
                                    <xsl:attribute name="name"><xsl:value-of select="./name" /></xsl:attribute>
                                </xsl:element>
                            </xsl:for-each>
                        </xsl:if>
			<xsl:element name="virtualization:addedon"><xsl:value-of select="./addedon" /></xsl:element>
			<xsl:choose>
				<xsl:when test="./addedby">
					<xsl:element name="virtualization:addedby">
						<xsl:attribute name="id"><xsl:value-of select="./addedby/id" /></xsl:attribute>
						<xsl:attribute name="cname"><xsl:value-of select="./addedby/cname" /></xsl:attribute>
						<xsl:element name="person:firstname"><xsl:value-of select="./addedby/firstname" /></xsl:element>
						<xsl:element name="person:lastname"><xsl:value-of select="./addedby/lastname" /></xsl:element>
						<xsl:element name="person:permalink"><xsl:value-of select="./addedby/permalink" /></xsl:element>
					</xsl:element>
				</xsl:when>
			</xsl:choose>
			<xsl:element name="virtualization:lastupdatedon"><xsl:value-of select="./lastupdatedon" /></xsl:element>
			<xsl:choose>
				<xsl:when test="./lastupdatedby">
					<xsl:element name="virtualization:lastupdatedby">
						<xsl:attribute name="id"><xsl:value-of select="./lastupdatedby/id" /></xsl:attribute>
						<xsl:attribute name="cname"><xsl:value-of select="./lastupdatedby/cname" /></xsl:attribute>
						<xsl:element name="person:firstname"><xsl:value-of select="./lastupdatedby/firstname" /></xsl:element>
						<xsl:element name="person:lastname"><xsl:value-of select="./lastupdatedby/lastname" /></xsl:element>
						<xsl:element name="person:permalink"><xsl:value-of select="./lastupdatedby/permalink" /></xsl:element>
					</xsl:element>
				</xsl:when>
			</xsl:choose>
			<xsl:element name="virtualization:mpuri">
				<xsl:value-of select="./mpuri"/>
			</xsl:element>
			<xsl:if test="./basempuri">
				<xsl:element name="virtualization:basempuri">
					<xsl:value-of select="./basempuri"/>
				</xsl:element>
			</xsl:if>
			<xsl:if test='./vo'>
				<xsl:element name="virtualization:vo">
					<xsl:attribute name='id'>
						<xsl:value-of select='./vo/id'></xsl:value-of>
					</xsl:attribute>
					<xsl:attribute name='name'>
						<xsl:value-of select='./vo/name'></xsl:value-of>
					</xsl:attribute>
					<xsl:attribute name='discipline'>
						<xsl:value-of select='./vo/domain'></xsl:value-of>
					</xsl:attribute>
					<xsl:element name='virtualization:voimagelist'>
						<xsl:attribute name='id'>
							<xsl:value-of select="./vo/voimagelist/id"></xsl:value-of>
						</xsl:attribute>
						<xsl:attribute name='state'>
							<xsl:value-of select="./vo/voimagelist/state"></xsl:value-of>
						</xsl:attribute>
						<xsl:element name='virtualization:voimage'>
							<xsl:attribute name='id'>
								<xsl:value-of select="./vo/voimagelist/voimage/id"></xsl:value-of>
							</xsl:attribute>
							<xsl:attribute name='state'>
								<xsl:value-of select="./vo/voimagelist/voimage/state"></xsl:value-of>
							</xsl:attribute>
						</xsl:element>
					</xsl:element>
				</xsl:element>
			</xsl:if>
			<xsl:if test='./contextscript'>
				<xsl:element name="virtualization:contextscript">
					<xsl:attribute name="id">
						<xsl:value-of select="./contextscript/id"></xsl:value-of>
					</xsl:attribute>
					<xsl:element name="url">
						<xsl:value-of select="./contextscript/url"></xsl:value-of>
					</xsl:element>
					<xsl:element name="checksum">
						<xsl:attribute name="hashtype">
							<xsl:value-of select="./contextscript/hashtype" />
						</xsl:attribute>
						<xsl:value-of select="./contextscript/checksum"></xsl:value-of>
					</xsl:element>
					<xsl:element name="size">
						<xsl:attribute name="unit">
							<xsl:text>byte</xsl:text>
						</xsl:attribute>
						<xsl:value-of select="./contextscript/size"></xsl:value-of>
					</xsl:element>
				</xsl:element>
			</xsl:if>
			<xsl:if test='./sites'>
				<xsl:for-each select="./sites/*[starts-with(name(),'item')]">
					<xsl:element name="site:site">
						<xsl:attribute name="id">
							<xsl:value-of select="./id"/>
						</xsl:attribute>
						<xsl:attribute name="name">
							<xsl:value-of select="./name"/>
						</xsl:attribute>
						<xsl:element name="site:officialname">
							<xsl:value-of select="./officialname" />
						</xsl:element>
						<xsl:for-each select='./url/*'>
							<xsl:element name='site:url'>
								<xsl:attribute name='type'>
									<xsl:value-of select='name()'/>
								</xsl:attribute>
								<xsl:value-of select='.'/>
							</xsl:element>
						</xsl:for-each>
						<xsl:for-each select="./services/*[starts-with(name(),'item')]">
							<xsl:element name="site:service">
								<xsl:attribute name="id">
									<xsl:value-of select='./id'/>
								</xsl:attribute>
								<xsl:attribute name="host">
									<xsl:value-of select="./hostname"></xsl:value-of>
								</xsl:attribute>
								<xsl:attribute name="ngi">
									<xsl:value-of select="./ngi"></xsl:value-of>
								</xsl:attribute>
								<xsl:for-each select="./url/*">
									<xsl:element name='siteservice:url'>
										<xsl:attribute name='type'>
											<xsl:value-of select='name()'/>
										</xsl:attribute>
										<xsl:value-of select='.'/>
									</xsl:element>
								</xsl:for-each>
								<xsl:for-each select="./vos/*">
									<xsl:element name="vo:vo">
										
											<xsl:attribute name='name'>
												<xsl:value-of select='./name'></xsl:value-of>
											</xsl:attribute>
											<xsl:if test="./imageliststate != ''">
												<xsl:attribute name='voimageliststate'>
													<xsl:value-of select='./imageliststate'></xsl:value-of>
												</xsl:attribute>
												<xsl:attribute name='voimagestate'>
													<xsl:value-of select='./imagestate'></xsl:value-of>
												</xsl:attribute>

												<xsl:for-each select="./url/*">
													<xsl:element name='vo:url'>
														<xsl:attribute name='type'>
															<xsl:value-of select='name()'/>
														</xsl:attribute>
														<xsl:value-of select='.'/>
													</xsl:element>
												</xsl:for-each>
											</xsl:if>
											<xsl:element name='siteservice:occi'>
												<xsl:element name='siteservice:id'><xsl:value-of select='./occi/id' /></xsl:element>
												<xsl:element name='siteservice:endpoint'><xsl:value-of select='./occi/endpoint' /></xsl:element>
											</xsl:element>
									</xsl:element>
								</xsl:for-each>
							</xsl:element>
						</xsl:for-each>
					</xsl:element>
				</xsl:for-each>
			</xsl:if>	
		</xsl:element>
	</xsl:template>
</xsl:stylesheet>
