DROP PROCEDURE IF EXISTS value_prime_p3_update_log_0010020;
CREATE PROCEDURE value_prime_p3_update_log_0010020
    (_user_id                 bigint,
     _change_action_id        smallint,
     _field_id_numeric_value  smallint,
     _numeric_value_old       numeric,
     _numeric_value           numeric,
     _group_id                bigint,
     _phrase_id_1             smallint,
     _phrase_id_2             smallint,
     _phrase_id_3             smallint,
     _phrase_id_4             smallint)

BEGIN

    INSERT INTO changes ( user_id, change_action_id, change_field_id,        old_value,         new_value,     group_id)
         SELECT          _user_id,_change_action_id,_field_id_numeric_value,_numeric_value_old,_numeric_value,_group_id ;

    UPDATE values_prime
       SET numeric_value = _numeric_value,
           last_update = Now()
     WHERE phrase_id_1 = _phrase_id_1
       AND phrase_id_2 = _phrase_id_2
       AND phrase_id_3 = _phrase_id_3
       AND phrase_id_4 = _phrase_id_4;

END;

PREPARE value_prime_p3_update_log_0010020_call FROM

    'SELECT value_prime_p3_update_log_0010020
       (?,?, ?, ?, ?, ?, ?, ?, ?, ?)';

SELECT value_prime_p3_update_log_0010020
       (1,
        1,
        1,
        123.456,
        3.1415926535898,
        32812,
        -44,
        null,
        null,
        null);