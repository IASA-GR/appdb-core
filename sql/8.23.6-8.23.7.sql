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
Previous version: 8.23.6
New version: 8.23.7
Author: wvkarag@lovecraft.priv.iasa.gr
*/
START TRANSACTION;

CREATE OR REPLACE VIEW egi_vo_contacts AS 
WITH emails AS (
         SELECT v.email,
            NULL::integer AS researcherid,
            v.dn
           FROM egiops.vo_contacts v
        UNION
         SELECT c.data AS email,
            c.researcherid,
            NULL::text AS dn
           FROM contacts c
          WHERE c.contacttypeid = 7
        )
 SELECT vos.id AS void,
    user_accounts.researcherid,
    vo_contacts.role,
    array_agg(DISTINCT emails.email) AS email,
        CASE
            WHEN user_accounts.researcherid IS NULL THEN vo_contacts.name
            ELSE researchers.name
        END AS name,
    researchers.cname
   FROM egiops.vo_contacts
     LEFT JOIN user_accounts ON 
     	(user_accounts.accountid = vo_contacts.dn AND user_accounts.account_type = 'x509'::e_account_type) OR
     	(user_accounts.accountid = REGEXP_REPLACE(vo_contacts.dn, '/O=AAI/.+/Id=', '') AND user_accounts.account_type = 'egi-aai'::e_account_type AND vo_contacts.dn LIKE '/O=AAI/%')
     LEFT JOIN researchers ON researchers.id = user_accounts.researcherid
     JOIN emails ON 
     	(emails.researcherid = researchers.id) OR 
     	(emails.dn = vo_contacts.dn)
     JOIN vos ON lower(vos.name) = lower(vo_contacts.vo) AND vos.deleted IS FALSE AND vos.sourceid = 1
  GROUP BY vos.id, user_accounts.researcherid, vo_contacts.role, vo_contacts.name, researchers.name, researchers.cname;

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 23, 7, E'Take under account AAI DNs from EGI Ops portal when matching VO contacts'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=23 AND revision=7);

COMMIT;	
