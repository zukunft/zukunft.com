PREPARE formula_link_update_000020000 FROM
    'UPDATE formula_links
        SET order_nbr = ?
      WHERE formula_link_id = ?';