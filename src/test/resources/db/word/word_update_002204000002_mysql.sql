PREPARE word_update_002204000002 FROM
    'UPDATE words
        SET word_name = ?,
            description = ?,
            phrase_type_id = ?,
            protect_id = ?
      WHERE word_id = ?';