PREPARE word_update_00111000000_user (text, text, text, bigint, bigint) AS
    UPDATE user_words
       SET word_name = $1,
           description = $2,
           phrase_type_id = $3
     WHERE word_id = $4
       AND user_id = $5;