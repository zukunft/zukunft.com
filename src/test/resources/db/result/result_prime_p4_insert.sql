PREPARE result_prime_p4_insert (bigint,bigint,bigint,bigint,bigint,numeric,bigint,bigint) AS
    INSERT INTO results_prime (phrase_id_1,phrase_id_2,phrase_id_3,phrase_id_4,user_id,numeric_value,last_update,formula_id,source_group_id)
         VALUES ($1,$2,$3,$4,$5,$6,Now(),$7,$8)
      RETURNING phrase_id_1,phrase_id_2,phrase_id_3,phrase_id_4;