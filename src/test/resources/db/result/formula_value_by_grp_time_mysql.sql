PREPARE formula_value_by_grp_time FROM
   'SELECT formula_value_id,
           formula_id,
           user_id,
           source_phrase_group_id,
           source_time_id,
           phrase_group_id,
           formula_value,
           last_update,
           dirty
      FROM formula_values
     WHERE phrase_group_id = ?';