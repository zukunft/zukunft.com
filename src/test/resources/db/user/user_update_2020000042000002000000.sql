PREPARE user_update_2020000042000002000000 (text, text, text, text, text, bigint) AS
    UPDATE users
       SET user_name       = $1,
           email           = $2,
           user_profile_id = $3,
           code_id         = $4,
           description     = $5
     WHERE user_id = $6;