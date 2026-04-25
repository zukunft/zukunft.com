PREPARE user_profile_insert_01111 (text, text, text, smallint) AS
    INSERT INTO user_profiles (user_profile_name, code_id, description, right_level)
    VALUES       ($1, $2, $3, $4)
    RETURNING user_profile_id;