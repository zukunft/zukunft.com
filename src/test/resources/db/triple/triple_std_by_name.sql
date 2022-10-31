PREPARE triple_std_by_name (text) AS
    SELECT triple_id,
           from_phrase_id,
           verb_id,
           to_phrase_id,
           word_type_id,
           triple_condition_id,
           triple_condition_type_id,
           triple_name,
           name_given,
           name_generated,
           description,
           values,
           excluded,
           share_type_id,
           protect_id,
           user_id
      FROM triples
     WHERE triple_name = $1;