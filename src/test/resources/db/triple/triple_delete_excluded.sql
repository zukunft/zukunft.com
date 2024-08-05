PREPARE triple_delete_excluded (bigint) AS
    DELETE FROM triples
          WHERE triple_id = $1
            AND excluded = 1;