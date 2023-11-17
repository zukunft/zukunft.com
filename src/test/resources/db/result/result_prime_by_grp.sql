PREPARE result_prime_by_grp (bigint) AS
    SELECT group_id,
           formula_id,
           user_id,
           source_group_id,
           numeric_value,
           last_update
      FROM results_prime
     WHERE group_id = $1;