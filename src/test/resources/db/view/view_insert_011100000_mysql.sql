PREPARE view_insert_011100000 FROM
    'INSERT INTO views (user_id, view_name, description)
          VALUES       (?, ?, ?)';