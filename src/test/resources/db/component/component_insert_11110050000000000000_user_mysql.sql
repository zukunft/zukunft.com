PREPARE component_insert_11110050000000000000_user FROM
    'INSERT INTO user_components (component_id, user_id, component_name, description, component_type_id)
          VALUES                 (?, ?, ?, ?, ?)';