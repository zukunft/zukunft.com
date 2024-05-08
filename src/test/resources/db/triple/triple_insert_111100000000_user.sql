PREPARE triple_insert_111100000000_user (bigint, bigint, text, text) AS
    INSERT INTO user_triples (triple_id, user_id, triple_name, description)
         VALUES              ($1, $2, $3, $4)
      RETURNING triple_id;