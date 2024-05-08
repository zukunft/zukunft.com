PREPARE triple_insert_111100000000_user FROM
    'INSERT INTO user_triples (triple_id, user_id, triple_name, description)
          VALUES              (?, ?, ?, ?)';
