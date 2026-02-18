CREATE OR REPLACE FUNCTION job_status_insert_log_11111
    (_status_name             text,
     _user_id                 bigint,
     _change_action_id        smallint,
     _field_id_status_name    smallint,
     _field_id_code_id        smallint,
     _code_id                 text,
     _field_id_description    smallint,
     _description             text,
     _field_id_priority       smallint,
     _priority                smallint) RETURNS bigint AS
$$
DECLARE new_job_status_id bigint;
BEGIN

        INSERT INTO job_statuus (status_name)
             SELECT             _status_name
          RETURNING job_status_id INTO new_job_status_id;

        INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
             SELECT          _user_id,_change_action_id,_field_id_status_name,_status_name, new_job_status_id ;

        INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
             SELECT          _user_id,_change_action_id,_field_id_code_id,    _code_id,     new_job_status_id ;

        INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
             SELECT          _user_id,_change_action_id,_field_id_description,_description, new_job_status_id ;

        INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
             SELECT          _user_id,_change_action_id,_field_id_priority,   _priority,    new_job_status_id ;

             UPDATE job_statuus
                SET code_id     = _code_id,
                    description = _description,
                    priority    = _priority
              WHERE job_statuus.job_status_id = new_job_status_id;

             RETURN new_job_status_id;

END
$$ LANGUAGE plpgsql;

PREPARE job_status_insert_log_11111_call
    (text,bigint,smallint,smallint,smallint,text,smallint,text,smallint,smallint) AS
SELECT job_status_insert_log_11111
    ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10);

SELECT job_status_insert_log_11111
    ('created'::text,
     1::bigint,
     1::smallint,
     855::smallint,
     856::smallint,
     'new'::text,
     857::smallint,
     'the job is not yet assigned to any calc engine'::text,
     858::smallint,
     0::smallint);