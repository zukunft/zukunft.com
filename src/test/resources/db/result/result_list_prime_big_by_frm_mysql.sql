PREPARE result_list_prime_big_by_frm FROM
       'SELECT phrase_id_1,
               phrase_id_2,
               phrase_id_3,
               phrase_id_4,
               formula_id,
               user_id,
               source_group_id,
               numeric_value,
               last_update
          FROM results_prime
         WHERE formula_id = ?
  UNION SELECT group_id,
               formula_id,
               user_id,
               source_group_id,
               numeric_value,
               last_update
          FROM results
         WHERE formula_id = ?
  UNION SELECT group_id,
               formula_id,
               user_id,
               source_group_id,
               numeric_value,
               last_update
          FROM results_big
         WHERE formula_id = ?';
