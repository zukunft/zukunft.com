DROP PROCEDURE IF EXISTS component_insert_log_1111050000000000_user;
CREATE PROCEDURE component_insert_log_1111050000000000_user
    (_user_id                    bigint,
     _change_action_id           smallint,
     _field_id_component_name    smallint,
     _component_name             text,
     _component_id               bigint,
     _field_id_description       smallint,
     _description                text,
     _field_id_component_type_id smallint,
     _type_name                  text,
     _component_type_id          smallint)
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,    new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_component_name,_component_name,_component_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,_description,_component_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,            new_value, new_id,         row_id)
         SELECT          _user_id,_change_action_id,_field_id_component_type_id,_type_name,_component_type_id,_component_id ;

    INSERT INTO user_components
                (component_id, user_id, component_name, description, component_type_id)
         SELECT _component_id,_user_id,_component_name,_description,_component_type_id ;

END;

PREPARE component_insert_log_1111050000000000_user_call FROM
    'SELECT component_insert_log_1111050000000000_user (?,?,?,?,?,?,?,?,?,?)';

SELECT component_insert_log_1111050000000000_user (
               1,
               1,
               51,
               'Word',
               1,
               52,
               'simply show the word or triple name',
               53,
               'phrase_name',
               8);