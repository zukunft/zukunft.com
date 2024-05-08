PREPARE triple_insert_011111110000000 (bigint,bigint,bigint,text,text,smallint) AS
    INSERT INTO triples (user_id,from_phrase_id,to_phrase_id,triple_name,description,verb_id)
         VALUES         ($1,$2,$3,$4,$5,$6)
      RETURNING triple_id;