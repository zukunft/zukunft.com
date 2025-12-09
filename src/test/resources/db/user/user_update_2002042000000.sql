PREPARE user_update_2002042000000 (text, text, text, text, bigint) AS
        UPDATE users
           SET user_name       = $1,
               description     = $2,
               user_profile_id = $3,
               email           = $4
         WHERE user_id = $5; ;