PREPARE view_term_link_insert_0155000
    (bigint, bigint, bigint) AS
INSERT INTO view_term_links (user_id, view_id, term_id)
     VALUES ($1, $2, $3)
  RETURNING view_term_link_id;