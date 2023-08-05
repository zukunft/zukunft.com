PREPARE formula_names_like FROM
   'SELECT
                s.formula_id,
                u.formula_id AS user_formula_id,
                s.user_id,
                IF(u.formula_name IS NULL, s.formula_name, u.formula_name) AS formula_name
           FROM formulas s
      LEFT JOIN user_formulas u ON s.formula_id = u.formula_id
            AND u.user_id = ?
          WHERE s.formula_name like ?
       ORDER BY s.formula_name
          LIMIT ?
         OFFSET ?';