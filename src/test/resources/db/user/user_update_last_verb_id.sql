PREPARE user_update_last_verb_id (bigint, bigint) AS
    UPDATE users
       SET verb_id = $1
     WHERE user_id = $2;
