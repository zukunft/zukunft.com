PREPARE word_insert_11111000000_user (bigint, bigint, text, text, bigint) AS
    INSERT INTO user_words (word_id, user_id, word_name, description, phrase_type_id)
         VALUES            ($1, $2, $3, $4, $5)
      RETURNING word_id;