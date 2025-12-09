PREPARE user_update_2002042000000 FROM
   ' UPDATE users
        SET user_name       = ?,
            description     = ?,
            user_profile_id = ?,
            email           = ?
      WHERE user_id = ?; ';