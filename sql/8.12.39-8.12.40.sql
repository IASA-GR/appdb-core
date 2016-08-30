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
Previous version: 8.12.39
New version: 8.12.40
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;
-- text ops, for properly supporting non-C locale
SET search_path = egiaai, pg_catalog;
CREATE INDEX egiaai_vo_contacts_puid_textops ON vo_contacts USING btree (puid text_pattern_ops);
CREATE INDEX egiaai_vo_contacts_role_textops ON vo_contacts USING btree (role text_pattern_ops);
CREATE INDEX egiaai_vo_contacts_role_vo_puid_textops ON vo_contacts USING btree (role, vo, puid text_pattern_ops);
CREATE INDEX egiaai_vo_contacts_vo_textops ON vo_contacts USING btree (vo text_pattern_ops);
SET search_path = egiops, pg_catalog;
CREATE INDEX vo_contacts_dn_idx_textops ON vo_contacts USING btree (dn text_pattern_ops);
CREATE INDEX vo_members_certdn_idx_textops ON vo_members USING btree (certdn text_pattern_ops);
SET search_path = gocdb, pg_catalog;
CREATE INDEX idx_gocdb_va_providers_hostos_low_textops ON va_providers USING btree (lower(host_os) text_pattern_ops);
CREATE INDEX idx_gocdb_va_providers_hostos_textops ON va_providers USING btree (host_os text_pattern_ops);
CREATE INDEX idx_site_contacts_dn_textops ON site_contacts USING btree (dn text_pattern_ops);
CREATE INDEX idx_site_contacts_role_textops ON site_contacts USING btree (role text_pattern_ops);
CREATE INDEX idx_site_contacts_site_pkey_textops ON site_contacts USING btree (site_pkey text_pattern_ops);
SET search_path = harvest, pg_catalog;
CREATE INDEX harvest_records_additional_name_idx_textops ON records_additional USING btree (name text_pattern_ops);
CREATE INDEX raw_fields_name_idx_textops ON raw_fields USING btree (name text_pattern_ops);
CREATE INDEX records_appdb_identifier_idx_textops ON records USING btree (appdb_identifier text_pattern_ops);
SET search_path = public, pg_catalog;
CREATE INDEX app_middlewares_comment_idx_textops ON app_middlewares USING btree (comment text_pattern_ops);
CREATE INDEX app_middlewares_comment_low_idx_textops ON app_middlewares USING btree (lower(comment) text_pattern_ops);
CREATE INDEX applications_abstract_idx_textops ON applications USING btree (abstract text_pattern_ops);
CREATE INDEX applications_abstract_low_idx_textops ON applications USING btree (lower(abstract) text_pattern_ops);
CREATE INDEX applications_cname_idx_textops ON applications USING btree (cname text_pattern_ops);
CREATE INDEX applications_cname_low_idx_textops ON applications USING btree (lower(cname) text_pattern_ops);
CREATE INDEX applications_description_idx_textops ON applications USING btree (description text_pattern_ops);
CREATE INDEX applications_description_low_idx_textops ON applications USING btree (lower(description) text_pattern_ops);
CREATE INDEX applications_name_idx_textops ON applications USING btree (name text_pattern_ops);
CREATE INDEX applications_name_low_idx_textops ON applications USING btree (lower(name) text_pattern_ops);
CREATE INDEX archs_name_idx_textops ON archs USING btree (name text_pattern_ops);
CREATE INDEX archs_name_low_idx_textops ON archs USING btree (lower(name) text_pattern_ops);
CREATE INDEX categories_name_idx_textops ON categories USING btree (name text_pattern_ops);
CREATE INDEX categories_name_low_idx_textops ON categories USING btree (lower(name) text_pattern_ops);
CREATE INDEX contacts_data_idx_textops ON contacts USING btree (data text_pattern_ops);
CREATE INDEX contacts_data_low_idx_textops ON contacts USING btree (lower(data) text_pattern_ops);
CREATE INDEX contacttypes_description_idx_textops ON contacttypes USING btree (description text_pattern_ops);
CREATE INDEX contacttypes_description_low_idx_textops ON contacttypes USING btree (lower(description) text_pattern_ops);
CREATE INDEX countries_isocode_idx_textops ON countries USING btree (isocode text_pattern_ops);
CREATE INDEX countries_isocode_low_idx_textops ON countries USING btree (lower(isocode) text_pattern_ops);
CREATE INDEX countries_name_idx_textops ON countries USING btree (name text_pattern_ops);
CREATE INDEX countries_name_low_idx_textops ON countries USING btree (lower(name) text_pattern_ops);
CREATE INDEX disciplines_name_idx_textops ON disciplines USING btree (name text_pattern_ops);
CREATE INDEX disciplines_name_low_idx_textops ON disciplines USING btree (lower(name) text_pattern_ops);
CREATE INDEX idx___actor_group_members_payload_textops ON __actor_group_members USING btree (payload text_pattern_ops);
CREATE INDEX idx__actor_group_members_payload_textops ON _actor_group_members USING btree (payload text_pattern_ops);
CREATE INDEX idx_access_token_netfilters_netfilter_textops ON access_token_netfilters USING btree (netfilter text_pattern_ops);
CREATE INDEX idx_apikey_netfilters_netfilter_textops ON apikey_netfilters USING btree (netfilter text_pattern_ops);
CREATE INDEX idx_app_cnames_value_textops ON app_cnames USING btree (value text_pattern_ops);
CREATE INDEX idx_app_oses_osversion_textops ON app_oses USING btree (osversion text_pattern_ops);
CREATE INDEX idx_app_releases_series_textops ON app_releases USING btree (series text_pattern_ops);
CREATE INDEX idx_app_urls_description_textops ON app_urls USING btree (description text_pattern_ops);
CREATE INDEX idx_appcontact_otheritems_item_textops ON appcontact_otheritems USING btree (item text_pattern_ops);
CREATE INDEX idx_appratings_submittername_textops ON appratings USING btree (submittername text_pattern_ops);
CREATE INDEX idx_contextscripts_checksum_textops ON contextscripts USING btree (checksum text_pattern_ops);
CREATE INDEX idx_extauthor_author_lower_textops ON extauthors USING btree (lower(author) text_pattern_ops);
CREATE INDEX idx_extauthor_author_textops ON extauthors USING btree (author text_pattern_ops);
CREATE INDEX idx_fundings_identifier_textops ON fundings USING btree (identifier text_pattern_ops);
CREATE INDEX idx_hypervisors_name_low_textops ON hypervisors USING btree (lower((name)::text) text_pattern_ops);
CREATE INDEX idx_licenses_name_textops ON licenses USING btree (name text_pattern_ops);
CREATE INDEX idx_mail_subscriptions_subjecttype_textops ON mail_subscriptions USING btree (subjecttype text_pattern_ops);
CREATE INDEX idx_news_action_textops ON news USING btree (action text_pattern_ops);
CREATE INDEX idx_normalized_vos_alias_low_textops ON normalized_vos USING btree (lower(alias) text_pattern_ops);
CREATE INDEX idx_normalized_vos_alias_textops ON normalized_vos USING btree (alias text_pattern_ops);
CREATE INDEX idx_normalized_vos_lowercase_name_textops ON normalized_vos USING btree (lower(name) text_pattern_ops);
CREATE INDEX idx_normalized_vos_name_textops ON normalized_vos USING btree (name text_pattern_ops);
CREATE INDEX idx_normalized_vos_status_low_textops ON normalized_vos USING btree (lower(status) text_pattern_ops);
CREATE INDEX idx_normalized_vos_status_textops ON normalized_vos USING btree (status text_pattern_ops);
CREATE INDEX idx_pending_accounts_accountid_textops ON pending_accounts USING btree (accountid text_pattern_ops);
CREATE INDEX idx_researcher_cnames_value_textops ON researcher_cnames USING btree (value text_pattern_ops);
CREATE INDEX idx_researchers_gender_textops ON researchers USING btree (gender text_pattern_ops);
CREATE INDEX idx_site_services_xml_sitename_textops ON site_services_xml USING btree (sitename text_pattern_ops);
CREATE INDEX idx_sites_name_textops ON sites USING btree (name text_pattern_ops);
CREATE INDEX idx_user_credentials_researcherid_sessionid_token_textops ON user_credentials USING btree (researcherid, sessionid, token text_pattern_ops);
CREATE INDEX idx_va_provider_endpoints_va_provider_id_textops ON va_provider_endpoints USING btree (va_provider_id text_pattern_ops);
CREATE INDEX idx_va_provider_images_va_provider_id_textops ON va_provider_images USING btree (va_provider_id text_pattern_ops);
CREATE INDEX idx_va_provider_templates_va_provider_id_textops ON va_provider_templates USING btree (va_provider_id text_pattern_ops);
CREATE INDEX idx_va_providers_sitename_textops ON va_providers USING btree (sitename text_pattern_ops);
CREATE INDEX idx_vapp_versions_status_textops ON vapp_versions USING btree (status text_pattern_ops);
CREATE INDEX idx_vapp_versions_vappid2_textops ON vapp_versions USING btree (vappid, published, enabled, archived, status) WHERE published;
CREATE INDEX idx_vapp_versions_vappid_textops ON vapp_versions USING btree (vappid, published, enabled, archived, status text_pattern_ops);
CREATE INDEX idx_vapplications_name_textops ON vapplications USING btree (name text_pattern_ops);
CREATE INDEX idx_vaviews_checksum_textops ON vaviews USING btree (checksum text_pattern_ops);
CREATE INDEX idx_vaviews_format_textops ON vaviews USING btree (format text_pattern_ops);
CREATE INDEX idx_vaviews_osversion_textops ON vaviews USING btree (osversion text_pattern_ops);
CREATE INDEX idx_vaviews_va_version_status_textops ON vaviews USING btree (va_version_status text_pattern_ops);
CREATE INDEX idx_vaviews_va_version_textops ON vaviews USING btree (va_version text_pattern_ops);
CREATE INDEX idx_vaviews_vmiinstance_version_textops ON vaviews USING btree (vmiinstance_version text_pattern_ops);
CREATE INDEX idx_vmiflavours_format_textops ON vmiflavours USING btree (format text_pattern_ops);
CREATE INDEX idx_vmiflavours_osversion_textops ON vmiflavours USING btree (osversion text_pattern_ops);
CREATE INDEX idx_vmiinstances_checksum_textops ON vmiinstances USING btree (checksum text_pattern_ops);
CREATE INDEX idx_vmis_groupname_textops ON vmis USING btree (groupname text_pattern_ops);
CREATE INDEX idx_vmis_name_low_textops ON vmis USING btree (lower(name) text_pattern_ops);
CREATE INDEX idx_vmis_name_textops ON vmis USING btree (name text_pattern_ops);
CREATE INDEX idx_vos_alias_low_textops ON vos USING btree (lower(alias) text_pattern_ops);
CREATE INDEX idx_vos_alias_textops ON vos USING btree (alias text_pattern_ops);
CREATE INDEX idx_vos_lowercase_name_textops ON vos USING btree (lower(name) text_pattern_ops);
CREATE INDEX idx_vos_status_low_textops ON vos USING btree (lower(status) text_pattern_ops);
CREATE INDEX idx_vos_status_textops ON vos USING btree (status text_pattern_ops);
CREATE INDEX middlewares_name_idx_textops ON middlewares USING btree (name text_pattern_ops);
CREATE INDEX middlewares_name_low_idx_textops ON middlewares USING btree (lower(name) text_pattern_ops);
CREATE INDEX organizations_identifier_idx_textops ON organizations USING btree (identifier text_pattern_ops);
CREATE INDEX oses_name_idx_textops ON oses USING btree (name text_pattern_ops);
CREATE INDEX oses_name_low_idx_textops ON oses USING btree (lower(name) text_pattern_ops);
CREATE INDEX positiontypes_idx_textops ON positiontypes USING btree (description text_pattern_ops);
CREATE INDEX positiontypes_low_idx_textops ON positiontypes USING btree (lower(description) text_pattern_ops);
CREATE INDEX proglangs_name_idx_textops ON proglangs USING btree (name text_pattern_ops);
CREATE INDEX proglangs_name_low_idx_textops ON proglangs USING btree (lower(name) text_pattern_ops);
CREATE INDEX projects_identifier_idx_textops ON projects USING btree (identifier text_pattern_ops);
CREATE INDEX researchers_cname_idx_textops ON researchers USING btree (cname text_pattern_ops);
CREATE INDEX researchers_cname_low_idx_textops ON researchers USING btree (lower(cname) text_pattern_ops);
CREATE INDEX researchers_firstname_idx_textops ON researchers USING btree (firstname text_pattern_ops);
CREATE INDEX researchers_firstname_low_idx_textops ON researchers USING btree (lower(firstname) text_pattern_ops);
CREATE INDEX researchers_institution_idx_textops ON researchers USING btree (institution text_pattern_ops);
CREATE INDEX researchers_institution_low_idx_textops ON researchers USING btree (lower(institution) text_pattern_ops);
CREATE INDEX researchers_lastname_idx_textops ON researchers USING btree (lastname text_pattern_ops);
CREATE INDEX researchers_lastname_low_idx_textops ON researchers USING btree (lower(lastname) text_pattern_ops);
CREATE INDEX researchers_name_idx_textops ON researchers USING btree (name text_pattern_ops);
CREATE INDEX researchers_name_low_idx_textops ON researchers USING btree (lower(name) text_pattern_ops);
CREATE INDEX ssp_saml_logoutstore_nameid_textops ON ssp_saml_logoutstore USING btree (_authsource, _nameid text_pattern_ops);
CREATE INDEX statuses_name_idx_textops ON statuses USING btree (name text_pattern_ops);
CREATE INDEX statuses_name_low_idx_textops ON statuses USING btree (lower((name)::text) text_pattern_ops);
CREATE INDEX user_accounts_accountid_idx_textops ON user_accounts USING btree (accountid text_pattern_ops);
CREATE INDEX vos_lower_idx_textops ON vos USING btree (lower(name) text_pattern_ops);
CREATE INDEX vos_name_idx1_textops ON vos USING btree (name text_pattern_ops);
CREATE INDEX vos_name_idx_textops ON vos USING btree (name text_pattern_ops);
CREATE INDEX vos_name_low_idx_textops ON vos USING btree (lower(name) text_pattern_ops);
-- TRGM (for extending pattern matching beyong left-anchored only
CREATE INDEX egiaai_vo_contacts_puid_trgmops ON egiaai.vo_contacts USING gin (puid gin_trgm_ops);
CREATE INDEX egiaai_vo_contacts_role_trgmops ON egiaai.vo_contacts USING gin (role gin_trgm_ops);
CREATE INDEX egiaai_vo_contacts_role_vo_puid_trgmops ON egiaai.vo_contacts USING gin (role, vo, puid gin_trgm_ops);
CREATE INDEX egiaai_vo_contacts_vo_trgmops ON egiaai.vo_contacts USING gin (vo gin_trgm_ops);
CREATE INDEX vo_contacts_dn_idx_trgmops ON egiops.vo_contacts USING gin (dn gin_trgm_ops);
CREATE INDEX vo_members_certdn_idx_trgmops ON egiops.vo_members USING gin (certdn gin_trgm_ops);
CREATE INDEX idx_gocdb_va_providers_hostos_low_trgmops ON gocdb.va_providers USING gin (lower(host_os) gin_trgm_ops);
CREATE INDEX idx_gocdb_va_providers_hostos_trgmops ON gocdb.va_providers USING gin (host_os gin_trgm_ops);
CREATE INDEX idx_site_contacts_dn_trgmops ON gocdb.site_contacts USING gin (dn gin_trgm_ops);
CREATE INDEX idx_site_contacts_role_trgmops ON gocdb.site_contacts USING gin (role gin_trgm_ops);
CREATE INDEX idx_site_contacts_site_pkey_trgmops ON gocdb.site_contacts USING gin (site_pkey gin_trgm_ops);
CREATE INDEX harvest_records_additional_name_idx_trgmops ON harvest.records_additional USING gin (name gin_trgm_ops);
CREATE INDEX raw_fields_name_idx_trgmops ON harvest.raw_fields USING gin (name gin_trgm_ops);
CREATE INDEX records_appdb_identifier_idx_trgmops ON harvest.records USING gin (appdb_identifier gin_trgm_ops);
CREATE INDEX app_middlewares_comment_idx_trgmops ON app_middlewares USING gin (comment gin_trgm_ops);
CREATE INDEX app_middlewares_comment_low_idx_trgmops ON app_middlewares USING gin (lower(comment) gin_trgm_ops);
CREATE INDEX applications_abstract_idx_trgmops ON applications USING gin (abstract gin_trgm_ops);
CREATE INDEX applications_abstract_low_idx_trgmops ON applications USING gin (lower(abstract) gin_trgm_ops);
CREATE INDEX applications_cname_idx_trgmops ON applications USING gin (cname gin_trgm_ops);
CREATE INDEX applications_cname_low_idx_trgmops ON applications USING gin (lower(cname) gin_trgm_ops);
CREATE INDEX applications_description_idx_trgmops ON applications USING gin (description gin_trgm_ops);
CREATE INDEX applications_description_low_idx_trgmops ON applications USING gin (lower(description) gin_trgm_ops);
CREATE INDEX applications_name_idx_trgmops ON applications USING gin (name gin_trgm_ops);
CREATE INDEX applications_name_low_idx_trgmops ON applications USING gin (lower(name) gin_trgm_ops);
CREATE INDEX archs_name_idx_trgmops ON archs USING gin (name gin_trgm_ops);
CREATE INDEX archs_name_low_idx_trgmops ON archs USING gin (lower(name) gin_trgm_ops);
CREATE INDEX categories_name_idx_trgmops ON categories USING gin (name gin_trgm_ops);
CREATE INDEX categories_name_low_idx_trgmops ON categories USING gin (lower(name) gin_trgm_ops);
CREATE INDEX contacts_data_idx_trgmops ON contacts USING gin (data gin_trgm_ops);
CREATE INDEX contacts_data_low_idx_trgmops ON contacts USING gin (lower(data) gin_trgm_ops);
CREATE INDEX contacttypes_description_idx_trgmops ON contacttypes USING gin (description gin_trgm_ops);
CREATE INDEX contacttypes_description_low_idx_trgmops ON contacttypes USING gin (lower(description) gin_trgm_ops);
CREATE INDEX countries_isocode_idx_trgmops ON countries USING gin (isocode gin_trgm_ops);
CREATE INDEX countries_isocode_low_idx_trgmops ON countries USING gin (lower(isocode) gin_trgm_ops);
CREATE INDEX countries_name_idx_trgmops ON countries USING gin (name gin_trgm_ops);
CREATE INDEX countries_name_low_idx_trgmops ON countries USING gin (lower(name) gin_trgm_ops);
CREATE INDEX disciplines_name_idx_trgmops ON disciplines USING gin (name gin_trgm_ops);
CREATE INDEX disciplines_name_low_idx_trgmops ON disciplines USING gin (lower(name) gin_trgm_ops);
CREATE INDEX idx___actor_group_members_payload_trgmops ON __actor_group_members USING gin (payload gin_trgm_ops);
CREATE INDEX idx__actor_group_members_payload_trgmops ON _actor_group_members USING gin (payload gin_trgm_ops);
CREATE INDEX idx_access_token_netfilters_netfilter_trgmops ON access_token_netfilters USING gin (netfilter gin_trgm_ops);
CREATE INDEX idx_apikey_netfilters_netfilter_trgmops ON apikey_netfilters USING gin (netfilter gin_trgm_ops);
CREATE INDEX idx_app_cnames_value_trgmops ON app_cnames USING gin (value gin_trgm_ops);
CREATE INDEX idx_app_oses_osversion_trgmops ON app_oses USING gin (osversion gin_trgm_ops);
CREATE INDEX idx_app_releases_series_trgmops ON app_releases USING gin (series gin_trgm_ops);
CREATE INDEX idx_app_urls_description_trgmops ON app_urls USING gin (description gin_trgm_ops);
CREATE INDEX idx_appcontact_otheritems_item_trgmops ON appcontact_otheritems USING gin (item gin_trgm_ops);
CREATE INDEX idx_appratings_submittername_trgmops ON appratings USING gin (submittername gin_trgm_ops);
CREATE INDEX idx_contextscripts_checksum_trgmops ON contextscripts USING gin (checksum gin_trgm_ops);
CREATE INDEX idx_extauthor_author_lower_trgmops ON extauthors USING gin (lower(author) gin_trgm_ops);
CREATE INDEX idx_extauthor_author_trgmops ON extauthors USING gin (author gin_trgm_ops);
CREATE INDEX idx_fundings_identifier_trgmops ON fundings USING gin (identifier gin_trgm_ops);
CREATE INDEX idx_hypervisors_name_low_trgmops ON hypervisors USING gin (lower((name)::text) gin_trgm_ops);
CREATE INDEX idx_licenses_name_trgmops ON licenses USING gin (name gin_trgm_ops);
CREATE INDEX idx_mail_subscriptions_subjecttype_trgmops ON mail_subscriptions USING gin (subjecttype gin_trgm_ops);
CREATE INDEX idx_news_action_trgmops ON news USING gin (action gin_trgm_ops);
CREATE INDEX idx_normalized_vos_alias_low_trgmops ON normalized_vos USING gin (lower(alias) gin_trgm_ops);
CREATE INDEX idx_normalized_vos_alias_trgmops ON normalized_vos USING gin (alias gin_trgm_ops);
CREATE INDEX idx_normalized_vos_lowercase_name_trgmops ON normalized_vos USING gin (lower(name) gin_trgm_ops);
CREATE INDEX idx_normalized_vos_name_trgmops ON normalized_vos USING gin (name gin_trgm_ops);
CREATE INDEX idx_normalized_vos_status_low_trgmops ON normalized_vos USING gin (lower(status) gin_trgm_ops);
CREATE INDEX idx_normalized_vos_status_trgmops ON normalized_vos USING gin (status gin_trgm_ops);
CREATE INDEX idx_pending_accounts_accountid_trgmops ON pending_accounts USING gin (accountid gin_trgm_ops);
CREATE INDEX idx_researcher_cnames_value_trgmops ON researcher_cnames USING gin (value gin_trgm_ops);
CREATE INDEX idx_researchers_gender_trgmops ON researchers USING gin (gender gin_trgm_ops);
CREATE INDEX idx_site_services_xml_sitename_trgmops ON site_services_xml USING gin (sitename gin_trgm_ops);
CREATE INDEX idx_sites_name_trgmops ON sites USING gin (name gin_trgm_ops);
CREATE INDEX idx_user_credentials_researcherid_sessionid_token_trgmops ON user_credentials USING gin (researcherid, sessionid, token gin_trgm_ops);
CREATE INDEX idx_va_provider_endpoints_va_provider_id_trgmops ON va_provider_endpoints USING gin (va_provider_id gin_trgm_ops);
CREATE INDEX idx_va_provider_images_va_provider_id_trgmops ON va_provider_images USING gin (va_provider_id gin_trgm_ops);
CREATE INDEX idx_va_provider_templates_va_provider_id_trgmops ON va_provider_templates USING gin (va_provider_id gin_trgm_ops);
CREATE INDEX idx_va_providers_sitename_trgmops ON va_providers USING gin (sitename gin_trgm_ops);
CREATE INDEX idx_vapp_versions_status_trgmops ON vapp_versions USING gin (status gin_trgm_ops);
CREATE INDEX idx_vapplications_name_trgmops ON vapplications USING gin (name gin_trgm_ops);
CREATE INDEX idx_vaviews_checksum_trgmops ON vaviews USING gin (checksum gin_trgm_ops);
CREATE INDEX idx_vaviews_format_trgmops ON vaviews USING gin (format gin_trgm_ops);
CREATE INDEX idx_vaviews_osversion_trgmops ON vaviews USING gin (osversion gin_trgm_ops);
CREATE INDEX idx_vaviews_va_version_status_trgmops ON vaviews USING gin (va_version_status gin_trgm_ops);
CREATE INDEX idx_vaviews_va_version_trgmops ON vaviews USING gin (va_version gin_trgm_ops);
CREATE INDEX idx_vaviews_vmiinstance_version_trgmops ON vaviews USING gin (vmiinstance_version gin_trgm_ops);
CREATE INDEX idx_vmiflavours_format_trgmops ON vmiflavours USING gin (format gin_trgm_ops);
CREATE INDEX idx_vmiflavours_osversion_trgmops ON vmiflavours USING gin (osversion gin_trgm_ops);
CREATE INDEX idx_vmiinstances_checksum_trgmops ON vmiinstances USING gin (checksum gin_trgm_ops);
CREATE INDEX idx_vmis_groupname_trgmops ON vmis USING gin (groupname gin_trgm_ops);
CREATE INDEX idx_vmis_name_low_trgmops ON vmis USING gin (lower(name) gin_trgm_ops);
CREATE INDEX idx_vmis_name_trgmops ON vmis USING gin (name gin_trgm_ops);
CREATE INDEX idx_vos_alias_low_trgmops ON vos USING gin (lower(alias) gin_trgm_ops);
CREATE INDEX idx_vos_alias_trgmops ON vos USING gin (alias gin_trgm_ops);
CREATE INDEX idx_vos_lowercase_name_trgmops ON vos USING gin (lower(name) gin_trgm_ops);
CREATE INDEX idx_vos_status_low_trgmops ON vos USING gin (lower(status) gin_trgm_ops);
CREATE INDEX idx_vos_status_trgmops ON vos USING gin (status gin_trgm_ops);
CREATE INDEX middlewares_name_idx_trgmops ON middlewares USING gin (name gin_trgm_ops);
CREATE INDEX middlewares_name_low_idx_trgmops ON middlewares USING gin (lower(name) gin_trgm_ops);
CREATE INDEX organizations_identifier_idx_trgmops ON organizations USING gin (identifier gin_trgm_ops);
CREATE INDEX oses_name_idx_trgmops ON oses USING gin (name gin_trgm_ops);
CREATE INDEX oses_name_low_idx_trgmops ON oses USING gin (lower(name) gin_trgm_ops);
CREATE INDEX positiontypes_idx_trgmops ON positiontypes USING gin (description gin_trgm_ops);
CREATE INDEX positiontypes_low_idx_trgmops ON positiontypes USING gin (lower(description) gin_trgm_ops);
CREATE INDEX proglangs_name_idx_trgmops ON proglangs USING gin (name gin_trgm_ops);
CREATE INDEX proglangs_name_low_idx_trgmops ON proglangs USING gin (lower(name) gin_trgm_ops);
CREATE INDEX projects_identifier_idx_trgmops ON projects USING gin (identifier gin_trgm_ops);
CREATE INDEX researchers_cname_idx_trgmops ON researchers USING gin (cname gin_trgm_ops);
CREATE INDEX researchers_cname_low_idx_trgmops ON researchers USING gin (lower(cname) gin_trgm_ops);
CREATE INDEX researchers_firstname_idx_trgmops ON researchers USING gin (firstname gin_trgm_ops);
CREATE INDEX researchers_firstname_low_idx_trgmops ON researchers USING gin (lower(firstname) gin_trgm_ops);
CREATE INDEX researchers_institution_idx_trgmops ON researchers USING gin (institution gin_trgm_ops);
CREATE INDEX researchers_institution_low_idx_trgmops ON researchers USING gin (lower(institution) gin_trgm_ops);
CREATE INDEX researchers_lastname_idx_trgmops ON researchers USING gin (lastname gin_trgm_ops);
CREATE INDEX researchers_lastname_low_idx_trgmops ON researchers USING gin (lower(lastname) gin_trgm_ops);
CREATE INDEX researchers_name_idx_trgmops ON researchers USING gin (name gin_trgm_ops);
CREATE INDEX researchers_name_low_idx_trgmops ON researchers USING gin (lower(name) gin_trgm_ops);
CREATE INDEX ssp_saml_logoutstore_nameid_trgmops ON ssp_saml_logoutstore USING gin (_authsource, _nameid gin_trgm_ops);
CREATE INDEX statuses_name_idx_trgmops ON statuses USING gin (name gin_trgm_ops);
CREATE INDEX statuses_name_low_idx_trgmops ON statuses USING gin (lower((name)::text) gin_trgm_ops);
CREATE INDEX user_accounts_accountid_idx_trgmops ON user_accounts USING gin (accountid gin_trgm_ops);
CREATE INDEX vos_lower_idx_trgmops ON vos USING gin (lower(name) gin_trgm_ops);
CREATE INDEX vos_name_idx1_trgmops ON vos USING gin (name gin_trgm_ops);
CREATE INDEX vos_name_idx_trgmops ON vos USING gin (name gin_trgm_ops);
CREATE INDEX vos_name_low_idx_trgmops ON vos USING gin (lower(name) gin_trgm_ops);

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 12, 40, E'TRGM and non-C locale support for text indices'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=12 AND revision=40);

COMMIT;
