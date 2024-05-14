PREPARE triple_update_000011100000000 FROM
   'UPDATE triples
       SET triple_name = ?,
           description = ?,
           phrase_type_id = ?
     WHERE triple_id = ?';