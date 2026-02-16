PREPARE view_relation_list_by_view_id (bigint, bigint, bigint) AS
    SELECT s.view_relation_id,
           u.view_relation_id AS user_view_relation_id,
           s.user_id,
           s.parent_view_id,
           s.view_relation_type_id,
           s.child_view_id,
           CASE WHEN (  u.description <> '' IS NOT TRUE) THEN  s.description   ELSE   u.description    END AS description,
           CASE WHEN ( ul.view_name   <> '' IS NOT TRUE) THEN  l.view_name     ELSE  ul.view_name      END AS view_name1,
           CASE WHEN ( ul.description <> '' IS NOT TRUE) THEN  l.description   ELSE  ul.description    END AS description1,
           CASE WHEN (ul2.view_name   <> '' IS NOT TRUE) THEN l2.view_name     ELSE ul2.view_name      END AS view_name2,
           CASE WHEN (ul2.description <> '' IS NOT TRUE) THEN l2.description   ELSE ul2.description    END AS description2
      FROM view_relations s
 LEFT JOIN user_view_relations u ON  s.view_relation_id =   u.view_relation_id AND u.user_id = $1
 LEFT JOIN views l               ON  s.parent_view_id   =   l.view_id
 LEFT JOIN user_views ul         ON  l.view_id          =  ul.view_id          AND ul.user_id = $1
 LEFT JOIN views l2              ON  s.child_view_id    =  l2.view_id
 LEFT JOIN user_views ul2        ON l2.view_id          = ul2.view_id          AND ul2.user_id = $1
     WHERE (s.parent_view_id = $2
        OR  s.child_view_id = $3);