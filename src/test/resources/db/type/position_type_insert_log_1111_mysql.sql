DROP PROCEDURE IF EXISTS position_type_insert_log_1111;
CREATE PROCEDURE position_type_insert_log_1111
    (_type_name               text,
     _user_id                 bigint,
     _change_action_id        smallint,
     _field_id_type_name      smallint,
     _field_id_code_id        smallint,
     _code_id                 text,
     _field_id_description    smallint,
     _description             text)
BEGIN

    INSERT INTO position_types ( type_name)
         SELECT               _type_name ;

         SELECT LAST_INSERT_ID()
             AS @new_position_type_id;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_type_name,  _type_name,  @new_position_type_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_code_id,    _code_id,    @new_position_type_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,_description,@new_position_type_id ;

        UPDATE position_types
           SET code_id     = _code_id,
               description = _description
         WHERE position_types.position_type_id = @new_position_type_id;

END;

PREPARE position_type_insert_log_1111_call
    FROM 'SELECT position_type_insert_log_1111 (?,?,?,?,?,?,?,?)';

SELECT position_type_insert_log_1111
    ('below',
     1,
     1,
     764,
     765,
     'below',
     766,
     'below the previous entry');
