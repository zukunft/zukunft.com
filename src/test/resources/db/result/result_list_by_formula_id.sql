PREPARE result_list_by_formula_id (int) AS
    SELECT group_id,
           formula_id,
           user_id,
           source_group_id,
           result,
           last_update,
           dirty
      FROM results
     WHERE formula_id = $1;