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
Previous version: 8.17.0
New version: 8.17.1
Author: wvkarag@lovecraft.priv.iasa.gr
*/

START TRANSACTION;

-- Table: public.va_sec_check_queue

-- DROP TABLE public.va_sec_check_queue;

CREATE TABLE public.va_sec_check_queue
(
  base_mpuri text NOT NULL,
  vmiinstanceid integer,
  queuedon timestamp without time zone NOT NULL DEFAULT now(),
  senton timestamp without time zone,
  closedon timestamp without time zone,
  state text NOT NULL DEFAULT 'queued'::text,
  report_outcome text,
  report_data text,
  secant_version text,
  image_list_path text,
  id bigserial NOT NULL, 
  comment text,
  CONSTRAINT va_sec_check_queue_pkey PRIMARY KEY (id),
  CONSTRAINT va_sec_check_queue_vmiinstanceid_fkey FOREIGN KEY (vmiinstanceid)
      REFERENCES public.vmiinstances (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION
)
WITH (
  OIDS=FALSE
);
ALTER TABLE public.va_sec_check_queue
  OWNER TO appdb;

-- Index: public.idx_va_sec_check_queue_base_mpuri

-- DROP INDEX public.idx_va_sec_check_queue_base_mpuri;

CREATE INDEX idx_va_sec_check_queue_base_mpuri
  ON public.va_sec_check_queue
  USING btree
  (base_mpuri COLLATE pg_catalog."default");

-- Index: public.idx_va_sec_check_queue_state

-- DROP INDEX public.idx_va_sec_check_queue_state;

CREATE INDEX idx_va_sec_check_queue_state
  ON public.va_sec_check_queue
  USING btree
  (state COLLATE pg_catalog."default");

-- Index: public.idx_va_sec_check_queue_vmiinstanceid

-- DROP INDEX public.idx_va_sec_check_queue_vmiinstanceid;

CREATE INDEX idx_va_sec_check_queue_vmiinstanceid
  ON public.va_sec_check_queue
  USING btree
  (vmiinstanceid);

-- Function: public.trfn_va_sec_check_queue()

-- DROP FUNCTION public.trfn_va_sec_check_queue();

CREATE OR REPLACE FUNCTION public.trfn_va_sec_check_queue()
  RETURNS trigger AS
$BODY$
DECLARE newoutcome TEXT;
BEGIN
	IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN
		IF NEW.state IS DISTINCT FROM NULL THEN
			NEW.state := TRIM(LOWER(NEW.state));
			IF NEW.state NOT IN ('queued', 'sent', 'closed', 'aborted') THEN
				RAISE EXCEPTION 'invalid state,must be one of `queued'', `sent'', `closed'', `aborted''.';
				RETURN NULL;
			END IF;
		END IF;
	END IF;
	newoutcome := NULL;
	IF NEW.report_data IS DISTINCT FROM NULL THEN

		BEGIN -- handle possible XML errors
			newoutcome := (SELECT UNNEST(XPATH('//OUTCOME/text()', (NEW.report_data)::XML)) LIMIT 1);
		EXCEPTION WHEN OTHERS THEN
			RAISE NOTICE 'error in XML: %', SQLERRM;
			newoutcome := NULL;
		END;
	END IF;
	IF newoutcome IS DISTINCT FROM NULL THEN
		NEW.report_outcome := newoutcome;
	END IF;
	RETURN NEW;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION public.trfn_va_sec_check_queue()
  OWNER TO appdb;


-- Trigger: rtr_va_sec_check_queue_10_before on public.va_sec_check_queue

-- DROP TRIGGER rtr_va_sec_check_queue_10_before ON public.va_sec_check_queue;

CREATE TRIGGER rtr_va_sec_check_queue_10_before
  BEFORE INSERT OR UPDATE
  ON public.va_sec_check_queue
  FOR EACH ROW
  EXECUTE PROCEDURE public.trfn_va_sec_check_queue();


INSERT INTO version (major,minor,revision,notes) 
	SELECT 8, 17, 1, E'Added table va_sec_check_queue (for SECANT)'
	WHERE NOT EXISTS (SELECT * FROM version WHERE major=8 AND minor=17 AND revision=1);

COMMIT;
