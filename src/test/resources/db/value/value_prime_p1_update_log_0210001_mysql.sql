DROP PROCEDURE IF EXISTS value_prime_p1_update_log_0210001;
CREATE PROCEDURE value_prime_p1_update_log_0210001
    (_user_id                 bigint,
     _change_action_id        smallint,
     _field_id_numeric_value  smallint,
     _numeric_value_old       numeric,
     _numeric_value           numeric,
     _group_id                bigint,
     _field_id_protect_id     smallint,
     _protect_id              smallint,
     _phrase_id_1             smallint,
     _phrase_id_2             smallint,
     _phrase_id_3             smallint,
     _phrase_id_4             smallint)

BEGIN

    INSERT INTO change_values_prime ( user_id, change_action_id, change_field_id,        old_value,         new_value,     group_id)
         SELECT                      _user_id,_change_action_id,_field_id_numeric_value,_numeric_value_old,_numeric_value,_group_id ;
    INSERT INTO change_values_prime ( user_id, change_action_id, change_field_id,     new_value,  group_id)
         SELECT                      _user_id,_change_action_id,_field_id_protect_id,_protect_id,_group_id ;

    UPDATE values_prime
       SET numeric_value = _numeric_value,
           protect_id    = _protect_id,
           last_update = Now()
     WHERE phrase_id_1 = _phrase_id_1
       AND phrase_id_2 = _phrase_id_2
       AND phrase_id_3 = _phrase_id_3
       AND phrase_id_4 = _phrase_id_4;

END;

PREPARE value_prime_p1_update_log_0210001_call FROM

    'SELECT value_prime_p1_update_log_0210001
       (?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

SELECT value_prime_p1_update_log_0210001
       (3,
        1,
        1,
        3.1415926535898,
        123.456,
        32770,
        4,
        2,
        -2,
        0,
        0,
        0);