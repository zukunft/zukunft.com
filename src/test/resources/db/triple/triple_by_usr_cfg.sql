PREPARE triple_by_usr_cfg (int, int) AS
    SELECT triple_id,
           triple_name,
           name_given,
           name_generated,
           description,
           phrase_type_id,
           values,
           excluded,
           share_type_id,
           protect_id
      FROM user_triples
     WHERE triple_id = $1
       AND user_id = $2;
