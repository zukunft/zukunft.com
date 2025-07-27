DROP PROCEDURE IF EXISTS user_delete_log;
CREATE PROCEDURE user_delete_log
    (_req_user_id        bigint,
     _change_action_id   smallint,
     _field_id_user_name smallint,
     _user_name          text,
     _user_id            bigint)

BEGIN

    INSERT INTO changes ( user_id,     change_action_id, change_field_id,    old_value, row_id)
         SELECT          _req_user_id,_change_action_id,_field_id_user_name,_user_name,_user_id ;

    DELETE
      FROM users
     WHERE user_id = _user_id;

END;

SELECT user_delete_log
       (2,
        3,
        211,
        'zukunft.com system test',
        3);