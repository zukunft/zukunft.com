PREPARE ref_delete (bigint) AS
    DELETE FROM refs
          WHERE ref_id = $1;