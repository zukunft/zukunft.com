PREPARE triple_update_00002240000000002 FROM
   'UPDATE triples
       SET triple_name = ?,
           description = ?,
           phrase_type_id = ?,
           protect_id = ?
     WHERE triple_id = ?';