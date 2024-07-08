DROP PROCEDURE IF EXISTS group_prime_delete_log;
CREATE PROCEDURE group_prime_delete_log
    (_user_id bigint,
     _change_action_id smallint,
     _field_id_group_name smallint,
     _group_name text,
     _group_id bigint)

BEGIN

    INSERT INTO changes (user_id,change_action_id,change_field_id,old_value,row_id)
         SELECT _user_id,_change_action_id,_field_id_group_name,_group_name,_group_id ;

    DELETE FROM groups_prime
          WHERE group_id = _group_id;

END;

SELECT group_prime_delete_log
    (1,
     3,
     320,
     'Pi',
     562956395970562);