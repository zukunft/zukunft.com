PREPARE view_delete_excluded FROM
     'DELETE FROM views
            WHERE view_id = ?
             AND excluded = 1';