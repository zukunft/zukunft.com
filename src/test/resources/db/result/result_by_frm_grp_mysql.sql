PREPARE result_by_frm_grp FROM
   'SELECT result_id,
           formula_id,
           user_id,
           source_phrase_group_id,
           phrase_group_id,
           result,
           last_update,
           dirty
      FROM results
     WHERE formula_id = ?
       AND phrase_group_id = ?';