PREPARE word_delete_excluded FROM
     'DELETE FROM words
            WHERE word_id = ?
             AND excluded = 1';