<?php

defined('_JEXEC') or die('Restricted access');

class plgSystemWhoops extends \JPlugin
{
    protected $app;

    public function __construct($subject, $config = array())
    {
        parent::__construct($subject, $config);

        if (!$this->shouldLoadWhoops()) {
            return;
        }

        require __DIR__ . "/vendor/autoload.php";

        $run = new \Whoops\Run;
        $handler = new \Whoops\Handler\PrettyPageHandler;

        // Add some custom tables with relevant info about your application,
        // that could prove useful in the error page:
        $config = \JFactory::getConfig();

        if ($this->params->get('remove_passwords', true)) {
            $config->remove('ftp_pass');
            $config->remove('password');
            $config->remove('smtppass');
            $config->remove('proxy_pass');
        }
        $handler->addDataTable('Joomla Config', $config->toArray());

        // Set the title of the error page:
        $handler->setPageTitle("Whoops! There was a problem.");

        $run->appendHandler($handler);

        // Add a special handler to deal with AJAX requests with an
        // equally-informative JSON response. Since this handler is
        // first in the stack, it will be executed before the error
        // page handler, and will have a chance to decide if anything
        // needs to be done.
        if (\Whoops\Util\Misc::isAjaxRequest()) {
            $run->appendHandler(new \Whoops\Handler\JsonResponseHandler);
        }

        // Register the handler with PHP, and you're set!
        $run->register();
    }

    private function shouldLoadWhoops(): bool
    {
        $enableJDebug = $this->params->get('enable_with_jdebug');
        $enableJDebug = $enableJDebug != '0';

        if ($enableJDebug && defined('JDEBUG') && JDEBUG) {
            return true;
        }

        $userGroups = $this->params->get('enable_user_groups', []);
        $user = \JFactory::getUser();

        foreach ($userGroups as $group) {
            if (in_array($group, $user->groups)) {
                return true;
            }
        }

        $urlKey = $this->params->get('enable_url_key', false);
        if ($urlKey && $this->app->input->exists($urlKey)) {
            return true;
        }

        return false;
    }
}
