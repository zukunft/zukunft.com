PREPARE value_std_by_id FROM
    'SELECT
            value_id,
            group_id,
            numeric_value,
            source_id,
            last_update,
            excluded,
            protect_id,
            user_id
       FROM `values`
      WHERE value_id = ?';