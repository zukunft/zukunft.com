PREPARE user_update_2000042000000 FROM
    'UPDATE users
        SET user_name       = ?,
            user_profile_id = ?,
            email           = ?
      WHERE user_id = ?';