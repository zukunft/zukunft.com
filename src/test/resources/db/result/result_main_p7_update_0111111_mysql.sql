PREPARE result_main_p7_update_0111111 FROM
    'UPDATE results_main
        SET numeric_value   = ?,
            last_update     = Now(),
            source_group_id = ?,
            excluded        = ?,
            share_type_id   = ?,
            protect_id      = ?
      WHERE formula_id = ?
        AND phrase_id_1 = ?
        AND phrase_id_2 = ?
        AND phrase_id_3 = ?
        AND phrase_id_4 = ?
        AND phrase_id_5 = ?
        AND phrase_id_6 = ?
        AND phrase_id_7 = ?';