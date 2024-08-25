PREPARE word_delete_excluded_user FROM
     'DELETE FROM user_words
            WHERE word_id = ?
             AND excluded = 1';