PREPARE word_insert_wrd_des_pty FROM
    'INSERT INTO words (word_name, description, phrase_type_id)
                VALUES (?, ?, ?)';