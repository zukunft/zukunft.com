PREPARE result_main_p7_update_0111111
    (numeric, bigint, smallint, smallint, smallint, smallint, smallint, smallint, smallint, smallint, smallint, smallint, smallint) AS
    UPDATE results_main
       SET numeric_value   = $1,
           last_update     = Now(),
           source_group_id = $2,
           excluded        = $3,
           share_type_id   = $4,
           protect_id      = $5
     WHERE formula_id = $6
       AND phrase_id_1 = $7
       AND phrase_id_2 = $8
       AND phrase_id_3 = $9
       AND phrase_id_4 = $10
       AND phrase_id_5 = $11
       AND phrase_id_6 = $12
       AND phrase_id_7 = $13;