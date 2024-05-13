PREPARE triple_insert_1111100000000_user (bigint, bigint, text, text, smallint) AS
    INSERT INTO user_triples (triple_id, user_id, triple_name, description, phrase_type_id)
         VALUES              ($1, $2, $3, $4, $5)
      RETURNING triple_id;