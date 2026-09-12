PREPARE formula_link_update_0001000_user FROM
    'UPDATE user_formula_links
        SET description = ?
      WHERE formula_link_id = ?
        AND user_id = ?';