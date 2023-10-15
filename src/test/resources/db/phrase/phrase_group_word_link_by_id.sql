PREPARE group_word_link_by_id (bigint) AS
    SELECT group_word_link_id,
           group_id,
           word_id
      FROM group_word_links
     WHERE group_word_link_id = $1;