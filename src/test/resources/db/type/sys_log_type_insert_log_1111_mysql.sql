DROP PROCEDURE IF EXISTS sys_log_type_insert_log_1111;
CREATE PROCEDURE sys_log_type_insert_log_1111
    (_type_name               text,
     _user_id                 bigint,
     _change_action_id        smallint,
     _field_id_type_name      smallint,
     _field_id_code_id        smallint,
     _code_id                 text,
     _field_id_description    smallint,
     _description             text)
BEGIN

    INSERT INTO sys_log_types ( type_name)
         SELECT               _type_name ;

         SELECT LAST_INSERT_ID()
             AS @new_sys_log_type_id;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_type_name,  _type_name,  @new_sys_log_type_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_code_id,    _code_id,    @new_sys_log_type_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,_description,@new_sys_log_type_id ;

        UPDATE sys_log_types
           SET code_id     = _code_id,
               description = _description
         WHERE sys_log_types.sys_log_type_id = @new_sys_log_type_id;

END;

PREPARE sys_log_type_insert_log_1111_call
    FROM 'SELECT sys_log_type_insert_log_1111 (?,?,?,?,?,?,?,?)';

SELECT sys_log_type_insert_log_1111
    ('Info',
     1,
     1,
     839,
     840,
     'log_info',
     841,
     'Information only message for debugging and execution time details');
