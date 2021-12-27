PREPARE word_std_by_id FROM
    'SELECT word_id,
            word_name,
            `values`,
            plural,
            description,
            word_type_id,
            view_id,
            excluded,
            share_type_id,
            protection_type_id,
            user_id
       FROM words
      WHERE word_id = ?';