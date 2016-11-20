# PHP Mail Attachment Downloader and Sorted

Current status: test development

## How to test

Simply clone the repository and run `composer update --install` afterwards. Then, copy the file `env.default` to `.env` and adjust the entries inside.
Finally, create a file called `filters.yaml` which is a YAML-formatted array of your filters for the attachments. It must have the following layout for each filter that should be used

~~~
-
    field: <>
    value: <>
    comparator: <>
    target: <>
~~~

The configuration options are


### field

Field to match againsts. Possbile values are

* `cc.address` matches the email address of any CC recipient i.e., `user@example.com` (currently not supported)
* `cc.fullAddress` mataches the full address of any CC recipient i.e., `John Doe <user@example.com>` (currently not supported)
* `cc.hostname` matches the hostname of any CC recipient i.e., `example.com` (currently not supported)
* `cc.mailbox` matches the mailbox of any CC recipient i.e., `user` (currently not supported)
* `cc.name` matches the name of any CC recipient i.e., `John Doe` (currently not supported)
* `charset` matches the email's character encoding set (currently not supported)
* `date` match the date of the email (currently not supported)
* `from.address` matches the email address of any CC recipient i.e., `user@example.com` (currently not supported)
* `from.fullAddress` mataches the full address of any CC recipient i.e., `John Doe <user@example.com>` (currently not supported)
* `from.hostname` matches the hostname of any CC recipient i.e., `example.com` (currently not supported)
* `from.mailbox` matches the mailbox of any CC recipient i.e., `user` (currently not supported)
* `from.name` matches the name of any CC recipient i.e., `John Doe` (currently not supported)
* `subject` matches the email's subject
* `to.address` matches the email address of any TO recipient i.e., `user@example.com` (currently not supported)
* `to.fullAddress` mataches the full address of any TO recipient i.e., `John Doe <user@example.com>` (currently not supported)
* `to.hostname` matches the hostname of any TO recipient i.e., `example.com` (currently not supported)
* `to.mailbox` matches the mailbox of any TO recipient i.e., `user` (currently not supported)
* `to.name` matches the name of any TO recipient i.e., `John Doe` (currently not supported)


### value

Which value to match against. Only makes sense to explain with the comparators


### comparator

Comparator to use in matching the value in the given field. Possible values are

* `equals` exact match
* `begins-with` matches the `value` against the beginning of value `field`
* `ends-with` matches the `value` against the end of value `field`
* `contains` matches if the `values` is contained in the value of `field`


### target

Specifies the path relative to folder `files` in the directory of `server.php` where the files should be saved to. Must not start or end with a slash (direcotry separator). Note that the directory must be writable by the user running PHP.
