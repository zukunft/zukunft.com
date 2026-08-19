PREPARE source_insert_111100110000_user FROM
    'INSERT INTO user_sources (source_id,user_id,source_name,description,source_type_id,`url`)  VALUES ( ?,?,?,?,?,? )';