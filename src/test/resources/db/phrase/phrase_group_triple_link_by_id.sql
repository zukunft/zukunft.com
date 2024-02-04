PREPARE group_triple_link_by_id (bigint) AS
    SELECT group_triple_link_id,
           group_id,
           triple_id
      FROM group_triple_links
     WHERE group_triple_link_id = $1;