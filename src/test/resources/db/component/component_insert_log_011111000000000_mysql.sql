DROP PROCEDURE IF EXISTS component_insert_log_011111000000000;
CREATE PROCEDURE component_insert_log_011111000000000
    (_component_name             text,
     _user_id                    bigint,
     _change_action_id           smallint,
     _field_id_component_name    smallint,
     _field_id_user_id           smallint,
     _field_id_description       smallint,
     _description                text,
     _field_id_component_type_id smallint,
     _component_type_id          smallint,
     _field_id_code_id           smallint,
     _code_id                    text)
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

    INSERT INTO changes ( user_id, change_action_id, change_field_id,            new_value,         row_id)
         SELECT          _user_id,_change_action_id,_field_id_component_type_id,_component_type_id,@new_component_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,  new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_code_id,_code_id,  @new_component_id ;

    UPDATE components
       SET user_id           = _user_id,
           description       = _description,
           component_type_id = _component_type_id,
           code_id           = _code_id
      WHERE components.component_id = @new_component_id;

END;

PREPARE component_insert_log_011111000000000_call FROM
    'SELECT component_insert_log_011111000000000 (?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

SELECT component_insert_log_011111000000000 (
               'form title',
               1,
               1,
               51,
               743,
               52,
               'show the language specific title of a add,change or delete form',
               53,
               17,
               63,
               'form_title');