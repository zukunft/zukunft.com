PREPARE component_link_list_by_component_id (bigint, bigint) AS
    SELECT s.component_link_id,
           u.component_link_id AS user_component_link_id,
           s.user_id,
           s.view_id,
           s.component_id,
           l.code_id,
           CASE WHEN (u.order_nbr          IS     NULL) THEN s.order_nbr      ELSE u.order_nbr       END AS order_nbr,
           CASE WHEN (u.position_type      IS     NULL) THEN s.position_type  ELSE u.position_type   END AS position_type,
           CASE WHEN (u.excluded           IS     NULL) THEN s.excluded       ELSE u.excluded        END AS excluded,
           CASE WHEN (u.share_type_id      IS     NULL) THEN s.share_type_id  ELSE u.share_type_id   END AS share_type_id,
           CASE WHEN (u.protect_id         IS     NULL) THEN s.protect_id     ELSE u.protect_id      END AS protect_id,
           CASE WHEN (ul.description <> '' IS NOT TRUE) THEN l.description    ELSE ul.description    END AS description,
           CASE WHEN (ul2.view_type_id     IS     NULL) THEN l2.view_type_id  ELSE ul2.view_type_id  END AS view_type_id2,
           CASE WHEN (ul2.excluded         IS     NULL) THEN l2.excluded      ELSE ul2.excluded      END AS excluded2,
           CASE WHEN (ul2.share_type_id    IS     NULL) THEN l2.share_type_id ELSE ul2.share_type_id END AS share_type_id2,
           CASE WHEN (ul2.protect_id       IS     NULL) THEN l2.protect_id    ELSE ul2.protect_id    END AS protect_id2
      FROM component_links s
 LEFT JOIN user_component_links u ON  s.component_link_id =   u.component_link_id AND u.user_id = $1
 LEFT JOIN views                l ON  s.view_id           =   l.view_id
 LEFT JOIN user_views          ul ON  l.view_id           =  ul.view_id           AND ul.user_id = $1
 LEFT JOIN views               l2 ON  s.view_id           =  l2.view_id
 LEFT JOIN user_views         ul2 ON l2.view_id           = ul2.view_id           AND ul2.user_id = $1
     WHERE s.component_id = $2;
