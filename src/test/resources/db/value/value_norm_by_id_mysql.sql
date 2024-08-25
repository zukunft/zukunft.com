PREPARE value_norm_by_id FROM
    'SELECT
            group_id,
            numeric_value,
            source_id,
            last_update,
            excluded,
            protect_id,
            user_id
       FROM `values`
      WHERE group_id = ?';