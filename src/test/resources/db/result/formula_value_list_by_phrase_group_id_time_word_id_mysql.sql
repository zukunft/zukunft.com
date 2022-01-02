PREPARE formula_value_list_by_phrase_group_id_time_word_id FROM
   'SELECT formula_value_id,
           formula_id,
           user_id,
           source_phrase_group_id,
           source_time_id,
           phrase_group_id,
           time_word_id,
           formula_value,
           last_update,
           dirty
      FROM formula_values
     WHERE phrase_group_id = ?
       AND time_word_id = ?';