PREPARE triple_insert_011111110000000 FROM
    'INSERT INTO triples (user_id,from_phrase_id,to_phrase_id,triple_name,description,verb_id)
          VALUES         (?,?,?,?,?,?)';