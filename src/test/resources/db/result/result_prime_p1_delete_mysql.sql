PREPARE result_prime_p1_delete FROM
    'DELETE FROM results_prime
           WHERE formula_id = ?
             AND phrase_id_1 = ?
             AND phrase_id_2 = ?
             AND phrase_id_3 = ?
             AND phrase_id_4 = ?';