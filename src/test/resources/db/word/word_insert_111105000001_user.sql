PREPARE word_insert_111105000001_user (bigint, bigint, text, text, smallint, smallint) AS
    INSERT INTO user_words (word_id, user_id, word_name, description, phrase_type_id, protect_id)
         VALUES            ($1, $2, $3, $4, $5, $6)
      RETURNING word_id;