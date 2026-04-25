PREPARE user_update_202000004000000200000 FROM
   ' UPDATE users
        SET user_name       = ?,
            email           = ?,
            user_profile_id = ?,
            description     = ?
      WHERE user_id = ?; ';