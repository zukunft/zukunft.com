DROP PROCEDURE IF EXISTS component_insert_log_0111500000000000;
CREATE PROCEDURE component_insert_log_0111500000000000
    (_component_name             text,
     _user_id                    bigint,
     _change_action_id           smallint,
     _field_id_component_name    smallint,
     _field_id_user_id           smallint,
     _field_id_description       smallint,
     _description                text,
     _field_id_component_type_id smallint,
     _type_name                  text,
     _component_type_id          smallint)
BEGIN

    INSERT INTO components ( component_name)
         SELECT             _component_name ;

    SELECT LAST_INSERT_ID() AS @new_component_id;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,    new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_component_name,_component_name,@new_component_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,  new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_user_id,_user_id,  @new_component_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,_description,@new_component_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,            new_value, new_id,           row_id)
         SELECT          _user_id,_change_action_id,_field_id_component_type_id,_type_name,_component_type_id,@new_component_id ;

    UPDATE components
       SET user_id           = _user_id,
           description       = _description,
           component_type_id = _component_type_id
     WHERE components.component_id = @new_component_id;

END;

PREPARE component_insert_log_0111500000000000_call FROM
    'SELECT component_insert_log_0111500000000000 (?,?, ?, ?, ?, ?, ?, ?, ?, ?)';

SELECT component_insert_log_0111500000000000 (
               'Word',
               1,
               1,
               51,
               743,
               52,
               'simply show the word or triple name',
               53,
               'phrase_name',
               8);