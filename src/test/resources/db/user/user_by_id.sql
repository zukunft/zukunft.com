PREPARE user_by_id (bigint) AS
    SELECT
         user_id,
         user_name,
         ip_address,
         password,
         description,
         code_id,
         user_profile_id,
         email,
         first_name,
         last_name,
         term_id,
         source_id,
         activation_key,
         activation_timeout
    FROM users
   WHERE user_id = $1;
