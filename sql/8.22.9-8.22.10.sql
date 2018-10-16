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
Previous version: 8.22.9
New version: 8.22.10
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

CREATE INDEX idx_projects_addedby ON projects (addedby);
CREATE INDEX idx_projects_fundingid ON projects (fundingid);
CREATE INDEX idx_projects_contracttypeid ON projects (contracttypeid);
CREATE INDEX idx_projects_funderid ON projects (funderid);
CREATE INDEX idx_vmcaster_requests ON vmcaster_requests (appid);
CREATE INDEX idx_organizations_addedby ON organizations (addedby);
CREATE INDEX idx_researcher_cnames_researcherid ON researcher_cnames (researcherid);
CREATE INDEX idx_app_cnames_appid ON app_cnames(appid);
CREATE INDEX idx_sci_class_cprops_cpropid ON sci_class.cprops(cpropid);
CREATE INDEX idx_user_accounts_stateid ON user_accounts(stateid);
CREATE INDEX idx_sci_class_cvers_cpropid ON sci_class.cvers(cpropid);
CREATE INDEX idx_vmi_supported_context_fmt_fmtid ON vmi_supported_context_fmt(fmtid);
CREATE INDEX idx_faq_history_submitter ON faq_history(submitter);
CREATE INDEX idx___app_tags_researcherid ON __app_tags(researcherid);
CREATE INDEX idx_faqs_submitter ON faqs(submitter);
CREATE INDEX idx_datasets_parentid ON datasets(parentid);
CREATE INDEX idx_datasets_addedby ON datasets(addedby);
CREATE INDEX idx_apikeys_sysaccountid ON apikeys(sysaccountid);
CREATE INDEX idx_access_tokens_addedby ON access_tokens(addedby);
CREATE INDEX idx_dataset_locations_exchange_fmt ON dataset_locations(exchange_fmt);
CREATE INDEX idx_dataset_versions_addedy ON dataset_versions(addedby);
CREATE INDEX idx_dataset_versions_parentid ON dataset_versions(parentid);
CREATE INDEX idx_vmi_net_traffic_vmiinstanceid ON vmi_net_traffic(vmiinstanceid);
CREATE INDEX idx_dataset_locations_addedby ON dataset_locations(addedby);
CREATE INDEX idx_dataset_licenses_licenseid ON dataset_licenses(licenseid);
CREATE INDEX idx_dataset_disciplines_disciplineid ON dataset_disciplines(disciplineid);
CREATE INDEX idx___actor_group_members_groupid ON __actor_group_members(groupid);
CREATE INDEX idx_dataset_locations_connection_type ON dataset_locations(connection_type);
CREATE INDEX idx_dataset_disciplines_datasetid ON dataset_disciplines(datasetid);
CREATE INDEX idx_appcontact_vos_appid_void ON appcontact_vos(appid,void);
CREATE INDEX idx_dataset_location_organizations_organizationid ON dataset_location_organizations(organizationid);
CREATE INDEX idx_t_citation_appid ON t_citation(appid);
DROP INDEX idx_pplproglangs_proglangid;
CREATE INDEX idx_pplproglangs_proglangid ON pplproglangs(proglangid);
CREATE INDEX idx_abusereports_submitterid ON abusereports(submitterid);
CREATE INDEX idx_cache_appprivsxmlcache_appid ON cache.appprivsxmlcache(appid);
CREATE INDEX idx_dataset_location_sites_siteid ON dataset_location_sites(siteid);
CREATE INDEX idx_dataset_versions_datasetid ON dataset_versions(datasetid);
CREATE INDEX idx_app_api_log_researcherid ON app_api_log(researcherid);

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 22, 10, E'Add missing indices on foreign keys'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=22 AND revision=10);

COMMIT;	
