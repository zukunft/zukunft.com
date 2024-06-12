PREPARE result_standard_main_p4_update_020 FROM
    'UPDATE results_standard_main
        SET numeric_value = ?
      WHERE formula_id = ?
        AND phrase_id_1 = ?
        AND phrase_id_2 = ?
        AND phrase_id_3 = ?
        AND phrase_id_4 = ?
        AND phrase_id_5 = ?
        AND phrase_id_6 = ?
        AND phrase_id_7 = ?';