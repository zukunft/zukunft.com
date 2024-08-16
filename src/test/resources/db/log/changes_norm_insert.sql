PREPARE changes_norm_insert (bigint, smallint, smallint, text, text) AS
    INSERT INTO changes_norm
                (user_id, change_action_id, change_field_id, new_value, row_id)
         VALUES ($1, $2, $3, $4, $5)
      RETURNING change_id;