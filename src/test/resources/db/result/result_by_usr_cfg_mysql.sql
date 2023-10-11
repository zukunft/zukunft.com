PREPARE result_by_usr_cfg FROM
   'SELECT group_id
      FROM user_results
     WHERE group_id = ?
       AND user_id = ?';