PREPARE change_insert (bigint, bigint, bigint, text, text, bigint) AS
    INSERT INTO changes
                (user_id, change_action_id, change_field_id, old_value, new_value, row_id)
         VALUES ($1, $2, $3, $4, $5, $6)
      RETURNING change_id;