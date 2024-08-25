PREPARE formula_link_delete (bigint) AS
    DELETE FROM formula_links
          WHERE formula_link_id = $1;