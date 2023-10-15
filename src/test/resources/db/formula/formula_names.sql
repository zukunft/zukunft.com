PREPARE formula_names (bigint, bigint, bigint) AS
         SELECT s.formula_id,
                u.formula_id AS user_formula_id,
                s.user_id,
                CASE WHEN (u.formula_name <> '' IS NOT TRUE) THEN s.formula_name ELSE u.formula_name END AS formula_name
           FROM formulas s
      LEFT JOIN user_formulas u ON s.formula_id = u.formula_id
            AND u.user_id = $1
       ORDER BY s.formula_name
          LIMIT $2
         OFFSET $3;
