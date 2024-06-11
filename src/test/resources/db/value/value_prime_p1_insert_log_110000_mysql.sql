DROP PROCEDURE IF EXISTS value_prime_p1_insert_log_110000;
CREATE PROCEDURE value_prime_p1_insert_log_110000
    (_user_id                 bigint,
     _change_action_id        smallint,
     _field_id_numeric_value  smallint,
     _numeric_value           numeric,
     _group_id                bigint,
     _phrase_id_1             smallint,
     _phrase_id_2             smallint,
     _phrase_id_3             smallint,
     _phrase_id_4             smallint)

BEGIN

    INSERT INTO change_values_prime ( user_id, change_action_id, change_field_id,        new_value,     group_id)
         SELECT                      _user_id,_change_action_id,_field_id_numeric_value,_numeric_value,_group_id ;

    INSERT INTO values_prime (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, user_id, numeric_value, last_update)
         SELECT              _phrase_id_1,_phrase_id_2,_phrase_id_3,_phrase_id_4,_user_id,_numeric_value,     Now();

END;

PREPARE value_prime_p1_insert_log_110000_call FROM

    'SELECT value_prime_p1_insert_log_110000
       (?,?, ?, ?, ?, ?, ?, ?, ?)';

SELECT value_prime_p1_insert_log_110000
       (1,
        1,
        1,
        3.1415926535898,
        5,
        -2,
        null,
        null,
        null);