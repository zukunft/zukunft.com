PREPARE result_by_frm_grp_lst (int, int[]) AS
    SELECT result_id,
           formula_id,
           user_id,
           source_phrase_group_id,
           source_time_id,
           phrase_group_id,
           result,
           last_update,
           dirty
      FROM results
     WHERE formula_id = $1
       AND phrase_group_id = ANY ($2);