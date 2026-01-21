DROP PROCEDURE IF EXISTS ip_range_update_log_111110;
CREATE PROCEDURE ip_range_update_log_111110
    (_user_id               bigint,
     _change_action_id      smallint,
     _field_id_ip_range_key smallint,
     _ip_range_key          text,
     _field_id_ip_from      smallint,
     _ip_from               text,
     _field_id_ip_to        smallint,
     _ip_to                 text,
     _field_id_reason       smallint,
     _reason                text)
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,       old_value,        new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_ip_range_key,_ip_range_key_old,_ip_range_key,new_ip_range_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,  old_value,   new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_ip_from,_ip_from_old,_ip_from,   new_ip_range_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,old_value, new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_ip_to,_ip_to_old,_ip_to,     new_ip_range_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id, old_value,  new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_reason,_reason_old,_reason,    new_ip_range_id ;

    UPDATE ip_ranges
       SET ip_range_key = _ip_range_key,
           ip_from      = _ip_from,
           ip_to        = _ip_to,
           reason       = _reason
     WHERE ip_ranges.ip_range_id = @new_ip_range_id;

END;

PREPARE ip_range_update_log_111110_call FROM
    'SELECT ip_range_update_log_111110 (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

SELECT ip_range_update_log_111110
    (1,
     1,
     918,
     '66.249.64.95-66.249.64.95',
     185,
     '66.249.64.95',
     186,
     '66.249.64.95',
     187,
     'too much damage from this IP');