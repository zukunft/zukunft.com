PREPARE view_insert_01110000000 FROM
    'INSERT INTO views (user_id, view_name, description)
          VALUES       (?, ?, ?)';