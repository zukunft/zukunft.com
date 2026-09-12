PREPARE formula_link_insert_1110000_user FROM
    'INSERT INTO user_formula_links (formula_link_id, user_id, formula_link_type_id, order_nbr)
          VALUES                    (?, ?, ?, ?)';