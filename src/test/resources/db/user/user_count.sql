PREPARE user_count AS
    SELECT COUNT(user_id) AS count
      FROM users;