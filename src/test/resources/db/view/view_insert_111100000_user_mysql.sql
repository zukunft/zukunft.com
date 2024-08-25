PREPARE view_insert_111100000_user FROM
    'INSERT INTO user_views (view_id, user_id, view_name, description)
          VALUES            (?, ?, ?, ?)';