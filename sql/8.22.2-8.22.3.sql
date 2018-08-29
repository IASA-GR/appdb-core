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
Previous version: 8.22.2
New version: 8.22.3
Author: wvkarag@lovecraft.priv.iasa.gr
*/

DROP INDEX IF EXISTS idx_vmiflavours_hypervisors_gin;
DROP FUNCTION IF EXISTS public.hypervisor_name(e_hypervisors[]);
DROP FUNCTION IF EXISTS public.hypervisor_name(e_hypervisors);

CREATE OR REPLACE FUNCTION is_valid_actor_guid(_guid uuid)
  RETURNS boolean AS
'SELECT EXISTS (SELECT 1 FROM public.actors WHERE guid = $1)'
  LANGUAGE sql STABLE
  COST 100;
ALTER FUNCTION is_valid_actor_guid(uuid)
  OWNER TO appdb;

CREATE OR REPLACE FUNCTION parent(categories)
  RETURNS categories AS
$BODY$
	SELECT t.* FROM categories t
	WHERE t.id = $1.parentid
$BODY$
  LANGUAGE sql STABLE
  COST 100;
ALTER FUNCTION parent(categories)
  OWNER TO appdb;
  
CREATE OR REPLACE FUNCTION public.category_level(mid integer)
  RETURNS integer AS
$BODY$
DECLARE lvl INT;
DECLARE pid INT;
BEGIN
	IF mid IS NULL THEN
		RETURN 0;
	ELSE
		lvl := -1;
		pid := mid;	
		WHILE NOT pid IS NULL LOOP
			pid := (SELECT parentid FROM public.categories WHERE id = pid);
			lvl := lvl + 1;
		END LOOP;
		RETURN lvl;	
	END IF;
END;
$BODY$
  LANGUAGE plpgsql STABLE
  COST 100;
ALTER FUNCTION public.category_level(integer)
  OWNER TO appdb;

CREATE OR REPLACE FUNCTION level(categories)
  RETURNS integer AS
$BODY$
DECLARE lvl INT;
DECLARE pid INT;
DECLARE c categories;
BEGIN
	IF $1 IS NULL THEN
		RETURN 0;
	ELSE
		lvl := 0;
		c := $1;
		WHILE NOT (c).parentid IS NULL LOOP
			c := (c).parent;
			lvl := lvl + 1;
		END LOOP;
		RETURN lvl;
	END IF;
END;
$BODY$
  LANGUAGE plpgsql STABLE CALLED ON NULL INPUT
  COST 100;
ALTER FUNCTION level(categories)
  OWNER TO appdb;

CREATE OR REPLACE FUNCTION valid_relation(uuid, integer, uuid)
 RETURNS boolean
 LANGUAGE sql
 STABLE
AS $function$
SELECT CASE WHEN EXISTS (SELECT 1 FROM public.find_relationtype($1, (SELECT verbid FROM public.relationtypes WHERE id = $2), $3)) THEN TRUE ELSE FALSE END;
$function$;

CREATE OR REPLACE FUNCTION public.dataset_parentid_valid(mid integer, pid integer) RETURNS boolean
    LANGUAGE sql STABLE
    AS $_$
SELECT (
        -- only allow null parents where there are no derived dataset versions with a parentid
        ($2 IS NULL) AND NOT EXISTS (
                SELECT 1 FROM public.dataset_versions WHERE datasetid = $1 AND NOT parentid IS NULL
        )
) OR (
        (NOT $2 IS NULL) AND NOT EXISTS (
                SELECT 1 FROM public.datasets WHERE id = $2 AND NOT parentid IS NULL -- parent must be primary dataset
        ) AND NOT EXISTS ( -- parent must not invalidate existing dataset versions' parents
                SELECT 1 FROM public.dataset_versions WHERE datasetid = $1 AND NOT parentid IS NULL AND parentid NOT IN (
                        SELECT id FROM public.dataset_versions WHERE datasetid = $2
                )
        )
)
$_$;

CREATE OR REPLACE FUNCTION public.derived_dataset_discipline_valid(mid integer, did integer)
 RETURNS boolean
 LANGUAGE sql
 STABLE STRICT
AS $function$
        SELECT (
                        (SELECT parentid FROM public.datasets WHERE id = $1) IS NULL
        ) OR (
                $2 IN (
                        SELECT dataset_disciplines.disciplineid 
                        FROM public.datasets
                        INNER JOIN public.dataset_disciplines ON dataset_disciplines.datasetid = datasets.parentid
                        WHERE datasets.id = $1
                )
        )
$function$;


CREATE OR REPLACE FUNCTION public.app_to_json(mid integer)
  RETURNS text AS
$BODY$
SELECT '{"application": {' ||
	'"id": ' || to_json(applications.id) || ', ' ||
--	'"handle": ' || COALESCE(to_json(applications.pidhandle), 'null') || ', ' || 
	'"handle": ' || COALESCE(to_json(COALESCE((SELECT data FROM public.config WHERE var = 'handleprefix'), '') || '/' || pidhandles.suffix), 'null') || ', ' || 
	'"name": ' || to_json(applications.name::text) || ', ' || 
	'"cname": ' || to_json(applications.cname::text) || ', ' || 
	'"description": ' || COALESCE(to_json(applications.description::text), 'null') ||', ' || 
	'"rating": ' || COALESCE(to_json(applications.rating), 'null') || ', ' ||
	'"tool": '|| to_json(applications.tool) || ', ' || 
	'"discipline": [' || array_to_string(array_agg(DISTINCT '{' || 
		'"id": ' || to_json(disciplines.id) || ', ' || 
		'"name": ' || to_json(disciplines.name::text) || '}'),',') || '], ' || 
	'"category": [' || array_to_string(array_agg(DISTINCT '{' || 
		'"id": '|| to_json(categories.id) || ', ' || 
		'"name": ' || to_json(categories.name::text) || ', ' || 
		'"isPrimary": ' || to_json(appcategories.isprimary) || ', ' || 
		'"parentid": ' || COALESCE(to_json(categories.parentid::text), 'null') || '}'),',') || ']}}'
FROM public.applications
LEFT OUTER JOIN public.appdisciplines appdisc ON appdisc.appid = public.applications.id
LEFT OUTER JOIN public.disciplines ON public.disciplines.id = appdisc.disciplineid
LEFT OUTER JOIN public.appcategories ON public.appcategories.appid = public.applications.id
LEFT OUTER JOIN public.categories ON public.categories.id = public.appcategories.categoryid
LEFT OUTER JOIN public.pidhandles ON ((entrytype = 'software') OR (entrytype = 'vappliance')) AND 
        (entryid = $1) AND 
        ((result & 1)::BOOLEAN) AND -- marked as registered
        (NOT ((result & 8)::BOOLEAN)) -- not marked as to-be-deleted
WHERE public.applications.id = $1
GROUP BY public.applications.id, public.applications.name, public.applications.description, public.applications.rating, public.applications.tool, public.pidhandles.suffix;
$BODY$
  LANGUAGE sql STABLE
  COST 100;
ALTER FUNCTION public.app_to_json(integer)
  OWNER TO appdb;

SELECT id,app_to_json(id) FROM applications;

  
CREATE OR REPLACE FUNCTION public.replace_vo_dupe(public.vos) RETURNS SETOF public.vos
    LANGUAGE sql STABLE
    AS $_$
SELECT (t.x).* FROM (
        SELECT CASE 
        WHEN EXISTS (SELECT * FROM public.vo_dupes WHERE egiid = $1.id) THEN
                (SELECT (vos.*)::public.vos FROM public.vos WHERE id = (SELECT ebiid FROM public.vo_dupes WHERE egiid = $1.id))
        ELSE
                (SELECT (vos.*)::public.vos FROM public.vos WHERE id = $1.id)
        END AS x
) AS t
$_$;

CREATE OR REPLACE FUNCTION public.good_vmiinstanceid(public.va_provider_images) RETURNS integer
    LANGUAGE sql STABLE
    AS $_$
--      SELECT public.get_good_vmiinstanceid($1.vmiinstanceid)
        SELECT CASE WHEN goodid IS NULL THEN $1.vmiinstanceid ELSE goodid END FROM (
                        SELECT max(t1.id) as goodid FROM public.vmiinstances AS t1
                        INNER JOIN public.vmiinstances AS t2 ON t1.checksum = t2.checksum AND t1.guid = t2.guid AND t2.id = $1.vmiinstanceid
                        INNER JOIN public.vapplists ON t1.id = vapplists.vmiinstanceid
                        INNER JOIN public.vapp_versions ON vapplists.vappversionid = vapp_versions.id 
                        WHERE vapp_versions.published
        ) AS t
$_$;

CREATE OR REPLACE FUNCTION public.contextfmts(v public.vmiinstances) RETURNS text[]
    LANGUAGE sql STABLE
    AS $$
        SELECT array_agg(DISTINCT cf.name)
        FROM public.vmi_supported_context_fmt AS f 
        INNER JOIN public.contextformats AS cf ON cf.id = f.fmtid
        WHERE f.vmiinstanceid = v.id
$$;

CREATE OR REPLACE FUNCTION public.contextfmtsxml(v public.vmiinstances) RETURNS xml
    LANGUAGE sql STABLE
    AS $$
        SELECT array_to_string(array_agg(XMLELEMENT(name "virtualization:contextformat", XMLATTRIBUTES(f.fmtid AS id, cf.name AS name, true AS "supported"), cf.description)::text), '')::XML
        FROM public.vmi_supported_context_fmt AS f 
        INNER JOIN public.contextformats AS cf ON cf.id = f.fmtid
        WHERE f.vmiinstanceid = v.id
$$;

CREATE OR REPLACE FUNCTION public.find_relationtype(
    subject_guid uuid,
    verbid integer,
    target_guid uuid)
  RETURNS SETOF relationtypes AS
$BODY$
SELECT relationtypes.* FROM public.relationtypes 
INNER JOIN public.entityguids AS e1 ON e1.entitytype = relationtypes.subject_type
INNER JOIN public.entityguids AS e2 ON e2.entitytype = relationtypes.target_type
WHERE relationtypes.verbid = $2
AND e1.guid = $1
AND e2.guid = $3;
$BODY$
  LANGUAGE sql STABLE;
ALTER FUNCTION public.find_relationtype(uuid, integer, uuid)
  OWNER TO appdb;

CREATE OR REPLACE FUNCTION public.site_service_imageocciids_to_xml(
    providerid text,
    vmiinstanceid integer)
  RETURNS xml AS
$BODY$
  SELECT xmlagg(siteimageoccids.x) FROM (
	  SELECT XMLELEMENT(NAME "siteservice:occi",
	      XMLATTRIBUTES(va_provider_images.va_provider_image_id AS id, va_provider_images.id AS providerimageid, vowide_image_list_images.id AS voimageid , vowide_image_lists.state AS voimagestate),
	      public.vo_to_xml(vowide_image_lists.void)
	  ) as x
	  FROM public.va_providers
	  INNER JOIN public.va_provider_images ON va_provider_images.va_provider_id = va_providers.id
	  LEFT OUTER JOIN public.vowide_image_list_images ON vowide_image_list_images.id = va_provider_images.vowide_vmiinstanceid
	  LEFT OUTER JOIN public.vowide_image_lists ON vowide_image_lists.id = vowide_image_list_images.vowide_image_list_id
	  WHERE va_providers.id = $1 AND va_provider_images.vmiinstanceid = $2
  ) as siteimageoccids
  $BODY$
  LANGUAGE sql STABLE
  COST 100;
ALTER FUNCTION public.site_service_imageocciids_to_xml(text, integer)
  OWNER TO appdb;

CREATE OR REPLACE FUNCTION public.site_service_imageocciids_to_xml(
    providerid text,
    vmiinstanceid integer,
    _vowide_vmiinstanceid integer)
  RETURNS xml AS
$BODY$
  SELECT xmlagg(siteimageoccids.x) FROM (
	  SELECT XMLELEMENT(NAME "siteservice:occi",
	      XMLATTRIBUTES(va_provider_images.va_provider_image_id AS id, va_provider_images.id AS providerimageid, vowide_image_list_images.id AS voimageid , vowide_image_lists.state AS voimagestate),
	      public.vo_to_xml(vowide_image_lists.void)                                                                                                                                          
	  ) as x                                                                                                                                                                          
	  FROM public.va_providers                                                                                                                                                               
	  INNER JOIN public.va_provider_images ON va_provider_images.va_provider_id = va_providers.id                                                                                            
	  LEFT OUTER JOIN public.vowide_image_list_images ON vowide_image_list_images.id = va_provider_images.vowide_vmiinstanceid                                                               
	  LEFT OUTER JOIN public.vowide_image_lists ON vowide_image_lists.id = vowide_image_list_images.vowide_image_list_id                                                                     
	  WHERE va_providers.id = $1 AND va_provider_images.vmiinstanceid = $2 AND NOT $3 IS DISTINCT FROM vowide_vmiinstanceid                                                           
  ) as siteimageoccids                                                                                                                                                            
  $BODY$
  LANGUAGE sql STABLE
  COST 100;
ALTER FUNCTION public.site_service_imageocciids_to_xml(text, integer, integer)
  OWNER TO appdb;

CREATE OR REPLACE FUNCTION public.logoid(v public.vos)
  RETURNS text AS
$BODY$
DECLARE i INT[];
BEGIN
	SET SCHEMA 'public';
	SELECT ARRAY_AGG(DISTINCT disciplines.logoid ORDER BY disciplines.logoid) 
	FROM public.disciplines 
	INNER JOIN (SELECT UNNEST(disciplineid) AS did FROM public.vos WHERE id = v.id) AS x ON disciplines.id = x.did	AND 
	(ARRAY_LENGTH(v.disciplineid, 1) = 1 OR public.vodiscisleaf(x.did, v.id))
	INTO i;
	IF ARRAY_LENGTH(i, 1) > 1 THEN
		RETURN ARRAY_TO_STRING(i, '_');
	ELSIF ARRAY_LENGTH(i, 1) = 1 THEN
		RETURN i[1]::text;
	ELSE
		RETURN 998::text; -- OTHER
	END IF;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION public.logoid(public.vos)
  OWNER TO appdb;

-- Function: public.logoid(disciplines)

-- DROP FUNCTION public.logoid(disciplines);

CREATE OR REPLACE FUNCTION public.logoid(d public.disciplines)
  RETURNS integer AS
$BODY$
DECLARE i INT;
BEGIN
	i := d.id;
	WHILE i NOT IN (1001, 1002, 1007, 1024, 1032, 1046, 1077, 1105, 1117, 1185, 1252, 1285, 1351, 1378, 998, 1092, 1082, 1102) LOOP
		SELECT (disciplines.*) FROM public.disciplines WHERE id = d.parentid INTO d;
		IF d IS NULL THEN
			EXIT;
		END IF;
		i := d.id;
	END LOOP;
	IF i NOT IN (1001, 1002, 1007, 1024, 1032, 1046, 1077, 1105, 1117, 1185, 1252, 1285, 1351, 1378, 998, 1092, 1082, 1102) THEN
		i := 998; -- OTHER
	END IF;
	RETURN i;
END;
$BODY$
  LANGUAGE plpgsql STABLE
  COST 100;
ALTER FUNCTION public.logoid(public.disciplines)
  OWNER TO appdb;

CREATE OR REPLACE FUNCTION public.vo_to_xml(mid integer)
  RETURNS xml AS
'SELECT public.vo_to_xml(ARRAY[$1])'
  LANGUAGE sql STABLE
  COST 100;
ALTER FUNCTION public.vo_to_xml(integer)
  OWNER TO appdb;
  
CREATE OR REPLACE FUNCTION public.vapp_version_to_json(mid integer)
  RETURNS text AS
$BODY$
BEGIN
SET SCHEMA 'public';
RETURN (
SELECT '{"vapp_version": {' ||
  '"id": ' || to_json(vapp_versions.id) || ', ' ||  
  '"version": ' || to_json(vapp_versions.version) || ', ' ||  
  '"guid": ' || to_json(vapp_versions.guid) || ', ' ||  
  '"notes": ' || COALESCE(to_json(vapp_versions.notes::text), 'null') || ', ' ||  
  '"vappid": ' || to_json(vapp_versions.vappid) || ', ' ||  
  '"published": ' || to_json(vapp_versions.published) || ', ' ||  
  '"createdon": ' || to_json(vapp_versions.createdon) || ', ' ||  
  '"expireson": ' || COALESCE(to_json(vapp_versions.expireson), 'null') || ', ' ||  
  '"expiresin": ' || COALESCE(to_json(vapp_versions.expiresin), 'null') || ', ' ||  
  '"enabled": ' || to_json(vapp_versions.enabled) || ', ' ||  
  '"archived": ' || to_json(vapp_versions.archived) || ', ' ||  
  '"status": ' || to_json(vapp_versions.status) || ', ' ||  
  '"archivedon": ' || COALESCE(to_json(vapp_versions.archivedon), 'null') || ', ' ||
  REGEXP_REPLACE(
	REGEXP_REPLACE(
		app_to_json(
			(SELECT appid FROM public.vapplications WHERE id = vappid)
		), '^{', ''), '}$', '')::text || 
'}}'::text
FROM public.vapp_versions WHERE id = $1);
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION public.vapp_version_to_json(integer)
  OWNER TO appdb;

CREATE OR REPLACE FUNCTION public.discipline_to_xml(mid integer)
  RETURNS xml AS
$BODY$
SELECT CASE WHEN $1 IS NULL THEN (SELECT xmlelement(name "discipline:discipline", xmlattributes(
'http://www.w3.org/2001/XMLSchema-instance' AS "xmlns:xsi", 'true' as "xsi:nil", 0 as id))) ELSE (
SELECT xmlelement(name "discipline:discipline", xmlattributes(
	id as id, 
	parentid as parentid,
	CASE WHEN ord > 0 THEN ord ELSE NULL END AS "order"
), name) FROM public.disciplines WHERE id = $1) END;
$BODY$
  LANGUAGE sql STABLE
  COST 100;
ALTER FUNCTION public.discipline_to_xml(integer)
  OWNER TO appdb;

-- Function: public.discipline_to_xml(integer[])

-- DROP FUNCTION public.discipline_to_xml(integer[]);

CREATE OR REPLACE FUNCTION public.discipline_to_xml(mid integer[])
  RETURNS xml AS
$BODY$
  SELECT CASE WHEN $1 IS NULL THEN (SELECT public.discipline_to_xml(NULL::int)) ELSE
  (SELECT array_to_string(array_agg(public.discipline_to_xml(id) ORDER BY public.idx(mid,id)),'')::xml FROM public.disciplines WHERE id = ANY($1)
  ) END;
  $BODY$
  LANGUAGE sql STABLE
  COST 100;
ALTER FUNCTION public.discipline_to_xml(integer[])
  OWNER TO appdb;


-- Function: public.vo_to_xml(integer[])

-- DROP FUNCTION public.vo_to_xml(integer[]);

CREATE OR REPLACE FUNCTION public.vo_to_xml(mid integer[])
  RETURNS SETOF xml AS
$BODY$
BEGIN
        IF NOT EXISTS (SELECT * FROM public.vos WHERE id = ANY(mid)) THEN
                RETURN QUERY SELECT NULL::xml FROM public.vos WHERE FALSE;
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
                        public.discipline_to_xml(disciplineid),
                        v.description
                ) 
        FROM 
                public.normalized_vos AS v
                LEFT OUTER JOIN public.domains as d ON d.id = v.domainid
                WHERE v.id = ANY($1)                
        ORDER BY 
                public.idx(mid, v.id);
END;
$BODY$
  LANGUAGE plpgsql STABLE
  COST 100
  ROWS 1000;
ALTER FUNCTION public.vo_to_xml(integer[])
  OWNER TO appdb;

  -- Function: public.vmiinst_cntxscripts_to_xml(integer)

-- DROP FUNCTION public.vmiinst_cntxscripts_to_xml(integer);

CREATE OR REPLACE FUNCTION public.vmiinst_cntxscripts_to_xml(vmiinstance_id integer)
  RETURNS xml AS
$BODY$
BEGIN
RETURN (
SELECT xmlagg(
	XMLELEMENT(NAME "virtualization:contextscript", XMLATTRIBUTES(contextscripts.id AS id, vmiinstance_contextscripts.addedon AS addedon, vmiinstance_contextscripts.id AS relationid ),
	CASE WHEN NOT applications.id IS NULL THEN 
		XMLELEMENT(NAME "application:application", 
			XMLATTRIBUTES(applications.id, applications.cname, applications.guid, applications.deleted, applications.moderated),
			XMLELEMENT(NAME "application:name", applications.name))
	ELSE NULL END,
	XMLELEMENT(NAME "virtualization:url", contextscripts.url ),
	XMLELEMENT(NAME "virtualization:title", contextscripts.title ),
	XMLELEMENT(NAME "virtualization:description", contextscripts.description ),
	XMLELEMENT(NAME "virtualization:name", contextscripts.name ),
	XMLELEMENT(NAME "virtualization:format", XMLATTRIBUTES(contextscripts.formatid AS id, contextformats.name AS name)),
	XMLELEMENT(NAME "virtualization:checksum", XMLATTRIBUTES(contextscripts.checksumfunc::text AS hashtype), contextscripts.checksum ),
	XMLELEMENT(NAME "virtualization:size", contextscripts.size ),
	public.researcher_to_xml(vmiinstance_contextscripts.addedby, 'addedby'::text)
)) AS xml
FROM public.vmiinstances
INNER JOIN public.vmiinstance_contextscripts ON vmiinstance_contextscripts.vmiinstanceid = vmiinstances.id
INNER JOIN public.contextscripts ON contextscripts.id = vmiinstance_contextscripts.contextscriptid
INNER JOIN public.contextformats ON contextformats.id = contextscripts.formatid
LEFT OUTER JOIN public.context_script_assocs ON context_script_assocs.scriptid = contextscripts.id
LEFT OUTER JOIN public.contexts ON contexts.id = context_script_assocs.contextid
LEFT OUTER JOIN public.applications ON applications.id = contexts.appid
WHERE vmiinstances.id = vmiinstance_id);
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION public.vmiinst_cntxscripts_to_xml(integer)
  OWNER TO appdb;

  CREATE OR REPLACE FUNCTION public.researcher_to_xml(
    mid integer,
    mname text DEFAULT ''::text,
    mappid integer DEFAULT NULL::integer)
  RETURNS xml AS
'SELECT public.researcher_to_xml(ARRAY[$1], $2, $3);'
  LANGUAGE sql STABLE
  COST 100;
ALTER FUNCTION public.researcher_to_xml(integer, text, integer)
  OWNER TO appdb;

CREATE OR REPLACE FUNCTION public.researcher_to_xml(
    mid integer[],
    mname text DEFAULT ''::text,
    mappid integer DEFAULT NULL::integer)
  RETURNS SETOF xml AS
$BODY$
DECLARE myxml XML[];
DECLARE ppl RECORD;
DECLARE m_ngis TEXT;
DECLARE m_contacts TEXT;
DECLARE m_contactitems TEXT;
DECLARE i INT;
BEGIN
	IF mname = 'simpleindex' THEN
		RETURN QUERY SELECT xmlelement(name "person:person", xmlattributes(id, nodissemination, '' as metatype,
		CASE WHEN deleted THEN 'true' END as deleted), xmlelement(name "person:firstname", TRIM(firstname)), 
		xmlelement(name "person:lastname", TRIM(lastname))) FROM public.researchers WHERE id = ANY(mid) ORDER BY public.idx(mid, id);
	ELSE
		IF mid IS NULL OR (array_length(mid, 1) > 0 AND mid[1] IS NULL) THEN
			RETURN QUERY SELECT xmlelement(name "person:person", xmlattributes('http://www.w3.org/2001/XMLSchema-instance' AS "xmlns:xsi", 'true' as "xsi:nil", mname AS metatype),'');
		ELSE
			myxml := NULL::XML[];
			FOR ppl IN SELECT researchers.id,
			researchers.guid,
			researchers.firstname,
			researchers.lastname,
			researchers.nodissemination,
			researchers.deleted,
			researchers.institution AS institute,
			researchers.dateinclusion AS registeredon,
			researchers.lastupdated AS lastupdated,
			(array_agg(researcherimages.image))[1] AS image,
			researchers.cname,
			researchers.hitcount,
			countries AS country,
			array_agg(DISTINCT ngis) AS ngis,
			positiontypes AS "role",
			array_agg(DISTINCT contacts) AS contacts,
			--array_agg(DISTINCT contacttypes) AS contacttypes,
			CASE WHEN mname = 'contact' AND NOT mappid IS NULL THEN
				array_agg(DISTINCT appcontact_items)
			ELSE				
				NULL::public.appcontact_items[]
			END AS contactitems
			FROM public.researchers
			INNER JOIN public.countries ON countries.id = researchers.countryid
			LEFT OUTER JOIN public.ngis ON ngis.countryid = researchers.countryid
			INNER JOIN public.positiontypes ON positiontypes.id = researchers.positiontypeid
			LEFT OUTER JOIN public.contacts ON contacts.researcherid = researchers.id
			LEFT OUTER JOIN public.contacttypes ON contacttypes.id = contacts.contacttypeid
			LEFT OUTER JOIN public.appcontact_items ON appcontact_items.researcherid = researchers.id AND appcontact_items.appid = mappid
			LEFT OUTER JOIN public.researcherimages ON researcherimages.researcherid = researchers.id
			WHERE researchers.id = ANY(mid)
			GROUP BY researchers.id,
			countries,
			positiontypes
			ORDER BY public.idx(mid, researchers.id) LOOP
				m_ngis := '';
				IF ppl.ngis IS NULL OR (array_length(ppl.ngis, 1) > 0 AND ppl.ngis[1] IS NULL) THEN
					m_ngis := '<regional:provider xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:nil="true" id="0" />';
				ELSE
					FOR i IN 1..array_length(ppl.ngis, 1) LOOP
						m_ngis := m_ngis || xmlelement(
							name "regional:provider", 
							xmlattributes(
							(ppl.ngis[i]).id AS id, 
							(ppl.ngis[i]).countryid AS countryid,
							CASE WHEN (ppl.ngis[i]).countryid IS NULL THEN 'EIRO' ELSE 'NGI' END AS "type",
							(ppl.ngis[i]).european AS european
							), 
							xmlelement(name "regional:name", 
							(ppl.ngis[i]).name),
							xmlelement(name "regional:description", 
							(ppl.ngis[i]).description),
							xmlelement(name "regional:url", 
							(ppl.ngis[i]).url),
							CASE WHEN NOT (ppl.ngis[i]).logo IS NULL THEN
							xmlelement(name "regional:logo", 'http://'||(SELECT data FROM public.config WHERE var='ui-host')||'/ngi/getlogo?id='||(ppl.ngis[i]).id::TEXT)
							END
						)::TEXT;
					END LOOP;
				END IF;
				m_contacts := '';
				IF ppl.contacts IS NULL OR (array_length(ppl.contacts, 1) > 0 AND ppl.contacts[1] IS NULL) THEN
					m_contacts = '<person:contact xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:nil="true" id="0" />';
				ELSE
					FOR i IN 1..array_length(ppl.contacts, 1) LOOP
						m_contacts := m_contacts || xmlelement(name "person:contact",
							xmlattributes(
							(SELECT description FROM public.contacttypes WHERE id = (ppl.contacts[i]).contacttypeid) AS "type",
							(ppl.contacts[i]).id AS id,
							(ppl.contacts[i]).isprimary AS "primary"),
							(ppl.contacts[i]).data
						)::TEXT;
					END LOOP;
				END IF;
				m_contactitems := '';
				IF mname = 'contact' AND NOT mappid IS NULL THEN
					IF ppl.contactitems IS NULL OR (array_length(ppl.contactitems, 1) > 0 AND ppl.contactitems[1] IS NULL) THEN
						m_contactitems := '<application:contactItem xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:nil="true" id="0" />';
					ELSE
						FOR i IN 1..array_length(ppl.contactitems, 1) LOOP
							m_contactitems := m_contactitems || xmlelement(name "application:contactItem",
							xmlattributes(
							CASE WHEN (ppl.contactitems[i]).itemid IS NULL THEN 0 ELSE (ppl.contactitems[i]).itemid END AS id,
							(ppl.contactitems[i]).itemtype AS "type",
							(ppl.contactitems[i]).note AS "note"
							), (ppl.contactitems[i]).item
							)::TEXT;
						END LOOP;
					END IF;
				ELSE
					m_contactitems := NULL;
				END IF;
				myxml := array_append(myxml, (SELECT xmlelement(
				name "person:person",
				xmlattributes(ppl.id,
				ppl.guid,
				ppl.nodissemination,
				mname AS metatype,
				ppl.cname,
				ppl.hitcount AS hitcount,
				CASE WHEN ppl.deleted THEN 'true' END as deleted), E'\n\t',
				xmlelement(name "person:firstname", ppl.firstname), E'\n\t',
				xmlelement(name "person:lastname", ppl.lastname), E'\n\t',
				xmlelement(name "person:registeredOn", ppl.registeredon), E'\n\t',
				xmlelement(name "person:lastUpdated", ppl.lastupdated), E'\n\t',
				xmlelement(name "person:institute", ppl.institute), E'\n\t',
				xmlelement(name "regional:country", xmlattributes(
					(ppl.country).id AS id,
					(ppl.country).isocode AS isocode,
					(ppl.country).regionid AS regionid),
					(ppl.country).name
				), E'\n\t',
				m_ngis::XML, E'\n\t',
				xmlelement(name "person:role", 
				xmlattributes((ppl.role).id AS id,
				(ppl.role).description AS "type")), E'\n\t',
				m_contacts::XML, E'\n\t',
				xmlelement(name "person:permalink",'http://'||(SELECT data FROM public.config WHERE var='ui-host')||'/?p='||encode(CAST('/people/details?id='||ppl.id::text AS bytea),'base64')), E'\n\t',
				CASE WHEN NOT ppl.image IS NULL THEN
					xmlelement(name "person:image",'http://'||(SELECT data FROM public.config WHERE var='ui-host')||'/people/getimage?id='||ppl.id::text)
				END, E'\n\t',
				m_contactitems::XML
				)));
			END LOOP;
			RETURN QUERY SELECT UNNEST(myxml);
		END IF;
	END IF;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 1000;
ALTER FUNCTION public.researcher_to_xml(integer[], text, integer)
  OWNER TO appdb;
  
INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 22, 3, E'Minor function tweaks'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=22 AND revision=3);
