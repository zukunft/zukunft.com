PREPARE triple_by_not_changed (bigint) AS
     SELECT user_id
       FROM user_triples
      WHERE triple_id = $1
        AND (excluded <> 1 OR excluded is NULL);