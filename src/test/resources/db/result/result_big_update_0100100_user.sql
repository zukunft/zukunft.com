PREPARE result_big_update_0100100_user (smallint, text, bigint) AS
    UPDATE user_results_big
       SET last_update = Now(),
           excluded    = $1
     WHERE group_id = $2
       AND user_id = $3;