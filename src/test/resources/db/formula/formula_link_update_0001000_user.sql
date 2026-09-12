PREPARE formula_link_update_0001000_user (text, bigint, bigint) AS
    UPDATE user_formula_links
       SET description = $1
     WHERE formula_link_id = $2
       AND user_id = $3;