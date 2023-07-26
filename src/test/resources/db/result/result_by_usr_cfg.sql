PREPARE result_by_usr_cfg (int, int) AS
    SELECT result_id
      FROM user_results
     WHERE result_id = $1
       AND user_id = $2;
