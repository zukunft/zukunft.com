PREPARE component_update_0022400000000000 (text, text, smallint, bigint) AS
    UPDATE components
       SET component_name    = $1,
           description       = $2,
           component_type_id = $3
     WHERE component_id = $4;