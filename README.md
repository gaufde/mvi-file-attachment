# mvi-file-attachment
A gated content plugin to handle adding files to Wordpress posts, capturing user information, and emailing links to users automatically. Supports Mailchimp. Built using MetaBox.

## Requirements
- PHP 7.4.30+
- MetaBox 5.6.7+
-- MB Admin Columns 1.6.2+
-- MB Custom Table 2.1.3+
-- MB Frontend Submission 4.1.1+
-- MB Settings Page 2.1.7+
-- Meta Box Columns 1.2.15+
-- Meta Box Tooltip 1.1.6+


## Set up
Download the zip and then upload it to WP. Install and activate the plugin.

Navigate to File Downloads -> Settings to configure the plugin. Choose which post types you would like to offer downloads on. Configure the 'From Address', 'From Name', and 'Email CSV exports' fields. The rest are optional.

## Use
On post types configured to accept downloads, there will be a field at the bottom of the page to upload a file. This is the file that will be served as a download. The other two optional fields allow you to configure the title of the form and a shortened title of the form. These titles will be used on the frontend of that post, unless the shortcode overrides them.

On the frontend, simply use the shortcode [mvi_fa_frontend_form] to display the form on the frontend. The form will only render if the current post has a download file attached.

The shortcode can be used to display the form title only: [mvi_fa_frontend_form form="false"].
If you would like to output a custom title for one instance, you can override the default form title in the shortcode: [mvi_fa_frontend_form title="My custom title"].
If you want to display the short title: [mvi_fa_frontend_form form="false" use_short_title="true"].

I suggest putting the shortcode [mvi_fa_frontend_form] in a template or theme hook so that it renders of every single page. Then, if you would like to make buttons that link to the form (or trigger a modal if you put the form in a pop-up), then use [mvi_fa_frontend_form form="false" use_short_title="true"] as the button text.

## Features

Submissions are stored in a custom table.

When a download file on a post is updated, all existing user download links will still find and deliver the new file, even if the file path changed.

Download files are stored in a folder in the website root. That way, with the proper folder permissions, it is impossible for anyone to directly access a download file.

Download files are not shown in Wordpress media. That way, an admin can not accidentally delete a download file.

Once a week, the site admin is emailed a CSV export of all submissions. Additionally, the CSV file is saved in a folder outside of the public root. This should keep user data reasonably safe.

If Mailchimp API details are saved in settings, then the plugin will handle subscribing users if the choose to. The plugin will tag the user with three tags: 'Downloaded PDF,' 'the_title_of_the_post_they_downloaded_from,' and 'their_professional_role.' Those last two tags are dynamic and so the values will depend on each submission.

If the user is already in the Mailchimp list, then each time they submit a download request, the tags will be updated appropriately. Each user is only allowed to have one professional role at a time.
