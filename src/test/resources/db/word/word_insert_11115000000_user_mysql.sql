PREPARE word_insert_11115000000_user FROM
    'INSERT INTO user_words (word_id, user_id, word_name, description, phrase_type_id)
          VALUES            (?, ?, ?, ?, ?)';