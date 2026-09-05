PREPARE formula_link_update_000001000 (text, bigint) AS
    UPDATE formula_links
       SET description = $1
     WHERE formula_link_id = $2;