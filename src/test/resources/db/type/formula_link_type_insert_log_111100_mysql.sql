DROP PROCEDURE IF EXISTS formula_link_type_insert_log_111100;
CREATE PROCEDURE formula_link_type_insert_log_111100
    (_type_name               text,
     _user_id                 bigint,
     _change_action_id        smallint,
     _field_id_type_name      smallint,
     _field_id_code_id        smallint,
     _code_id                 text,
     _field_id_description    smallint,
     _description             text)
BEGIN

    INSERT INTO formula_link_types ( type_name)
         SELECT               _type_name ;

         SELECT LAST_INSERT_ID()
             AS @new_formula_link_type_id;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_type_name,  _type_name,  @new_formula_link_type_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_code_id,    _code_id,    @new_formula_link_type_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,_description,@new_formula_link_type_id ;

        UPDATE formula_link_types
           SET code_id     = _code_id,
               description = _description
         WHERE formula_link_types.formula_link_type_id = @new_formula_link_type_id;

END;

PREPARE formula_link_type_insert_log_111100_call
    FROM 'SELECT formula_link_type_insert_log_111100 (?,?,?,?,?,?,?,?)';

SELECT formula_link_type_insert_log_111100
    ('default',
     1,
     1,
     704,
     705,
     'default',
     706,
     'default');
