PREPARE formula_update_00100000000000_user (text,bigint,bigint) AS
    UPDATE user_formulas
       SET formula_name = $1
     WHERE formula_id = $2
       AND user_id = $3;
