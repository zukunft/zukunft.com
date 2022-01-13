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
           CASE WHEN (u.word_link_name  <> '' IS NOT TRUE) THEN s.word_link_name ELSE u.word_link_name  END AS word_link_name,
           CASE WHEN (u.description     <> '' IS NOT TRUE) THEN s.description    ELSE u.description     END AS description,
           CASE WHEN (u.excluded              IS     NULL) THEN s.excluded       ELSE u.excluded        END AS excluded,
           CASE WHEN (u.share_type_id         IS     NULL) THEN s.share_type_id  ELSE u.share_type_id   END AS share_type_id,
           CASE WHEN (u.protect_id            IS     NULL) THEN s.protect_id     ELSE u.protect_id      END AS protect_id,
           CASE WHEN (ul.word_name      <> '' IS NOT TRUE) THEN l.word_name      ELSE ul.word_name      END AS word_name,
           CASE WHEN (ul.plural         <> '' IS NOT TRUE) THEN l.plural         ELSE ul.plural         END AS plural,
           CASE WHEN (ul.description    <> '' IS NOT TRUE) THEN l.description    ELSE ul.description    END AS description,
           CASE WHEN (ul.word_type_id   <> '' IS NOT TRUE) THEN l.word_type_id   ELSE ul.word_type_id   END AS word_type_id,
           CASE WHEN (ul.view_id        <> '' IS NOT TRUE) THEN l.view_id        ELSE ul.view_id        END AS view_id,
           CASE WHEN (ul.excluded       <> '' IS NOT TRUE) THEN l.excluded       ELSE ul.excluded       END AS excluded,
           CASE WHEN (ul2.word_name     <> '' IS NOT TRUE) THEN l2.word_name     ELSE ul2.word_name     END AS word_name,
           CASE WHEN (ul2.plural        <> '' IS NOT TRUE) THEN l2.plural        ELSE ul2.plural        END AS plural,
           CASE WHEN (ul2.description   <> '' IS NOT TRUE) THEN l2.description   ELSE ul2.description   END AS description,
           CASE WHEN (ul2.word_type_id  <> '' IS NOT TRUE) THEN l2.word_type_id  ELSE ul2.word_type_id  END AS word_type_id,
           CASE WHEN (ul2.view_id       <> '' IS NOT TRUE) THEN l2.view_id       ELSE ul2.view_id       END AS view_id,
           CASE WHEN (ul2.excluded      <> '' IS NOT TRUE) THEN l2.excluded      ELSE ul2.excluded      END AS excluded
      FROM word_links s
 LEFT JOIN user_word_links u ON  s.word_link_id   =   u.word_link_id AND u.user_id = $1
 LEFT JOIN words l           ON  s.from_phrase_id =   l.word_id
 LEFT JOIN user_words ul     ON  l.word_id        =  ul.word_id      AND u.user_id = $1
 LEFT JOIN words l2          ON  s.to_phrase_id   =  l2.word_id
 LEFT JOIN user_words ul2    ON l2.word_id        = ul2.word_id      AND u.user_id = $1
     WHERE s.word_link_id = ANY ($2)
  ORDER BY s.verb_id, word_link_name;


PREPARE word_link_by_id (int, int[]) AS
    SELECT
            s.word_link_id,
            s.from_phrase_id,
            s.verb_id,
            s.to_phrase_id,
            s.description,
            s.word_link_name,
            CASE WHEN (u.excluded          IS     NULL) THEN s.excluded      ELSE u.excluded     END AS excluded,
            t1.word_id AS word_id1,
            t1.user_id AS user_id1,
            CASE WHEN (u1.word_name    <> '' IS NOT TRUE) THEN t1.word_name     ELSE u1.word_name     END AS word_name1,
            CASE WHEN (u1.plural       <> '' IS NOT TRUE) THEN t1.plural        ELSE u1.plural        END AS plural1,
            CASE WHEN (u1.description  <> '' IS NOT TRUE) THEN t1.description   ELSE u1.description   END AS description1,
            CASE WHEN (u1.word_type_id       IS     NULL) THEN t1.word_type_id  ELSE u1.word_type_id  END AS word_type_id1,
            CASE WHEN (u1.view_id            IS     NULL) THEN t1.view_id       ELSE u1.view_id       END AS view_id1,
            CASE WHEN (u1.excluded           IS     NULL) THEN t1.excluded      ELSE u1.excluded      END AS excluded1,
            t1.values AS values1,
            t2.word_id AS word_id2,
            t2.user_id AS user_id2,
            CASE WHEN (u2.word_name   <> '' IS NOT TRUE) THEN t2.word_name    ELSE u2.word_name    END AS word_name2,
            CASE WHEN (u2.plural      <> '' IS NOT TRUE) THEN t2.plural       ELSE u2.plural       END AS plural2,
            CASE WHEN (u2.description <> '' IS NOT TRUE) THEN t2.description  ELSE u2.description  END AS description2,
            CASE WHEN (u2.word_type_id      IS     NULL) THEN t2.word_type_id ELSE u2.word_type_id END AS word_type_id2,
            CASE WHEN (u2.view_id           IS     NULL) THEN t2.view_id      ELSE u2.view_id      END AS view_id2,
            CASE WHEN (u2.excluded          IS     NULL) THEN t2.excluded     ELSE u2.excluded     END AS excluded2,
            t2.values AS values2
       FROM word_links s
  LEFT JOIN user_word_links u ON s.word_link_id = u.word_link_id AND u.user_id = $1,
         words t1     LEFT JOIN user_words u1       ON u1.word_id  = t1.word_id
             AND u1.user_id  = $1 ,
         words t2     LEFT JOIN user_words u2       ON u2.word_id = t2.word_id
             AND u2.user_id = $1
    WHERE s.from_phrase_id = t1.word_id
      AND s.to_phrase_id   = t2.word_id
      AND s.word_link_id   = ANY ($2)
    ORDER BY s.verb_id, word_link_name;