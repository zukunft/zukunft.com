PREPARE view_insert_0111000000 (bigint, text, text) AS
    INSERT INTO views (user_id, view_name, description)
         VALUES       ($1, $2, $3)
      RETURNING view_id;