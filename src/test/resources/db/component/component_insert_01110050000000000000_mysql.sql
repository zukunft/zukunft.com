PREPARE component_insert_01110050000000000000 FROM
    'INSERT INTO components (user_id, component_name, description, component_type_id)
          VALUES            (?, ?, ?, ?)';