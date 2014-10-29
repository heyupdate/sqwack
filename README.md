# Sqwack

Semi-realtime profile photos in Slack.

Take a photo from your webcam every few minutes and automagically update your Slack profile photo!

![Funny faces](https://raw.githubusercontent.com/heyupdate/Sqwack/gh-pages/sqwack.gif)

We’re big fans of [Slack](http://www.slack.com) and [Sqwiggle](http://www.sqwiggle.com), but this is
is no way affiliated with either organisation. Use at your own risk and remember to turn it off
when you finish work to save embarrassment.

## Installation

You'll need ImageSnap installed. [Homebrew](http://mxcl.github.com/homebrew/)
makes this easy.

    brew install imagesnap

There are two ways to install Sqwack, using Composer or downloading a Phar binary.

### Using Composer

Install globally with [Composer](https://getcomposer.org/doc/03-cli.md#global):

    composer global require 'heyupdate/sqwack=~0.1'

To update you can then use:

    composer global update

Be sure to add `~/.composer/vendor/bin` to your `$PATH`.

### Download the Phar binary

Download the `sqwack.phar` binary from the latest release.

https://github.com/heyupdate/Sqwack/releases/latest

Make the file executable

    chmod +x ~/Downloads/sqwack.phar

Run it

    ~/Downloads/sqwack.phar

## Snap a photo

Run the command with your Slack team domain as the `-t` option
(i.e. for https://team.slack.com we would use "team")

    sqwack snap -t team

Enter your Slack email and password. Once logged in your cookie will be saved in `~/.sqwack` so you
don't need to keep entering your credentials.

To continuously capture a new photo every few minutes use the `cron` command:

    sqwack cron -t team

## Help

Get help by running:

    sqwack --help
