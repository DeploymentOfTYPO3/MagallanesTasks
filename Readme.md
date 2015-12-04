# Deployment tasks for Magallanes

Magallanes is a simple deployment tool, based on PHP. See http://magephp.com/ for details.

Take a look at https://github.com/DeploymentOfTYPO3/Magallanes-Example-Rsync for a full setup.

## Tasks

### Typo3Artifact

This task collects all data which needs to be deployed. Use the property `excludes` to define the files and folders which should not be deployed later on.
 
```
  tasks:
    - typo3-artifact:
        excludes:
          - /deployment
          - /fileadmin
          - /typo3
```

### Typo3Console

This task is a wrapper for the TYPO3 CMS extension "typo3_console" which can be found at https://github.com/helhum/typo3_console.

A typical call cna look like that: 

```
tasks:
  ...
  post-release:
    - typo3-release
    - typo3-console: {command: "database:updateschema '*.add,*.change,*.clear'"}
    - typo3-console: {command: cache:flush}
  ...
```

This will call the commands `database:updateschema` and `cache:flush`.
 
By setting the additional property `copyEntryPoint` to 1, the file `typo3cms` will be created automatically.
 
```
    - typo3-console: {copyEntryPoint:1, command: "database:updateschema '*.add,*.change,*.clear'"}
```

### ClearOpCache

If an OPcache is used, it needs to be cleared via http and not via SSH. Therefore you need to define the URL of your project

```
  post-release:
    - clear-op-cache: {frontend-url: 'http://www.example.org'}
```


## Full example

```
#dev
deployment:
  strategy: rsync
  user: root
  from: ./.mage/artifact/
  to: /var/www/deploy
  document-root: /var/www/html
releases:
  enabled: true
  max: 5
  symlink: current
  directory: releases
hosts:
  - "your-website.at"
tasks:
  pre-deploy:
    - typo3-artifact:
        excludes:
          - /deployment
          - /fileadmin
          - /typo3
          - /typo3temp
          - /uploads
          - composer.lock
          - /composer.json
          - /index.php
          - typo3conf/LocalConfiguration.php
          - atlassian-ide-plugin.xml
          - .editorconfig
          - .git*
          - .idea
          - .mage
          - .vagrant
          - bower_components
          - node_modules
          - Vagrantfile
  on-deploy:
  post-release:
    - typo3-release:
    - clear-op-cache: {frontend-url: 'https://your-website.at'}
  post-deploy:
```