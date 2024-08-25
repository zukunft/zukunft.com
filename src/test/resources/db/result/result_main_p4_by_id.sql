PREPARE result_main_p4_by_id (bigint, bigint, bigint, bigint, bigint, bigint, bigint) AS
    SELECT formula_id,
           phrase_id_1,
           phrase_id_2,
           phrase_id_3,
           phrase_id_4,
           phrase_id_5,
           phrase_id_6,
           phrase_id_7,
           user_id,
           source_group_id,
           numeric_value,
           last_update
    FROM results_main
    WHERE phrase_id_1 = $1
      AND phrase_id_2 = $2
      AND phrase_id_3 = $3
      AND phrase_id_4 = $4
      AND phrase_id_5 = $5
      AND phrase_id_6 = $6
      AND phrase_id_7 = $7;
