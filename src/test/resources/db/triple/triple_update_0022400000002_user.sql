PREPARE triple_update_0022400000002_user (text, text, smallint, smallint, bigint, bigint) AS
    UPDATE user_triples
       SET triple_name = $1,
           description = $2,
           phrase_type_id = $3,
           protect_id = $4
     WHERE triple_id = $5
       AND user_id = $6;