DROP PROCEDURE IF EXISTS component_link_type_insert_log_1111;
CREATE PROCEDURE component_link_type_insert_log_1111
    (_type_name               text,
     _user_id                 bigint,
     _change_action_id        smallint,
     _field_id_type_name      smallint,
     _field_id_code_id        smallint,
     _code_id                 text,
     _field_id_description    smallint,
     _description             text)
BEGIN

    INSERT INTO component_link_types ( type_name)
         SELECT               _type_name ;

         SELECT LAST_INSERT_ID()
             AS @new_component_link_type_id;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_type_name,  _type_name,  @new_component_link_type_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_code_id,    _code_id,    @new_component_link_type_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,_description,@new_component_link_type_id ;

        UPDATE component_link_types
           SET code_id     = _code_id,
               description = _description
         WHERE component_link_types.component_link_type_id = @new_component_link_type_id;

END;

PREPARE component_link_type_insert_log_1111_call
    FROM 'SELECT component_link_type_insert_log_1111 (?,?,?,?,?,?,?,?)';

SELECT component_link_type_insert_log_1111
    ('always',
     1,
     1,
     760,
     761,
     'always',
     762,
     'the component is always shown as it is');
