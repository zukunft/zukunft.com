PREPARE result_big_by_id (text) AS
    SELECT group_id, formula_id, user_id, source_group_id, numeric_value, last_update
      FROM results_big
     WHERE group_id = $1;