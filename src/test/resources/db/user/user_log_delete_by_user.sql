PREPARE user_log_delete_by_user (bigint) AS
    DELETE FROM user_log
          WHERE user_id = $1;
