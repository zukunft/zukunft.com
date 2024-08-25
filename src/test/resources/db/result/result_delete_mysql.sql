PREPARE result_delete FROM
    'DELETE FROM results
           WHERE group_id = ?';