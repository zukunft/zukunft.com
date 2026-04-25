PREPARE element_delete (bigint) AS
    DELETE FROM elements
          WHERE element_id = $1;