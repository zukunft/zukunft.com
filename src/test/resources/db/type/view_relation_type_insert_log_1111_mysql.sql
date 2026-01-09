DROP PROCEDURE IF EXISTS view_relation_type_insert_log_1111;
CREATE PROCEDURE view_relation_type_insert_log_1111
    (_type_name               text,
     _user_id                 bigint,
     _change_action_id        smallint,
     _field_id_type_name      smallint,
     _field_id_code_id        smallint,
     _code_id                 text,
     _field_id_description    smallint,
     _description             text)
BEGIN

    INSERT INTO view_relation_types ( type_name)
         SELECT               _type_name ;

         SELECT LAST_INSERT_ID()
             AS @new_view_relation_type_id;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_type_name,  _type_name,  @new_view_relation_type_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_code_id,    _code_id,    @new_view_relation_type_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,_description,@new_view_relation_type_id ;

        UPDATE view_relation_types
           SET code_id     = _code_id,
               description = _description
         WHERE view_relation_types.view_relation_type_id = @new_view_relation_type_id;

END;

PREPARE view_relation_type_insert_log_1111_call
    FROM 'SELECT view_relation_type_insert_log_1111 (?,?,?,?,?,?,?,?)';

SELECT view_relation_type_insert_log_1111
    ('add components',
     1,
     1,
     900,
     901,
     'add_components',
     902,
     'add the components of the child view to the parent view at the start position');
