DROP PROCEDURE IF EXISTS job_status_insert_log_11111;
CREATE PROCEDURE job_status_insert_log_11111
    (_status_name             text,
     _user_id                 bigint,
     _change_action_id        smallint,
     _field_id_status_name    smallint,
     _field_id_code_id        smallint,
     _code_id                 text,
     _field_id_description    smallint,
     _description             text,
     _field_id_priority       smallint,
     _priority                smallint)
BEGIN

    INSERT INTO job_statuum ( status_name)
         SELECT              _status_name ;

         SELECT LAST_INSERT_ID()
             AS @new_job_status_id;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_status_name,_status_name,@new_job_status_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_code_id,    _code_id,    @new_job_status_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,_description,@new_job_status_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_priority,   _priority,   @new_job_status_id ;

        UPDATE job_statuum
           SET code_id     = _code_id,
               description = _description,
               priority    = _priority
         WHERE job_statuum.job_status_id = @new_job_status_id;

END;

PREPARE job_status_insert_log_11111_call
    FROM 'SELECT job_status_insert_log_11111 (?,?,?,?,?,?,?,?,?,?)';

SELECT job_status_insert_log_11111
    ('created',
     1,
     1,
     855,
     856,
     'new',
     857,
     'the job is not yet assigned to any calc engine',
     858,
     0);
