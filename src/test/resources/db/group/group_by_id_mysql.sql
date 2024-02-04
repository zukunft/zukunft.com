PREPARE group_by_id FROM
'SELECT group_id,
           group_name,
           description
      FROM `groups`
     WHERE group_id = ?';