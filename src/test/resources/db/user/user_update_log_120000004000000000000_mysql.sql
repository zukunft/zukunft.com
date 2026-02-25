DROP PROCEDURE IF EXISTS user_update_log_120000004000000000000;
CREATE PROCEDURE user_update_log_120000004000000000000
    (_user_id                  bigint,
     _change_action_id         smallint,
     _field_id_user_name       smallint,
     _user_name_old            text,
     _user_name                text,
     _field_id_ip_address      smallint,
     _ip_address_old           text,
     _ip_address               text,
     _field_id_user_profile_id smallint,
     _user_profile_name_old    text,
     _user_profile_id_old      smallint,
     _user_profile_name        text,
     _user_profile_id          smallint)

BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,    old_value,     new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_user_name,_user_name_old,_user_name,_user_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,     old_value,      new_value,  row_id)
         SELECT          _user_id,_change_action_id,_field_id_ip_address,_ip_address_old,_ip_address,_user_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,          old_value,             new_value,         old_id,              new_id,          row_id)
         SELECT          _user_id,_change_action_id,_field_id_user_profile_id,_user_profile_name_old,_user_profile_name,_user_profile_id_old,_user_profile_id,_user_id ;

    UPDATE users
       SET user_name       = _user_name,
           ip_address      = _ip_address,
           user_profile_id = _user_profile_id
     WHERE user_id         = _user_id;

END;

PREPARE user_update_log_120000004000000000000_call FROM
    'SELECT user_update_log_120000004000000000000 (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

SELECT user_update_log_120000004000000000000
        (2,
         2,
         211,
         null,
         'zukunft.com system write test user changed',
         75,
         '258.257.256.255',
         null,
         81,
         'ip only',
         1,
         null,
         null);