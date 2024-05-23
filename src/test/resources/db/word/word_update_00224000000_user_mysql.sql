PREPARE word_update_00224000000_user FROM
    'UPDATE user_words
        SET word_name = ?,
            description = ?,
            phrase_type_id = ?
      WHERE word_id = ?
        AND user_id = ?';