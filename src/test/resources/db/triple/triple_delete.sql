PREPARE triple_delete (bigint) AS
    DELETE FROM triples
          WHERE triple_id = $1;