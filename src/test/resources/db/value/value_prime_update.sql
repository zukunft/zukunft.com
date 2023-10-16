PREPARE value_prime_update (numeric, timestamp, bigint) AS
    UPDATE values_prime
       SET numeric_value = $1, last_update = $2
     WHERE group_id = $3;