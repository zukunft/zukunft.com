PREPARE word_insert_00111000000 FROM
    'INSERT INTO words (word_name, description, phrase_type_id)
                VALUES (?, ?, ?)';