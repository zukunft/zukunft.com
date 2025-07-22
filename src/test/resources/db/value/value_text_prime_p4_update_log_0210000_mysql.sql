DROP PROCEDURE IF EXISTS value_text_prime_p4_update_log_0210000;
CREATE PROCEDURE value_text_prime_p4_update_log_0210000
    (_user_id              bigint,
     _change_action_id     smallint,
     _field_id_text_value  smallint,
     _text_value_old       text,
     _text_value           text,
     _group_id             bigint,
     _phrase_id_1          smallint,
     _phrase_id_2          smallint,
     _phrase_id_3          smallint,
     _phrase_id_4          smallint)

BEGIN

    INSERT INTO change_values_text_prime ( user_id, change_action_id, change_field_id,        old_value,         new_value,     group_id)
         SELECT                           _user_id,_change_action_id,_field_id_text_value,_text_value_old,_text_value,_group_id ;

    UPDATE values_text_prime
       SET text_value = _text_value,
           last_update = Now()
     WHERE phrase_id_1 = _phrase_id_1
       AND phrase_id_2 = _phrase_id_2
       AND phrase_id_3 = _phrase_id_3
       AND phrase_id_4 = _phrase_id_4;

END;

PREPARE value_text_prime_p4_update_log_0210000_call FROM

    'SELECT value_text_prime_p4_update_log_0210000
       (?,?, ?, ?, ?, ?, ?, ?, ?, ?)';

SELECT value_text_prime_p4_update_log_0210000
       (1,
        1,
        421,
        'old db text sample value',
        'zukunft.com',
        93169617471111306,
        331,
        326,
        313,
        -138);