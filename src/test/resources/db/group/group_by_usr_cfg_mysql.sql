PREPARE group_by_usr_cfg FROM
    'SELECT group_id,
            group_name,
            description
       FROM user_groups
      WHERE group_id = ?
        AND user_id = ?';