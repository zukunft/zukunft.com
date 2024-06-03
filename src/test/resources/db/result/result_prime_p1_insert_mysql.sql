PREPARE result_prime_p1_insert FROM
    'INSERT INTO results_prime (phrase_id_1,user_id,numeric_value,last_update,formula_id,source_group_id)
          VALUES (?,?,?,Now(),?,?)';