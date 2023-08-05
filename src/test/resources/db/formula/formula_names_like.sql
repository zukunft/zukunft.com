PREPARE formula_names_like (int, text, int, int) AS
         SELECT s.formula_id,
                u.formula_id AS user_formula_id,
                s.user_id,
                CASE WHEN (u.formula_name <> '' IS NOT TRUE) THEN s.formula_name ELSE u.formula_name END AS formula_name
           FROM formulas s
      LEFT JOIN user_formulas u ON s.formula_id = u.formula_id
            AND u.user_id = $1
          WHERE s.formula_name like $2
       ORDER BY s.formula_name
          LIMIT $3
         OFFSET $4;
