# Sqwack

## Installation

### Mac OS X

You'll need ImageSnap installed. [Homebrew](http://mxcl.github.com/homebrew/)
makes this easy. Simply do:

    brew install imagesnap

Install the dependencies with [Composer]([http://getcomposer.org]):

    composer install

## Snap a photo

Run the command with your Slack team domain as the `-t` option
(i.e. for https://team.slack.com we would use "team")

    ./bin/sqwack snap -t team

Enter your Slack email and password. Once logged in your cookie will be saved in `~/.sqwack` so you
don't need to keep entering your credentials.
