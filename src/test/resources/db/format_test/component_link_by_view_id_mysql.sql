PREPARE component_link_by_view_id FROM
   'SELECT     s.component_link_id,
               IF(ul2.component_link_type_id IS NULL, l2.component_link_type_id, ul2.component_link_type_id) AS component_link_type_id2,
               IF(ul2.linked_component_id    IS NULL, l2.linked_component_id,    ul2.linked_component_id)    AS linked_component_id2,
               IF(ul2.description            IS NULL, l2.description,            ul2.description)            AS description2
          FROM component_links s
     LEFT JOIN user_components ul2 ON s.component_id = ul2.component_id
                                  AND ul2.user_id = ?
         WHERE s.view_id = ?';