PREPARE word_link_std_by_link_ids (int, int, int) AS
    SELECT word_link_id,
           name,
           from_phrase_id,
           verb_id,
           to_phrase_id,
           word_type_id,
           word_link_condition_id,
           word_link_condition_type_id,
           name_generated,
           description,
           values,
           excluded,
           share_type_id,
           protect_id,
           user_id
      FROM word_links
     WHERE from_phrase_id = $1
       AND to_phrase_id = $2
       AND verb_id = $3;