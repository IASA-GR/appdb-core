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
Previous version: 8.12.12
New version: 8.12.13
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

CREATE SCHEMA egiaai
  AUTHORIZATION appdb;

CREATE TABLE egiaai.vo_contacts
(
  vo text, -- VO Name
  name text, -- User Given name
  role text, -- User role in VO
  email text, -- User email
  puid text -- User EGI AAI persitent unique ID
)
WITH (
  OIDS=FALSE
);
ALTER TABLE egiaai.vo_contacts
  OWNER TO appdb;

CREATE TABLE egiaai.vo_members
(
  uservo text, -- User Given name for the VO
  puid text, -- User egi aai persitent unique ID
  vo text, -- VO name
  last_update timestamp without time zone,
  first_update timestamp without time zone
)
WITH (
  OIDS=FALSE
);
ALTER TABLE egiaai.vo_members
  OWNER TO appdb;

-- VIEW: EGI AAI VO Contacts
CREATE OR REPLACE VIEW egiaai_vo_contacts AS
 WITH emails AS (
         SELECT v.email,
            NULL::integer AS researcherid
           FROM egiaai.vo_contacts v
        UNION
         SELECT c.data AS email,
            c.researcherid
           FROM contacts c
          WHERE c.contacttypeid = 7
        )
 SELECT vos.id AS void,
    user_accounts.researcherid,
    vo_contacts.role,
        CASE
            WHEN array_agg(DISTINCT emails.email) = '{NULL}'::text[] THEN NULL::text[]
            ELSE array_agg(DISTINCT emails.email)
        END AS email,
        CASE
            WHEN user_accounts.researcherid IS NULL THEN egiaai.vo_contacts.name
            ELSE researchers.name
        END AS name,
    researchers.cname
   FROM egiaai.vo_contacts
     LEFT JOIN user_accounts ON user_accounts.accountid = egiaai.vo_contacts.puid AND user_accounts.account_type = 'egi-aai'::e_account_type
     LEFT JOIN researchers ON researchers.id = user_accounts.researcherid
     LEFT JOIN emails ON emails.email = egiaai.vo_contacts.email OR emails.researcherid = researchers.id
     JOIN vos ON lower(vos.name) = lower(egiaai.vo_contacts.vo) AND vos.deleted IS FALSE
  GROUP BY vos.id, user_accounts.researcherid, egiaai.vo_contacts.role, egiaai.vo_contacts.name, researchers.name, researchers.cname;

ALTER TABLE egiaai_vo_contacts
  OWNER TO appdb;

CREATE OR REPLACE VIEW vo_contacts AS
 SELECT egi_vo_contacts.void,
    egi_vo_contacts.researcherid,
    egi_vo_contacts.role,
    egi_vo_contacts.email,
    egi_vo_contacts.name,
    egi_vo_contacts.cname
   FROM egi_vo_contacts
UNION
 SELECT ebi_vo_contacts.void,
    ebi_vo_contacts.researcherid,
    ebi_vo_contacts.role,
    ebi_vo_contacts.email,
    ebi_vo_contacts.name,
    ebi_vo_contacts.cname
   FROM ebi_vo_contacts
UNION
 SELECT egiaai_vo_contacts.void,
    egiaai_vo_contacts.researcherid,
    egiaai_vo_contacts.role,
    egiaai_vo_contacts.email,
    egiaai_vo_contacts.name,
    egiaai_vo_contacts.cname
   FROM egiaai_vo_contacts;

CREATE OR REPLACE VIEW vo_members AS
 SELECT vos.id AS void,
    researchers.id AS researcherid,
    NULL::timestamp AS member_since
   FROM egiaai.vo_members
     JOIN vos ON lower(vos.name) = lower(vo_members.vo) AND NOT vos.deleted AND vos.sourceid = 2
     JOIN user_accounts ON (user_accounts.accountid = vo_members.puid) AND user_accounts.account_type = 'egi-aai'::e_account_type
     JOIN researchers ON researchers.id = user_accounts.researcherid
UNION ALL
 SELECT vos.id AS void,
    researchers.id AS researcherid,
    vo_members.first_update AS member_since
   FROM egiops.vo_members
     JOIN vos ON lower(vos.name) = lower(vo_members.vo) AND NOT vos.deleted AND vos.sourceid = 1
     JOIN user_accounts ON user_accounts.accountid = vo_members.certdn AND user_accounts.account_type = 'x509'::e_account_type
     JOIN researchers ON researchers.id = user_accounts.researcherid
UNION ALL
 SELECT vos.id AS void,
    researchers.id AS researcherid,
    vo_members.first_update AS member_since
   FROM perun.vo_members
     JOIN vos ON lower(vos.name) = lower(vo_members.vo) AND NOT vos.deleted AND vos.sourceid = 2
     JOIN user_accounts ON (user_accounts.accountid = ANY (vo_members.certdn)) AND user_accounts.account_type = 'x509'::e_account_type
     JOIN researchers ON researchers.id = user_accounts.researcherid;

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 12, 13, E'Add EGI AAI support for VO contacts and members'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=12 AND revision=13);

COMMIT;

