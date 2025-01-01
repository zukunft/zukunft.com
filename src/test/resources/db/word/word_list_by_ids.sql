PREPARE word_list_by_ids (bigint, bigint[]) AS
    SELECT     s.word_id,
               u.word_id AS user_word_id,
               s.user_id,
               s.values,
               s.code_id,
               CASE WHEN (u.word_name   <> ''  IS NOT TRUE) THEN s.word_name      ELSE u.word_name      END AS word_name,
               CASE WHEN (u.plural      <> ''  IS NOT TRUE) THEN s.plural         ELSE u.plural         END AS plural,
               CASE WHEN (u.description <> ''  IS NOT TRUE) THEN s.description    ELSE u.description    END AS description,
               CASE WHEN (u.phrase_type_id     IS     NULL) THEN s.phrase_type_id ELSE u.phrase_type_id END AS phrase_type_id,
               CASE WHEN (u.view_id            IS     NULL) THEN s.view_id        ELSE u.view_id        END AS view_id,
               CASE WHEN (u.excluded           IS     NULL) THEN s.excluded       ELSE u.excluded       END AS excluded,
               CASE WHEN (u.share_type_id      IS     NULL) THEN s.share_type_id  ELSE u.share_type_id  END AS share_type_id,
               CASE WHEN (u.protect_id         IS     NULL) THEN s.protect_id     ELSE u.protect_id     END AS protect_id
          FROM words s
     LEFT JOIN user_words u ON s.word_id = u.word_id
           AND u.user_id = $1
         WHERE s.word_id = ANY ($2)
      ORDER BY s.values DESC, word_name;