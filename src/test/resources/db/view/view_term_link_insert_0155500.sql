PREPARE view_term_link_insert_0155500
    (bigint,bigint) AS
INSERT INTO view_term_links (user_id,)
     VALUES ($1,$2)
  RETURNING view_term_link_id;