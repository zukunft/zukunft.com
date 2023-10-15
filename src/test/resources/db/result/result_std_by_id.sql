PREPARE result_std_by_id (bigint) AS
    SELECT group_id,
           formula_id,
           user_id,
           source_group_id,
           result,
           last_update,
           dirty
      FROM results
     WHERE group_id = $1;