PREPARE triple_update_000022400000002 FROM
   'UPDATE triples
       SET triple_name = ?,
           description = ?,
           phrase_type_id = ?,
           protect_id = ?
     WHERE triple_id = ?';