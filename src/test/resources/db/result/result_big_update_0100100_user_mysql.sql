PREPARE result_big_update_0100100_user FROM
    'UPDATE user_results_big
        SET last_update = Now(),
            excluded    = ?
      WHERE group_id = ?
        AND user_id = ?';