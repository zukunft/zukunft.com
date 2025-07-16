PREPARE source_insert_1111011000_user (bigint, bigint, text, text, smallint, text) AS
    INSERT INTO user_sources (source_id, user_id, source_name, description, source_type_id, url)
         VALUES              ($1, $2, $3, $4, $5, $6)
      RETURNING source_id;
