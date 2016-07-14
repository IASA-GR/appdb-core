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
Previous version: 8.12.29
New version: 8.12.30
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

ALTER VIEW vaviews RENAME TO __vaviews;
CREATE MATERIALIZED VIEW vaviews AS 
  SELECT * FROM __vaviews;
ALTER MATERIALIZED VIEW vaviews OWNER TO appdb;

CREATE INDEX idx_vaviews_vapplistid ON vaviews(vapplistid);
CREATE INDEX idx_vaviews_vappversionid ON vaviews(vappversionid);
CREATE INDEX idx_vaviews_vmiinstanceid ON vaviews(vmiinstanceid);
CREATE INDEX idx_vaviews_vmiinstance_version ON vaviews(vmiinstance_version);
CREATE INDEX idx_vaviews_checksum ON vaviews(checksum);
CREATE INDEX idx_vaviews_vmiinstance_guid ON vaviews(vmiinstance_guid);
CREATE INDEX idx_vaviews_vmiinstance_addedby ON vaviews(vmiinstance_addedby);
CREATE INDEX idx_vaviews_vmiflavourid ON vaviews(vmiflavourid);
CREATE INDEX idx_vaviews_vmiinstance_lastupdatedby ON vaviews(vmiinstance_lastupdatedby);
CREATE INDEX idx_vaviews_vmiinstance_enabled ON vaviews(vmiinstance_enabled);
CREATE INDEX idx_vaviews_vmiid ON vaviews(vmiid);
CREATE INDEX idx_vaviews_archid ON vaviews(archid);
CREATE INDEX idx_vaviews_osid ON vaviews(osid);
CREATE INDEX idx_vaviews_osversion ON vaviews(osversion);
CREATE INDEX idx_vaviews_format ON vaviews(format);
CREATE INDEX idx_vaviews_vmi_guid ON vaviews(vmi_guid);
CREATE INDEX idx_vaviews_va_id ON vaviews(va_id);
CREATE INDEX idx_vaviews_appid ON vaviews(appid);
CREATE INDEX idx_vaviews_va_guid ON vaviews(va_guid);
CREATE INDEX idx_vaviews_imglst_private ON vaviews(imglst_private);
CREATE INDEX idx_vaviews_va_version ON vaviews(va_version);
CREATE INDEX idx_vaviews_va_version_guid ON vaviews(va_version_guid);
CREATE INDEX idx_vaviews_va_version_published ON vaviews(va_version_published);
CREATE INDEX idx_vaviews_va_version_enabled ON vaviews(va_version_enabled);
CREATE INDEX idx_vaviews_va_version_archived ON vaviews(va_version_archived);
CREATE INDEX idx_vaviews_va_version_status ON vaviews(va_version_status);
CREATE INDEX idx_vaviews_submissionid ON vaviews(submissionid);
CREATE INDEX idx_vaviews_va_version_isexternal ON vaviews(va_version_isexternal);

CREATE OR REPLACE FUNCTION trfn_refresh_vaviews() 
RETURNS TRIGGER 
AS
$$
BEGIN
	REFRESH MATERIALIZED VIEW vaviews;
	IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN RETURN NEW; ELSE RETURN OLD; END IF;
END;
$$
LANGUAGE plpgsql VOLATILE;
ALTER FUNCTION trfn_refresh_vaviews() OWNER TO appdb;

DROP TRIGGER IF EXISTS tr_vapplists_99_refresh_vaviews ON vapplists;

CREATE TRIGGER tr_vapplists_99_refresh_vaviews
  AFTER INSERT OR UPDATE OR DELETE
  ON vapplists
  FOR EACH STATEMENT
  EXECUTE PROCEDURE trfn_refresh_permissions();

DROP TRIGGER IF EXISTS tr_vmiinstances_99_refresh_vaviews ON vmiinstances;

CREATE TRIGGER tr_vmiinstances_99_refresh_vaviews
  AFTER INSERT OR UPDATE OR DELETE
  ON vmiinstances
  FOR EACH STATEMENT
  EXECUTE PROCEDURE trfn_refresh_permissions();

DROP TRIGGER IF EXISTS tr_vmiflavours_99_refresh_vaviews ON vmiflavours;

CREATE TRIGGER tr_vmiflavours_99_refresh_vaviews
  AFTER INSERT OR UPDATE OR DELETE
  ON vmiflavours
  FOR EACH STATEMENT
  EXECUTE PROCEDURE trfn_refresh_permissions();

DROP TRIGGER IF EXISTS tr_vmis_99_refresh_vaviews ON vmis;

CREATE TRIGGER tr_vmis_99_refresh_vaviews
  AFTER INSERT OR UPDATE OR DELETE
  ON vmis
  FOR EACH STATEMENT
  EXECUTE PROCEDURE trfn_refresh_permissions();

DROP TRIGGER IF EXISTS tr_vapplications_99_refresh_vaviews ON vapplications;

CREATE TRIGGER tr_vapplications_99_refresh_vaviews
  AFTER INSERT OR UPDATE OR DELETE
  ON vapplications
  FOR EACH STATEMENT
  EXECUTE PROCEDURE trfn_refresh_permissions();

DROP TRIGGER IF EXISTS tr_vapp_versions_99_refresh_vaviews ON vapp_versions;

CREATE TRIGGER tr_vapp_versions_99_refresh_vaviews
  AFTER INSERT OR UPDATE OR DELETE
  ON vapp_versions
  FOR EACH STATEMENT
  EXECUTE PROCEDURE trfn_refresh_permissions();

DROP TRIGGER IF EXISTS tr_applications_99_refresh_vaviews ON applications;

CREATE TRIGGER tr_applications_99_refresh_vaviews
  AFTER INSERT OR UPDATE OR DELETE
  ON applications
  FOR EACH STATEMENT
  EXECUTE PROCEDURE trfn_refresh_permissions();

DROP TRIGGER IF EXISTS tr_app_order_hack_99_refresh_vaviews ON app_order_hack;

CREATE TRIGGER tr_app_order_hack_99_refresh_vaviews
  AFTER INSERT OR UPDATE OR DELETE
  ON app_order_hack
  FOR EACH STATEMENT
  EXECUTE PROCEDURE trfn_refresh_permissions();

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 12, 30, E'Convert vaviews into a materialized view'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=12 AND revision=30);

COMMIT;
