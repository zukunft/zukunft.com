PREPARE result_prime_p1_delete_user FROM
    'DELETE FROM user_results_prime
           WHERE formula_id = ?
             AND phrase_id_1 = ?
             AND phrase_id_2 = ?
             AND phrase_id_3 = ?
             AND phrase_id_4 = ?
             AND user_id = ?';