PREPARE word_user_delete_excluded FROM
     'DELETE FROM user_words
            WHERE word_id = ?
             AND excluded = 1';