PREPARE value_insert_111111 (text, bigint, numeric, bigint, smallint, smallint, smallint) AS
    INSERT INTO values (group_id, user_id, numeric_value, last_update, source_id, excluded, share_type_id, protect_id)
         VALUES        ($1, $2, $3, Now(), $4, $5, $6, $7)
      RETURNING group_id;