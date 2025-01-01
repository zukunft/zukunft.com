PREPARE component_link_list_by_component_id FROM
    'SELECT s.component_link_id,
            u.component_link_id AS user_component_link_id,
            s.user_id,
            s.view_id,
            s.component_id,
            l.code_id,
            IF(u.order_nbr        IS NULL,  s.order_nbr,        u.order_nbr)        AS order_nbr,
            IF(u.position_type_id IS NULL,  s.position_type_id, u.position_type_id) AS position_type_id,
            IF(u.view_style_id    IS NULL, s.view_style_id,     u.view_style_id)    AS view_style_id,
            IF(u.excluded         IS NULL,  s.excluded,         u.excluded)         AS excluded,
            IF(u.share_type_id    IS NULL,  s.share_type_id,    u.share_type_id)    AS share_type_id,
            IF(u.protect_id       IS NULL,  s.protect_id,       u.protect_id)       AS protect_id,
            IF(ul.description     IS NULL,  l.description,     ul.description)      AS description,
            IF(ul2.view_type_id   IS NULL, l2.view_type_id,   ul2.view_type_id)     AS view_type_id2,
            IF(ul2.view_style_id  IS NULL, l2.view_style_id,  ul2.view_style_id)    AS view_style_id2,
            IF(ul2.excluded       IS NULL, l2.excluded,       ul2.excluded)         AS excluded2,
            IF(ul2.share_type_id  IS NULL, l2.share_type_id,  ul2.share_type_id)    AS share_type_id2,
            IF(ul2.protect_id     IS NULL, l2.protect_id,     ul2.protect_id)       AS protect_id2
       FROM component_links s
  LEFT JOIN user_component_links u ON s.component_link_id = u.component_link_id AND   u.user_id = ?
  LEFT JOIN views                l ON s.view_id           =   l.view_id
  LEFT JOIN user_views          ul ON l.view_id           =  ul.view_id         AND  ul.user_id = ?
  LEFT JOIN views               l2 ON s.view_id           =  l2.view_id
  LEFT JOIN user_views         ul2 ON l2.view_id          = ul2.view_id         AND ul2.user_id = ?
      WHERE s.component_id = ?';
