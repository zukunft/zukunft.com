PREPARE group_by_name FROM
   'SELECT group_id,
           group_name,
           description
      FROM `groups`
     WHERE group_name = ?';