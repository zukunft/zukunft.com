PREPARE word_insert_111105000001_user FROM
    'INSERT INTO user_words (word_id, user_id, word_name, description, phrase_type_id, protect_id)
          VALUES            (?, ?, ?, ?, ?, ?)';