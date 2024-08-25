PREPARE component_link_delete (bigint) AS
    DELETE FROM component_links
          WHERE component_link_id = $1;