PREPARE phrase_group_word_link_by_grp_id FROM
   'SELECT
            phrase_group_word_link_id,
            phrase_group_id,
            word_id
       FROM group_links
      WHERE phrase_group_id = ?';