CREATE OR REPLACE FUNCTION value_prime_p3_update_log_0010000
    (_phrase_id_1 smallint,
     _phrase_id_2 smallint,
     _phrase_id_3 smallint,
     _phrase_id_4 smallint) RETURNS void AS
$$
BEGIN

    UPDATE values_prime
       SET last_update = Now()
     WHERE phrase_id_1 = _phrase_id_1
       AND phrase_id_2 = _phrase_id_2
       AND phrase_id_3 = _phrase_id_3
       AND phrase_id_4 = _phrase_id_4;

END
$$ LANGUAGE plpgsql;

PREPARE value_prime_p3_update_log_0010000_call
        (smallint, smallint, smallint, smallint) AS
SELECT value_prime_p3_update_log_0010000
        ($1,$2,$3,$4);

SELECT value_prime_p3_update_log_0010000
       (228::smallint,
        212::smallint,
        135::smallint,
        0::smallint);