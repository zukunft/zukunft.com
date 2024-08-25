PREPARE triple_delete_user (bigint, bigint) AS
    DELETE FROM user_triples
           WHERE triple_id = $1
             AND user_id = $2;
