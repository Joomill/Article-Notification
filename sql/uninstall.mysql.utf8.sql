/*
 *  package: articlenotification
 *  copyright: Copyright (c) 2025. Jeroen Moolenschot | Joomill
 *  license: GNU General Public License version 2 or later
 *  link: https://www.joomill-extensions.com
 */

DELETE
FROM `#__mail_templates`
WHERE `template_id` = 'plg_system_articlenotification.mail';