PREPARE formula_link_list_by_frm_id (bigint, bigint) AS
    SELECT s.formula_link_id,
           u.formula_link_id AS user_formula_link_id,
           s.user_id,
           s.formula_id,
           s.phrase_id,
           l.phrase_type_id AS phrase_type_id1,
           CASE WHEN (u.formula_link_type_id IS     NULL) THEN s.formula_link_type_id ELSE u.formula_link_type_id END AS formula_link_type_id,
           CASE WHEN (u.excluded             IS     NULL) THEN s.excluded             ELSE u.excluded             END AS excluded,
           CASE WHEN (u.share_type_id        IS     NULL) THEN s.share_type_id        ELSE u.share_type_id        END AS share_type_id,
           CASE WHEN (u.protect_id           IS     NULL) THEN s.protect_id           ELSE u.protect_id           END AS protect_id,
           CASE WHEN (ul.phrase_name   <> '' IS NOT TRUE) THEN l.phrase_name          ELSE ul.phrase_name         END AS phrase_name1,
           CASE WHEN (ul.description   <> '' IS NOT TRUE) THEN l.description          ELSE ul.description         END AS description1,
           CASE WHEN (ul.values              IS     NULL) THEN l.values               ELSE ul.values              END AS values1,
           CASE WHEN (ul.excluded            IS     NULL) THEN l.excluded             ELSE ul.excluded            END AS excluded1,
           CASE WHEN (ul.share_type_id       IS     NULL) THEN l.share_type_id        ELSE ul.share_type_id       END AS share_type_id1,
           CASE WHEN (ul.protect_id          IS     NULL) THEN l.protect_id           ELSE ul.protect_id          END AS protect_id1
      FROM formula_links s
 LEFT JOIN user_formula_links u ON s.formula_link_id =  u.formula_link_id AND  u.user_id = $1
 LEFT JOIN phrases l            ON s.phrase_id       =  l.phrase_id
 LEFT JOIN user_phrases ul      ON l.phrase_id       = ul.phrase_id       AND ul.user_id = $1
     WHERE s.formula_id = $2;