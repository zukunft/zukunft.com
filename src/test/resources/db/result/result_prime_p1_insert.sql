PREPARE result_prime_p1_insert (bigint,bigint,numeric,bigint,bigint) AS
    INSERT INTO results_prime (phrase_id_1,user_id,numeric_value,last_update,formula_id,source_group_id)
         VALUES ($1,$2,$3,Now(),$4,$5)
      RETURNING phrase_id_1,phrase_id_2,phrase_id_3,phrase_id_4;