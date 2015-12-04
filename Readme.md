# Deployment tasks for Magallanes

Magallanes is a simple deployment tool, based on PHP. See http://magephp.com/ for details

## Tasks

### Typo3Artifact

This task collects all data which needs to be deployed and creates a shell script for finalizing the deployment
 
*heavily under development*

### Typo3Release

*heavily under development*

This task calls the shell script previously created.

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