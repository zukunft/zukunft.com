PREPARE change_delete_o (bigint, smallint, smallint, text, bigint) AS
    INSERT INTO changes
                (user_id, change_action_id, change_field_id, old_value, row_id)
         VALUES ($1, $2, $3, $4, $5)
      RETURNING change_id;
