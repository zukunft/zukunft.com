PREPARE word_delete_user FROM
     'DELETE FROM user_words
            WHERE word_id = ?
              AND user_id = ?';