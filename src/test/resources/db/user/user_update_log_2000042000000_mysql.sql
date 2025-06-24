DROP PROCEDURE IF EXISTS user_update_log_2000042000000;
CREATE PROCEDURE user_update_log_2000042000000
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
     _email                    text)

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

END;

PREPARE user_update_log_2000042000000_call FROM
    'SELECT user_update_log_2000042000000 (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

SELECT user_update_log_2000042000000
        (3,
         2,
         211,
         'zukunft.com system test',
         'zukunft.com system test partner',
         81,
         'system test',
         2,
         null,
         null,
         76,
         'test@zukunft.com',
         null);