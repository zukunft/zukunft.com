PREPARE formula_update_00100000000000 (text,bigint) AS
    UPDATE formulas
       SET formula_name = $1
     WHERE formula_id = $2;
