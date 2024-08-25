PREPARE formula_update_00200000100000 (text,bigint) AS
    UPDATE formulas
       SET formula_name = $1,
           last_update = Now()
     WHERE formula_id = $2;
