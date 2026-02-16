DROP PROCEDURE IF EXISTS job_type_insert_log_1111;
CREATE PROCEDURE job_type_insert_log_1111
    (_type_name               text,
     _user_id                 bigint,
     _change_action_id        smallint,
     _field_id_type_name      smallint,
     _field_id_code_id        smallint,
     _code_id                 text,
     _field_id_description    smallint,
     _description             text)
BEGIN

    INSERT INTO job_types ( type_name)
         SELECT               _type_name ;

         SELECT LAST_INSERT_ID()
             AS @new_job_type_id;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_type_name,  _type_name,  @new_job_type_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_code_id,    _code_id,    @new_job_type_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,_description,@new_job_type_id ;

        UPDATE job_types
           SET code_id     = _code_id,
               description = _description
         WHERE job_types.job_type_id = @new_job_type_id;

END;

PREPARE job_type_insert_log_1111_call
    FROM 'SELECT job_type_insert_log_1111 (?,?,?,?,?,?,?,?)';

SELECT job_type_insert_log_1111
    ('update value',
     1,
     1,
     232,
     233,
     'value_update',
     234,
     'if a value is updated all the depending results should be calculated again');
