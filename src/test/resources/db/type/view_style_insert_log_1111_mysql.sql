DROP PROCEDURE IF EXISTS view_style_insert_log_1111;
CREATE PROCEDURE view_style_insert_log_1111
    (_view_style_name          text,
     _user_id                  bigint,
     _change_action_id         smallint,
     _field_id_view_style_name smallint,
     _field_id_code_id         smallint,
     _code_id                  text,
     _field_id_description     smallint,
     _description              text)
BEGIN

    INSERT INTO view_styles ( view_style_name)
         SELECT               _view_style_name ;

         SELECT LAST_INSERT_ID()
             AS @new_view_style_id;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,          new_value,        row_id)
         SELECT          _user_id,_change_action_id,_field_id_view_style_name,_view_style_name, @new_view_style_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,          new_value,        row_id)
         SELECT          _user_id,_change_action_id,_field_id_code_id,        _code_id,         @new_view_style_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,          new_value,        row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,    _description,     @new_view_style_id ;

        UPDATE view_styles
           SET code_id     = _code_id,
               description = _description
         WHERE view_styles.view_style_id = @new_view_style_id;

END;

PREPARE view_style_insert_log_1111_call
    FROM 'SELECT view_style_insert_log_1111 (?,?,?,?,?,?,?,?)';

SELECT view_style_insert_log_1111
    ('1/3 width',
     1,
     1,
     785,
     786,
     'col-md-4',
     783,
     'use 1/3 of the width (col-md-4)');
