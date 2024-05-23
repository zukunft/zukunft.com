PREPARE triple_insert_1111500000000_user FROM
    'INSERT INTO user_triples (triple_id, user_id, triple_name, description, phrase_type_id)
          VALUES              (?, ?, ?, ?, ?)';
