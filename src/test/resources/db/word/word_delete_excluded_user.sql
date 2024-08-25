PREPARE word_delete_excluded_user (bigint) AS
    DELETE FROM user_words
           WHERE word_id = $1
             AND excluded = 1;
