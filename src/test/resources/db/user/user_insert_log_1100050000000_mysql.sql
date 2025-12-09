DROP PROCEDURE IF EXISTS user_insert_log_1100050000000;
CREATE PROCEDURE user_insert_log_1100050000000
    (_user_name                text,
     _user_id                  bigint,
     _change_action_id         smallint,
     _field_id_user_name       smallint,
     _field_id_ip_address      smallint,
     _ip_address               text,
     _field_id_user_profile_id smallint,
     _type_name                text,
     _user_profile_id          smallint)

BEGIN

    INSERT INTO users ( user_name)
         SELECT        _user_name ;

    SELECT LAST_INSERT_ID() AS @new_user_id;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,    new_value, row_id)
         SELECT          _user_id,_change_action_id,_field_id_user_name,_user_name, @new_user_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,     new_value,  row_id)
         SELECT          _user_id,_change_action_id,_field_id_ip_address,_ip_address, @new_user_id ;

    INSERT INTO changes ( user_id, change_action_id, change_field_id,          new_value, new_id,          row_id)
         SELECT          _user_id,_change_action_id,_field_id_user_profile_id,_type_name,_user_profile_id,@new_user_id ;

    UPDATE users
            SET ip_address = _ip_address,
                user_profile_id = _user_profile_id
          WHERE users.user_id = @new_user_id;

END;

PREPARE user_insert_log_1100050000000_call FROM
    'SELECT user_insert_log_1100050000000 (?,?, ?, ?, ?, ?, ?, ?, ?)';

SELECT user_insert_log_1100050000000
        ('258.257.256.255',
         2,
         1,
         211,
         75,
         '258.257.256.255',
         81,
         'ip only',
         1);