CREATE OR REPLACE FUNCTION value_prime_p1_delete_log_user
    (_user_id                bigint,
     _change_action_id       smallint,
     _field_id_numeric_value smallint,
     _group_id               bigint,
     _phrase_id_1            smallint,
     _phrase_id_2            smallint,
     _phrase_id_3            smallint,
     _phrase_id_4            smallint,
     _source_id              bigint) RETURNS void AS
$$
BEGIN

    INSERT INTO change_values_prime ( user_id, change_action_id, change_field_id,        group_id)
         SELECT                      _user_id,_change_action_id,_field_id_numeric_value,_group_id ;

    DELETE
      FROM user_values_prime
     WHERE phrase_id_1 = _phrase_id_1
       AND phrase_id_2 = _phrase_id_2
       AND phrase_id_3 = _phrase_id_3
       AND phrase_id_4 = _phrase_id_4
       AND user_id = _user_id
       AND source_id = _source_id;

END
$$ LANGUAGE plpgsql;

SELECT value_prime_p1_delete_log_user
       (1::bigint,
        3::smallint,
        1::smallint,
        32819::bigint,
        -51::smallint,
        0::smallint,
        0::smallint,
        0::smallint,
        null::bigint);
