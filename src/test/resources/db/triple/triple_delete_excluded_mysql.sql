PREPARE triple_delete_excluded FROM
     'DELETE FROM triples
            WHERE triple_id = ?
              AND excluded = 1';