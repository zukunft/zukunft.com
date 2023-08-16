PREPARE result_by_grp_std (int) AS
    SELECT result_id,
           formula_id,
           user_id,
           source_phrase_group_id,
           phrase_group_id,
           result,
           last_update,
           dirty
      FROM results
     WHERE phrase_group_id = $1;
