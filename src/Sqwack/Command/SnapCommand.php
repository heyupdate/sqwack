<?php

namespace Sqwack\Command;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\FileCookieJar;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\CssSelector\CssSelector;
use Symfony\Component\DomCrawler\Crawler;
use Sqwack\Capturer\ImagesnapCapturer;
use Sqwack\Slack\SlackService;

class SnapCommand extends Command
{
    protected function configure()
    {
        $this->setName('snap');

        $this->addOption('device', 'd', InputOption::VALUE_REQUIRED, 'The name of camera to use. Use `imagesnap -l` to find a list of available devices.');
        $this->addOption('team', 't', InputOption::VALUE_REQUIRED, 'The team domain, e.g. "test" if you access Slack on https://test.slack.com.');
        $this->addOption('slack-app-open', 'a', InputOption::VALUE_OPTIONAL, "Don't snap if Slack.app is not running. Default to 0.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Store cookies and captured images in the home directory
        $homeDir = $_SERVER['HOME'] . '/.sqwack';
        if (!is_dir($homeDir)) {
            mkdir($homeDir, 0777, true);
        }

        $team = $input->getOption('team');
        if (!$team) {
            throw new \InvalidArgumentException('The team domain option --team must be provided');
        }

        if ($input->getOption('slack-app-open')) {
            $exec_result=null;
            // Next command tries to see if Slack.app is running, if so, we get back a 1 with some spaces as a result
            // if not we get back a 0 with some spaces before
            exec('ps aux | grep "[S]lack.app" | wc -l',$exec_result);
            $slackapp_is_running = $exec_result[0]*1;
            if(!$slackapp_is_running) {
                $output->writeln('<error>slack-app-open option is set but Slack.app is not running</error>');
            }
        }

        $client = new Client([
            'base_url' => sprintf('https://%s.slack.com', $team),
            'defaults' => [
                'allow_redirects' => false,
                'cookies' => new FileCookieJar(sprintf($homeDir . '/%s.cookies', $team))
            ]
        ]);

        $slack = new SlackService($client);

        if (!$slack->hasSession()) {
            list($email, $password) = $this->requestSlackCredentials($input, $output);

            $slack->login($email, $password);
        }

        $capturer = new ImagesnapCapturer();

        if ($output->isVerbose()) {
            $output->writeln('Capturing photo');
        }

        // Capture the photo
        $photoPath = $homeDir . '/photo.png';

        try {
            $capturer->capture($photoPath, 2, $input->getOption('device'));
        } catch (\Exception $exception) {
            $output->writeln('<error>Unable to capture new photo</error>');

            return;
        }

        if ($output->isVerbose()) {
            $output->writeln('Uploading photo');
        }

        try {
            // Upload the photo
            $slack->uploadPhoto($photoPath);
        } catch (\Exception $exception) {
            $output->writeln('<error>Unable to upload new photo</error>');

            return;
        }

        $output->writeln(sprintf('<info>Uploaded new photo at %s</info>', date('H:i')));
    }

    protected function requestSlackCredentials(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        $output->writeln('<info>Please sign in to Slack to continue. Your username and password are not stored.</info>');

        $emailQuestion = new Question('Email: ');
        $email = $helper->ask($input, $output, $emailQuestion);

        $passwordQuestion = new Question('Password: ');
        $passwordQuestion->setHidden(true);
        $passwordQuestion->setHiddenFallback(false);
        $password = $helper->ask($input, $output, $passwordQuestion);

        return array($email, $password);
    }
}