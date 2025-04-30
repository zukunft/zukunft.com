CREATE OR REPLACE FUNCTION value_text_prime_p4_insert_log_11000_user
    (_user_id              bigint,
     _change_action_id     smallint,
     _field_id_text_value  smallint,
     _text_value           text,
     _group_id             bigint,
     _phrase_id_1          smallint,
     _phrase_id_2          smallint,
     _phrase_id_3          smallint,
     _phrase_id_4          smallint,
     _source_id            bigint) RETURNS void AS
$$
BEGIN

    INSERT INTO change_values_text_prime ( user_id, change_action_id, change_field_id,        new_value,     group_id)
         SELECT                            _user_id,_change_action_id,_field_id_text_value,_text_value,_group_id ;

    INSERT INTO user_values_text_prime (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, user_id, source_id, text_value, last_update)
         SELECT                        _phrase_id_1,_phrase_id_2,_phrase_id_3,_phrase_id_4,_user_id, _source_id,_text_value, Now();

END
$$ LANGUAGE plpgsql;

PREPARE value_text_prime_p4_insert_log_11000_user_call
        (bigint, smallint, smallint, text, bigint, smallint, smallint, smallint, smallint, bigint) AS
    SELECT value_text_prime_p4_insert_log_11000_user
        ($1,$2, $3, $4, $5, $6, $7, $8, $9, $10);

SELECT value_text_prime_p4_insert_log_11000_user
       (1::bigint,
        1::smallint,
        421::smallint,
        'zukunft.com'::text,
        88947428394958964::bigint,
        316::smallint,
        311::smallint,
        298::smallint,
        -116::smallint,
        null::bigint);
