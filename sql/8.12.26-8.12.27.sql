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
Previous version: 8.12.26
New version: 8.12.27
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;
CREATE INDEX idx_app_middlewares_comment5 ON app_middlewares(comment) WHERE middlewareid = 5;
CREATE INDEX idx_app_middlewares_comment5_lower ON app_middlewares(lower(comment)) WHERE middlewareid = 5;
CREATE INDEX idx_access_tokens_token ON access_tokens(token);
CREATE INDEX idx_access_token_netfilters_netfilter ON access_token_netfilters(netfilter);
CREATE INDEX idx_apikeys_key ON apikeys(key);
CREATE INDEX idx_apikeys_ownerid ON apikeys(ownerid);
CREATE INDEX idx_apikey_netfilters_netfilter ON apikey_netfilters(netfilter);
CREATE INDEX idx_app_api_log_appid ON app_api_log(appid);
CREATE INDEX idx_app_api_log_researcherid ON app_api_log(researcherid);
CREATE INDEX idx_app_archs_appid ON app_archs(appid);
CREATE INDEX idx_app_archs_archid ON app_archs(archid);
CREATE INDEX idx_app_cnames_value ON app_cnames(value);
CREATE INDEX idx_app_del_infos_appid ON app_del_infos(appid);
CREATE INDEX idx_app_del_infos_deletedby ON app_del_infos(deletedby);
CREATE INDEX idx_app_del_infos_roleid ON app_del_infos(roleid);
CREATE INDEX idx_app_mod_infos_appid ON app_mod_infos(appid);
CREATE INDEX idx_app_mod_infos_moddedby ON app_mod_infos(moddedby);
CREATE INDEX idx_app_licenses_appid ON app_licenses(appid);
CREATE INDEX idx_app_licenses_licid ON app_licenses(licenseid);
CREATE INDEX idx_app_oses_appid ON app_oses(appid);
CREATE INDEX idx_app_oses_osid ON app_oses(osid);
CREATE INDEX idx_app_oses_osversion ON app_oses(osversion);
CREATE INDEX idx_app_releases_appid ON app_releases(appid);
CREATE INDEX idx_app_releases_manager ON app_releases(manager);
CREATE INDEX idx_app_releases_series ON app_releases(series);
CREATE INDEX idx_app_releases_state ON app_releases(state);
CREATE INDEX idx_app_urls_description ON app_urls(description);
CREATE INDEX idx_app_validation_log_appid ON app_validation_log(appid);
CREATE INDEX idx_appbookmarks_appid ON appbookmarks(appid);
CREATE INDEX idx_appcategories_appid ON appcategories(appid);
CREATE INDEX idx_appcategories_catid ON appcategories(categoryid);
CREATE INDEX idx_appcontact_middlewares_appid ON appcontact_middlewares(appid);
CREATE INDEX idx_appcontact_middlewares_researcherid ON appcontact_middlewares(researcherid);
CREATE INDEX idx_appcontact_middlewares_appmwid ON appcontact_middlewares(appmiddlewareid);
CREATE INDEX idx_appcontact_vos_appid ON appcontact_vos(appid);
CREATE INDEX idx_appcontact_vos_researcherid ON appcontact_vos(researcherid);
CREATE INDEX idx_appcontact_vos_appmwid ON appcontact_vos(void);
CREATE INDEX idx_appcontact_otheritems_appid ON appcontact_otheritems(appid);
CREATE INDEX idx_appcontact_otheritems_researcherid ON appcontact_otheritems(researcherid);
CREATE INDEX idx_appcontact_otheritems_item ON appcontact_otheritems(item);
CREATE INDEX idx_appdisciplines_appid ON appdisciplines(appid);
CREATE INDEX idx_appdisciplines_discid ON appdisciplines(disciplineid);
CREATE INDEX idx_appdomains_appid ON appdomains(appid);
CREATE INDEX idx_appdomains_domainid ON appdomains(domainid);
CREATE INDEX idx_applications_catid ON applications USING GIN (categoryid);
CREATE INDEX idx_applications_discid ON applications USING GIN (disciplineid);
CREATE INDEX idx_applogos_appid ON applogos(appid);
CREATE INDEX idx_appproglangs_appid ON appproglangs(appid);
CREATE INDEX idx_appproglangs_proglangid ON appproglangs(proglangid);
CREATE INDEX idx_appratings_appid ON appratings(appid);
CREATE INDEX idx_appratings_submitterid ON appratings(submitterid);
CREATE INDEX idx_appratings_submittername ON appratings(submittername);
CREATE INDEX idx_appratings_moderated ON appratings(moderated);
CREATE INDEX idx_appratings_guid ON appratings(guid);
CREATE INDEX idx_appsubdomains_appid ON appsubdomains(appid);
CREATE INDEX idx_appsubdomains_subdomainid ON appsubdomains(subdomainid);
CREATE INDEX idx_categories_parentid ON categories(parentid);
CREATE INDEX idx_category_help_catid ON category_help(categoryid);
CREATE INDEX idx_category_help_type ON category_help(type);
CREATE INDEX idx_context_script_assocs_addedby ON context_script_assocs(addedby);
CREATE INDEX idx_contexts_appid ON contexts(appid);
CREATE INDEX idx_contexts_addedby ON contexts(addedby);
CREATE INDEX idx_contexts_guid ON contexts(guid);
CREATE INDEX idx_contexts_lastupdatedby ON contexts(lastupdatedby);
CREATE INDEX idx_contextscripts_formatid ON contextscripts(formatid);
CREATE INDEX idx_contextscripts_checksum ON contextscripts(checksum);
CREATE INDEX idx_contextscripts_checksumfunc ON contextscripts(checksumfunc);
CREATE INDEX idx_contextscripts_checksumfunc2 ON contextscripts(checksum,checksumfunc);
CREATE INDEX idx_contextscripts_guid ON contextscripts(guid);
CREATE INDEX idx_contextscripts_addedby ON contextscripts(addedby);
CREATE INDEX idx_contextscripts_lastupdatedby ON contextscripts(lastupdatedby);
CREATE INDEX idx_discipline_help_discid ON discipline_help(disciplineid);
CREATE INDEX idx_discipline_help_type ON discipline_help(type);
CREATE INDEX idx_disciplines_parentid ON disciplines(parentid);
CREATE INDEX idx_dissemination_composerid ON dissemination(composerid);
CREATE INDEX idx_dissemination_recipients ON dissemination USING GIN (recipients);
CREATE INDEX idx_extauthor_author ON extauthors(author);
CREATE INDEX idx_extauthor_author_lower ON extauthors(lower(author));
CREATE INDEX idx_fundings_parentid ON fundings(parentid);
CREATE INDEX idx_fundings_identifier ON fundings(identifier);
CREATE INDEX idx_licenses_name ON licenses(name);
CREATE INDEX idx_mail_subscriptions_subjecttype ON mail_subscriptions(subjecttype);
CREATE INDEX idx_mail_subscriptions_researcherid ON mail_subscriptions(researcherid);
CREATE INDEX idx_mail_subscriptions_flthash ON mail_subscriptions(flthash);
CREATE INDEX idx_mail_subscriptions_delivery ON mail_subscriptions(delivery);
CREATE INDEX idx_messages_isread ON messages(isread);
CREATE INDEX idx_news_fields ON news USING GIN (fields);
CREATE INDEX idx_organizations_guid ON organizations(guid);
CREATE INDEX idx_organizations_countryid ON organizations(countryid);
CREATE INDEX idx_organizations_countryid_src1 ON organizations(countryid) WHERE sourceid = 1;
CREATE INDEX idx_organizations_sourceid ON organizations(sourceid);
CREATE INDEX idx_organizations_deletedby ON organizations(deletedby);
CREATE INDEX idx_organizations_deleted ON organizations(deleted);
CREATE INDEX idx_oses_os_family_id ON oses(os_family_id);
CREATE INDEX idx_gocdb_va_providers_hostos ON gocdb.va_providers(host_os);
CREATE INDEX idx_pending_accounts_code ON pending_accounts(code);
CREATE INDEX idx_pending_accounts_researcherid ON pending_accounts(researcherid);
CREATE INDEX idx_pending_accounts_accountid ON pending_accounts(accountid);
CREATE INDEX idx_pending_accounts_resolved ON pending_accounts(resolved);
CREATE INDEX idx_ppl_api_log_pplid ON ppl_api_log(pplid);
CREATE INDEX idx_ppl_api_log_researcherid ON ppl_api_log(researcherid);
CREATE INDEX idx_ppl_del_infos_appid ON ppl_del_infos(researcherid);
CREATE INDEX idx_ppl_del_infos_deletedby ON ppl_del_infos(deletedby);
CREATE INDEX idx_ppl_del_infos_roleid ON ppl_del_infos(roleid);
CREATE INDEX idx_pplproglangs_researcherid ON pplproglangs(researcherid);
CREATE INDEX idx_pplproglangs_proglangid ON pplproglangs(researcherid);
CREATE INDEX idx_projects_guid ON projects(guid);
CREATE INDEX idx_projects_srcid ON projects(sourceid);
CREATE INDEX idx_projects_delby ON projects(deletedby);
CREATE INDEX idx_projects_deleted ON projects(deleted);
CREATE INDEX idx_gocdb_va_providers_hostos_low ON gocdb.va_providers(lower(host_os));
CREATE INDEX idx_user_accounts_account_researcherid_type_state1 ON user_accounts(researcherid, account_type) WHERE stateid = 1;
CREATE INDEX egiaai_vo_contacts_role_vo_puid ON egiaai.vo_contacts(role,vo,puid);
CREATE INDEX egiaai_vo_contacts_role ON egiaai.vo_contacts(role);
CREATE INDEX egiaai_vo_contacts_vo ON egiaai.vo_contacts(vo);
CREATE INDEX egiaai_vo_contacts_puid ON egiaai.vo_contacts(puid);
CREATE UNIQUE INDEX idx_permissions_actor_actionid_objnull ON permissions(actor,actionid) WHERE object IS NULL;
CREATE UNIQUE INDEX idx_permissions_actor_actionid_objnotnull ON permissions(actor,actionid) WHERE NOT object IS NULL;

CREATE INDEX idx_relations_reltypeid ON relations(reltypeid);
CREATE INDEX idx_relations_parentid ON relations(parentid);
CREATE INDEX idx_relations_addedby ON relations(addedby);
CREATE INDEX idx_relations_denyby ON relations(denyby);
CREATE INDEX idx_relations_hiddenby ON relations(hiddenby);
CREATE INDEX idx_relations_guid ON relations(guid);
CREATE INDEX idx_relationtypes_guid ON relationtypes(guid);
CREATE INDEX idx_relationtypes_verbid ON relationtypes(verbid);
CREATE INDEX idx_relationtypes_subject_type ON relationtypes(subject_type);
CREATE INDEX idx_relationtypes_actionid ON relationtypes(actionid);
CREATE INDEX idx_researcher_cnames_value ON researcher_cnames(value);
CREATE INDEX idx_researcherimages_researcherid ON researcherimages(researcherid);
CREATE INDEX idx_researchers_apps_appid ON researchers_apps(appid);
CREATE INDEX idx_researchers_apps_researcherid ON researchers_apps(researcherid);
CREATE INDEX idx_researchers_apps_guid ON researchers_apps(guid);
CREATE INDEX idx_researchers_gender ON researchers(gender);
CREATE INDEX idx_researchers_addedby ON researchers(addedby);
CREATE INDEX idx_user_accounts_researcherid ON user_accounts(researcherid);
CREATE INDEX idx_user_accounts_account_type ON user_accounts(account_type);
CREATE INDEX idx_user_credentials_researcherid ON user_credentials(researcherid);
CREATE INDEX idx_user_credentials_researcherid_sessionid_token ON user_credentials(researcherid, sessionid, token);
CREATE INDEX idx_userrequests_typeid ON userrequests(typeid);
CREATE INDEX idx_userrequests_userid ON userrequests(userguid);
CREATE INDEX idx_userrequests_targetguid ON userrequests(targetguid);
CREATE INDEX idx_userrequests_actorguid ON userrequests(actorguid);
CREATE INDEX idx_userrequests_stateid ON userrequests(stateid);
CREATE INDEX idx_va_provider_endpoints_va_provider_id ON va_provider_endpoints(va_provider_id);
CREATE INDEX idx_va_provider_images_va_provider_id ON va_provider_images(va_provider_id);
CREATE INDEX idx_va_provider_templates_va_provider_id ON va_provider_templates(va_provider_id);
CREATE INDEX idx_vapp_versions_guid ON vapp_versions(guid);
CREATE INDEX idx_vapp_versions_vappid ON vapp_versions(vappid,published,enabled,archived,status);
CREATE INDEX idx_vapp_versions_vappid2 ON vapp_versions(vappid,published,enabled,archived,status) WHERE published;
CREATE INDEX idx_vapp_versions_vappid3 ON vapp_versions(vappid,published,enabled,archived,status) WHERE published AND enabled AND NOT archived;
CREATE INDEX idx_vapp_versions_vappid4 ON vapp_versions(vappid,published,enabled,archived,status) WHERE published AND enabled AND NOT archived AND status = 'verified';
CREATE INDEX idx_vapp_versions_published ON vapp_versions(published);
CREATE INDEX idx_vapp_versions_enabled ON vapp_versions(enabled);
CREATE INDEX idx_vapp_versions_archived ON vapp_versions(archived);
CREATE INDEX idx_vapp_versions_status ON vapp_versions(status);
CREATE INDEX idx_vapp_versions_expireson ON vapp_versions(expireson);
CREATE INDEX idx_vapplications_name ON vapplications(name);
CREATE INDEX idx_vapplications_guid ON vapplications(guid);
CREATE INDEX idx_vmiflavours_vmiid ON vmiflavours(vmiid);
CREATE INDEX idx_vmiflavours_hypervisors ON vmiflavours (hypervisors);

CREATE OR REPLACE FUNCTION hypervisor_name(e_hypervisors) RETURNS TEXT AS
$$
 SELECT e.enumlabel::text AS name
   FROM pg_enum e
     JOIN pg_type t ON e.enumtypid = t.oid
  WHERE t.typname = 'e_hypervisors'::name;
$$ LANGUAGE SQL IMMUTABLE;
ALTER FUNCTION hypervisor_name(e_hypervisors) OWNER TO appdb;

CREATE OR REPLACE FUNCTION hypervisor_name(e_hypervisors[]) RETURNS TEXT[] AS
$$
 SELECT ARRAY_AGG(hypervisor_name(x)) FROM UNNEST($1) AS x;
$$ LANGUAGE SQL IMMUTABLE;
ALTER FUNCTION hypervisor_name(e_hypervisors[]) OWNER TO appdb;

CREATE INDEX idx_vmiflavours_hypervisors_gin ON vmiflavours USING GIN (hypervisor_name(hypervisors));
CREATE INDEX idx_vmiflavours_archid ON vmiflavours(archid);
CREATE INDEX idx_vmiflavours_osid ON vmiflavours(osid);
CREATE INDEX idx_vmiflavours_format ON vmiflavours(format);
CREATE INDEX idx_vmiflavours_osversion ON vmiflavours(osversion);
CREATE INDEX idx_vmiinstance_contextscripts ON vmiinstance_contextscripts(addedby);
CREATE INDEX idx_vmiinstances_addedby ON vmiinstances(addedby);
CREATE INDEX idx_vmiinstances_enabled ON vmiinstances(enabled);
CREATE INDEX idx_vmiinstances_lastupdatedby ON vmiinstances(lastupdatedby);
CREATE INDEX idx_vmiinstances_flavorid ON vmiinstances(vmiflavourid);
CREATE INDEX idx_vmis_name ON vmis(name);
CREATE INDEX idx_vmis_name_low ON vmis(lower(name));
CREATE INDEX idx_vmis_guid ON vmis(guid);
CREATE INDEX idx_vmis_vappid ON vmis(vappid);
CREATE INDEX idx_vmis_groupname ON vmis(groupname);
CREATE INDEX idx_vo_middlewares_void ON vo_middlewares(void);
CREATE INDEX idx_vo_middlewares_mwid ON vo_middlewares(middlewareid);
CREATE INDEX idx_vomses_void ON vomses(void);
CREATE INDEX idx_vos_deleted ON vos(deleted);
CREATE INDEX idx_vos_alias ON vos(alias);
CREATE INDEX idx_vos_alias_low ON vos(lower(alias));
CREATE INDEX idx_vos_guid ON vos(guid);
CREATE INDEX idx_vos_domainid ON vos(domainid);
CREATE INDEX idx_vos_sourceid ON vos(sourceid);
CREATE INDEX idx_vos_status ON vos(status);
CREATE INDEX idx_vos_status_low ON vos(lower(status));
CREATE INDEX idx_vos_disciplineid ON vos USING GIN (disciplineid);
CREATE INDEX idx_vowide_image_list_images_state ON vowide_image_list_images(state);
CREATE INDEX idx_vowide_image_lists_expires_on ON vowide_image_lists(expires_on);
CREATE INDEX idx_vowide_image_lists_alteredby ON vowide_image_lists(alteredby);
CREATE INDEX idx_vowide_image_lists_publishedby ON vowide_image_lists(publishedby);

ALTER VIEW va_providers RENAME TO __va_providers;
CREATE MATERIALIZED VIEW va_providers AS SELECT * FROM __va_providers;
CREATE UNIQUE INDEX idx_va_providers_id ON va_providers(id);
CREATE INDEX idx_va_providers_guid ON va_providers(guid);
CREATE INDEX idx_va_providers_sitename ON va_providers(sitename);
CREATE INDEX idx_va_providers_sitename_isprod ON va_providers(sitename) WHERE in_production IS TRUE;
CREATE INDEX idx_va_providers_sitename_in_production ON va_providers(sitename, in_production);

ALTER VIEW sites RENAME TO __sites;
CREATE MATERIALIZED VIEW sites AS SELECT * FROM __sites;
CREATE UNIQUE INDEX idx_sites_id ON sites(id);
CREATE INDEX idx_sites_name ON sites(name);
CREATE INDEX idx_sites_countryid ON sites(countryid);
CREATE INDEX idx_sites_regionid ON sites(regionid);
CREATE INDEX idx_sites_guid ON sites(guid);
CREATE INDEX idx_sites_deleted ON sites(deleted);

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 12, 27, E'Performance improvements'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=12 AND revision=27);

COMMIT;
