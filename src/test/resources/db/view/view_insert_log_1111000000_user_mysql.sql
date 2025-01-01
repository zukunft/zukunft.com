DROP PROCEDURE IF EXISTS view_insert_log_1111000000_user;
CREATE PROCEDURE view_insert_log_1111000000_user
    (_user_id                 bigint,
     _change_action_id        smallint,
     _field_id_view_name      smallint,
     _view_name               text,
     _view_id                 bigint,
     _field_id_description    smallint,
     _description             text)
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,    new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_view_name,_view_name,_view_id ;
    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,_description,_view_id ;

    INSERT INTO user_views
                (view_id, user_id, view_name, description)
         SELECT _view_id,_user_id,_view_name,_description ;

END;

PREPARE view_insert_log_1111000000_user_call FROM
    'SELECT view_insert_log_1111000000_user (?,?,?,?,?,?,?)';

SELECT view_insert_log_1111000000_user (
               1,
               1,
               42,
               'Start view',
               1,
               43,
               'A dynamic entry mask that initially shows a table for calculations with the biggest problems from the user point of view and suggestions what the user can do to solve these problems. Used also as fallback view.');