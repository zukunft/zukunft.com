PREPARE formula_link_update_000001000 FROM
    'UPDATE formula_links
        SET description = ?
      WHERE formula_link_id = ?';