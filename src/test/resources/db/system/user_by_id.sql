PREPARE user_by_id (bigint) AS
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
     WHERE user_id = $1;