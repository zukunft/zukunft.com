CREATE OR REPLACE FUNCTION user_update_log_2000042000000
    (_user_id                  bigint,
     _change_action_id         smallint,
     _field_id_user_name       smallint,
     _user_name_old            text,
     _user_name                text,
     _field_id_user_profile_id smallint,
     _type_name_old            text,
     _user_profile_id_old      smallint,
     _type_name                text,
     _user_profile_id          smallint,
     _field_id_email           smallint,
     _email_old                text,
     _email                    text) RETURNS void AS
$$

BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,    old_value,     new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_user_name,_user_name_old,_user_name,_user_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,          old_value,     new_value, old_id,              new_id,          row_id)
         SELECT          _user_id,_change_action_id,_field_id_user_profile_id,_type_name_old,_type_name,_user_profile_id_old,_user_profile_id,_user_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,old_value, new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_email,_email_old,_email,    _user_id ;

         UPDATE users
            SET user_name       = _user_name,
                user_profile_id = _user_profile_id,
                email           = _email
          WHERE user_id         = _user_id;

END
$$ LANGUAGE plpgsql;

PREPARE user_update_log_2000042000000_call
    (bigint, smallint, smallint, text, text, smallint, text, smallint, text, smallint, smallint, text, text) AS
SELECT user_update_log_2000042000000
    ($1,$2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13);

SELECT user_update_log_2000042000000
        (3::bigint,
         2::smallint,
         211::smallint,
         'zukunft.com system test'::text,
         'zukunft.com system test partner'::text,
         81::smallint,
         'system test'::text,
         2::smallint,
         null::text,
         null::smallint,
         76::smallint,
         'test@zukunft.com'::text,
         null::text);
