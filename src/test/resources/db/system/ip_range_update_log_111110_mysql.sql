DROP PROCEDURE IF EXISTS ip_range_update_log_111110;
CREATE PROCEDURE ip_range_update_log_111110
    (_user_id               bigint,
     _change_action_id      smallint,
     _field_id_ip_range_key smallint,
     _ip_range_key_old      text,
     _ip_range_key          text,
     _ip_range_id           bigint,
     _field_id_ip_from      smallint,
     _ip_from_old           text,
     _ip_from               text,
     _field_id_ip_to        smallint,
     _ip_to_old             text,
     _ip_to                 text,
     _field_id_reason       smallint,
     _reason_old            text,
     _reason                text)
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,       old_value,        new_value,    row_id)
         SELECT          _user_id,_change_action_id,_field_id_ip_range_key,_ip_range_key_old,_ip_range_key,_ip_range_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,  old_value,   new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_ip_from,_ip_from_old,_ip_from,  _ip_range_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,old_value, new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_ip_to,_ip_to_old,_ip_to,    _ip_range_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id, old_value,  new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_reason,_reason_old,_reason,   _ip_range_id ;

    UPDATE ip_ranges
       SET ip_range_key = _ip_range_key,
           ip_from      = _ip_from,
           ip_to        = _ip_to,
           reason       = _reason
     WHERE ip_range_id = _ip_range_id;

END;

PREPARE ip_range_update_log_111110_call FROM
    'SELECT ip_range_update_log_111110 (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

SELECT ip_range_update_log_111110
    (1,
     1,
     918,
     null,
     '66.249.64.95-66.249.64.95',
     0,
     185,
     null,
     '66.249.64.95',
     186,
     null,
     '66.249.64.95',
     187,
     null,
     'too much damage from this IP');