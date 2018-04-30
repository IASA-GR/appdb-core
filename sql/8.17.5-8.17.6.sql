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
Previous version: 8.17.5
New version: 8.17.6
Author: nakos.al@iasa.gr
*/

START TRANSACTION;

-- drop table if exists cd_logs;
-- drop table if exists cd_instance_states;
-- drop table if exists cd_task_instances;
-- drop table if exists cd_instances;
-- drop table if exists cds;
-- drop table if exists cd_trigger_types;
-- drop table if exists cd_flow_tasks;
-- drop table if exists cd_tasks;
-- drop table if exists cd_task_groups;
-- drop table if exists cd_flows;
-- drop table if exists cd_configs;

CREATE TABLE public.cd_configs
(
  id SERIAL,
  cname TEXT NOT NULL,
  name TEXT,
  description TEXT,
  data TEXT,
  idx INTEGER DEFAULT 0,
  CONSTRAINT cd_configs_pk PRIMARY KEY (id),
  CONSTRAINT uniq_cd_config_cname UNIQUE (cname, idx)
)
WITH (
  OIDS=FALSE
);
ALTER TABLE public.cd_configs
  OWNER TO appdb;

INSERT INTO public.cd_configs (cname, name, description, data) VALUES ('service.cd.nextTaskInterval.value', 'Next check time offset', 'Constrain the next check to not start only after the given time passed from the last check.', '1800');
INSERT INTO public.cd_configs (cname, name, description, data) VALUES ('service.cd.runningTasks.size.value', 'Maximum number of running tasks', 'Constrain the number of tasks running at the same time.', '20');
INSERT INTO public.cd_configs (cname, name, description, data) VALUES ('service.cd.failedAttempts.maximum.value', 'Maximum number of failed start attempts', 'Number of successively failed attempts to start continuous delivery process for a virtual appliance. If this number is reached then continuous delivery of the specific virtual appliance will be paused.', '50');
INSERT INTO public.cd_configs (cname, name, description, data) VALUES ('service.process.heartbeat.interval.value', 'Service heartbeat interval value', 'How often should a backend service send a heartbeat before considered dead.', '60');

CREATE TABLE public.cd_flows
(
  id SERIAL,
  name text NOT NULL,
  cname text,
  description text,
  CONSTRAINT uniq_cd_flow_name UNIQUE (name),
  CONSTRAINT uniq_cd_flow_cname UNIQUE (cname),
  CONSTRAINT cd_flows_pk PRIMARY KEY (id)
)
WITH (
  OIDS=FALSE
);
ALTER TABLE public.cd_flows
  OWNER TO appdb;

INSERT INTO public.cd_flows VALUES (1, 'Publish new VA Version', 'publish.vaversion', 'Check remote files for new VA versions and publish them to AppDB');

CREATE TABLE public.cd_task_groups
(
  id SERIAL,
  name text NOT NULL,
  description text,
  CONSTRAINT cd_task_groups_pk PRIMARY KEY (id)
)
WITH (
  OIDS=FALSE
);
ALTER TABLE public.cd_task_groups
  OWNER TO appdb;

CREATE TABLE public.cd_tasks
(
  id SERIAL,
  name text NOT NULL,
  cname text NOT NULL,
  description text,
  group_id integer,
  CONSTRAINT unique_cname UNIQUE (cname),
  CONSTRAINT cd_tasks_pk PRIMARY KEY (id),
  CONSTRAINT fk_cd_tasks_cd_task_groups FOREIGN KEY (group_id)
      REFERENCES public.cd_task_groups (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE CASCADE
)
WITH (
  OIDS=FALSE
);
ALTER TABLE public.cd_tasks
  OWNER TO appdb;

INSERT INTO public.cd_task_groups VALUES (1, 'Check for new version', 'Retrieve remote CD file to check if a new vappliance version is available for publishing');
INSERT INTO public.cd_task_groups VALUES (2, 'Perform VM image integrity check', 'Download VM image from remote url and calculate its checksum');
INSERT INTO public.cd_task_groups VALUES (3, 'Publish new VA version', 'Publish new VA version to the AppDB');

INSERT INTO public.cd_tasks  VALUES (1, 'Fetch file', 'appdb.cd.task.checkvaversion.fetchfile', 'Download file from remote location', 1);
INSERT INTO public.cd_tasks VALUES (2, 'Check file format', 'appdb.cd.task.checkvaversion.checkfile', 'Check if downloaded file is in a readable valid format', 1);
INSERT INTO public.cd_tasks VALUES (3, 'Check new VA version', 'appdb.cd.task.checkvaversion.checkfilevaversion','Check if given version is different from the current version and does not exist in archived', 1);
INSERT INTO public.cd_tasks VALUES (4, 'Validate file metadata', 'appdb.cd.task.checkvaversion.validatemetadata', 'Check if given metadata are correct and all mandatory fields exist', 1);

INSERT INTO public.cd_tasks VALUES (5, 'Integrity check', 'appdb.cd.task.integrity.check', 'Run integrity check of remote VM image.', 2);

INSERT INTO public.cd_tasks VALUES (6, 'Prepare VA version publish contents', 'appdb.cd.task.publishvaversion.preparecontents','Check if given version is different from the current version and does not exist in archived', 3);
INSERT INTO public.cd_tasks VALUES (7, 'Publish new VA Version', 'appdb.cd.task.publishvaversion.publish', 'Publish new VA Version to AppDB', 3);

CREATE TABLE public.cd_flow_tasks
(
  id SERIAL,
  cd_flow_id integer NOT NULL,
  cd_task_id integer NOT NULL,
  ord integer NOT NULL DEFAULT 0,
  CONSTRAINT unique_order_per_flow_id UNIQUE (cd_flow_id, ord),
  CONSTRAINT cd_flow_tasks_pk PRIMARY KEY (id),
  CONSTRAINT fk_cd_flow_tasks_cd_task_id FOREIGN KEY (cd_task_id)
      REFERENCES public.cd_tasks (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT fk_cd_flow_tasks_cd_flow_id FOREIGN KEY (cd_flow_id)
      REFERENCES public.cd_flows (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE CASCADE
)
WITH (
  OIDS=FALSE
);
ALTER TABLE public.cd_flow_tasks
  OWNER TO appdb;

INSERT INTO public.cd_flow_tasks VALUES (1, 1, 1, 0);
INSERT INTO public.cd_flow_tasks VALUES (2, 1, 2, 1);
INSERT INTO public.cd_flow_tasks VALUES (3, 1, 3, 2);
INSERT INTO public.cd_flow_tasks VALUES (4, 1, 4, 3);
INSERT INTO public.cd_flow_tasks VALUES (5, 1, 5, 4);
INSERT INTO public.cd_flow_tasks VALUES (6, 1, 6, 5);
INSERT INTO public.cd_flow_tasks VALUES (7, 1, 7, 6);


CREATE TABLE public.cd_trigger_types
(
  id SERIAL,
  name text NOT NULL,
  cname text NOT NULL,
  description text,
  CONSTRAINT cd_trigger_types_pk PRIMARY KEY (id)
)
WITH (
  OIDS=FALSE
);
ALTER TABLE public.cd_trigger_types
  OWNER TO appdb;

INSERT INTO public.cd_trigger_types VALUES (1, 'AppDB Backend Service', 'appdb.service.cron', 'Triggered on time intervals from AppDB service.');
INSERT INTO public.cd_trigger_types VALUES (2, 'AppDB Portal Service', 'appdb.registry.user', 'Triggered from AppDB portal by a registered user.');

CREATE TABLE public.cds
(
  id SERIAL,
  cd_flow_id integer NOT NULL,
  app_id integer NOT NULL,
  enabled boolean NOT NULL DEFAULT false,
  paused boolean NOT NULL DEFAULT true,
  url text DEFAULT NULL,
  default_actor_id integer DEFAULT NULL,
  failed_attempts integer DEFAULT 0,
  last_failed_attempt_at timestamp without time zone DEFAULT NULL,
  last_attempt_error TEXT DEFAULT NULL,
  CONSTRAINT cds_pk PRIMARY KEY (id),
  CONSTRAINT fk_cds_cd_flow_id FOREIGN KEY (cd_flow_id)
      REFERENCES public.cd_flows (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT fk_cds_app_id FOREIGN KEY (app_id)
      REFERENCES public.applications (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT fk_cds_default_actor FOREIGN KEY (default_actor_id)
      REFERENCES public.researchers (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION
)
WITH (
  OIDS=FALSE
);
ALTER TABLE public.cds
  OWNER TO appdb;

CREATE TABLE public.cd_instances
(
  id SERIAL,
  cd_id integer NOT NULL,
  trigger_type integer NOT NULL,
  trigger_by_id integer,
  request_id text NOT NULL,
  process_id text NOT NULL,
  default_actor_id integer,
  url text,
  started_at timestamp without time zone DEFAULT now(),
  completed_at timestamp without time zone DEFAULT NULL,
  lastupdated_at timestamp without time zone DEFAULT NULL,
  progress_min integer DEFAULT 0,
  progress_max integer DEFAULT 0,
  progress_val integer DEFAULT 0,
  state text, -- running, success, error, canceled
  result text,
  CONSTRAINT cd_instances_pk PRIMARY KEY (id),
  CONSTRAINT fk_cd_instances_cd_id FOREIGN KEY (cd_id)
      REFERENCES public.cds (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT fk_cd_instances_trigger_type FOREIGN KEY (trigger_type)
      REFERENCES public.cd_trigger_types (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT fk_cd_instances_default_actor FOREIGN KEY (default_actor_id)
      REFERENCES public.researchers (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION
  CONSTRAINT fk_cd_instances_trigger_by_id FOREIGN KEY (trigger_by_id)
      REFERENCES public.researchers (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION
)
WITH (
  OIDS=FALSE
);
ALTER TABLE public.cd_instances
  OWNER TO appdb;

CREATE TABLE public.cd_task_instances
(
  id SERIAL,
  cd_task_id integer NOT NULL,
  cd_instance_id integer NOT NULL,
  request_id text NOT NULL,
  started_at timestamp without time zone DEFAULT now(),
  completed_at timestamp without time zone DEFAULT NULL,
  lastupdated_at timestamp without time zone DEFAULT NULL,
  progress_min integer DEFAULT 0,
  progress_max integer DEFAULT 0,
  progress_val integer DEFAULT 0,
  state text, -- running, success, error, canceled
  result text,
  CONSTRAINT cd_task_instances_pk PRIMARY KEY (id),
  CONSTRAINT fk_cd_task_instances_cd_task_id FOREIGN KEY (cd_task_id)
      REFERENCES public.cd_tasks (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT fk_cd_task_instances_cd_instance_id FOREIGN KEY (cd_instance_id)
      REFERENCES public.cd_instances (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE CASCADE
)
WITH (
  OIDS=FALSE
);
ALTER TABLE public.cd_task_instances
  OWNER TO appdb;

CREATE TABLE public.cd_instance_states
(
  id SERIAL,
  cd_instance_id integer NOT NULL,
  cd_task_instance_id integer,
  group_name text,
  name text NOT NULL,
  value text,
  idx integer DEFAULT 0,
  created_at timestamp without time zone DEFAULT now(),
  updated_at timestamp without time zone DEFAULT NULL,
  CONSTRAINT unique_state_keys UNIQUE(cd_instance_id, group_name, name, idx),
  CONSTRAINT cd_instance_states_pk PRIMARY KEY (id),
  CONSTRAINT fk_cd_instance_states_cd_instance_id FOREIGN KEY (cd_instance_id)
      REFERENCES public.cd_instances (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT fk_cd_instance_states_cd_task_instance_id FOREIGN KEY (cd_task_instance_id)
      REFERENCES public.cd_task_instances (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION
)
WITH (
  OIDS=FALSE
);
ALTER TABLE public.cd_instance_states
  OWNER TO appdb;

CREATE TABLE public.cd_logs
(
  id SERIAL,
  cd_id integer NOT NULL,
  cd_instance_id integer,
  cd_task_instance_id integer,
  created_at timestamp without time zone DEFAULT now(),
  action text, -- completed, failed, canceled, error, cd-prop-change, task-completed, task-started, task-failed, task-canceled
  subject text,-- CdInstance, defaultActorId, enabled, paused, url, and cd task cnames
  payload text,
  actor_id integer,
  comments text,
  CONSTRAINT cd_logs_pk PRIMARY KEY (id),
  CONSTRAINT fk_cd_logs_cd_task_instance_id FOREIGN KEY (cd_task_instance_id)
      REFERENCES public.cd_task_instances (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT fk_cd_logs_cd_instance_id FOREIGN KEY (cd_instance_id)
      REFERENCES public.cd_instances (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT fk_cd_logs_cd_id FOREIGN KEY (cd_id)
      REFERENCES public.cds (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT fk_cd_logs_actor_id FOREIGN KEY (actor_id)
      REFERENCES public.researchers (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION
)
WITH (
  OIDS=FALSE
);
ALTER TABLE public.cd_logs
  OWNER TO appdb;


CREATE TABLE public.cd_published_vaversions
(
  id SERIAL,
  app_id INTEGER NOT NULL,
  vapp_version_id INTEGER NOT NULL,
  cd_instance_id INTEGER NOT NULL,
  created_at timestamp without time zone DEFAULT now(),
  CONSTRAINT cd_published_vas_pk PRIMARY KEY (id),
  CONSTRAINT fk_cd_published_vaversions_app_id FOREIGN KEY (app_id)
      REFERENCES public.applications (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT fk_cd_published_vaversions_vapp_version_id FOREIGN KEY (vapp_version_id)
      REFERENCES public.vapp_versions (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT fk_cd_published_vaversions_cd_instance_id FOREIGN KEY (cd_instance_id)
      REFERENCES public.cd_instances (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION
)
WITH (
  OIDS=FALSE
);
ALTER TABLE public.cd_published_vaversions
  OWNER TO appdb;

INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 17, 6, E'Add continuous delivery service tables'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=17 AND revision=6);

COMMIT;
