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
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0"
  xmlns:appdb="http://appdb.egi.eu/api/1.0/appdb"
  xmlns:virtualization="http://appdb.egi.eu/api/1.0/virtualization"
  xmlns:application="http://appdb.egi.eu/api/1.0/application"
  xmlns:person="http://appdb.egi.eu/api/1.0/person">
	<xsl:output method="xml" indent="yes" />
	<xsl:strip-space elements="*" />
	
	<xsl:key name="vappid" match="virtualization:appliance" use="@vappid" />
	<xsl:key name="vmiid" match="virtualization:image" use="@vmiid" />
	<xsl:key name="flavourid" match="virtualization:image" use="@flavourid" />
	<xsl:param name="isprivateversion" select="//virtualization:appliance/@imageListsPrivate" />
	<xsl:template match="appdb:appdb">
		<xsl:copy>
			<xsl:copy-of select="@*"/>
			<xsl:apply-templates select="virtualization:appliance[generate-id(.)=generate-id(key('vappid', @vappid)[1])]" />
		</xsl:copy>
	</xsl:template>
	<xsl:template match="virtualization:appliance">
		<xsl:element name="virtualization:appliance">
			<xsl:attribute name="id"><xsl:value-of select="@vappid" /></xsl:attribute>
			<xsl:attribute name="appid"><xsl:value-of select="@appid" /></xsl:attribute>
			<xsl:attribute name="identifier"><xsl:value-of select="@vappidentifier" /></xsl:attribute>
			<xsl:attribute name="name"><xsl:value-of select="virtualization:name" /></xsl:attribute>
			<xsl:attribute name="imageListsPrivate"><xsl:value-of select="@imageListsPrivate" /></xsl:attribute>
			<xsl:for-each select="key('vappid', @vappid)">
				<xsl:element name="virtualization:instance">
					<xsl:attribute name="id"><xsl:value-of select="@vaversionid" /></xsl:attribute>
					<xsl:attribute name="version"><xsl:value-of select="@version" /></xsl:attribute>
					<xsl:attribute name="published"><xsl:value-of select="@published" /></xsl:attribute>
					<xsl:attribute name="publishedon"><xsl:value-of select="@publishedon" /></xsl:attribute>
					<xsl:attribute name="createdon"><xsl:value-of select="@createdon" /></xsl:attribute>
					<xsl:attribute name="enabled"><xsl:value-of select="@enabled" /></xsl:attribute>
					<xsl:attribute name="enabledon"><xsl:value-of select="@enabledon" /></xsl:attribute>
					<xsl:attribute name="archived"><xsl:value-of select="@archived" /></xsl:attribute>
					<xsl:if test="@archivedon">
						<xsl:attribute name="archivedon"><xsl:value-of select="@archivedon" /></xsl:attribute>
					</xsl:if>
					<xsl:attribute name="status"><xsl:value-of select="@status" /></xsl:attribute>
					<xsl:if test="@expireson">
						<xsl:attribute name="expireson"><xsl:value-of select="@expireson" /></xsl:attribute>
					</xsl:if>
					<xsl:if test="@expiresin">
						<xsl:attribute name="expiresin"><xsl:value-of select="@expiresin" /></xsl:attribute>
					</xsl:if>
					<xsl:if test="person:publishedby/@id">
						<xsl:element name="virtualization:publishedby">
							<xsl:choose>
								<xsl:when test="person:publishedby">
									<xsl:attribute name="id"><xsl:value-of select="person:publishedby/@id" /></xsl:attribute>
									<xsl:attribute name="cname"><xsl:value-of select="person:publishedby/@cname" /></xsl:attribute>
									<xsl:element name="person:firstname"><xsl:value-of select="person:publishedby/person:firstname" /></xsl:element>
									<xsl:element name="person:lastname"><xsl:value-of select="person:publishedby/person:lastname" /></xsl:element>
									<xsl:element name="person:institute"><xsl:value-of select="person:publishedby/person:institute" /></xsl:element>
									<xsl:element name="person:role">
										<xsl:attribute name="id"><xsl:value-of select="person:publishedby/person:role/@id" /></xsl:attribute>
										<xsl:attribute name="type"><xsl:value-of select="person:publishedby/person:role/@type" /></xsl:attribute>
									</xsl:element>
								</xsl:when>
							</xsl:choose>
						</xsl:element>
					</xsl:if>
					<xsl:if test="person:enabledby/@id">
						<xsl:element name="virtualization:enabledby">
							<xsl:choose>
								<xsl:when test="person:enabledby">
									<xsl:attribute name="id"><xsl:value-of select="person:enabledby/@id" /></xsl:attribute>
									<xsl:attribute name="cname"><xsl:value-of select="person:enabledby/@cname" /></xsl:attribute>
									<xsl:element name="person:firstname"><xsl:value-of select="person:enabledby/person:firstname" /></xsl:element>
									<xsl:element name="person:lastname"><xsl:value-of select="person:enabledby/person:lastname" /></xsl:element>
									<xsl:element name="person:institute"><xsl:value-of select="person:enabledby/person:institute" /></xsl:element>
									<xsl:element name="person:role">
										<xsl:attribute name="id"><xsl:value-of select="person:enabledby/person:role/@id" /></xsl:attribute>
										<xsl:attribute name="type"><xsl:value-of select="person:enabledby/person:role/@type" /></xsl:attribute>
									</xsl:element>
								</xsl:when>
							</xsl:choose>
						</xsl:element>
					</xsl:if>

					<xsl:element name="virtualization:identifier">
						<xsl:value-of select="virtualization:identifier" />
					</xsl:element>
					<xsl:element name="virtualization:notes">
						<xsl:value-of select="virtualization:notes" />
					</xsl:element>
					<xsl:apply-templates select="virtualization:image[generate-id(.)=generate-id(key('vmiid', @vmiid)[1])]" />
				</xsl:element>
			</xsl:for-each>
		</xsl:element>
	</xsl:template>

	<xsl:template match="virtualization:image">
		<xsl:element name="virtualization:image">
		<xsl:attribute name="id"><xsl:value-of select="@vmiid" /></xsl:attribute>
		<xsl:attribute name="name"><xsl:value-of select="./virtualization:vmititle" /></xsl:attribute>
			<xsl:element name="virtualization:description">
				<xsl:value-of select="virtualization:description" />
			</xsl:element>
			<xsl:element name="virtualization:group">
				<xsl:value-of select="virtualization:group" />
			</xsl:element>
			<xsl:element name="virtualization:identifier">
				<xsl:value-of select="virtualization:identifier" />
			</xsl:element>
			<xsl:element name="virtualization:notes">
				<xsl:value-of select="virtualization:notes" />
			</xsl:element>
			<xsl:for-each select="key('vmiid', @vmiid)">
				<xsl:if test="@vmiinstanceid">
					<xsl:element name="virtualization:instance">
						<xsl:attribute name="id"><xsl:value-of select="@vmiinstanceid" /></xsl:attribute>
						<xsl:attribute name="flavourid"><xsl:value-of select="@flavourid" /></xsl:attribute>
						<xsl:attribute name="version"><xsl:value-of select="@version" /></xsl:attribute>
						<xsl:attribute name="integrity"><xsl:value-of select="./virtualization:autointegrity" /></xsl:attribute>
						<xsl:attribute name="enabled"><xsl:value-of select="@enabled" /></xsl:attribute>
						<xsl:attribute name="isprivate"><xsl:value-of select="$isprivateversion" /></xsl:attribute>
						<xsl:choose>
							<xsl:when test="$isprivateversion='false'">
								<xsl:attribute name="protected"><xsl:value-of select="'false'" /></xsl:attribute>
							</xsl:when>
							<xsl:when test="//virtualization:image/@protected">
								<xsl:attribute name="protected"><xsl:value-of select='//virtualization:image/@protected'/></xsl:attribute>
							</xsl:when>
							<xsl:otherwise>
								<xsl:attribute name="protected"><xsl:value-of select="'false'" /></xsl:attribute>
							</xsl:otherwise>
						</xsl:choose>
						<xsl:element name="virtualization:integritycheck">
							<xsl:attribute name="status">
								<xsl:choose>
								<xsl:when test="string-length(./virtualization:integrity/@status) = 0">
									<xsl:text>unchecked</xsl:text>
								</xsl:when>
								<xsl:otherwise>
									<xsl:value-of select="./virtualization:integrity/@status" />
								</xsl:otherwise>
								</xsl:choose>
							</xsl:attribute>
							<xsl:value-of select="./virtualization:integrity/text()" />
						</xsl:element>
						<xsl:element name="virtualization:arch">
							<xsl:attribute name="id"><xsl:value-of select="virtualization:arch/@id" /></xsl:attribute>
							<xsl:value-of select="virtualization:arch" />
						</xsl:element>
						<xsl:element name="virtualization:os">
							<xsl:attribute name="id"><xsl:value-of select="virtualization:os/@id" /></xsl:attribute>
							<xsl:attribute name="version"><xsl:value-of select="virtualization:osversion" /></xsl:attribute>
							<xsl:value-of select="virtualization:os" />
						</xsl:element>
						<xsl:choose>
							<xsl:when test="virtualization:format">
								<xsl:element name="virtualization:format">
									<!-- <xsl:attribute name="id"><xsl:value-of select="virtualization:format/@id" /></xsl:attribute> -->
									<xsl:value-of select="virtualization:format" />
								</xsl:element>
							</xsl:when>
						</xsl:choose>
						<xsl:for-each select="virtualization:hypervisor">
							<xsl:element name="virtualization:hypervisor">
								<xsl:attribute name="id"><xsl:value-of select="@id" /></xsl:attribute>
								<xsl:value-of select="." />
							</xsl:element>
						</xsl:for-each>
						<xsl:element name="virtualization:identifier"><xsl:value-of select="virtualization:identifier"/></xsl:element>
						<xsl:element name="virtualization:size">
							<xsl:if test="virtualization:size/@protected" >
								<xsl:attribute name="protected"><xsl:value-of select="virtualization:size/@protected"/></xsl:attribute>
							</xsl:if>
							<xsl:value-of select="virtualization:size"/>
						</xsl:element>
						<xsl:element name="virtualization:url">
							<xsl:if test="virtualization:url/@protected" >
								<xsl:attribute name="protected"><xsl:value-of select="virtualization:url/@protected" /></xsl:attribute>
							</xsl:if>
							<xsl:value-of select="virtualization:url"/>
						</xsl:element>
						<xsl:element name="virtualization:checksum">
							<xsl:attribute name="hash"><xsl:value-of select="virtualization:checksum/@hash"/></xsl:attribute>
							<xsl:if test="virtualization:checksum/@protected" >
								<xsl:attribute name="protected"><xsl:value-of select="virtualization:checksum/@protected"/></xsl:attribute>
							</xsl:if>
							<xsl:value-of select="virtualization:checksum"/>							
						</xsl:element>
						<xsl:element name="virtualization:cores">
							<xsl:attribute name="minimum"><xsl:value-of select="virtualization:cores/@minimum"/></xsl:attribute>
							<xsl:attribute name="recommended"><xsl:value-of select="virtualization:cores/@recommended"/></xsl:attribute>
						</xsl:element>
						<xsl:if test="virtualization:network_traffic" >
							<xsl:for-each select="virtualization:network_traffic">
								<xsl:element name="virtualization:network_traffic">
									<xsl:attribute name="direction"><xsl:value-of select="@direction"/></xsl:attribute>
									<xsl:attribute name="protocols"><xsl:value-of select="@protocols"/></xsl:attribute>
									<xsl:attribute name="ip_range"><xsl:value-of select="@ip_range"/></xsl:attribute>
									<xsl:attribute name="port_range"><xsl:value-of select="@port_range"/></xsl:attribute>
								</xsl:element>
							</xsl:for-each>
						</xsl:if>
						<xsl:if test="virtualization:accelerators" >
							<xsl:element name="virtualization:accelerators">
								<xsl:attribute name="minimum"><xsl:value-of select="virtualization:accelerators/@minimum"/></xsl:attribute>
								<xsl:attribute name="recommended"><xsl:value-of select="virtualization:accelerators/@recommended"/></xsl:attribute>
								<xsl:attribute name="type"><xsl:value-of select="virtualization:accelerators/@type"/></xsl:attribute>
							</xsl:element>
						</xsl:if>
						<xsl:element name="virtualization:ram">
							<xsl:attribute name="minimum"><xsl:value-of select="virtualization:ram/@minimum"/></xsl:attribute>
							<xsl:attribute name="recommended"><xsl:value-of select="virtualization:ram/@recommended"/></xsl:attribute>
						</xsl:element>
						<xsl:element name="virtualization:ovf">
							<xsl:if test="virtualization:ovf/@protected" >
								<xsl:attribute name="protected"><xsl:value-of select="virtualization:ovf/@protected"/></xsl:attribute>
							</xsl:if>
							<xsl:attribute name="url"><xsl:value-of select="virtualization:ovf/@url"/></xsl:attribute>
						</xsl:element>
						<xsl:element name="virtualization:defaultaccess"><xsl:value-of select="virtualization:defaultaccess" /></xsl:element>
						<xsl:element name="virtualization:title"><xsl:value-of select="virtualization:releasetitle" /></xsl:element>
						<xsl:element name="virtualization:notes"><xsl:value-of select="virtualization:releasenotes" /></xsl:element>
						<xsl:element name="virtualization:description"><xsl:value-of select="virtualization:releasedescription" /></xsl:element>
						<xsl:element name="virtualization:addedon"><xsl:value-of select="virtualization:addedon" /></xsl:element>
						<xsl:element name="virtualization:addedby">
							<xsl:choose>
								<xsl:when test="person:addedby">
									<xsl:attribute name="id"><xsl:value-of select="person:addedby/@id" /></xsl:attribute>
									<xsl:attribute name="cname"><xsl:value-of select="person:addedby/@cname" /></xsl:attribute>
									<xsl:element name="person:firstname"><xsl:value-of select="person:addedby/person:firstname" /></xsl:element>
									<xsl:element name="person:lastname"><xsl:value-of select="person:addedby/person:lastname" /></xsl:element>
									<xsl:element name="person:institute"><xsl:value-of select="person:addedby/person:institute" /></xsl:element>
									<xsl:element name="person:role">
										<xsl:attribute name="id"><xsl:value-of select="person:addedby/person:role/@id" /></xsl:attribute>
										<xsl:attribute name="type"><xsl:value-of select="person:addedby/person:role/@type" /></xsl:attribute>
									</xsl:element>
								</xsl:when>
							</xsl:choose>
						</xsl:element>
						<xsl:if test="virtualization:lastupdatedon">
							<xsl:element name="virtualization:lastupdatedon"><xsl:value-of select="virtualization:lastupdatedon" /></xsl:element>
						</xsl:if>
						<xsl:if test="virtualization:lastupdatedby/@id">
							<xsl:element name="virtualization:lastupdatedby">
								<xsl:choose>
									<xsl:when test="person:lastupdatedby">
										<xsl:attribute name="id"><xsl:value-of select="person:lastupdatedby/@id" /></xsl:attribute>
										<xsl:attribute name="cname"><xsl:value-of select="person:lastupdatedby/@cname" /></xsl:attribute>
										<xsl:element name="person:firstname"><xsl:value-of select="person:lastupdatedby/person:firstname" /></xsl:element>
										<xsl:element name="person:lastname"><xsl:value-of select="person:lastupdatedby/person:lastname" /></xsl:element>
										<xsl:element name="person:institute"><xsl:value-of select="person:lastupdatedby/person:institute" /></xsl:element>
										<xsl:element name="person:role">
											<xsl:attribute name="id"><xsl:value-of select="person:lastupdatedby/person:role/@id" /></xsl:attribute>
											<xsl:attribute name="type"><xsl:value-of select="person:lastupdatedby/person:role/@type" /></xsl:attribute>
										</xsl:element>
									</xsl:when>
								</xsl:choose>
							</xsl:element>
						</xsl:if>
						<xsl:if test="virtualization:contextformat" >
							<xsl:for-each select="virtualization:contextformat">
								<xsl:element name="virtualization:contextformat">
									<xsl:value-of select="." />
									<xsl:attribute name="id"><xsl:value-of select="@id"/></xsl:attribute>
									<xsl:attribute name="name"><xsl:value-of select="@name"/></xsl:attribute>
									<xsl:attribute name="supported"><xsl:value-of select="@supported"/></xsl:attribute>
								</xsl:element>
							</xsl:for-each>
						</xsl:if>
						<xsl:if test="virtualization:contextscript/@id">
							<xsl:for-each select="virtualization:contextscript[not(application:application)]">
							<xsl:element name="virtualization:contextscript">
								<xsl:attribute name="id">
									<xsl:value-of select="@id"></xsl:value-of>
								</xsl:attribute>
								<xsl:attribute name="addedon">
									<xsl:value-of select="@addedon"></xsl:value-of>
								</xsl:attribute>
								<xsl:attribute name="relationid">
									<xsl:value-of select="@relationid"></xsl:value-of>
								</xsl:attribute>
								<xsl:if test="virtualization:name/text()">
									<xsl:element name="virtualization:name">
										<xsl:value-of select="virtualization:name" />
									</xsl:element>
								</xsl:if>
								<xsl:if test="application:application/@id">
									<xsl:element name="application:application">
										<xsl:attribute name="id">
											<xsl:value-of select="application:application/@id"/>
										</xsl:attribute>
									</xsl:element>
								</xsl:if>
								<xsl:element name="virtualization:url">
									<xsl:value-of select="virtualization:url" />
								</xsl:element>
								<xsl:if test="virtualization:title">
									<xsl:element name="virtualization:title">
										<xsl:value-of select="virtualization:title" />
									</xsl:element>
								</xsl:if>
								<xsl:if test="virtualization:description/text()">
									<xsl:element name="virtualization:description">
										<xsl:value-of select="virtualization:description" />
									</xsl:element>
								</xsl:if>
								<xsl:element name="virtualization:format">
									<xsl:attribute name="id">
										<xsl:value-of select="virtualization:format/@id"/>
									</xsl:attribute>
									<xsl:attribute name="name">
										<xsl:value-of select="virtualization:format/@name"/>
									</xsl:attribute>
								</xsl:element>
								<xsl:element name="virtualization:checksum">
									<xsl:attribute name="hashtype">
										<xsl:value-of select="virtualization:checksum/@hashtype"/>
									</xsl:attribute>
									<xsl:value-of select="virtualization:checksum"/>
								</xsl:element>
								<xsl:element name="virtualization:size">
									<xsl:value-of select="virtualization:size"/>
								</xsl:element>
								<xsl:element name="virtualization:addedby">
									<xsl:choose>
										<xsl:when test="person:person">
											<xsl:attribute name="id"><xsl:value-of select="person:person/@id" /></xsl:attribute>
											<xsl:attribute name="cname"><xsl:value-of select="person:person/@cname" /></xsl:attribute>
											<xsl:element name="person:firstname"><xsl:value-of select="person:person/person:firstname" /></xsl:element>
											<xsl:element name="person:lastname"><xsl:value-of select="person:person/person:lastname" /></xsl:element>
											<xsl:element name="person:gender"><xsl:value-of select="person:person/person:gender" /></xsl:element>
											<xsl:element name="person:institute"><xsl:value-of select="person:person/person:institute" /></xsl:element>
											<xsl:element name="person:role">
												<xsl:attribute name="id"><xsl:value-of select="person:person/person:role/@id" /></xsl:attribute>
												<xsl:attribute name="type"><xsl:value-of select="person:person/person:role/@type" /></xsl:attribute>
											</xsl:element>
											<xsl:element name="person:permalink"><xsl:value-of select="person:person/person:permalink" /></xsl:element>
											<xsl:element name="person:image"><xsl:value-of select="person:person/person:image" /></xsl:element>
										</xsl:when>
									</xsl:choose>
								</xsl:element>
							</xsl:element>
							</xsl:for-each>
						</xsl:if>
					</xsl:element>
				</xsl:if>
			</xsl:for-each>
		</xsl:element>
	</xsl:template>

</xsl:stylesheet>
