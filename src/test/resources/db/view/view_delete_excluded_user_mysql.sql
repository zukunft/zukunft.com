PREPARE view_delete_excluded_user FROM
     'DELETE FROM user_views
            WHERE view_id = ?
             AND excluded = 1';