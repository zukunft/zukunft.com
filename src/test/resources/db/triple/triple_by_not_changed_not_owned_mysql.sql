PREPARE triple_by_not_changed_not_owned FROM
    'SELECT user_id
       FROM user_triples
      WHERE triple_id = ?
        AND (excluded <> 1 OR excluded is NULL)
        AND user_id <> ?';
