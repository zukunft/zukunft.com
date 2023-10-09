PREPARE group_word_link_by_grp_id (int) AS
    SELECT
           group_word_link_id,
           group_id,
           word_id
      FROM group_word_links
     WHERE group_id = $1;