PREPARE result_by_usr_cfg FROM
   'SELECT result_id
      FROM user_results
     WHERE result_id = ?
       AND user_id = ?';