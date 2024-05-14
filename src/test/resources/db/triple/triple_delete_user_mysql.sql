PREPARE triple_delete_user FROM
     'DELETE FROM user_triples
            WHERE triple_id = ?
              AND user_id = ?';