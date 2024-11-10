DROP PROCEDURE IF EXISTS component_update_log_002240000000000_user;
CREATE PROCEDURE component_update_log_002240000000000_user
    (_user_id                    bigint,
     _change_action_id           smallint,
     _field_id_component_name    smallint,
     _component_name_old         text,
     _component_name             text,
     _component_id               bigint,
     _field_id_description       smallint,
     _description_old            text,
     _description                text,
     _field_id_component_type_id smallint,
     _type_name_old              text,
     _component_type_id_old      smallint,
     _type_name                  text,
     _component_type_id          smallint)
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,    old_value,     new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_component_name,_component_name_old,_component_name,_component_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,      old_value,       new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,_description_old,_description,_component_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,            old_value,     new_value, old_id,                new_id,         row_id)
         SELECT          _user_id,_change_action_id,_field_id_component_type_id,_type_name_old,_type_name,_component_type_id_old,_component_type_id,_component_id ;

    UPDATE user_components
       SET component_name    = _component_name,
           description       = _description,
           component_type_id = _component_type_id
     WHERE component_id = _component_id
       AND user_id = _user_id;

END;

PREPARE component_update_log_002240000000000_user_call FROM
    'SELECT component_update_log_002240000000000_user (?,?,?,?,?,?,?,?,?,?,?,?,?,?)';

SELECT component_update_log_002240000000000_user
       (1,
        2,
        51,
        'Word',
        'System Test View Component Renamed',
        1,
        52,
        'simply show the word or triple name',
        null,
        53,
        'phrase_name',
        8,
        null,
        null);