-- --------------------------------------------------------
--
-- indexes for table sessions
--

ALTER TABLE sessions
    ADD KEY sessions_uid_idx (uid);
