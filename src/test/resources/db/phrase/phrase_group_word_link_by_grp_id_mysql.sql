PREPARE group_word_link_by_grp_id FROM
   'SELECT
            group_word_link_id,
            group_id,
            word_id
       FROM group_links
      WHERE group_id = ?';