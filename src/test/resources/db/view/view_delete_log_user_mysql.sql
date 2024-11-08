DROP PROCEDURE IF EXISTS view_delete_log_user;
CREATE PROCEDURE view_delete_log_user
    (_user_id            bigint,
     _change_action_id   smallint,
     _field_id_view_name smallint,
     _view_name          text,
     _view_id            bigint)

BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,    old_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_view_name,_view_name,_view_id ;

    DELETE
      FROM user_views
     WHERE view_id = _view_id
       AND user_id = _user_id;

END;

SELECT view_delete_log_user
       (1,
        3,
        42,
        'Start view',
        1);