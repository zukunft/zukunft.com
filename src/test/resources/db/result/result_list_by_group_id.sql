PREPARE result_list_by_group_id (bigint) AS
    SELECT group_id,
           formula_id,
           user_id,
           source_group_id,
           numeric_value,
           last_update
      FROM results
     WHERE group_id = $1;