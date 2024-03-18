-- --------------------------------------------------------

--
-- indexes for table formula_links
--

CREATE INDEX formula_links_user_idx ON formula_links (user_id);
CREATE INDEX formula_links_link_type_idx ON formula_links (link_type_id);
CREATE INDEX formula_links_formula_idx ON formula_links (formula_id);
CREATE INDEX formula_links_phrase_idx ON formula_links (phrase_id);

--
-- indexes for table user_formula_links
--

ALTER TABLE user_formula_links ADD CONSTRAINT user_formula_links_pkey PRIMARY KEY (formula_link_id,user_id);
CREATE INDEX user_formula_links_formula_link_idx ON user_formula_links (formula_link_id);
CREATE INDEX user_formula_links_user_idx ON user_formula_links (user_id);
CREATE INDEX user_formula_links_link_type_idx ON user_formula_links (link_type_id);
