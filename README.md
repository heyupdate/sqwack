# Sqwack

## Installation

### Mac OS X

You'll need ImageSnap installed. [Homebrew](http://mxcl.github.com/homebrew/)
makes this easy. Simply do:

    brew install imagesnap

Install globally with [Composer]([https://getcomposer.org/doc/03-cli.md#global]):

    composer global require 'heyupdate/sqwack=~0.1'

To update you can then use:

    composer global update

Be sure to add `~/.composer/bin` to your `$PATH`.

## Snap a photo

Run the command with your Slack team domain as the `-t` option
(i.e. for https://team.slack.com we would use "team")

    ./bin/sqwack snap -t team

Enter your Slack email and password. Once logged in your cookie will be saved in `~/.sqwack` so you
don't need to keep entering your credentials.
