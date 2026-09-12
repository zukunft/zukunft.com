PREPARE formula_link_norm_by_id (text) AS
    SELECT user_id,
           user_name
      FROM users
     WHERE user_name = $1;