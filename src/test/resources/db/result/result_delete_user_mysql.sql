PREPARE result_delete_user FROM
    'DELETE FROM user_results
           WHERE group_id = ?
             AND user_id = ?';