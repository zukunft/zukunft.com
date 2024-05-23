PREPARE triple_update_0022400000000_user (text, text, smallint, bigint, bigint) AS
    UPDATE user_triples
       SET triple_name = $1,
           description = $2,
           phrase_type_id = $3
     WHERE triple_id = $4
       AND user_id = $5;