PREPARE word_insert_01111000000 FROM
    'INSERT INTO words (user_id, word_name, description, phrase_type_id)
          VALUES       (?, ?, ?, ?)';