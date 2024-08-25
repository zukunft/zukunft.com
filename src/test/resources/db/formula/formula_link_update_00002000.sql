PREPARE formula_link_update_00002000 (bigint,bigint) AS
    UPDATE formula_links
       SET order_nbr = $1
     WHERE formula_link_id = $2;