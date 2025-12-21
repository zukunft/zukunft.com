DROP PROCEDURE IF EXISTS user_delete_log_ip_address;
CREATE PROCEDURE user_delete_log_ip_address
    (_req_user_id        bigint,
     _change_action_id   smallint,
     _field_id_ip_address smallint,
     _ip_address          text,
     _user_id            bigint)

BEGIN

    INSERT INTO changes ( user_id,     change_action_id, change_field_id,     old_value,  row_id)
         SELECT          _req_user_id,_change_action_id,_field_id_ip_address,_ip_address,_user_id ;

    DELETE
      FROM users
     WHERE user_id = _user_id;

END;

SELECT user_delete_log_ip_address
       (2,
        3,
        75,
        '258.257.256.255',
        0);