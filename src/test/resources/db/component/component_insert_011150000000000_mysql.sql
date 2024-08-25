PREPARE component_insert_011150000000000 FROM
    'INSERT INTO components (user_id, component_name, description, component_type_id)
          VALUES            (?, ?, ?, ?)';