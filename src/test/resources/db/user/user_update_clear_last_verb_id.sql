PREPARE user_update_clear_last_verb_id (bigint, bigint) AS
    UPDATE users
       SET verb_id = $1
     WHERE verb_id = $2;
