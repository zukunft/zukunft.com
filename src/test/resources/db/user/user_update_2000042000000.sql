PREPARE user_update_2000042000000 (text, smallint, text, bigint) AS
        UPDATE users
           SET user_name       = $1,
               user_profile_id = $2,
               email           = $3
         WHERE user_id = $4;