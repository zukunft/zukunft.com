CREATE OR REPLACE FUNCTION value_prime_p3_insert_log_11000_user
    (_user_id                 bigint,
     _change_action_id        smallint,
     _field_id_numeric_value  smallint,
     _numeric_value           numeric,
     _group_id                bigint,
     _phrase_id_1             smallint,
     _phrase_id_2             smallint,
     _phrase_id_3             smallint,
     _phrase_id_4             smallint,
     _source_id               bigint) RETURNS void AS
$$
BEGIN

    INSERT INTO change_values_prime ( user_id, change_action_id, change_field_id,        new_value,     group_id)
         SELECT                      _user_id,_change_action_id,_field_id_numeric_value,_numeric_value,_group_id ;

    INSERT INTO user_values_prime (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, user_id, source_id, numeric_value, last_update)
         SELECT                   _phrase_id_1,_phrase_id_2,_phrase_id_3,_phrase_id_4,_user_id, _source_id,_numeric_value, Now();

END
$$ LANGUAGE plpgsql;

PREPARE value_prime_p3_insert_log_11000_user_call
        (bigint, smallint, smallint, numeric, bigint, smallint, smallint, smallint, smallint, bigint) AS
    SELECT value_prime_p3_insert_log_11000_user
        ($1,$2, $3, $4, $5, $6, $7, $8, $9, $10);

SELECT value_prime_p3_insert_log_11000_user
       (1::bigint,
        1::smallint,
        1::smallint,
        3.1415926535898::numeric,
         1163953635467::bigint,
        271::smallint,
        267::smallint,
        139::smallint,
        0::smallint,
        null::bigint);
