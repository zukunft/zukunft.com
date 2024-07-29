PREPARE triple_update_000022400000002 (text, text, smallint, smallint, bigint) AS
    UPDATE triples
       SET triple_name = $1,
           description = $2,
           phrase_type_id = $3,
           protect_id = $4
     WHERE triple_id = $5;