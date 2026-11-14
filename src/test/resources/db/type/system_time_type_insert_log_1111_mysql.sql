DROP PROCEDURE IF EXISTS system_time_type_insert_log_1111;
CREATE PROCEDURE system_time_type_insert_log_1111
    (_type_name            text,
     _user_id              bigint,
     _change_action_id     smallint,
     _field_id_type_name   smallint,
     _field_id_code_id     smallint,
     _code_id              text,
     _field_id_description smallint,
     _description          text)
BEGIN

    INSERT INTO system_time_types ( type_name)
         SELECT                    _type_name ;

    SELECT LAST_INSERT_ID() AS @new_system_time_type_id;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_type_name,  _type_name,  @new_system_time_type_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_code_id,    _code_id,    @new_system_time_type_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,_description,@new_system_time_type_id ;

    UPDATE system_time_types
       SET code_id     = _code_id,
           description = _description
     WHERE system_time_types.system_time_type_id = @new_system_time_type_id;

END;

PREPARE system_time_type_insert_log_1111_call FROM
    'SELECT system_time_type_insert_log_1111 (?,?,?,?,?,?,?,?)';

SELECT system_time_type_insert_log_1111
       ('not classified',
        3,
        1,
        886,
        887,
        'not_specified',
        888,
        'the execution time not yet assigned to any other group');