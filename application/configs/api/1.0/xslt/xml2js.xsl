<?xml version="1.0" encoding="UTF-8" ?>
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

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="text" encoding="utf-8"/>
    
    <!--
        If set to 1 it instructs the template "cast-type" to implicitly 
        cast values to numbers or booleans where valid, instead of just 
        double quoted strings. Defualt to 0.
    -->
    <xsl:param name="castImplicitTypes" select="0"/>
    
    <!--
        Returns the given parameter $val as a double quoted string, unless the
        the external parameter "castImplicitTypes" is set to 1, in which case it
        implicitly casts values to numbers and/or booleans when is valid.
    -->
    <xsl:template name="cast-type" >
        <xsl:param name="val" />
        <xsl:choose>
            <xsl:when test="$castImplicitTypes=1">
                <xsl:choose>
                    <xsl:when test="$val='true'">
                        <xsl:text>true</xsl:text>
                    </xsl:when>
                    <xsl:when test="$val='false'">
                        <xsl:text>false</xsl:text>
                    </xsl:when>
                    <xsl:when test="number($val) = number($val) and $val != '-'">
                        <xsl:value-of select="$val"/>
                    </xsl:when>
                    <xsl:otherwise><xsl:text>"</xsl:text><xsl:value-of select="$val"/><xsl:text>"</xsl:text></xsl:otherwise>
                </xsl:choose>
            </xsl:when>
            <xsl:otherwise><xsl:text>"</xsl:text><xsl:value-of select="$val"/><xsl:text>"</xsl:text></xsl:otherwise>
        </xsl:choose>
    </xsl:template>

    <!--
    Strips the given parameter $s from leading \n,\t and \r characters
    -->
    <xsl:template name="strip-ws">
        <xsl:param name="s" select="normalize-space(.)"/>
        <xsl:choose>
            <xsl:when test="starts-with($s, '\n')">
                <xsl:call-template name="strip-ws">
                    <xsl:with-param name="s" select="substring-after($s, '\n')"/>
                </xsl:call-template>
            </xsl:when>
            <xsl:when test="starts-with($s, '\t')">
                <xsl:call-template name="strip-ws">
                    <xsl:with-param name="s" select="substring-after($s, '\t')"/>
                </xsl:call-template>
            </xsl:when>
            <xsl:when test="starts-with($s, '\r')">
                <xsl:call-template name="strip-ws">
                    <xsl:with-param name="s" select="substring-after($s, '\r')"/>
                </xsl:call-template>
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="$s" />
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

    <xsl:template match="/*[node()]">
        <xsl:text>{</xsl:text>
        <xsl:apply-templates select="." mode="detect" />
        <xsl:text>}</xsl:text>
    </xsl:template>

    <xsl:template match="*" mode="detect">
        <xsl:choose>
            <xsl:when test="name(preceding-sibling::*[1]) = name(current()) and name(following-sibling::*[1]) != name(current())">
                    <xsl:apply-templates select="." mode="obj-content" />
                <xsl:text>]</xsl:text>
                <xsl:if test="count(following-sibling::*[name() != name(current())]) &gt; 0">, </xsl:if>
            </xsl:when>
            <xsl:when test="name(preceding-sibling::*[1]) = name(current())">
                    <xsl:apply-templates select="." mode="obj-content" />
                    <xsl:if test="name(following-sibling::*) = name(current())">, </xsl:if>
            </xsl:when>
            <xsl:when test="following-sibling::*[1][name() = name(current())]">
                <xsl:text>"</xsl:text><xsl:value-of select="name()"/><xsl:text>" : [</xsl:text>
                    <xsl:apply-templates select="." mode="obj-content" /><xsl:text>, </xsl:text>
            </xsl:when>
            <xsl:when test="count(./child::*) > 0 or count(@*) > 0">
                <xsl:text>"</xsl:text><xsl:value-of select="name()"/>" : <xsl:apply-templates select="." mode="obj-content" />
                <xsl:if test="count(following-sibling::*) &gt; 0">, </xsl:if>
            </xsl:when>
            <xsl:when test="count(./child::*) = 0">
                <xsl:text>"</xsl:text><xsl:value-of select="name()"/>" : <xsl:call-template name="cast-type"><xsl:with-param name="val" select="."/></xsl:call-template><!--"<xsl:apply-templates select="."/><xsl:text>"</xsl:text>-->
                <xsl:if test="count(following-sibling::*) &gt; 0">, </xsl:if>
            </xsl:when>
        </xsl:choose>
    </xsl:template>

    <xsl:template match="*" mode="obj-content">
        <xsl:text>{</xsl:text>
            <xsl:apply-templates select="@*" mode="attr" />
            <xsl:if test="count(@*) &gt; 0 and (count(child::*) &gt; 0 or text())">, </xsl:if>
            <xsl:apply-templates select="./*" mode="detect" />
            <xsl:if test="count(child::*) = 0 and text() and not(@*)">
                <xsl:text>"</xsl:text><xsl:value-of select="name()"/>" : <xsl:call-template name="cast-type"><xsl:with-param name="val" select="text()"/></xsl:call-template>
                <!--"<xsl:value-of select="text()"/><xsl:text>"</xsl:text>-->
            </xsl:if>
            <xsl:if test="count(child::*) = 0 and text() and @*">
                <!--<xsl:text>"text" : "</xsl:text><xsl:value-of select="text()"/><xsl:text>"</xsl:text>-->
                <xsl:text>"text" :</xsl:text><xsl:call-template name="cast-type"><xsl:with-param name="val" select="text()"/></xsl:call-template>
            </xsl:if>
        <xsl:text>}</xsl:text>
        <xsl:if test="position() &lt; last()">, </xsl:if>
    </xsl:template>

    <xsl:template match="@*" mode="attr">
        <xsl:text>"</xsl:text><xsl:value-of select="name()"/>" : <xsl:call-template name="cast-type"><xsl:with-param name="val" select="."/></xsl:call-template><!--"<xsl:value-of select="."/><xsl:text>"</xsl:text>-->
        <xsl:if test="position() &lt; last()">,</xsl:if>
    </xsl:template>

    <xsl:template match="node/@TEXT | text()" name="removeBreaks">
        <xsl:param name="pText" select="normalize-space(.)"/>
        <xsl:choose>
            <xsl:when test="not(contains($pText, '&#xA;'))">
                <xsl:call-template name="strip-ws">
                    <xsl:with-param name="s" select="$pText"/>
                </xsl:call-template>
            </xsl:when>
            <xsl:otherwise>
                 <xsl:value-of select="concat(substring-before($pText, '&#xD;&#xA;'), ' ')"/>
                <xsl:call-template name="removeBreaks">
                    <xsl:with-param name="pText" select="substring-after($pText, '&#xD;&#xA;')"/>
                </xsl:call-template>               
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
</xsl:stylesheet>

