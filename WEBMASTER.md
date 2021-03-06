# Instructions for webmaster/site-builders

## Adding Organization pages

For now, we hide the required field 'Organization taxonomy term' so that no one accidentally edits it. But to add a new page, it needs to be visible on the content form.

### Make 'Organization taxonomy term' visible

* Edit the [form display](/admin/structure/types/manage/organization_page/form-display) for Organization pages to show the 'Organization taxonomy term' field.
* [Add org page](/node/add/organization_page), selecting the 'Organization taxonomy term' as you do.
* Return to the form display and hide the 'Organization taxonomy term' field again.

## Embedding forms

We use [formstack](https://formstack.com) javascript embeds. The administrator account can use the 'Full HTML' editing mode to change the embed snippet.

To add/edit fields on the form itself, login to formstack and make changes as needed. The changes will appear on our site.
