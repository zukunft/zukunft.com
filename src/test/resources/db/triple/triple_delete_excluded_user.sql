PREPARE triple_delete_excluded_user (bigint) AS
    DELETE FROM user_triples
          WHERE triple_id = $1
            AND excluded = 1;