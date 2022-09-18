PREPARE word_link_by_usr_cfg (int, int) AS
    SELECT word_link_id,
           name_given,
           name_generated,
           description,
           values,
           excluded,
           share_type_id,
           protect_id
      FROM user_word_links
     WHERE word_link_id = $1
       AND user_id = $2;
