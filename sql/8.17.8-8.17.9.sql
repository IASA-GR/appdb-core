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
Previous version: 8.17.8
New version: 8.17.9
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

-- Function: public.refresh_sites(text, boolean)

-- DROP FUNCTION public.refresh_sites(text, boolean);

CREATE OR REPLACE FUNCTION public.refresh_sites(
    va_sync_scopes text DEFAULT 'FedCloud'::text,
    forced boolean DEFAULT false)
  RETURNS integer AS
$BODY$
-- DECLARE deltime TEXT;
DECLARE scopes TEXT[];
DECLARE doSites BOOL;
DECLARE doVap BOOL;
DECLARE doDT BOOL;
DECLARE doArgo BOOL;
BEGIN
	-- check if imported data has changed
    IF NOT forced THEN
        doSites := egiis.sitej_changed();
        doVap := egiis.vapj_changed() OR egiis.tvapj_changed();
        doDT := egiis.downtimes_changed();
        doArgo := egiis.argo_changed();
        IF NOT (doSites OR doVap OR doDT OR doArgo) THEN
            RETURN 0;
        END IF;
    ELSE
    	doSites := TRUE;
        doVap := TRUE;
        doDT := TRUE;
        doArgo := TRUE;
    END IF;

    scopes := ('{' || COALESCE(va_sync_scopes, '') || '}')::text[];
--  deltime := '1 minute';

    IF doSites THEN
        -- TRUNCATE TABLE gocdb.site_contacts;  -- OBSOLETED

        -- mark sites that weren't seen during the last json sync from the infosys service as deleted (key missing, or old timestamp)
        UPDATE gocdb.sites
        SET
            deleted = TRUE,
            deletedon = NOW(),
            deletedby = 'gocdb'
        WHERE pkey IN (
            SELECT pkey
                FROM egiis.sitej
                -- WHERE ((NOW() - lastseen)::INTERVAL > deltime::INTERVAL)
                WHERE lastseen < (SELECT MAX(lastseen) FROM egiis.sitej)
        ) OR pkey NOT IN (SELECT pkey FROM egiis.sitej);

        -- upsert json data into sites table
        INSERT INTO gocdb.sites
            (pkey, name, shortname, officialname, description, portalurl, homeurl, contactemail, contacttel, alarmemail, csirtemail, giisurl,
            countrycode, country, tier, subgrid, roc, prodinfrastructure, certstatus, timezone, latitude, longitude, domainname, siteip,
            deleted, deletedon, deletedby)
        SELECT
            g.pkey,
            ((g.j->>'info')::jsonb->>'SiteName')::text AS name,
            ((g.j->>'info')::jsonb->>'SiteShortName')::text AS shortname,
            ((g.j->>'info')::jsonb->>'SiteOfficialName')::text AS officialname,
            ((g.j->>'info')::jsonb->>'SiteDescription')::text AS description,
            ((g.j->>'info')::jsonb->>'SiteGocdbPortalUrl')::text AS portalurl,
            ((g.j->>'info')::jsonb->>'SiteHomeUrl')::text AS homeurl,
            NULL::text AS contactemail,
            NULL::text AS contacttel,
            NULL::text AS alarmemail,
            NULL::text AS csirtemail,
            ((g.j->>'info')::jsonb->>'SiteGiisUrl')::text AS giisurl,
            ((g.j->>'info')::jsonb->>'SiteCountryCode')::text AS countrycode,
            ((g.j->>'info')::jsonb->>'SiteCountry')::text AS country,
            ((g.j->>'info')::jsonb->>'SiteTier')::text AS tier,
            ((g.j->>'info')::jsonb->>'SiteSubgrid')::text AS subgrid,
            ((g.j->>'info')::jsonb->>'SiteRoc')::text AS roc,
            ((g.j->>'info')::jsonb->>'SiteProdInfrastructure')::text AS prodinfrastructure,
            ((g.j->>'info')::jsonb->>'SiteCertStatus')::text AS certstatus,
            ((g.j->>'info')::jsonb->>'SiteTimezone')::text AS timezone,
            ((g.j->>'info')::jsonb->>'SiteLatitude')::text AS latitude,
            ((g.j->>'info')::jsonb->>'SiteLongitude')::text AS longtitude,
            ((g.j->>'info')::jsonb->>'SiteDomainname')::text AS domainname,
            NULL::text AS siteip,
            FALSE, NULL, NULL
        FROM egiis.sitej AS g
        -- WHERE (NOW() - g.lastseen)::INTERVAL <= deltime::INTERVAL;
        WHERE g.lastseen = (SELECT MAX(lastseen) FROM egiis.sitej);
    END IF;

	-- ******************
	-- VA PROVIDERS
	-- ******************

    IF doVap THEN
        -- ALTER TABLE gocdb.va_providers
            -- DISABLE TRIGGER tr_gocdb_va_providers_99_refresh_permissions;

        -- remove entries that either
        -- 1) weren't seen during the last json sync from the infosys service (key missing, or old timestamp)
        -- 2) don't have at least one scope that matches the VA scopes given
        DELETE FROM gocdb.va_providers
        WHERE pkey IN (
            SELECT pkey
                FROM egiis.vapj
                -- WHERE ((NOW() - lastseen)::INTERVAL > deltime::INTERVAL)
                WHERE lastseen < (SELECT MAX(lastseen) FROM egiis.vapj)
        ) OR (
            pkey NOT IN (SELECT pkey FROM egiis.vapj)
        ) OR ( NOT (
            SELECT array_agg(s) && scopes
            FROM (SELECT jsonb_array_elements_text(((g.j->>'info')::jsonb->>'SiteEndpointScopes')::jsonb)::text AS s FROM egiis.vapj AS g WHERE g.pkey = va_providers.pkey) AS ts
        ));

        -- make sure any OS declared by the VA exists in our OSes table
        INSERT INTO oses (name)
            SELECT DISTINCT
                TRIM(((g.j->>'info')::jsonb->>'SiteEndpointHostOS')::text)
            FROM
                egiis.vapj AS g
            WHERE
                (((g.j->>'info')::jsonb->>'SiteEndpointHostOS')::text IS DISTINCT FROM NULL) AND
                (TRIM(((g.j->>'info')::jsonb->>'SiteEndpointHostOS')::text) <> '') AND
                (LOWER(TRIM(((g.j->>'info')::jsonb->>'SiteEndpointHostOS')::text)) NOT IN (
                    SELECT LOWER(name) FROM oses
                ));

        INSERT INTO gocdb.va_providers
        SELECT
            g.pkey,
            ((g.j->>'info')::jsonb->>'SiteEndpointHostname')::text AS hostname,
            ((g.j->>'info')::jsonb->>'SiteEndpointGocPortalUrl')::text AS gocdb_url,
            ((g.j->>'info')::jsonb->>'SiteEndpointHostDN')::text AS host_dn,
            ((g.j->>'info')::jsonb->>'SiteEndpointHostOS')::text AS host_os,
            ((g.j->>'info')::jsonb->>'SiteEndpointHostArch')::text AS host_arch,
            ((g.j->>'info')::jsonb->>'SiteEndpointBeta')::text::boolean AS beta,
            ((g.j->>'info')::jsonb->>'SiteEndpointServiceType')::text AS service_type,
            ((g.j->>'info')::jsonb->>'SiteEndpointHostIP')::text AS host_ip,
            ((g.j->>'info')::jsonb->>'SiteEndpointInProduction')::text::boolean AS in_production,
            ((g.j->>'info')::jsonb->>'SiteEndpointNodeMonitored')::text::boolean AS node_monitored,
            ((g.j->>'info')::jsonb->>'SiteName')::text AS sitename,
            ((g.j->>'info')::jsonb->>'SiteEndpointCountryName')::text AS country_name,
            ((g.j->>'info')::jsonb->>'SiteEndpointCountryCode')::text AS country_code,
            ((g.j->>'info')::jsonb->>'SiteEndpointRocName')::text AS roc_name,
            ((g.j->>'info')::jsonb->>'SiteEndpointUrl')::text AS url,
            ((t.j->>'info')::jsonb->>'GLUE2ComputingEndpointComputingServiceForeignKey')::text AS serviceid
        FROM
            egiis.vapj AS g
        LEFT OUTER JOIN egiis.tvapj AS t ON t.pkey = g.pkey
        WHERE
            -- ((NOW() - g.lastseen)::INTERVAL <= deltime::INTERVAL) AND
            (
                g.lastseen = (SELECT MAX(lastseen) FROM egiis.vapj)
            ) AND (
                SELECT array_agg(s) && scopes
                FROM (SELECT jsonb_array_elements_text(((g.j->>'info')::jsonb->>'SiteEndpointScopes')::jsonb)::text AS s) AS ts
            )
        ;
    END IF;

    IF doArgo THEN
    	PERFORM process_site_argo_status();
    END IF;
    IF doDT THEN
    	PERFORM process_site_downtimes();
    END IF;

	-- refresh all related materialized views
    IF doSites THEN
		REFRESH MATERIALIZED VIEW CONCURRENTLY sites;
    END IF;
    IF doVap OR doDT OR doArgo THEN
		REFRESH MATERIALIZED VIEW CONCURRENTLY va_providers;
		REFRESH MATERIALIZED VIEW CONCURRENTLY va_provider_endpoints;
		REFRESH MATERIALIZED VIEW CONCURRENTLY va_provider_images;
		REFRESH MATERIALIZED VIEW CONCURRENTLY va_provider_templates;
	END IF;
    IF doSites OR doVap THEN
		REFRESH MATERIALIZED VIEW CONCURRENTLY _actor_group_members;
    	REFRESH MATERIALIZED VIEW CONCURRENTLY _actor_group_members2;
    	REFRESH MATERIALIZED VIEW CONCURRENTLY permissions;
    END IF;

	IF doSites OR doVap THEN
		REFRESH MATERIALIZED VIEW CONCURRENTLY site_services_xml;
		REFRESH MATERIALIZED VIEW CONCURRENTLY site_service_images_xml;
    END IF;

	-- ALTER TABLE gocdb.va_providers
		-- ENABLE TRIGGER tr_gocdb_va_providers_99_refresh_permissions;
    RETURN (doSites::int<<0) | (doVap::int<<1) | (doDT::int<<2) | (doArgo::int<<3);
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION public.refresh_sites(text, boolean)
  OWNER TO appdb;


INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 17, 9, E'Do not truncate gocdb.site_contacts where refreshing sites'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=17 AND revision=9);

COMMIT;	
