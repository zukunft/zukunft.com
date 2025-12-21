PREPARE triple_insert_111150000000001_user FROM
    'INSERT INTO user_triples (triple_id, user_id, triple_name, description, phrase_type_id, protect_id)
          VALUES              (?, ?, ?, ?, ?, ?)';
