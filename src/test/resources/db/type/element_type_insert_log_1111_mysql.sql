DROP PROCEDURE IF EXISTS element_type_insert_log_1111;
CREATE PROCEDURE element_type_insert_log_1111
    (_type_name               text,
     _user_id                 bigint,
     _change_action_id        smallint,
     _field_id_type_name      smallint,
     _field_id_code_id        smallint,
     _code_id                 text,
     _field_id_description    smallint,
     _description             text)
BEGIN

    INSERT INTO element_types ( type_name)
         SELECT               _type_name ;

         SELECT LAST_INSERT_ID()
             AS @new_element_type_id;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_type_name,  _type_name,  @new_element_type_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_code_id,    _code_id,    @new_element_type_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,_description,@new_element_type_id ;

        UPDATE element_types
           SET code_id     = _code_id,
               description = _description
         WHERE element_types.element_type_id = @new_element_type_id;

END;

PREPARE element_type_insert_log_1111_call
    FROM 'SELECT element_type_insert_log_1111 (?,?,?,?,?,?,?,?)';

SELECT element_type_insert_log_1111
    ('Word selector',
     1,
     1,
     687,
     688,
     'word',
     689,
     'a reference to a word used to select values using "and"');
