PREPARE result_main_p4_by_id FROM
    'SELECT formula_id,
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
      WHERE phrase_id_1 = ?
        AND phrase_id_2 = ?
        AND phrase_id_3 = ?
        AND phrase_id_4 = ?
        AND phrase_id_5 = ?
        AND phrase_id_6 = ?
        AND phrase_id_7 = ?';