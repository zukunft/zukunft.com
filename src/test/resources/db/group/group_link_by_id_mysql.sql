PREPARE group_link_by_id FROM
'SELECT group_id,
           group_name,
           description
      FROM group_links
     WHERE group_id = ?';