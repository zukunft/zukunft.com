PREPARE word_insert_01111000000 (bigint, text, text, smallint) AS
    INSERT INTO words (user_id, word_name, description, phrase_type_id)
         VALUES       ($1, $2, $3, $4)
      RETURNING word_id;