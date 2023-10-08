PREPARE group_link_by_id (text, int) AS
    SELECT group_id,
           phrase_id
      FROM group_links
     WHERE group_id = $1
       AND phrase_id = $2;
