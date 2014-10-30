<?php

namespace Sqwack\Command;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\FileCookieJar;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CronCommand extends Command
{
    protected function configure()
    {
        $this->setName('cron');

        $this->addOption('device', 'd', InputOption::VALUE_REQUIRED, 'The name of camera to use. Use `imagesnap -l` to find a list of available devices.');
        $this->addOption('team', 't', InputOption::VALUE_REQUIRED, 'The team domain, e.g. "test" if you access Slack on https://test.slack.com.');
        $this->addOption('sleep', 's', InputOption::VALUE_REQUIRED, 'The number of minutes to wait before taking the next photo. Defaults to 3.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $waitMinutes = $input->getOption('sleep');
        if (!$waitMinutes) {
            $waitMinutes = 3;
        }

        while (true) {
            $this->getApplication()->find('snap')->run($input, $output);

            // Wait between snaps
            sleep(60 * $waitMinutes);
        }
    }
}
