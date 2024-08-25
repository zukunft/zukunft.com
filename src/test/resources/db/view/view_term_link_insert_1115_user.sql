PREPARE view_term_link_insert_1115_user
    (bigint, bigint, text, smallint) AS
INSERT INTO user_view_term_links (view_term_link_id, user_id, description, view_link_type_id)
     VALUES ($1, $2, $3, $4)
  RETURNING view_term_link_id;