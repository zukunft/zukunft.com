PREPARE group_prime_update_group_name (text, bigint) AS
    UPDATE groups_prime
       SET group_name = $1
     WHERE group_id = $2;