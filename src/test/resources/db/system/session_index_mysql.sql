-- --------------------------------------------------------
--
-- indexes for table sessions
--

ALTER TABLE sessions
    ADD PRIMARY KEY (session_id),
    ADD KEY sessions_uid_idx (uid);
