PREPARE group_big_insert_user_111 FROM
    'INSERT INTO user_groups_big
                 (group_id, group_name, user_id, description)
          VALUES (?, ?, ?, ?)';