PREPARE group_big_delete FROM
   'DELETE FROM groups_big
          WHERE group_id = ?';