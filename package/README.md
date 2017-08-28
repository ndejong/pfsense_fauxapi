# FauxAPI - release packages

Until the FauxAPI is added to the pfSense FreeBSD-ports tree you will need to 
install manually as shown

Simply download the latest `pfSense-pkg-FauxAPI` package file directly onto 
your pfSense system and perform a manual pkg install as shown in the
installation example below.  It take just 2x commands to install and just 1x
command to de-install if you need to.

## Current Version
 - pfSense-pkg-FauxAPI-1.2_1.txz
 - SHA256: 4e93e8ba4266b6524d6347bb15fd7214a8c64a2061517706939f5dc7261d7408

## Installation Example
```
2.3.4-RELEASE][root@pfsense]/root: 
[2.3.4-RELEASE][root@pfsense]/root: curl -s -O https://raw.githubusercontent.com/ndejong/pfsense_fauxapi/master/package/pfSense-pkg-FauxAPI-1.1_2.txz
[2.3.4-RELEASE][root@pfsense]/root: pkg install pfSense-pkg-FauxAPI-1.1_2.txz
Updating pfSense-core repository catalogue...
pfSense-core repository is up to date.
Updating pfSense repository catalogue...
pfSense repository is up to date.
All repositories are up to date.
Checking integrity... done (0 conflicting)
The following 1 package(s) will be affected (of 0 checked):

New packages to be INSTALLED:
	pfSense-pkg-FauxAPI: 1.1_2 [unknown-repository]

Number of packages to be installed: 1

Proceed with this action? [y/N]: y
[1/1] Installing pfSense-pkg-FauxAPI-1.1_2...
Extracting pfSense-pkg-FauxAPI-1.1_2: 100%
Saving updated package information...
overwrite!
Loading package configuration... done.
Configuring package components...
Custom commands...
Menu items... done.
Writing configuration... done.
[2.3.4-RELEASE][root@pfsense]/root: 
```

## Uninstallation Example
```
[2.3.4-RELEASE][root@pfsense]/root: 
[2.3.4-RELEASE][root@pfsense]/root: pkg delete pfSense-pkg-FauxAPI
Checking integrity... done (0 conflicting)
Deinstallation has been requested for the following 1 packages (of 0 packages in the universe):

Installed packages to be REMOVED:
	pfSense-pkg-FauxAPI-1.1

Number of packages to be removed: 1

Proceed with deinstalling packages? [y/N]: y
[1/1] Deinstalling pfSense-pkg-FauxAPI-1.1...
Removing FauxAPI components...
Menu items... done.
[1/1] Deleting files for pfSense-pkg-FauxAPI-1.1: 100%
Removing FauxAPI components...
Configuration... done.
[2.3.4-RELEASE][root@pfsense]/root: 
```

## FauxAPI - Menu
![alt text](README-menu-screenshot-01.png "menu-screenshot")
