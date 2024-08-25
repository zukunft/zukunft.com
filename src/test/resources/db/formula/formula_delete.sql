PREPARE formula_delete (bigint) AS
    DELETE FROM formulas
          WHERE formula_id = $1;