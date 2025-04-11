PREPARE change_ref_delete (bigint, smallint, smallint, text, bigint, bigint) AS
    INSERT INTO changes
                (user_id, change_action_id, change_field_id, old_value, old_id, row_id)
         VALUES ($1, $2, $3, $4, $5, $6)
      RETURNING change_id;
