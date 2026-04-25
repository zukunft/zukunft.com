DROP PROCEDURE IF EXISTS sys_log_status_insert_log_11110;
CREATE PROCEDURE sys_log_status_insert_log_11110
    (_status_name             text,
     _user_id                 bigint,
     _change_action_id        smallint,
     _field_id_status_name    smallint,
     _field_id_code_id        smallint,
     _code_id                 text,
     _field_id_description    smallint,
     _description             text)
BEGIN

    INSERT INTO sys_log_statuum (status_name)
         SELECT                 _status_name ;

         SELECT LAST_INSERT_ID()
             AS @new_sys_log_status_id;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_status_name,_status_name,@new_sys_log_status_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_code_id,    _code_id,    @new_sys_log_status_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,_description,@new_sys_log_status_id ;

        UPDATE sys_log_statuum
           SET code_id     = _code_id,
               description = _description
         WHERE sys_log_statuum.sys_log_status_id = @new_sys_log_status_id;

END;

PREPARE sys_log_status_insert_log_11110_call
    FROM 'SELECT sys_log_status_insert_log_11110 (?,?,?,?,?,?,?,?)';

SELECT sys_log_status_insert_log_11110
    ('new',
     1,
     1,
     843,
     844,
     'new',
     845,
     'the error has just being logged and no one has yet looked at it');
