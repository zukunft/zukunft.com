PREPARE user_by_profile (int) AS
    SELECT
         user_id,
         user_name,
         code_id,
         ip_address,
         email,
         first_name,
         last_name,
         last_word_id,
         source_id,
         user_profile_id,
         activation_key,
         activation_key_timeout,
         NOW() AS db_now
    FROM users
   WHERE user_profile_id = $1;