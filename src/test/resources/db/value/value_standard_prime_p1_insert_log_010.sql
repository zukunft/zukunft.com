CREATE OR REPLACE FUNCTION value_standard_prime_p1_insert_log_010
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

    INSERT INTO values_standard_prime (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, numeric_value)
         SELECT                       _phrase_id_1,_phrase_id_2,_phrase_id_3,_phrase_id_4,_numeric_value ;

END
$$ LANGUAGE plpgsql;

PREPARE value_standard_prime_p1_insert_log_010_call
        (bigint, smallint, smallint, numeric, bigint, smallint, smallint, smallint, smallint) AS
    SELECT value_standard_prime_p1_insert_log_010
        ($1,$2, $3, $4, $5, $6, $7, $8, $9);

SELECT value_standard_prime_p1_insert_log_010
       (1::bigint,
        1::smallint,
        1::smallint,
        3.1415926535898::numeric,
        32770::bigint,
        -2::smallint,
        null::smallint,
        null::smallint,
        null::smallint);
