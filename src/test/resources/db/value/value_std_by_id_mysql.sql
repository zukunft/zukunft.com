PREPARE value_std_by_id FROM
    'SELECT
            value_id,
            phrase_group_id,
            time_word_id,
            word_value,
            source_id,
            last_update,
            excluded,
            protection_type_id,
            user_id
       FROM `values`
      WHERE value_id = ?';