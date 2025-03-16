-- OpenRouter Requests Table Schema
-- This file defines the schema for the openrouter_requests table to log API interactions

-- Create the openrouter_requests table if it doesn't exist
CREATE TABLE IF NOT EXISTS `openrouter_requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `prompt` text NOT NULL,
  `model` varchar(100) NOT NULL,
  `temperature` float NOT NULL DEFAULT '0.7',
  `max_tokens` int(11) NOT NULL DEFAULT '1024',
  `request_type` varchar(50) DEFAULT NULL COMMENT 'e.g., content, research, keyword-extraction',
  `request_source` varchar(100) DEFAULT NULL COMMENT 'which part of the application made the request',
  `additional_params` json DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `response_text` longtext,
  `completion_tokens` int(11) DEFAULT NULL,
  `prompt_tokens` int(11) DEFAULT NULL,
  `total_tokens` int(11) DEFAULT NULL,
  `cost` decimal(10,6) DEFAULT NULL,
  `request_duration` int(11) DEFAULT NULL COMMENT 'in milliseconds',
  `status` varchar(20) NOT NULL DEFAULT 'pending' COMMENT 'pending, completed, failed',
  `error_message` text,
  PRIMARY KEY (`id`),
  KEY `openrouter_requests_model_index` (`model`),
  KEY `openrouter_requests_status_index` (`status`),
  KEY `openrouter_requests_created_at_index` (`created_at`),
  KEY `openrouter_requests_request_type_index` (`request_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; 