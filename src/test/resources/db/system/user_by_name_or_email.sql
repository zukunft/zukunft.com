PREPARE user_by_name_or_email (text,text) AS
    SELECT user_id,
           user_name,
           code_id,
           ip_address,
           email,
           first_name,
           last_name,
           last_word_id,
           source_id,
           user_profile_id
      FROM users
     WHERE user_name = $1 OR email = $2;