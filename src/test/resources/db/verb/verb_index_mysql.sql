-- --------------------------------------------------------

--
-- indexes for table verbs
--

ALTER TABLE verbs
    ADD PRIMARY KEY (verb_id),
    ADD KEY verbs_verb_name_idx (verb_name);
