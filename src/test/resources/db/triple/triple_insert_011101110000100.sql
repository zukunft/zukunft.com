PREPARE triple_insert_011101110000100 (bigint, bigint, bigint, text, smallint, smallint, smallint) AS
    INSERT INTO triples (user_id, from_phrase_id, to_phrase_id, description, phrase_type_id, verb_id, excluded)
         VALUES         ($1, $2, $3, $4, $5, $6, $7)
      RETURNING triple_id;