DROP PROCEDURE IF EXISTS ip_range_delete_log;
CREATE PROCEDURE ip_range_delete_log
    (_user_id            bigint,
     _change_action_id   smallint,
     _field_id_ip_range_name smallint,
     _ip_range_name          text,
     _ip_range_id            bigint)

BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,    old_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_ip_range_name,_ip_range_name,_ip_range_id ;

    DELETE
      FROM user_ip_ranges
     WHERE ip_range_id = _ip_range_id
       AND excluded = 1;

    DELETE
      FROM ip_ranges
     WHERE ip_range_id = _ip_range_id;

END;

SELECT ip_range_delete_log
       (3,
        3,
        10,
        'mathematics',
        1);