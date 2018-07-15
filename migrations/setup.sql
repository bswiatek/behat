CREATE TABLE IF NOT EXISTS migrations(
  version INT UNSIGNED NOT NULL,
  `time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  status ENUM('success', 'error'),
  PRIMARY KEY (version, status)
);