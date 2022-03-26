PREPARE view_components_by_view_id (int, int) AS
    SELECT      s.view_component_link_id,
                u.view_component_link_id AS user_view_component_link_id,
                s.user_id,
                s.view_id,
                s.view_component_id,
                CASE WHEN (u.order_nbr                  IS     NULL) THEN s.order_nbr               ELSE u.order_nbr                END AS order_nbr,
                CASE WHEN (u.position_type              IS     NULL) THEN s.position_type           ELSE u.position_type            END AS position_type,
                CASE WHEN (u.excluded                   IS     NULL) THEN s.excluded                ELSE u.excluded                 END AS excluded,
                CASE WHEN (u.share_type_id              IS     NULL) THEN s.share_type_id           ELSE u.share_type_id            END AS share_type_id,
                CASE WHEN (u.protect_id                 IS     NULL) THEN s.protect_id              ELSE u.protect_id               END AS protect_id,
                CASE WHEN (ul.comment             <> '' IS NOT TRUE) THEN l.comment                 ELSE ul.comment                 END AS comment,
                CASE WHEN (ul.view_component_name <> '' IS NOT TRUE) THEN l.view_component_name     ELSE ul.view_component_name     END AS view_component_name,
                CASE WHEN (ul2.view_component_type_id   IS     NULL) THEN l2.view_component_type_id ELSE ul2.view_component_type_id END AS view_component_type_id2,
                CASE WHEN (ul2.word_id_row              IS     NULL) THEN l2.word_id_row            ELSE ul2.word_id_row            END AS word_id_row2,
                CASE WHEN (ul2.link_type_id             IS     NULL) THEN l2.link_type_id           ELSE ul2.link_type_id           END AS link_type_id2,
                CASE WHEN (ul2.formula_id               IS     NULL) THEN l2.formula_id             ELSE ul2.formula_id             END AS formula_id2,
                CASE WHEN (ul2.word_id_col              IS     NULL) THEN l2.word_id_col            ELSE ul2.word_id_col            END AS word_id_col2,
                CASE WHEN (ul2.word_id_col2             IS     NULL) THEN l2.word_id_col2           ELSE ul2.word_id_col2           END AS word_id_col22,
                CASE WHEN (ul2.excluded                 IS     NULL) THEN l2.excluded               ELSE ul2.excluded               END AS excluded2,
                CASE WHEN (ul2.share_type_id            IS     NULL) THEN l2.share_type_id          ELSE ul2.share_type_id          END AS share_type_id2,
                CASE WHEN (ul2.protect_id               IS     NULL) THEN l2.protect_id             ELSE ul2.protect_id             END AS protect_id2
           FROM view_component_links s
      LEFT JOIN user_view_component_links u ON s.view_component_link_id = u.view_component_link_id
                                           AND u.user_id = $1
      LEFT JOIN view_components l           ON s.view_component_id = l.view_component_id
      LEFT JOIN user_view_components ul     ON l.view_component_id = ul.view_component_id
                                           AND ul.user_id = $1
      LEFT JOIN view_components l2          ON s.view_component_id = l2.view_component_id
      LEFT JOIN user_view_components ul2    ON l2.view_component_id = ul2.view_component_id
                                           AND ul2.user_id = $1
    WHERE s.view_id = $2
       ORDER BY s.order_nbr;
