PREPARE word_norm_by_id FROM
    'SELECT word_id,
            word_name,
            `values`,
            code_id,
            plural,
            description,
            phrase_type_id,
            view_id,
            excluded,
            share_type_id,
            protect_id,
            user_id
       FROM words
      WHERE word_id = ?';