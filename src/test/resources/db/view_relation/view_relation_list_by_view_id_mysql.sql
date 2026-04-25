PREPARE view_relation_list_by_view_id FROM
    'SELECT s.view_relation_id,
            u.view_relation_id AS user_view_relation_id,
            s.user_id,
            s.parent_view_id,
            s.view_relation_type_id,
            s.child_view_id,
            IF(  u.description  IS NULL,  s.description,   u.description)  AS description,
            IF( ul.view_name    IS NULL,  l.view_name,    ul.view_name)    AS view_name1,
            IF( ul.description  IS NULL,  l.description,  ul.description)  AS description1,
            IF(ul2.view_name    IS NULL, l2.view_name,    ul2.view_name)   AS view_name2,
            IF(ul2.description  IS NULL, l2.description,  ul2.description) AS description2
       FROM view_relations s
  LEFT JOIN user_view_relations u ON  s.view_relation_id =   u.view_relation_id AND u.user_id = ?
  LEFT JOIN views l               ON  s.parent_view_id   =   l.view_id
  LEFT JOIN user_views ul         ON  l.view_id          =  ul.view_id          AND ul.user_id = ?
  LEFT JOIN views l2              ON  s.child_view_id    =  l2.view_id
  LEFT JOIN user_views ul2        ON l2.view_id          = ul2.view_id          AND ul2.user_id = ?
     WHERE (s.parent_view_id = ?
        OR  s.child_view_id = ?)';