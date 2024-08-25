PREPARE word_delete FROM
     'DELETE FROM words
            WHERE word_id = ?';