CREATE OR REPLACE FUNCTION ip_range_insert_log_111100
    (_ip_range_key          text,
     _user_id               bigint,
     _change_action_id      smallint,
     _field_id_ip_range_key smallint,
     _field_id_ip_from      smallint,
     _ip_from               text,
     _field_id_ip_to        smallint,
     _ip_to                 text) RETURNS bigint AS
$$
DECLARE new_ip_range_id bigint;
BEGIN

    INSERT INTO ip_ranges ( ip_range_key)
         SELECT            _ip_range_key
      RETURNING             ip_range_id INTO new_ip_range_id;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,       new_value,   row_id)
         SELECT          _user_id,_change_action_id,_field_id_ip_range_key,_ip_range_key,new_ip_range_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,  new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_ip_from,_ip_from,   new_ip_range_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id, new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_ip_to, _ip_to,     new_ip_range_id ;

         UPDATE ip_ranges
            SET ip_from = _ip_from,
                ip_to   = _ip_to
          WHERE ip_ranges.ip_range_id = new_ip_range_id;

         RETURN new_ip_range_id;

END
$$ LANGUAGE plpgsql;

PREPARE ip_range_insert_log_111100_call
    (text, bigint, smallint, smallint, smallint, text, smallint, text) AS
SELECT ip_range_insert_log_111100
    ($1, $2, $3, $4, $5, $6, $7, $8);

SELECT ip_range_insert_log_111100
        ('66.249.64.95-66.249.64.95'::text,
         1::bigint,
         1::smallint,
         918::smallint,
         185::smallint,
         '66.249.64.95'::text,
         186::smallint,
         '66.249.64.95'::text);
