/*
 Copyright (C) 2015 IASA - Institute of Accelerating Systems and Applications (http://www.iasa.gr)

 Licensed under the Apache License, Version 2.0 (the "License");
 you may not use this file except in compliance with the License.
 You may obtain a copy of the License at
 
 http://www.apache.org/licenses/LICENSE-2.0

 Unless required by applicable law or agreed to in writing, software
 distributed under the License is distributed on an "AS IS" BASIS,
 WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 See the License for the specific language governing permissions and 
 limitations under the License.
*/

/* 
EGI AppDB incremental SQL script
Previous version: 8.12.36
New version: 8.12.37
Author: wvkarag@lovecraft.priv.iasa.gr
*/
START TRANSACTION;

CREATE OR REPLACE VIEW vo_dupes AS
SELECT v1.id AS egiid,
    v2.id AS ebiid
   FROM vos v1,
    vos v2
  WHERE lower(v1.name) = lower(v2.name) AND v1.sourceid = 1 AND v2.sourceid = 2 AND NOT v1.deleted AND NOT v2.deleted;

CREATE MATERIALIZED VIEW normalized_vos AS 
SELECT DISTINCT (vos.replace_vo_dupe).*, vos.logoid FROM vos;

CREATE INDEX "idx_normalized_vos_alias" ON normalized_vos USING btree (alias);
CREATE INDEX "idx_normalized_vos_alias_low" ON normalized_vos USING btree (lower(alias));
CREATE INDEX "idx_normalized_vos_deleted" ON normalized_vos USING btree (deleted);
CREATE INDEX "idx_normalized_vos_disciplineid" ON normalized_vos USING gin (disciplineid);
CREATE INDEX "idx_normalized_vos_domainid" ON normalized_vos USING btree (domainid);
CREATE INDEX "idx_normalized_vos_guid" ON normalized_vos USING btree (guid);
CREATE INDEX "idx_normalized_vos_lowercase_name" ON normalized_vos USING btree (lower(name));
CREATE INDEX "idx_normalized_vos_name" ON normalized_vos USING btree (name);
CREATE INDEX "idx_normalized_vos_sourceid" ON normalized_vos USING btree (sourceid);
CREATE INDEX "idx_normalized_vos_status" ON normalized_vos USING btree (status);
CREATE INDEX "idx_normalized_vos_status_low" ON normalized_vos USING btree (lower(status));
                        
ALTER FUNCTION discipline_to_xml(int) STABLE;
ALTER FUNCTION discipline_to_xml(int[]) STABLE;
ALTER FUNCTION vo_to_xml(int) STABLE;

CREATE OR REPLACE FUNCTION public.vo_to_xml(mid integer[])
 RETURNS SETOF xml
 LANGUAGE plpgsql
 STABLE
AS $function$
BEGIN
        IF NOT EXISTS (SELECT * FROM vos WHERE id = ANY(mid)) THEN
                RETURN QUERY SELECT NULL::xml FROM vos WHERE FALSE;
        END IF;
        RETURN QUERY 
        SELECT 
                xmlelement(
                        name "vo:vo", 
                        xmlattributes(
                                v.id as id, 
                                v.name as name, 
                                v.alias as alias,
                                v.status as status,
                                v.scope as scope,
                                v.validated as "validatedOn",
                                d."name" as discipline,
                                v.sourceid as sourceid,
                                v.logoid as logoid
                        ),
                        discipline_to_xml(disciplineid),
                        v.description
                ) 
        FROM 
                normalized_vos AS v
                LEFT OUTER JOIN domains as d ON d.id = v.domainid
                WHERE v.id = ANY($1)                
        ORDER BY 
                idx(mid, v.id);
END;
$function$;

DROP FUNCTION public.vo_to_xml_ext(mid integer[]);
CREATE OR REPLACE FUNCTION public.vo_to_xml_ext(mid integer[])
 RETURNS SETOF xml
 LANGUAGE sql
 STABLE
AS $function$
        SELECT x::xml FROM (SELECT DISTINCT vo_to_xml_ext(t.id)::text AS x FROM (SELECT UNNEST(mid) AS id) AS t) AS tt;
$function$;

DROP FUNCTION public.vo_to_xml_ext(mid integer);
CREATE OR REPLACE FUNCTION public.vo_to_xml_ext(mid integer)
 RETURNS xml
 LANGUAGE sql
 STABLE
AS $function$
                SELECT
                xmlelement(
                        name "vo:vo", 
                        xmlattributes(
                                v.id as id, 
                                v.name as name, 
                                v.alias as alias,
                                v.status as status,
                                v.scope as scope,
                                v.validated as "validatedOn",
                                d."name" as discipline,
                                v.sourceid as sourceid
                        ),
                        CASE WHEN TRIM(COALESCE(v.homepage, '')) <> '' THEN
                        xmlelement(
                                name "vo:url",
                                xmlattributes(
                                        'homepage' as "type"
                                ),
                                v.homepage
                        ) END,
                        CASE WHEN TRIM(COALESCE(v.enrollment, '')) <> '' THEN
                        xmlelement(
                                name "vo:url",
                                xmlattributes(
                                        'enrollment' as "type"
                                ),
                                v.enrollment
                        ) END,
                        CASE WHEN TRIM(COALESCE(v.aup, '')) <> '' THEN
                        xmlelement(
                                name "vo:aup",
                                v.aup
                        ) END,
                        xmlelement(
                                name "vo:description",
                                v.description
                        ),
                        CASE WHEN COUNT(res.*) > 0 THEN
                        array_to_string(array_agg(DISTINCT
                                xmlelement(
                                        name "vo:resource",
                                        xmlattributes(
                                                res.name as "type"
                                        ),
                                        res.value
                                )::text
                        ), '')::xml END,
                        CASE WHEN COUNT(con.*) > 0 THEN
                        array_to_string(array_agg(DISTINCT
                                xmlelement(
                                        name "vo:contact",
                                        xmlattributes(
                                                con.role AS "role",
                                                con.name AS "name",
                                                array_to_string(con.email, ', ') AS "email",
                                                CASE WHEN con.researcherid IS NULL THEN
                                                        'external'
                                                ELSE
                                                        'internal'
                                                END as "type",
                                                con.researcherid AS id,
                                                con.cname AS cname
                                        )/*,
                                        CASE WHEN NOT con.researcherid IS NULL THEN
                                                researcher_to_xml(con.researcherid::int)
                                        END*/
                                )::text
                        ), '')::xml END,
                        CASE WHEN COUNT(vomses.*) > 0 THEN
                        array_to_string(array_agg(DISTINCT
                                xmlelement(
                                        name "vo:voms",
                                        xmlattributes(
                                                vomses.hostname,
                                                vomses.https_port,
                                                vomses.vomses_port AS "voms_port",
                                                vomses.is_admin AS "admin"
                                        ),
                                        vomses.member_list_url
                                )::text
                         ), '')::xml END,
                        vowide_image_list_to_xml(v.id),
                        discipline_to_xml(disciplineid)
                )
        FROM 
                normalized_vos AS v
                LEFT OUTER JOIN domains as d ON d.id = v.domainid
                LEFT OUTER JOIN vo_resources AS res ON res.void = v.id
                LEFT OUTER JOIN vo_contacts AS con ON con.void = v.id
                LEFT OUTER JOIN vomses ON vomses.void = v.id
        WHERE v.id = $1
        GROUP BY
                v.id,
                v.name,
                v.description,
                v.scope,
                v.alias,
                v.validated,
                v.aup,
                v.homepage,
                v.enrollment,
                d.name,
                v.sourceid,
                v.status,
                v.disciplineid
        ;
$function$;
  
  CREATE OR REPLACE FUNCTION public.site_service_imageocciids_to_xml(providerid text, vmiinstanceid integer, _vowide_vmiinstanceid integer)                                       
   RETURNS xml
   LANGUAGE sql                                                                                                                                                                   
   STABLE
  AS $function$
  SELECT xmlagg(siteimageoccids.x) FROM (
  SELECT XMLELEMENT(NAME "siteservice:occi",
      XMLATTRIBUTES(va_provider_images.va_provider_image_id AS id, va_provider_images.id AS providerimageid, vowide_image_list_images.id AS voimageid , vowide_image_lists.state AS voimagestate),
      vo_to_xml(vowide_image_lists.void)                                                                                                                                          
      /*XMLELEMENT(                                                                                                                                                                 
          NAME "vo:vo",                                                                                                                                                           
          XMLATTRIBUTES(                                                                                                                                                          
              vowide_image_lists.void AS id,                                                                                                                                      
              -- (SELECT name FROM vos WHERE id = vowide_image_lists.void) AS name
              vos.name AS name
          )                                                                                                                                                                       
      )*/                                                                                                                                                                           
  ) as x                                                                                                                                                                          
  FROM va_providers                                                                                                                                                               
  INNER JOIN va_provider_images ON va_provider_images.va_provider_id = va_providers.id                                                                                            
  LEFT OUTER JOIN vowide_image_list_images ON vowide_image_list_images.id = va_provider_images.vowide_vmiinstanceid                                                               
  LEFT OUTER JOIN vowide_image_lists ON vowide_image_lists.id = vowide_image_list_images.vowide_image_list_id                                                                     
  -- LEFT OUTER JOIN normalized_vos AS vos ON vos.id = vowide_image_lists.void AND NOT vos.deleted
  WHERE va_providers.id = $1 AND va_provider_images.vmiinstanceid = $2 AND NOT $3 IS DISTINCT FROM vowide_vmiinstanceid                                                           
  ) as siteimageoccids                                                                                                                                                            
  $function$;         
 
  CREATE OR REPLACE FUNCTION public.site_service_imageocciids_to_xml(providerid text, vmiinstanceid integer)
   RETURNS xml 
   LANGUAGE sql 
   STABLE
  AS $function$SELECT xmlagg(siteimageoccids.x) FROM (
  SELECT XMLELEMENT(NAME "siteservice:occi",
      XMLATTRIBUTES(va_provider_images.va_provider_image_id AS id, va_provider_images.id AS providerimageid, vowide_image_list_images.id AS voimageid , vowide_image_lists.state AS voimagestate),
      vo_to_xml(vowide_image_lists.void)
      /*XMLELEMENT(
          NAME "vo:vo",
          XMLATTRIBUTES(
              vowide_image_lists.void AS id, 
              vos.name AS name
          )   
      )*/
  ) as x
  FROM va_providers
  INNER JOIN va_provider_images ON va_provider_images.va_provider_id = va_providers.id
  LEFT OUTER JOIN vowide_image_list_images ON vowide_image_list_images.id = va_provider_images.vowide_vmiinstanceid
  LEFT OUTER JOIN vowide_image_lists ON vowide_image_lists.id = vowide_image_list_images.vowide_image_list_id
  -- LEFT OUTER JOIN normalized_vos AS vos ON vos.id = vowide_image_lists.void AND NOT vos.deleted
  WHERE va_providers.id = $1 AND va_provider_images.vmiinstanceid = $2
  ) as siteimageoccids
  $function$;

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 12, 37, E''
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=12 AND revision=37);

COMMIT;
