PREPARE element_list_by_frm_and_type_id FROM
    'SELECT element_id,
            formula_id,
            user_id,
            order_nbr,
            element_type_id,
            ref_id
       FROM elements
      WHERE formula_id = ?
        AND element_type_id = ?
        AND user_id = ?';