PREPARE result_by_grp (text) AS
    SELECT group_id,
           formula_id,
           user_id,
           source_group_id,
           numeric_value,
           last_update
      FROM results
     WHERE group_id = $1;