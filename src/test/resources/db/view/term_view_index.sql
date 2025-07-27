-- --------------------------------------------------------

--
-- indexes for table term_views
--

CREATE INDEX term_views_term_idx ON term_views (term_id);
CREATE INDEX term_views_view_idx ON term_views (view_id);
CREATE INDEX term_views_view_link_type_idx ON term_views (view_link_type_id);
CREATE INDEX term_views_user_idx ON term_views (user_id);

--
-- indexes for table user_term_views
--

ALTER TABLE user_term_views
    ADD CONSTRAINT user_term_views_pkey PRIMARY KEY (term_view_id,user_id);
CREATE INDEX user_term_views_term_view_idx ON user_term_views (term_view_id);
CREATE INDEX user_term_views_user_idx ON user_term_views (user_id);
CREATE INDEX user_term_views_view_link_type_idx ON user_term_views (view_link_type_id);
