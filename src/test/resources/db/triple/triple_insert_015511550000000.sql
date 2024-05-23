PREPARE triple_insert_015511550000000 (bigint, bigint, bigint, text, text, smallint, smallint) AS
    INSERT INTO triples (user_id, from_phrase_id, to_phrase_id, triple_name, description, phrase_type_id,verb_id)
         VALUES         ($1, $2, $3, $4, $5, $6, $7)
      RETURNING triple_id;