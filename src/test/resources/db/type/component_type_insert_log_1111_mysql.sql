DROP PROCEDURE IF EXISTS component_type_insert_log_1111;
CREATE PROCEDURE component_type_insert_log_1111
    (_type_name               text,
     _user_id                 bigint,
     _change_action_id        smallint,
     _field_id_type_name      smallint,
     _field_id_code_id        smallint,
     _code_id                 text,
     _field_id_description    smallint,
     _description             text)
BEGIN

    INSERT INTO component_types ( type_name)
         SELECT               _type_name ;

         SELECT LAST_INSERT_ID()
             AS @new_component_type_id;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_type_name,  _type_name,  @new_component_type_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_code_id,    _code_id,    @new_component_type_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,_description,@new_component_type_id ;

        UPDATE component_types
           SET code_id     = _code_id,
               description = _description
         WHERE component_types.component_type_id = @new_component_type_id;

END;

PREPARE component_type_insert_log_1111_call
    FROM 'SELECT component_type_insert_log_1111 (?,?,?,?,?,?,?,?)';

SELECT component_type_insert_log_1111
    ('spreadsheet',
     1,
     1,
     747,
     748,
     'calc_sheet',
     749,
     'changeable spreadsheet with words,number and formulas that allow changes');
