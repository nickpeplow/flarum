-- Keywords Table Schema
-- This file defines the schema for the keywords table used by the automation dashboard

-- Create the keywords table if it doesn't exist
CREATE TABLE IF NOT EXISTS `keywords` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `keyword` varchar(255) NOT NULL,
  `post_id` int(10) unsigned DEFAULT NULL,
  `tag_id` int(10) unsigned DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=pending, 1=approved, 2=rejected',
  `research` TEXT DEFAULT NULL COMMENT 'AI-generated research about the keyword',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `keywords_keyword_index` (`keyword`(191)),
  KEY `keywords_post_id_index` (`post_id`),
  KEY `keywords_tag_id_index` (`tag_id`),
  KEY `keywords_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add foreign key constraints if posts and tags tables exist
-- These are commented out by default since they depend on the Flarum structure
-- Uncomment and modify as needed

/*
ALTER TABLE `keywords`
  ADD CONSTRAINT `keywords_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `keywords_tag_id_foreign` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE;
*/

-- Example seed data (commented out by default)
/*
INSERT INTO `keywords` (`keyword`, `post_id`, `tag_id`, `status`, `created_at`)
VALUES 
  ('example keyword', 1, NULL, 0, NOW()),
  ('approved keyword', 2, 1, 1, NOW()),
  ('rejected keyword', 3, 2, 2, NOW());
*/ 