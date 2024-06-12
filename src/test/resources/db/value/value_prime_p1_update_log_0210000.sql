CREATE OR REPLACE FUNCTION value_prime_p1_update_log_0210000
    (_user_id                 bigint,
     _change_action_id        smallint,
     _field_id_numeric_value  smallint,
     _numeric_value_old       numeric,
     _numeric_value           numeric,
     _group_id                bigint,
     _phrase_id_1             smallint,
     _phrase_id_2             smallint,
     _phrase_id_3             smallint,
     _phrase_id_4             smallint) RETURNS void AS
$$
BEGIN

    INSERT INTO change_values_prime ( user_id, change_action_id, change_field_id,        old_value,         new_value,     group_id)
         SELECT                      _user_id,_change_action_id,_field_id_numeric_value,_numeric_value_old,_numeric_value,_group_id ;

    UPDATE values_prime
       SET numeric_value = _numeric_value,
           last_update = Now()
     WHERE phrase_id_1 = _phrase_id_1
       AND phrase_id_2 = _phrase_id_2
       AND phrase_id_3 = _phrase_id_3
       AND phrase_id_4 = _phrase_id_4;

END
$$ LANGUAGE plpgsql;

PREPARE value_prime_p1_update_log_0210000_call
        (bigint, smallint, smallint, numeric, numeric, bigint, smallint, smallint, smallint, smallint) AS
    SELECT value_prime_p1_update_log_0210000
        ($1,$2, $3, $4, $5, $6, $7, $8, $9, $10);

SELECT value_prime_p1_update_log_0210000
       (1::bigint,
        1::smallint,
        1::smallint,
        123.456::numeric,
        3.1415926535898::numeric,
        5::bigint,
        -2::smallint,
        null::smallint,
        null::smallint,
        null::smallint);
