PREPARE source_insert_0111011000 FROM
    'INSERT INTO sources (user_id, source_name, description, source_type_id, `url`)
          VALUES         (?, ?, ?, ?, ?)';