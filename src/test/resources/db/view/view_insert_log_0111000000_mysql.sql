DROP PROCEDURE IF EXISTS view_insert_log_0111000000;
CREATE PROCEDURE view_insert_log_0111000000
    (_view_name               text,
     _user_id                 bigint,
     _change_action_id        smallint,
     _field_id_view_name      smallint,
     _field_id_user_id        smallint,
     _field_id_description    smallint,
     _description             text)
BEGIN

    INSERT INTO views ( view_name)
         SELECT        _view_name ;

    SELECT LAST_INSERT_ID() AS @new_view_id;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,    new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_view_name,_view_name,@new_view_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,  new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_user_id,_user_id,  @new_view_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,      new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_description,_description,@new_view_id ;

    UPDATE views
       SET user_id        = _user_id,
           description    = _description
     WHERE views.view_id = @new_view_id;

END;

PREPARE view_insert_log_0111000000_call FROM
    'SELECT view_insert_log_0111000000 (?,?, ?, ?, ?, ?, ?)';

SELECT view_insert_log_0111000000 (
               'Start view',
               1,
               1,
               42,
               278,
               43,
               'A dynamic entry mask that initially shows a table for calculations with the biggest problems from the user point of view and suggestions what the user can do to solve these problems. Used also as fallback view.');