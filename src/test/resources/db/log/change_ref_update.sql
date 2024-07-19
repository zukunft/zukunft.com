PREPARE change_ref_update (bigint, smallint, smallint, text, text, bigint, bigint, bigint) AS
    INSERT INTO changes
                (user_id, change_action_id, change_field_id, old_value, new_value, old_id, new_id, row_id)
         VALUES ($1, $2, $3, $4, $5, $6, $7, $8)
      RETURNING change_id;
