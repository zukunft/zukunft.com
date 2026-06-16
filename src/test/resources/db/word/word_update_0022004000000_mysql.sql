PREPARE word_update_0022004000000 FROM
    'UPDATE words
        SET word_name      = ?,
            description    = ?,
            phrase_type_id = ?
      WHERE word_id = ?';