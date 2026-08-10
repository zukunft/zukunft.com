PREPARE group_big_by_usr_cfg FROM
    'SELECT group_id,
            group_name,
            description
       FROM user_groups_big
      WHERE group_id = ?
        AND user_id = ?';