PREPARE formula_count AS
    SELECT count(s.formula_id) + count(u.formula_id) AS count
      FROM formulas s
 LEFT JOIN user_formulas  u ON s.formula_id = u.formula_id;