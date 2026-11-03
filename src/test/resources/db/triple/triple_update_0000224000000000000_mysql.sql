PREPARE triple_update_0000224000000000000 FROM
    'UPDATE triples
        SET triple_name    = ?,
            description    = ?,
            phrase_type_id = ?
      WHERE triple_id = ?';