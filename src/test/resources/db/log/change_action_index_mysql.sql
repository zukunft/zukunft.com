-- --------------------------------------------------------

--
-- indexes for table change_actions
--

ALTER TABLE change_actions
    ADD PRIMARY KEY (change_action_id),
    ADD KEY change_actions_change_action_name_idx (change_action_name);
