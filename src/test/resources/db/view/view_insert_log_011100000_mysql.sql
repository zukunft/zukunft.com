DROP PROCEDURE IF EXISTS view_insert_log_011100000;
CREATE PROCEDURE view_insert_log_011100000
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

PREPARE view_insert_log_011100000_call FROM
    'SELECT view_insert_log_011100000 (?,?, ?, ?, ?, ?, ?)';

SELECT view_insert_log_011100000 (
               'Word',
               1,
               1,
               42,
               278,
               43,
               'the default view for words');