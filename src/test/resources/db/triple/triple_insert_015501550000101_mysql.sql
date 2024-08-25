PREPARE triple_insert_015501550000101 FROM
    'INSERT INTO triples (user_id, from_phrase_id, to_phrase_id, description, phrase_type_id, verb_id, excluded, protect_id)
          VALUES         (?,?,?,?,?,?,?,?)';