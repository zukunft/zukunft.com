DROP PROCEDURE IF EXISTS view_insert_log_0111551111;
CREATE PROCEDURE view_insert_log_0111551111
    (_view_name               text,
     _user_id                 bigint,
     _change_action_id        smallint,
     _field_id_view_name      smallint,
     _field_id_user_id        smallint,
     _field_id_description    smallint,
     _description             text,
     _field_id_view_type_id   smallint,
     _type_name               text,
     _view_type_id            smallint,
     _field_id_view_style_id  smallint,
     _view_style_name         text,
     _view_style_id           smallint,
     _field_id_code_id        smallint,
     _code_id                 text,
     _field_id_excluded       smallint,
     _excluded                smallint,
     _field_id_share_type_id  smallint,
     _share_type_id           smallint,
     _field_id_protect_id     smallint,
     _protect_id              smallint)
BEGIN

    INSERT INTO views ( view_name)
         SELECT        _view_name ;

    SELECT LAST_INSERT_ID() AS @new_view_id;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,                     row_id)
         SELECT          _user_id,_change_action_id,_field_id_view_name,  _view_name,               @new_view_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,                     row_id)
         SELECT          _user_id,_change_action_id,_field_id_user_id,    _user_id,                 @new_view_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,                     row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,_description,             @new_view_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,        new_value, new_id,            row_id)
         SELECT          _user_id,_change_action_id,_field_id_view_type_id, _type_name,_view_type_id,@new_view_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,        new_value,  new_id,      row_id)
         SELECT          _user_id,_change_action_id,_field_id_view_style_id,_view_style_name,_view_style_id,@new_view_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,       new_value,                    row_id)
         SELECT          _user_id,_change_action_id,_field_id_code_id,     _code_id,                @new_view_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,        new_value,                    row_id)
         SELECT          _user_id,_change_action_id,_field_id_excluded,     _excluded,               @new_view_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,        new_value,                    row_id)
         SELECT          _user_id,_change_action_id,_field_id_share_type_id,_share_type_id,          @new_view_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,        new_value,                    row_id)
         SELECT          _user_id,_change_action_id,_field_id_protect_id,   _protect_id,             @new_view_id ;

    UPDATE views
       SET user_id        = _user_id,
           description    = _description,
           view_type_id   = _view_type_id,
           view_style_id  = _view_style_id,
           code_id        = _code_id,
           excluded       = _excluded,
           share_type_id  = _share_type_id,
           protect_id     = _protect_id
     WHERE views.view_id = @new_view_id;

END;

PREPARE view_insert_log_0111551111_call FROM
    'SELECT view_insert_log_0111551111 (?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

SELECT view_insert_log_0111551111 (
               'Start view',
               1,
               1,
               42,
               278,
               43,
               'A dynamic entry mask that initially shows a table for calcalations with the biggest problems from the user point of view and suggestions what the user can do to solve these problems. Used also as fallback view.',
               45,
               'details',
               6,
               777,
               'col-md-4',
               1,
               44,
               'entry_view',
               72,
               1,
               131,
               3,
               132,
               2);