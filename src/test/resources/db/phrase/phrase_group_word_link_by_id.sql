PREPARE phrase_group_word_link_by_id (int) AS
    SELECT phrase_group_word_link_id,
           phrase_group_id,
           word_id
      FROM phrase_group_word_links
     WHERE phrase_group_word_link_id = $1;