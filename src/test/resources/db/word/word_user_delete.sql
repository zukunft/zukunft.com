PREPARE word_user_delete (bigint, bigint) AS
    DELETE FROM user_words
           WHERE word_id = $1
             AND user_id = $2;
