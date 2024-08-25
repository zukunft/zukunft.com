PREPARE triple_insert_1111500000001_user (bigint, bigint, text, text, smallint, smallint) AS
    INSERT INTO user_triples (triple_id, user_id, triple_name, description, phrase_type_id, protect_id)
         VALUES              ($1, $2, $3, $4, $5, $6)
      RETURNING triple_id;