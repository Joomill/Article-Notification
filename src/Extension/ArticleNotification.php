<?php
/*
 *  package: articlenotification
 *  copyright: Copyright (c) 2025. Jeroen Moolenschot | Joomill
 *  license: GNU General Public License version 2 or later
 *  link: https://www.joomill-extensions.com
 */

namespace Joomill\Plugin\System\ArticleNotification\Extension;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Mail\Exception\MailDisabledException;
use Joomla\CMS\Mail\MailTemplate;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\User\UserFactoryAwareTrait;
use Joomla\Database\DatabaseAwareTrait;
use PHPMailer\PHPMailer\Exception as phpMailerException;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

final class ArticleNotification extends CMSPlugin
{
    use DatabaseAwareTrait;
    use UserFactoryAwareTrait;

    /**
     * Database driver
     *
     * @var    \Joomla\Database\DatabaseInterface
     * @since  4.0.0
     */
    protected $db;

    /**
     * Load plugin language files automatically
     *
     * @var    boolean
     * @since  3.6.3
     */
    protected $autoloadLanguage = true;

    /**
     * The notification email code is triggered after the page has fully rendered.
     *
     * @return  void
     *
     * @since   3.5
     */

    function onContentAfterSave($context, $article, $isNew)
    {

        // Don't send when article is created in the backend
        if ($this->getApplication()->isClient('administrator')) {
            return true;
        }

        // Don't send when exsisting article is edited
        if (!$isNew) {
            return false;
        }

        // Get Category name
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('title'))
            ->from($db->quoteName('#__categories'))
            ->where($db->quoteName('id') . ' = ' . $db->quote($article->catid));
        $db->setQuery($query);
        $article->category = $db->loadResult();

        // Check if Category needs to send a Notification
        if ($this->params->get('categories')) {
            if (!in_array($article->catid, $this->params->get('categories'))) {
                return false;
            }
        }

        // Get Author name
        $user = Factory::getApplication()->getIdentity($article->created_by);
        $article->author = $user->name;

        // Get Article text
        $introText = $article->introtext;
        $fullText = $article->fulltext;
        $fullArticleText = $introText . ' ' . $fullText;
        $article->content = strip_tags($fullArticleText);

        // Tags
        $substitutions = [
            'sitename' => $this->getApplication()->get('sitename'),
            'title' => $article->title,
            'text' => $article->content,
            'category' => $article->category,
            'author' => $article->author
        ];

        /*
         * Load the appropriate language. We try to load English (UK), the current user's language and the forced
         * language preference, in this order. This ensures that we'll never end up with untranslated strings in the
         * update email which would make Joomla! seem bad. So, please, if you don't fully understand what the
         * following code does DO NOT TOUCH IT. It makes the difference between a hobbyist CMS and a professional
         * solution!
         */
        $language = $this->getApplication()->getLanguage();
        $language->load('plg_system_articlenotification', JPATH_ADMINISTRATOR, 'en-GB', true, true);
        $language->load('plg_system_articlenotification', JPATH_ADMINISTRATOR, null, true, false);

        // Then try loading the preferred (forced) language
        $forcedLanguage = $this->params->get('language_override', '');

        if (!empty($forcedLanguage)) {
            $language->load('plg_system_articlenotification', JPATH_ADMINISTRATOR, $forcedLanguage, true, false);
        }

        // Let's find out the email addresses to notify
        $emails = explode(',', $this->params->get('email', ''));

        // Send the emails
        foreach ($emails as $email) {

            try {
                $mailer = new MailTemplate('plg_system_articlenotification.mail', $language->getTag());
                $mailer->addRecipient($email);
                $mailer->addTemplateData($substitutions);
                $mailer->send();
            } catch (MailDisabledException|phpMailerException $exception) {

                try {
                    Log::add(Text::_($exception->getMessage()), Log::WARNING, 'jerror');
                } catch (\RuntimeException $exception) {
                    $this->getApplication()->enqueueMessage(Text::_($exception->articleMessage()), 'warning');
                }

            }
        }
    }
}
