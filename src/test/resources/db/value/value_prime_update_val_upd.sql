PREPARE value_prime_update_val_upd (numeric, bigint) AS
    UPDATE values_prime
       SET numeric_value = $1, last_update = Now()
     WHERE group_id = $2;