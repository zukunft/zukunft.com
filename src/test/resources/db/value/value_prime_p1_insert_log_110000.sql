CREATE OR REPLACE FUNCTION value_prime_p1_insert_log_110000
    (_user_id                 bigint,
     _change_action_id        smallint,
     _field_id_numeric_value  smallint,
     _numeric_value           numeric,
     _group_id                bigint,
     _phrase_id_1             smallint,
     _phrase_id_2             smallint,
     _phrase_id_3             smallint,
     _phrase_id_4             smallint) RETURNS void AS
$$
BEGIN

    INSERT INTO change_values_prime ( user_id, change_action_id, change_field_id,        new_value,     group_id)
         SELECT                      _user_id,_change_action_id,_field_id_numeric_value,_numeric_value,_group_id ;

    INSERT INTO values_prime (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, user_id, numeric_value, last_update)
         SELECT              _phrase_id_1,_phrase_id_2,_phrase_id_3,_phrase_id_4,_user_id,_numeric_value, Now();

END
$$ LANGUAGE plpgsql;

PREPARE value_prime_p1_insert_log_110000_call
        (bigint, smallint, smallint, numeric, bigint, smallint, smallint, smallint, smallint) AS
    SELECT value_prime_p1_insert_log_110000
        ($1,$2, $3, $4, $5, $6, $7, $8, $9);

SELECT value_prime_p1_insert_log_110000
       (1::bigint,
        1::smallint,
        1::smallint,
        3.1415926535898::numeric,
        32770::bigint,
        -2::smallint,
        null::smallint,
        null::smallint,
        null::smallint);
