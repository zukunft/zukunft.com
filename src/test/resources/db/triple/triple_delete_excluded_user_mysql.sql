PREPARE triple_delete_excluded_user FROM
     'DELETE FROM user_triples
            WHERE triple_id = ?
              AND excluded = 1';