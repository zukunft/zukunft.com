PREPARE group_insert FROM
    'INSERT INTO `groups`
                 (group_id, user_id, group_name, description)
          VALUES (?, ?, ?, ?)';