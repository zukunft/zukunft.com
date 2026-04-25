PREPARE group_insert_110 FROM
    'INSERT INTO `groups`
                 (group_id, group_name, user_id)
          VALUES (?, ?, ?)';