PREPARE triple_delete FROM
     'DELETE FROM triples
            WHERE triple_id = ?';