PREPARE user_update_202000004000000200000 (text, text, text, text, bigint) AS
    UPDATE users
       SET user_name       = $1,
           email           = $2,
           user_profile_id = $3,
           description     = $4
     WHERE user_id = $5;