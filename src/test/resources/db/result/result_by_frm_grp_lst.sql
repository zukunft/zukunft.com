PREPARE result_by_frm_grp_lst (int, int[]) AS
    SELECT result_id,
           formula_id,
           user_id,
           source_group_id,
           group_id,
           result,
           last_update,
           dirty
      FROM results
     WHERE formula_id = $1
       AND group_id = ANY ($2);