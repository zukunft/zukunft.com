PREPARE triple_insert_015511550000000 FROM
    'INSERT INTO triples (user_id,from_phrase_id,to_phrase_id,triple_name,description,phrase_type_id,verb_id)
          VALUES         (?,?,?,?,?,?,?)';