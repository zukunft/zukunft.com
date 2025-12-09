PREPARE triple_norm_by_id FROM
   'SELECT triple_id,
           from_phrase_id,
           verb_id,
           to_phrase_id,
           code_id,
           `usage`,
           triple_condition_id,
           triple_name,
           name_given,
           name_generated,
           description,
           weight,
           phrase_type_id,
           view_id,
           impact,
           excluded,
           share_type_id,
           protect_id,
           user_id
      FROM triples
     WHERE triple_id = ?';