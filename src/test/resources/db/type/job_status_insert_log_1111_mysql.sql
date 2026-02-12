DROP PROCEDURE IF EXISTS job_status_insert_log_1111;
CREATE PROCEDURE job_status_insert_log_1111
    (_status_name             text,
     _user_id                 bigint,
     _change_action_id        smallint,
     _field_id_status_name    smallint,
     _field_id_code_id        smallint,
     _code_id                 text,
     _field_id_description    smallint,
     _description             text)
BEGIN

    INSERT INTO job_statuus ( status_name)
         SELECT              _status_name ;

         SELECT LAST_INSERT_ID()
             AS @new_job_status_id;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_status_name,_status_name,@new_job_status_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_code_id,    _code_id,    @new_job_status_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,_description,@new_job_status_id ;

        UPDATE job_statuus
           SET code_id     = _code_id,
               description = _description
         WHERE job_statuus.job_status_id = @new_job_status_id;

END;

PREPARE job_status_insert_log_1111_call
    FROM 'SELECT job_status_insert_log_1111 (?,?,?,?,?,?,?,?)';

SELECT job_status_insert_log_1111
    ('created',
     1,
     1,
     914,
     915,
     'new',
     916,
     'the job is not yet assigned to any calc engine');
