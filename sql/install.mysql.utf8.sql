/*
 *  package: articlenotification
 *  copyright: Copyright (c) 2025. Jeroen Moolenschot | Joomill
 *  license: GNU General Public License version 2 or later
 *  link: https://www.joomill-extensions.com
 */

INSERT INTO `#__mail_templates` (`template_id`, `extension`, `language`, `subject`, `body`, `htmlbody`, `attachments`,
                                 `params`)
VALUES ('plg_system_articlenotification.mail', 'plg_system_articlenotification', '',
        'PLG_SYSTEM_ARTICLENOTIFICATION_EMAIL_SUBJECT', 'PLG_SYSTEM_ARTICLENOTIFICATION_EMAIL_BODY', '', '',
        '{"tags":["sitename", "title","text", "category","author"]}');