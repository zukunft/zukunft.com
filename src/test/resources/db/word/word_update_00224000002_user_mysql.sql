PREPARE word_update_00224000002_user FROM
    'UPDATE user_words
        SET word_name = ?,
            description = ?,
            phrase_type_id = ?,
            protect_id = ?
      WHERE word_id = ?
        AND user_id = ?';