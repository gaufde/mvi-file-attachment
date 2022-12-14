# mvi-file-attachment
A gated content plugin to handle adding files to Wordpress posts, capturing user information, and emailing links to users automatically. Supports Mailchimp. Built using MetaBox.

## Requirements
- PHP 7.4.30+
- MetaBox 5.6.7+
  - MB Admin Columns 1.6.2+
  - MB Custom Table 2.1.3+
  - MB Frontend Submission 4.1.1+
  - MB Settings Page 2.1.7+
  - Meta Box Columns 1.2.15+
  - Meta Box Tooltip 1.1.6+


## Set up
Download the zip and then upload it to WP. Install and activate the plugin.

Navigate to File Downloads -> Settings to configure the plugin. Choose which post types you would like to offer downloads on. Configure the 'From Address', 'From Name', and 'Email CSV exports' fields. The rest are optional.

## Use
On post types configured to accept downloads, there will be a field at the bottom of the page to upload a file. This is the file that will be served as a download. The other two optional fields allow you to configure the title of the form and a shortened title of the form. These titles will be used on the frontend of that post, unless the shortcode overrides them.

On the frontend, simply use the shortcode `[mvi_fa_frontend_form]` to display the form on the frontend. The form will only render if the current post has a download file attached.

The shortcode can be used to display the form title only: `[mvi_fa_frontend_form form="false"]`.
If you would like to output a custom title for one instance, you can override the default form title in the shortcode: `[mvi_fa_frontend_form title="My custom title"]`.
If you want to display the short title: `[mvi_fa_frontend_form form="false" use_short_title="true"]`.

I suggest putting the shortcode `[mvi_fa_frontend_form]` in a template or theme hook so that it renders of every single page. Then, if you would like to make buttons that link to the form (or trigger a modal if you put the form in a pop-up), then use `[mvi_fa_frontend_form form="false" use_short_title="true"]` as the button text.

## Features
- Submissions are stored in a custom table.
- Download file paths can be updated without breaking download links that were already generated.
- If a post has a file attached, then the term `has_file` will automatically be applied in the taxonomy `mvi_fa_file-status`. This lets you do conditional filtering on the frontend with many themes.
- Download files are stored in /{root}/file_downloads/secure/ so that you can set custom folder permissions and prevent direct access.
- Download files are not added to Wordpress media. That way they can't be accidentally deleted
- Once a week, the plugin will export a CSV will all submissions. CSV files are generated above the root in /csvoutput/.
- If the recaptcha key and secret are configured, then the download form will use them.
- If Mailchimp API settings are configured, then a subscribe option will be added to the form.
  - When users subscribe, they are automatically tagged with 'Downloaded PDF,' 'the_title_of_the_post_they_downloaded_from,' and 'their_professional_role.'
  - If a user is already subscribed to the Mailchimp list, then their tags will be updated every time they submit the form.
  - A user can only have one professional role tag in Mailchimp at a time.
