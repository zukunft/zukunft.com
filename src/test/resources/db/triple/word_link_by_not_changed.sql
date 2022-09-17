PREPARE word_link_by_not_changed (int) AS
     SELECT user_id
       FROM user_word_links
      WHERE word_link_id = $1
        AND (excluded <> 1 OR excluded is NULL);