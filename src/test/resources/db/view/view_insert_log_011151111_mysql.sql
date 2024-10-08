DROP PROCEDURE IF EXISTS view_insert_log_011151111;
CREATE PROCEDURE view_insert_log_011151111
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

    INSERT INTO changes (user_id, change_action_id, change_field_id,        new_value, new_id,            row_id)
         SELECT         _user_id,_change_action_id,_field_id_view_type_id, _type_name,_view_type_id,@new_view_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,       new_value,                    row_id)
         SELECT          _user_id,_change_action_id,_field_id_code_id,     _code_id,                @new_view_id ;

    INSERT INTO changes (user_id, change_action_id, change_field_id,        new_value,                    row_id)
         SELECT         _user_id,_change_action_id,_field_id_excluded,     _excluded,               @new_view_id ;

    INSERT INTO changes (user_id, change_action_id, change_field_id,        new_value,                    row_id)
         SELECT         _user_id,_change_action_id,_field_id_share_type_id,_share_type_id,          @new_view_id ;

    INSERT INTO changes (user_id, change_action_id, change_field_id,        new_value,                    row_id)
         SELECT         _user_id,_change_action_id,_field_id_protect_id,   _protect_id,             @new_view_id ;

    UPDATE views
       SET user_id        = _user_id,
           description    = _description,
           view_type_id   = _view_type_id,
           code_id        = _code_id,
           excluded       = _excluded,
           share_type_id  = _share_type_id,
           protect_id     = _protect_id
     WHERE views.view_id = @new_view_id;

END;

PREPARE view_insert_log_011151111_call FROM
    'SELECT view_insert_log_011151111 (?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

SELECT view_insert_log_011151111 (
               'Word',
               1,
               1,
               42,
               278,
               43,
               'the default view for words',
               45,
               'details',
               6,
               44,
               'word',
               72,
               1,
               131,
               3,
               132,
               2);