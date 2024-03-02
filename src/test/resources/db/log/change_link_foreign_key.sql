-- --------------------------------------------------------

--
-- constraints for table change_links
--

ALTER TABLE change_links
    ADD CONSTRAINT change_links_user_fk FOREIGN KEY (user_id) REFERENCES users (user_id),
    ADD CONSTRAINT change_links_change_action_fk FOREIGN KEY (change_action_id) REFERENCES change_actions (change_action_id),
    ADD CONSTRAINT change_links_change_table_fk FOREIGN KEY (change_table_id) REFERENCES change_tables (change_table_id);
