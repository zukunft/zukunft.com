PREPARE source_insert_0111110000 (bigint, text, text, bigint, text) AS
    INSERT INTO sources (user_id, source_name, description, source_type_id, url)
         VALUES         ($1, $2, $3, $4, $5)
      RETURNING source_id;