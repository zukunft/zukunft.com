PREPARE triple_update_000011100000000 (text, text, smallint, bigint) AS
    UPDATE triples
       SET triple_name = $1,
           description = $2,
           phrase_type_id = $3
     WHERE triple_id = $4;