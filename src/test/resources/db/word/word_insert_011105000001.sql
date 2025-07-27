PREPARE word_insert_011105000001 (bigint, text, text, smallint, smallint) AS
    INSERT INTO words (user_id, word_name, description, phrase_type_id, protect_id)
         VALUES       ($1, $2, $3, $4, $5)
      RETURNING word_id;