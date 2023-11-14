PREPARE group_prime_user_upd_group_name (text, bigint) AS
    UPDATE user_groups_prime
       SET group_name = $1
     WHERE group_id = $2;