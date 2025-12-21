PREPARE triple_by_usr_cfg (bigint, bigint) AS
    SELECT triple_id,
           triple_name,
           name_given,
           name_generated,
           description,
           weight,
           phrase_type_id,
           view_id,
           usage,
           impact,
           excluded,
           share_type_id,
           protect_id
      FROM user_triples
     WHERE triple_id = $1
       AND user_id = $2;
