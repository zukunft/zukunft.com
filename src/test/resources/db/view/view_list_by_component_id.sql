PREPARE view_list_by_component_id (int, int) AS
    SELECT s.view_id,
           u.view_id AS user_view_id,
           s.user_id,
           s.code_id,
           l.view_id,
           l.component_id,
           CASE WHEN (u.view_name   <> '' IS NOT TRUE) THEN s.view_name     ELSE u.view_name     END AS view_name,
           CASE WHEN (u.description <> '' IS NOT TRUE) THEN s.description   ELSE u.description   END AS description,
           CASE WHEN (u.view_type_id      IS     NULL) THEN s.view_type_id  ELSE u.view_type_id  END AS view_type_id,
           CASE WHEN (u.excluded          IS     NULL) THEN s.excluded      ELSE u.excluded      END AS excluded,
           CASE WHEN (u.share_type_id     IS     NULL) THEN s.share_type_id ELSE u.share_type_id END AS share_type_id,
           CASE WHEN (u.protect_id        IS     NULL) THEN s.protect_id    ELSE u.protect_id    END AS protect_id
      FROM views s
 LEFT JOIN user_views u      ON s.view_id = u.view_id AND u.user_id = $1
 LEFT JOIN component_links l ON s.view_id = l.view_id
     WHERE l.component_id = $2
  ORDER BY l.order_nbr;