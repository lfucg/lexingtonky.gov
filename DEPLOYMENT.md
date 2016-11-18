# Upgrading dependencies and deploying

## Uprading Drupal core

This project has run into issues using Pantheon's built-in core upgrading mechanism. To sync to Pantheon's
D8 version:

Create a branch

`git pull -Xtheirs git://github.com/pantheon-systems/drops-8.git master`

And commit the merge

## Recommended process for upgrading modules

* Read release notes for potential issues
* Check bug reports for problems with new version. If not a security update, often best to wait a few weeks to let bug reports filter in. Checking usage statistics is helpful to make sure the version is being used.
* `drush pm-update my-module`
* Scan code diffs for any red flags
* Create a branch from master, commit and push to GitHub

## Life cycle of upgrading, testing, and deploying

1. Local: make changes to branch
2. Local: push to GitHub
3. CircleCI: creates throwaway pantheon environment to run tests against
4. CircelCI: tests pass
5. Local: merge branch to master
6. Local: push master to GitHub
7. CircleCI: deploys master to Pantheon Dev environment
8. CircleCI: runs test against Pantheon Dev environment
9. CircleCI: Tests pass
10. Local: `./deploy-to-pantheon.sh test`
11. Pantheon: manual smoke testing on test-lexky-d8.pantheonsite.io
12. Pantheon: make sure that Pantheon systems are fully operational at status.pantheon.io
13. Local: deploy test->live `./deploy-to-pantheon.sh live`

Post Release

1. [Check status report](https://www.lexingtonky.gov/admin/reports/status)
2. [Check logs](https://www.lexingtonky.gov/admin/reports/dblog) for unusual messages

Troubleshooting

* Pantheon: Click `Live tab` > `Clear caches` before diving into any CSS, javascript, or other issues related to a release
* Pantheon: If there was a problem readying configuration during the release, clear caches and run `terminus drush "cim -y" --env=live` again

## Configure SMTP settings for live env

add to `sites/default/files/private/config.overrides.json`

```
{
  "smtp.settings": {
    "smtp_host": "the.host.name",
    "smtp_username": "the-username",
    "smtp_password": "the-password"
  }
}
```
## Status report on Pantheon

Drupal flags 2 false positives on the [status report](https://www.lexingtonky.gov/admin/reports/status) on Pantheon

* 'Configuration directory: sync	The directory sites/default/config is not writable.' Config can not be written in the live env by design
* `Public files directory	Not fully protected `. This is an Apache warning, where Pantheon uses nginx.
