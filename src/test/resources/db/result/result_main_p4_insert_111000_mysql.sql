PREPARE result_main_p4_insert_111000 FROM
    'INSERT INTO results_main (formula_id, phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4, user_id, numeric_value, last_update, source_group_id)
          VALUES (?,?,?,?,?,?,?,Now(),?)';