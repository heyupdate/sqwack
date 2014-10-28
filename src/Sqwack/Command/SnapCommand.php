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
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Store cookies and captured images in the home directory
        $homeDir = $_SERVER['HOME'] . '/.sqwack';
        if (!is_dir($homeDir)) {
            mkdir($homeDir, 777, true);
        }

        $team = $input->getOption('team');
        if (!$team) {
            throw new \InvalidArgumentException('The team domain option --team must be provided');
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

        // Capture the photo
        $photoPath = $homeDir . '/photo.png';
        $capturer->capture($photoPath, 2, $input->getOption('device'));

        // Upload the photo
        $slack->uploadPhoto($photoPath);

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