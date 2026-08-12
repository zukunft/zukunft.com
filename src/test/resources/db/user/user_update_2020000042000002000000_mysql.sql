PREPARE user_update_2020000042000002000000 FROM
   ' UPDATE users
        SET user_name       = ?,
            email           = ?,
            user_profile_id = ?,
            code_id = ?,
            description     = ?
      WHERE user_id = ?; ';