PREPARE component_insert_111110000000000_user FROM
    'INSERT INTO user_components (component_id, user_id, component_name, description, component_type_id)
          VALUES                 (?, ?, ?, ?, ?)';