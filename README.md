# mvi-file-attachment
A gated content plugin to handle adding files to wordpress posts, capturing user information, and emailing links to users automatically. Supports Mailchimp. Built using MetaBox.

##Requirements
This plugin requires the following to be installed: MetaBox, and MetaBox AIO. Currently, neither are included automatically. PHP 7.4.30+

##Set up
Download the zip and then upload it to WP. Install and activate the plugin.

Navigate to File Downloads -> Settings to configure the plugin. Choose which post types you would like to offer downloads on. Configure the 'From Address', 'From Name', and 'Email CSV exports' fields. The rest are optional.

##Use
On post types configured to accept downloads, there will be a field at the bottom of the page to upload a file. This is the file that will be served as a download. The other two fields allow you to configure the title of the form and a shortened title of the form.

On the frontend, simply use the shortcode [post_file_download_form] to display the form on the frontend. The form will only render if the current post has a download file attatched.
