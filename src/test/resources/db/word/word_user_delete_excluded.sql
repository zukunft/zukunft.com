PREPARE word_user_delete_excluded (bigint) AS
    DELETE FROM user_words
           WHERE word_id = $1
             AND excluded = 1;
