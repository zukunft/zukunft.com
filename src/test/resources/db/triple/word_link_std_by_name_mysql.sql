PREPARE word_link_std_by_name FROM
   'SELECT word_link_id,
           name,
           from_phrase_id,
           verb_id,
           to_phrase_id,
           word_type_id,
           word_link_condition_id,
           word_link_condition_type_id,
           name_generated,
           description,
           `values`,
           excluded,
           share_type_id,
           protect_id,
           user_id
      FROM word_links
     WHERE name = ?';