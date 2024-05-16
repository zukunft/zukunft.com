PREPARE view_delete_user FROM
     'DELETE FROM user_views
            WHERE view_id = ?
              AND user_id = ?';