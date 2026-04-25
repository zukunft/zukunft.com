CREATE OR REPLACE FUNCTION ip_range_update_log_111110
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
     _reason                text) RETURNS void AS
$$
BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,       old_value,        new_value,    row_id)
         SELECT          _user_id,_change_action_id,_field_id_ip_range_key,_ip_range_key_old,_ip_range_key,_ip_range_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,  old_value,   new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_ip_from,_ip_from_old,_ip_from,  _ip_range_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id, old_value, new_value,  row_id)
         SELECT          _user_id,_change_action_id,_field_id_ip_to, _ip_to_old,_ip_to,     _ip_range_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id, old_value,  new_value,  row_id)
         SELECT          _user_id,_change_action_id,_field_id_reason,_reason_old,_reason,    _ip_range_id ;

         UPDATE ip_ranges
            SET ip_range_key = _ip_range_key,
                ip_from      = _ip_from,
                ip_to        = _ip_to,
                reason       = _reason
          WHERE ip_range_id = _ip_range_id;

END
$$ LANGUAGE plpgsql;

PREPARE ip_range_update_log_111110_call
    (bigint, smallint, smallint, text, text, bigint, smallint, text, text, smallint, text, text, smallint, text, text) AS
SELECT ip_range_update_log_111110
    ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14, $15);

SELECT ip_range_update_log_111110
        (1::bigint,
         1::smallint,
         859::smallint,
         null::text,
         '66.249.64.95-66.249.64.95'::text,
         0::bigint,
         185::smallint,
         null::text,
         '66.249.64.95'::text,
         186::smallint,
         null::text,
         '66.249.64.95'::text,
         187::smallint,
         null::text,
         'too much damage from this IP'::text);
