PREPARE group_big_delete_user FROM
   'DELETE FROM user_groups_big
          WHERE group_id = ?
            AND user_id = ?';