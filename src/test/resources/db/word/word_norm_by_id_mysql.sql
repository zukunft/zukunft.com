PREPARE word_norm_by_id FROM
    'SELECT word_id,
            word_name,
            code_id,
            `usage`,
            plural,
            description,
            phrase_type_id,
            view_id,
            impact,
            excluded,
            share_type_id,
            protect_id,
            user_id
       FROM words
      WHERE word_id = ?';