PREPARE result_main_p4_update_0111000
    (numeric, bigint, smallint, smallint, smallint, smallint, smallint, smallint, smallint, smallint) AS
    UPDATE results_main
       SET numeric_value   = $1,
           last_update     = Now(),
           source_group_id = $2
     WHERE formula_id = $3
       AND phrase_id_1 = $4
       AND phrase_id_2 = $5
       AND phrase_id_3 = $6
       AND phrase_id_4 = $7
       AND phrase_id_5 = $8
       AND phrase_id_6 = $9
       AND phrase_id_7 = $10;