-- --------------------------------------------------------

--
-- indexes for table config
--

ALTER TABLE config
    ADD KEY config_config_name_idx (config_name),
    ADD KEY config_code_idx (code_id);