PREPARE result_list_by_source_group_id (bigint) AS
    SELECT group_id,
           formula_id,
           user_id,
           source_group_id,
           result,
           last_update,
           dirty
      FROM results
     WHERE source_group_id = $1;