-- --------------------------------------------------------

--
-- constraints for table formula_links
--

ALTER TABLE formula_links
    ADD CONSTRAINT formula_links_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT formula_links_formula_link_type_fk FOREIGN KEY (formula_link_type_id) REFERENCES formula_link_types (formula_link_type_id),
    ADD CONSTRAINT formula_links_formula_fk FOREIGN KEY (formula_id) REFERENCES formulas (formula_id);

--
-- constraints for table user_formula_links
--

ALTER TABLE user_formula_links
    ADD CONSTRAINT user_formula_links_formula_link_fk FOREIGN KEY (formula_link_id) REFERENCES formula_links (formula_link_id),
    ADD CONSTRAINT user_formula_links_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT user_formula_links_formula_link_type_fk FOREIGN KEY (formula_link_type_id) REFERENCES formula_link_types (formula_link_type_id);
