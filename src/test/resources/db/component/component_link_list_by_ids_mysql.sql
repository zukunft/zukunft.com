PREPARE component_link_list_by_ids FROM
    'SELECT s.component_link_id,
            u.component_link_id AS user_component_link_id,
            s.user_id,
            s.view_id,
            s.component_id,
            IF(u.order_nbr        IS NULL, s.order_nbr,        u.order_nbr)        AS order_nbr,
            IF(u.position_type_id IS NULL, s.position_type_id, u.position_type_id) AS position_type_id,
            IF(u.view_style_id    IS NULL, s.view_style_id,    u.view_style_id)    AS view_style_id,
            IF(u.excluded         IS NULL, s.excluded,         u.excluded)         AS excluded,
            IF(u.share_type_id    IS NULL, s.share_type_id,    u.share_type_id)    AS share_type_id,
            IF(u.protect_id       IS NULL, s.protect_id,       u.protect_id)       AS protect_id,
            IF(ul.view_name       IS NULL, l.view_name,        ul.view_name)       AS view_name1,
            IF(ul.description     IS NULL, l.description,      ul.description)     AS description1,
            IF(ul2.component_name IS NULL, l2.component_name,  ul2.component_name) AS component_name2,
            IF(ul2.description    IS NULL, l2.description,     ul2.description)    AS description2
       FROM component_links s
  LEFT JOIN user_component_links u ON s.component_link_id =  u.component_link_id AND  u.user_id = ?
  LEFT JOIN views l                ON s.view_id           =  l.view_id
  LEFT JOIN user_views ul          ON l.view_id           = ul.view_id           AND ul.user_id = ?
  LEFT JOIN components l2          ON s.component_id      = l2.component_id
  LEFT JOIN user_components ul2    ON l2.component_id     = ul2.component_id     AND ul2.user_id = ?
      WHERE s.component_link_id IN (?)';