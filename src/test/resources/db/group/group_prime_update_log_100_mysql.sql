DROP PROCEDURE IF EXISTS group_prime_update_log_100;
CREATE PROCEDURE group_prime_update_log_100
    (_user_id             bigint,
     _change_action_id    smallint,
     _field_id_group_name smallint,
     _group_name          text,
     _group_id            bigint)

BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,     new_value,  row_id)
         SELECT          _user_id,_change_action_id,_field_id_group_name,_group_name,_group_id ;

         UPDATE groups_prime
            SET group_name = _group_name
          WHERE group_id = _group_id;

END;

PREPARE group_prime_update_log_100_call FROM
    'SELECT group_prime_update_log_100 (?,?,?,?,?)';

SELECT group_prime_update_log_100
        (3,
         1,
         320,
         'System Test Group Renamed',
         32770);