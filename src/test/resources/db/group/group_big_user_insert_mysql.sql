PREPARE group_big_user_insert FROM
    'INSERT INTO user_groups_big
                 (group_id, user_id, group_name, description)
          VALUES (?, ?, ?, ?)';