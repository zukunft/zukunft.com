DROP PROCEDURE IF EXISTS view_update_log_002200000_user;
CREATE PROCEDURE view_update_log_002200000_user
    (_user_id                 bigint,
     _change_action_id        smallint,
     _field_id_view_name      smallint,
     _view_name_old           text,
     _view_name               text,
     _view_id                 bigint,
     _field_id_description    smallint,
     _description_old         text,
     _description             text)
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,    old_value,     new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_view_name,_view_name_old,_view_name,_view_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,      old_value,       new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,_description_old,_description,_view_id ;

    UPDATE user_views
       SET view_name      = _view_name,
           description    = _description
     WHERE view_id = _view_id
       AND user_id = _user_id;

END;

PREPARE view_update_log_002200000_user_call FROM
    'SELECT view_update_log_002200000_user (?,?,?,?,?,?,?,?,?)';

SELECT view_update_log_002200000_user
       (1,
        2,
        42,
        'Word',
        'System Test View Renamed',
        1,
        43,
        'the default view for words',
        null);