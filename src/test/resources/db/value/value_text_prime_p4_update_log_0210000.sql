CREATE OR REPLACE FUNCTION value_text_prime_p4_update_log_0210000
    (_user_id              bigint,
     _change_action_id     smallint,
     _field_id_text_value  smallint,
     _text_value_old       text,
     _text_value           text,
     _group_id             bigint,
     _phrase_id_1          smallint,
     _phrase_id_2          smallint,
     _phrase_id_3          smallint,
     _phrase_id_4          smallint) RETURNS void AS
$$
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

END
$$ LANGUAGE plpgsql;

PREPARE value_text_prime_p4_update_log_0210000_call
        (bigint, smallint, smallint, text, text, bigint, smallint, smallint, smallint, smallint) AS
    SELECT value_text_prime_p4_update_log_0210000
        ($1,$2, $3, $4, $5, $6, $7, $8, $9, $10);

SELECT value_text_prime_p4_update_log_0210000
       (1::bigint,
        1::smallint,
        421::smallint,
        'old db text sample value'::text,
        'zukunft.com'::text,
        92888138199367808::bigint,
        330::smallint,
        325::smallint,
        312::smallint,
        -128::smallint);
