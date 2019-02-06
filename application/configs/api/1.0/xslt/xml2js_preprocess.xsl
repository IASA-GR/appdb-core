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

  
  <!-- Escape the backslash (\) before everything else. -->
  <xsl:template name="escape-string">
    <xsl:param name="s"/>
    
    <xsl:choose>
        <xsl:when test="contains($s,'\')">
            <xsl:call-template name="escape-quot-string">
                <xsl:with-param name="s" select="concat(substring-before($s,'\'),'\\')"/>
            </xsl:call-template>
            <xsl:call-template name="escape-bs-string">
                <xsl:with-param name="s" select="substring-after($s,'\')"/>
            </xsl:call-template>
            <xsl:call-template name="strip-whitespace">
                <xsl:with-param name="s" select="$s"/>
            </xsl:call-template>
        </xsl:when>
        <xsl:otherwise>
            <xsl:call-template name="escape-quot-string">
                <xsl:with-param name="s" select="$s"/>
            </xsl:call-template>
        </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
  
  <!-- Escape the double quote ("). -->
  <xsl:template name="escape-quot-string">
    <xsl:param name="s"/>
    <xsl:choose>
        <xsl:when test="contains($s,'&quot;')">
            <xsl:call-template name="encode-string">
                <xsl:with-param name="s" select="concat(substring-before($s,'&quot;'),'\&quot;')"/>
            </xsl:call-template>
            <xsl:call-template name="escape-quot-string">
                <xsl:with-param name="s" select="substring-after($s,'&quot;')"/>
            </xsl:call-template>
        </xsl:when>
        <xsl:otherwise>
            <xsl:call-template name="encode-string">
              <xsl:with-param name="s" select="$s"/>
            </xsl:call-template>
        </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
  
  <!-- Replace tab, line feed and/or carriage return by its matching escape code. Can't escape backslash
       or double quote here, because they don't replace characters (&#x0; becomes \t), but they prefix 
       characters (\ becomes \\). Besides, backslash should be seperate anyway, because it should be 
       processed first. This function can't do that. -->
    <xsl:template name="encode-string">
        <xsl:param name="s"/>
        <xsl:choose>
            <!-- tab -->
            <xsl:when test="contains($s,'&#x9;')">
                <xsl:call-template name="encode-string">
                    <xsl:with-param name="s" select="concat(substring-before($s,'&#x9;'),'\t',substring-after($s,'&#x9;'))"/>
                </xsl:call-template>
            </xsl:when>
            <!-- line feed -->
            <xsl:when test="contains($s,'&#xA;')">
                <xsl:call-template name="encode-string">
                    <xsl:with-param name="s" select="concat(substring-before($s,'&#xA;'),'\n',substring-after($s,'&#xA;'))"/>
                </xsl:call-template>
            </xsl:when>
            <!-- carriage return -->
            <xsl:when test="contains($s,'&#xD;')">
                <xsl:call-template name="encode-string">
                    <xsl:with-param name="s" select="concat(substring-before($s,'&#xD;'),'\r',substring-after($s,'&#xD;'))"/>
                </xsl:call-template>
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="$s" />
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template> 
    

  
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
                                                <xsl:call-template name="escape-string">
                                                    <xsl:with-param name="s" select="text()"/>
                                                </xsl:call-template>
                                            </xsl:element>
                                            <xsl:text></xsl:text>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <xsl:call-template name="escape-string">
                                                <xsl:with-param name="s" select="text()"/>
                                            </xsl:call-template>
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
                                    <xsl:call-template name="escape-string">
                                        <xsl:with-param name="s" select="text()"/>
                                    </xsl:call-template>
                                </xsl:element>
                                <xsl:text></xsl:text>
                            </xsl:when>
                            <xsl:otherwise>
                                <xsl:call-template name="escape-string">
                                    <xsl:with-param name="s" select="text()"/>
                                </xsl:call-template>
                            </xsl:otherwise>
                        </xsl:choose>
                    </xsl:if>
                    <xsl:apply-templates select="*"/>
                </xsl:element>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
</xsl:stylesheet>
