PREPARE user_profile_insert_01111 FROM
    'INSERT INTO user_profiles (type_name, code_id, description, right_level)
    VALUES       (?, ?, ?, ?)';