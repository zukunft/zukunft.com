CREATE OR REPLACE FUNCTION user_update_log_1200040000000
    (_user_id                  bigint,
     _change_action_id         smallint,
     _field_id_user_name       smallint,
     _user_name_old            text,
     _user_name                text,
     _field_id_ip_address      smallint,
     _ip_address_old           text,
     _ip_address               text,
     _field_id_user_profile_id smallint,
     _type_name_old            text,
     _user_profile_id_old      smallint,
     _type_name                text,
     _user_profile_id          smallint) RETURNS void AS
$$

BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,    old_value,     new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_user_name,_user_name_old,_user_name,_user_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,     old_value,      new_value,  row_id)
         SELECT          _user_id,_change_action_id,_field_id_ip_address,_ip_address_old,_ip_address,_user_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,          old_value,     new_value, old_id,              new_id,          row_id)
         SELECT          _user_id,_change_action_id,_field_id_user_profile_id,_type_name_old,_type_name,_user_profile_id_old,_user_profile_id,_user_id ;

         UPDATE users
            SET user_name       = _user_name,
                ip_address      = _ip_address,
                user_profile_id = _user_profile_id
          WHERE user_id         = _user_id;

END
$$ LANGUAGE plpgsql;

PREPARE user_update_log_1200040000000_call
    (bigint, smallint, smallint, text, text, smallint, text, text, smallint, text, smallint, text, smallint) AS
SELECT user_update_log_1200040000000
    ($1,$2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13);

SELECT user_update_log_1200040000000
        (2::bigint,
         2::smallint,
         211::smallint,
         null::text,
         'zukunft.com system write test user changed'::text,
         75::smallint,
         '258.257.256.255'::text,
         null::text,
         81::smallint,
         'ip only'::text,
         1::smallint,
         null::text,
         null::smallint);
