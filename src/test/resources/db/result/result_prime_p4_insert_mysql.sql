PREPARE result_prime_p4_insert FROM
    'INSERT INTO results_prime (phrase_id_1,phrase_id_2,phrase_id_3,phrase_id_4,user_id,numeric_value,last_update,formula_id,source_group_id)
          VALUES (?,?,?,?,?,?,Now(),?,?)';