-- --------------------------------------------------------

--
-- indexes for table view_term_links
--

CREATE INDEX view_term_links_term_idx ON view_term_links (term_id);
CREATE INDEX view_term_links_view_idx ON view_term_links (view_id);
CREATE INDEX view_term_links_type_idx ON view_term_links (type_id);
CREATE INDEX view_term_links_user_idx ON view_term_links (user_id);
CREATE INDEX view_term_links_view_link_type_idx ON view_term_links (view_link_type_id);

--
-- indexes for table user_view_term_links
--

ALTER TABLE user_view_term_links
    ADD CONSTRAINT user_view_term_links_pkey PRIMARY KEY (view_term_link_id,user_id);
CREATE INDEX user_view_term_links_view_term_link_idx ON user_view_term_links (view_term_link_id);
CREATE INDEX user_view_term_links_user_idx ON user_view_term_links (user_id);
CREATE INDEX user_view_term_links_view_link_type_idx ON user_view_term_links (view_link_type_id);
