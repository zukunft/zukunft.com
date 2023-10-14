PREPARE result_by_frm_grp (int, int) AS
    SELECT group_id,
           formula_id,
           user_id,
           source_group_id,
           result,
           last_update,
           dirty
      FROM results
     WHERE formula_id = $1
       AND group_id = $2;