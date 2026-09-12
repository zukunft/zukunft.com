PREPARE formula_link_norm_by_id_mysql FROM
    'SELECT user_id,
            user_name
       FROM users
      WHERE user_name = ?';