PREPARE word_user_delete FROM
     'DELETE FROM user_words
            WHERE word_id = ?
              AND user_id = ?';