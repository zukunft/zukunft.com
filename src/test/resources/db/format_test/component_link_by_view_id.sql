PREPARE component_link_by_view_id (bigint, bigint) AS
    SELECT     s.component_link_id,
               CASE WHEN (ul2.component_link_type_id IS     NULL) THEN l2.component_link_type_id ELSE ul2.component_link_type_id END AS component_link_type_id2,
               CASE WHEN (ul2.linked_component_id    IS     NULL) THEN l2.linked_component_id    ELSE ul2.linked_component_id    END AS linked_component_id2,
               CASE WHEN (ul2.description     <> ''  IS NOT TRUE) THEN l2.description            ELSE ul2.description            END AS description2
          FROM component_links s
     LEFT JOIN user_components ul2 ON s.component_id = ul2.component_id
                                  AND ul2.user_id = $1
         WHERE s.view_id = $2;