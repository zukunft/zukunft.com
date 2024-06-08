PREPARE result_standard_main_p4_update_010
    (numeric, smallint, smallint, smallint, smallint, smallint, smallint, smallint, smallint) AS
    UPDATE results_standard_main
       SET numeric_value = $1
     WHERE formula_id = $2
       AND phrase_id_1 = $3
       AND phrase_id_2 = $4
       AND phrase_id_3 = $5
       AND phrase_id_4 = $6
       AND phrase_id_5 = $7
       AND phrase_id_6 = $8
       AND phrase_id_7 = $9;