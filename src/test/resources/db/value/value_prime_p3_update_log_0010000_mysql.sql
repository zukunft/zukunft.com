DROP PROCEDURE IF EXISTS value_prime_p3_update_log_0010000;
CREATE PROCEDURE value_prime_p3_update_log_0010000
    (_phrase_id_1 smallint,
     _phrase_id_2 smallint,
     _phrase_id_3 smallint,
     _phrase_id_4 smallint)
BEGIN

    UPDATE values_prime
       SET last_update = Now()
     WHERE phrase_id_1 = _phrase_id_1
       AND phrase_id_2 = _phrase_id_2
       AND phrase_id_3 = _phrase_id_3
       AND phrase_id_4 = _phrase_id_4;

END;

PREPARE value_prime_p3_update_log_0010000_call FROM
    'SELECT value_prime_p3_update_log_0010000 (?,?,?,?)';

SELECT value_prime_p3_update_log_0010000
       (228,
        212,
        135,
        0);