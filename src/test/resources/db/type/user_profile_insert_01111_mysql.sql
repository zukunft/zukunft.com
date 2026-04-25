PREPARE user_profile_insert_01111 FROM
    'INSERT INTO user_profiles (user_profile_name, code_id, description, right_level)
    VALUES       (?, ?, ?, ?)';