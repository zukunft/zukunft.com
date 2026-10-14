PREPARE user_log_delete_by_user FROM
    'DELETE FROM user_log
           WHERE user_id = ?';
