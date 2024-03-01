PREPARE user_by_code_id (text) AS
    SELECT user_id,
           user_name,
           code_id,
           ip_address,
           email,
           first_name,
           last_name,
           term_id,
           source_id,
           user_profile_id
      FROM users
     WHERE code_id = $1 AND code_id IS NOT NULL;
