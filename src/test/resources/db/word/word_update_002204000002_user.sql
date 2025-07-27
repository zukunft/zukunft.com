PREPARE word_update_002204000002_user (text, text, smallint, smallint, bigint, bigint) AS
    UPDATE user_words
       SET word_name = $1,
           description = $2,
           phrase_type_id = $3,
           protect_id = $4
     WHERE word_id = $5
       AND user_id = $6;