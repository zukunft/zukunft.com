PREPARE element_by_id FROM
    'SELECT element_id,
            formula_id,
            user_id,
            order_nbr,
            element_type_id,
            ref_id
       FROM elements
      WHERE element_id = ?';