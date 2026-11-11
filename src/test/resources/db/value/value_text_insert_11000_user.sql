PREPARE value_text_insert_11000_user (text, bigint, bigint, text) AS
    INSERT INTO user_values_text (group_id, user_id, source_id, text_value, last_update)
         VALUES                  ($1, $2, $3, $4, Now())
      RETURNING group_id;