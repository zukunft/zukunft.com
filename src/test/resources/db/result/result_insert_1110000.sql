PREPARE result_insert_1110000 (text, bigint, numeric, text) AS
    INSERT INTO results (group_id, user_id, numeric_value, last_update, source_group_id)
         VALUES         ($1, $2, $3, Now(), $4)
      RETURNING group_id;