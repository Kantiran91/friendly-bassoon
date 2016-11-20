<?php

require( 'vendor/autoload.php' );

define('DS', DIRECTORY_SEPARATOR);

use Dotenv\Dotenv;
use Gherkins\RegExpBuilderPHP\RegExpBuilder;
use Symfony\Component\Yaml\Yaml;

// Load environment variables from the .env file
$dotenv = new Dotenv(__DIR__);
$dotenv->load();

// Create a new RegExpBuilder instance
$builder = new RegExpBuilder();

// Read filters
$filters = Yaml::parse(file_get_contents(__DIR__ . '/filters.yaml')) ? : [];

// Connect to the server, authenticate, and select the inbox
$server = new \Ddeboer\Imap\Server(getenv('MAIL_HOST'), getenv('MAIL_PORT'));
$conn = $server->authenticate(getenv('MAIL_USER'), getenv('MAIL_PASS'));
$mailbox = $conn->getMailbox('INBOX');

// Debug
print( 'Number of mails: ' . $mailbox->count() . "\n" );

// Get all messages in the current mailbox
$msgs = $mailbox->getMessages();

// No messages, then let user know and exit with 1
if ( ! $msgs ) {
    print( 'Mailbox is empty' );
    exit(1);
}

// Debug
print( 'Reading mails' . "\n" );

// Process each mail
foreach ( $msgs as $msg ) {
    /** @var \Ddeboer\Imap\Message $msg */
    $msg = $msg->keepUnseen();

    // Loop over all filters
    foreach ( $filters as $filter ) {
        // Ensure we have all the required array objects
        if ( ! ( array_key_exists('field', $filter) && array_key_exists('value', $filter) && array_key_exists('comparator', $filter) && array_key_exists('target', $filter) ) ) {
            print( 'not all required fields in config' . "\n" );

            continue;
        }

        // This is a bit cumbersome, we shouldn't be creating a new regexp for
        // every email. But, with RegExpBuilderPHP we should be able to build
        // all RegExps prior to processing each email and combine them with
        // `eitherFind` and `orFind`
        switch ( $filter['comparator'] ) {
            case 'equals':
                $regexp = $builder
                    ->getNew()
                    ->startOfInput()
                    ->find($filter['value'])
                    ->endOfInput()
                    ->getRegExp();

                break;
            case 'contains':
                $regexp = $builder
                    ->getNew()
                    ->find($filter['value'])
                    ->getRegExp();
                break;
            case 'ends-with':
                $regexp = $builder
                    ->getNew()
                    ->find($filter['value'])
                    ->endOfInput()
                    ->getRegExp();

                break;
            case 'starts-with':
                $regexp = $builder
                    ->getNew()
                    ->startOfInput()
                    ->find($filter['value'])
                    ->getRegExp();

                break;
            default:
                print( 'invalid regexp comparator ' . $filter['comparator'] . "\n" );
                continue;
        }

        $properties = explode('.', $filter['field']);
        $property = array_shift($properties);

        try {
            $value = $msg->{'get' . ucfirst($property)}();
        } catch ( \Exception $e ) {
            print('invalid field property ' . $property . "\n");

            continue;
        }

        // If the data we have retrieved is an object, we will have to recurse
        if ( is_object($value) ) {
            // If there are more properties to get i.e., 'from.*' or 'to.*' or
            // 'cc.*', we need to extract that
            if ( count($properties) ) {
                $property = array_shift($properties);

                try {
                    $value = $value->{'get' . ucfirst($property)}();
                } catch ( \Exception $e ) {
                    print('invalid field property ' . $property . "\n");

                    continue;
                }
            } else {
                $value = (string) $value;
            }
        // Just convert the value to a string, so we'll always be comparing
        // strings with strings
        } else {
            $value = (string) $value;
        }

        // If the mail matches some pattern
        if ( $regexp->matches($value) ) {
            // Make a file-system compatible directory name from the email's
            // subject
            // @TODO: this should, in a proper release, only be the case if an
            // option like "sort by subject" or "sort by thread" is enabled
            $replace = array(
                '/\s/' => '_',
                '/[^0-9a-zа-яіїє_\.]/iu' => '',
                '/_+/' => '_',
                '/(^_)|(_$)/' => '',
            );
            $dirSysName = preg_replace('~[\\\\/]~', '', preg_replace(array_keys($replace), $replace, $msg->getSubject()));

            // Create the base directory
            $base = __DIR__ . DS . 'files' . DS . trim($filter['target'], DIRECTORY_SEPARATOR) . DS . $dirSysName;

            // Make sure the path exists
            if ( ! is_dir($base) ) {
                mkdir($base, 0755, TRUE);
            }

            // Loop over each attachment and store it in the desired path
            foreach ( $msg->keepUnseen(true)->getAttachments() as $attachment ) {
                /** @var \Ddeboer\Imap\Message\Attachment $attachment */

                // Make a file-system compatible file name from the attachment
                // name
                $replace = array(
                    '/\s/' => '_',
                    '/[^0-9a-zа-яіїє_\.]/iu' => '',
                    '/_+/' => '_',
                    '/(^_)|(_$)/' => '',
                );
                $fileSysName = preg_replace('~[\\\\/]~', '', preg_replace(array_keys($replace), $replace, $attachment->getFilename()));

                // Download the attachment
                file_put_contents(
                    $base . DS . $fileSysName,
                    $attachment->getDecodedContent()
                );
            }
        }
    }

    // Don't crash our server
    sleep(0.5);
}

// Everything successful, end now
exit(0);
