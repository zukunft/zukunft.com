-- --------------------------------------------------------

--
-- indexes for table ip_ranges
--

CREATE INDEX ip_ranges_ip_from_idx ON ip_ranges (ip_from);
CREATE INDEX ip_ranges_ip_to_idx ON ip_ranges (ip_to);
