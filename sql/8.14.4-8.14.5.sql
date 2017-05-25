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
Previous version: 8.14.4
New version: 8.14.5
Author: wvkarag@kadath.priv.iasa.gr
*/

START TRANSACTION;

DROP TRIGGER tr_app_order_hack_99_refresh_vaviews ON app_order_hack;
DROP TRIGGER tr_applications_99_refresh_vaviews ON applications;
DROP TRIGGER tr_vapp_versions_99_refresh_vaviews ON vapp_versions;
DROP TRIGGER tr_vapplications_99_refresh_vaviews ON vapplications;
DROP TRIGGER tr_vapplists_99_refresh_vaviews ON vapplists;
DROP TRIGGER tr_vmiflavours_99_refresh_vaviews ON vmiflavours;
DROP TRIGGER tr_vmiinstances_99_refresh_vaviews ON vmiinstances;
DROP TRIGGER tr_vmis_99_refresh_vaviews ON vmis;

CREATE TRIGGER tr_app_order_hack_99_refresh_vaviews AFTER INSERT OR DELETE OR UPDATE ON app_order_hack FOR EACH STATEMENT EXECUTE PROCEDURE trfn_refresh_vaviews();
CREATE TRIGGER tr_applications_99_refresh_vaviews AFTER INSERT OR DELETE OR UPDATE ON applications FOR EACH STATEMENT EXECUTE PROCEDURE trfn_refresh_vaviews();
CREATE TRIGGER tr_vapp_versions_99_refresh_vaviews AFTER INSERT OR DELETE OR UPDATE ON vapp_versions FOR EACH STATEMENT EXECUTE PROCEDURE trfn_refresh_vaviews();
CREATE TRIGGER tr_vapplications_99_refresh_vaviews AFTER INSERT OR DELETE OR UPDATE ON vapplications FOR EACH STATEMENT EXECUTE PROCEDURE trfn_refresh_vaviews();
CREATE TRIGGER tr_vapplists_99_refresh_vaviews AFTER INSERT OR DELETE OR UPDATE ON vapplists FOR EACH STATEMENT EXECUTE PROCEDURE trfn_refresh_vaviews();
CREATE TRIGGER tr_vmiflavours_99_refresh_vaviews AFTER INSERT OR DELETE OR UPDATE ON vmiflavours FOR EACH STATEMENT EXECUTE PROCEDURE trfn_refresh_vaviews();
CREATE TRIGGER tr_vmiinstances_99_refresh_vaviews AFTER INSERT OR DELETE OR UPDATE ON vmiinstances FOR EACH STATEMENT EXECUTE PROCEDURE trfn_refresh_vaviews();
CREATE TRIGGER tr_vmis_99_refresh_vaviews AFTER INSERT OR DELETE OR UPDATE ON vmis FOR EACH STATEMENT EXECUTE PROCEDURE trfn_refresh_vaviews();

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 14, 5, E'Fix triggers which are supposed to refresh the vaviews materialized view'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=14 AND revision=5);

COMMIT;
