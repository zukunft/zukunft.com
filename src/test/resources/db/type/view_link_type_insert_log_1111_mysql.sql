DROP PROCEDURE IF EXISTS view_link_type_insert_log_1111;
CREATE PROCEDURE view_link_type_insert_log_1111
    (_type_name               text,
     _user_id                 bigint,
     _change_action_id        smallint,
     _field_id_type_name      smallint,
     _field_id_code_id        smallint,
     _code_id                 text,
     _field_id_description    smallint,
     _description             text)
BEGIN

    INSERT INTO view_link_types ( type_name)
         SELECT               _type_name ;

         SELECT LAST_INSERT_ID()
             AS @new_view_link_type_id;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_type_name,  _type_name,  @new_view_link_type_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_code_id,    _code_id,    @new_view_link_type_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,_description,@new_view_link_type_id ;

        UPDATE view_link_types
           SET code_id     = _code_id,
               description = _description
         WHERE view_link_types.view_link_type_id = @new_view_link_type_id;

END;

PREPARE view_link_type_insert_log_1111_call
    FROM 'SELECT view_link_type_insert_log_1111 (?,?,?,?,?,?,?,?)';

SELECT view_link_type_insert_log_1111
    ('main word',
     1,
     1,
     732,
     733,
     'main_word',
     734,
     'use the main word as start for the view');
