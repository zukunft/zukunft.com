PREPARE word_insert_11115000000_user (bigint, bigint, text, text, smallint) AS
    INSERT INTO user_words (word_id, user_id, word_name, description, phrase_type_id)
         VALUES            ($1, $2, $3, $4, $5)
      RETURNING word_id;