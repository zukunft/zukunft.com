PREPARE formula_update_002000000100000_user (text,bigint,bigint) AS
    UPDATE user_formulas
       SET formula_name = $1,
           last_update = Now()
     WHERE formula_id = $2
       AND user_id = $3;
