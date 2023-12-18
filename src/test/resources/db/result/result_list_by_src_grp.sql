PREPARE result_list_by_src_grp (text) AS
    SELECT phrase_id_1,
           phrase_id_2,
           phrase_id_3,
           phrase_id_4,
           formula_id,
           user_id,
           source_group_id,
           numeric_value,
           last_update
      FROM results
     WHERE source_group_id = $1;