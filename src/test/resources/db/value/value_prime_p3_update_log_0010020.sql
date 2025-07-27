CREATE OR REPLACE FUNCTION value_prime_p3_update_log_0010020
    (_user_id                 bigint,
     _change_action_id        smallint,
     _field_id_share_type_id  smallint,
     _share_type_id_old       smallint,
     _share_type_id           smallint,
     _group_id                bigint,
     _phrase_id_1             smallint,
     _phrase_id_2             smallint,
     _phrase_id_3             smallint,
     _phrase_id_4             smallint) RETURNS void AS
$$
BEGIN

    INSERT INTO change_values_prime ( user_id, change_action_id, change_field_id,        old_value,         group_id)
         SELECT                      _user_id,_change_action_id,_field_id_share_type_id,_share_type_id_old,_group_id ;

    UPDATE values_prime
       SET share_type_id = _share_type_id,
           last_update = Now()
     WHERE phrase_id_1 = _phrase_id_1
       AND phrase_id_2 = _phrase_id_2
       AND phrase_id_3 = _phrase_id_3
       AND phrase_id_4 = _phrase_id_4;

END
$$ LANGUAGE plpgsql;

PREPARE value_prime_p3_update_log_0010020_call
        (bigint, smallint, smallint, smallint, smallint, bigint, smallint, smallint, smallint, smallint) AS
    SELECT value_prime_p3_update_log_0010020
        ($1,$2, $3, $4, $5, $6, $7, $8, $9, $10);

SELECT value_prime_p3_update_log_0010020
       (1::bigint,
        1::smallint,
        3::smallint,
        3::smallint,
        null::smallint,
        1163953635467::bigint,
        271::smallint,
        267::smallint,
        139::smallint,
        0::smallint);
