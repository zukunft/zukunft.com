PREPARE result_by_usr_cfg (bigint, bigint) AS
    SELECT group_id
      FROM user_results
     WHERE group_id = $1
       AND user_id = $2;
