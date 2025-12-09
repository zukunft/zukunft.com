-- --------------------------------------------------------

--
-- indexes for table ip_ranges
--

ALTER TABLE ip_ranges
    ADD KEY ip_ranges_ip_from_idx (ip_from),
    ADD KEY ip_ranges_ip_to_idx (ip_to);
