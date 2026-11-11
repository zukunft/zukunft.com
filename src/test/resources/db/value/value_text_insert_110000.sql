PREPARE value_text_insert_110000 (text, bigint, text) AS
    INSERT INTO values_text (group_id, user_id, text_value, last_update)
         VALUES             ($1, $2, $3, Now())
      RETURNING group_id;