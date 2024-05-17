PREPARE component_delete (bigint) AS
    DELETE FROM components
           WHERE component_id = $1;