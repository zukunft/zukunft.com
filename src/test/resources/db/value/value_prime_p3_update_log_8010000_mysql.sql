DROP PROCEDURE IF EXISTS value_prime_p3_update_log_8010000;
CREATE PROCEDURE value_prime_p3_update_log_8010000
    (_user_id           bigint,
     _change_action_id  smallint,
     _field_id_user_id  smallint,
     _user_name_old     text,
     _user_id_old       bigint,
     _user_name         text,
     _group_id          bigint,
     _phrase_id_1       smallint,
     _phrase_id_2       smallint,
     _phrase_id_3       smallint,
     _phrase_id_4       smallint)

BEGIN

    INSERT INTO change_values_prime ( user_id, change_action_id, change_field_id,  old_value,     new_value, old_id,      new_id,  group_id)
         SELECT                      _user_id,_change_action_id,_field_id_user_id,_user_name_old,_user_name,_user_id_old,_user_id,_group_id ;

    UPDATE values_prime
       SET user_id = _user_id,
           last_update = Now()
     WHERE phrase_id_1 = _phrase_id_1
       AND phrase_id_2 = _phrase_id_2
       AND phrase_id_3 = _phrase_id_3
       AND phrase_id_4 = _phrase_id_4;

END;

PREPARE value_prime_p3_update_log_8010000_call FROM

    'SELECT value_prime_p3_update_log_8010000
       (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

SELECT value_prime_p3_update_log_8010000
       (4,
        1,
        370,
        'zukunft.com system test',
        3,
        'zukunft.com system test partner',
        919135977611,
        214,
        198,
        139,
        0);