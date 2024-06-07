PREPARE result_main_p4_update_0111000 FROM
    'UPDATE results_main
        SET numeric_value   = ?,
            last_update     = Now(),
            source_group_id = ?
      WHERE formula_id = ?
        AND phrase_id_1 = ?
        AND phrase_id_2 = ?
        AND phrase_id_3 = ?
        AND phrase_id_4 = ?';