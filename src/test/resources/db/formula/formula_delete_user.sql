PREPARE formula_delete_user (bigint,bigint) AS
    DELETE FROM user_formulas
          WHERE formula_id = $1
            AND user_id = $2;