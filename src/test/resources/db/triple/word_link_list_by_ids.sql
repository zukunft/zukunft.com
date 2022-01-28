PREPARE word_link_list_by_ids (int, int[]) AS
    SELECT s.word_link_id,
           u.word_link_id AS user_word_link_id,
           s.user_id,
           s.from_phrase_id,
           s.to_phrase_id,
           s.verb_id,
           s.word_type_id,
           s.word_link_condition_id,
           s.word_link_condition_type_id,
           l.word_type_id AS word_type_id1,
           l2.word_type_id AS word_type_id2,
           CASE WHEN (u.name_given     <> '' IS NOT TRUE) THEN s.name_given     ELSE u.name_given      END AS name_given,
           CASE WHEN (u.name_generated <> '' IS NOT TRUE) THEN s.name_generated ELSE u.name_generated  END AS name_generated,
           CASE WHEN (u.description    <> '' IS NOT TRUE) THEN s.description    ELSE u.description     END AS description,
           CASE WHEN (u.values               IS     NULL) THEN s.values         ELSE u.values          END AS values,
           CASE WHEN (u.excluded             IS     NULL) THEN s.excluded       ELSE u.excluded        END AS excluded,
           CASE WHEN (u.share_type_id        IS     NULL) THEN s.share_type_id  ELSE u.share_type_id   END AS share_type_id,
           CASE WHEN (u.protect_id           IS     NULL) THEN s.protect_id     ELSE u.protect_id      END AS protect_id,
           CASE WHEN (ul.name_used     <> '' IS NOT TRUE) THEN l.name_used      ELSE ul.name_used      END AS name_used1,
           CASE WHEN (ul.description   <> '' IS NOT TRUE) THEN l.description    ELSE ul.description    END AS description1,
           CASE WHEN (ul.values              IS     NULL) THEN l.values         ELSE ul.values         END AS values1,
           CASE WHEN (ul.excluded            IS     NULL) THEN l.excluded       ELSE ul.excluded       END AS excluded1,
           CASE WHEN (ul.share_type_id       IS     NULL) THEN l.share_type_id  ELSE ul.share_type_id  END AS share_type_id1,
           CASE WHEN (ul.protect_id          IS     NULL) THEN l.protect_id     ELSE ul.protect_id     END AS protect_id1,
           CASE WHEN (ul2.name_used    <> '' IS NOT TRUE) THEN l2.name_used     ELSE ul2.name_used     END AS name_used2,
           CASE WHEN (ul2.description  <> '' IS NOT TRUE) THEN l2.description   ELSE ul2.description   END AS description2,
           CASE WHEN (ul2.values             IS     NULL) THEN l2.values        ELSE ul2.values        END AS values2,
           CASE WHEN (ul2.excluded           IS     NULL) THEN l2.excluded      ELSE ul2.excluded      END AS excluded2,
           CASE WHEN (ul2.share_type_id      IS     NULL) THEN l2.share_type_id ELSE ul2.share_type_id END AS share_type_id2,
           CASE WHEN (ul2.protect_id         IS     NULL) THEN l2.protect_id    ELSE ul2.protect_id    END AS protect_id2
      FROM word_links s
 LEFT JOIN user_word_links u ON  s.word_link_id   =   u.word_link_id AND   u.user_id = $1
 LEFT JOIN phrases l         ON  s.from_phrase_id =   l.phrase_id
 LEFT JOIN user_phrases ul   ON  l.phrase_id      =  ul.phrase_id    AND  ul.user_id = $1
 LEFT JOIN phrases l2        ON  s.to_phrase_id   =  l2.phrase_id
 LEFT JOIN user_phrases ul2  ON l2.phrase_id      = ul2.phrase_id    AND ul2.user_id = $1
     WHERE s.word_link_id = ANY ($2)
  ORDER BY s.verb_id, name_given;
