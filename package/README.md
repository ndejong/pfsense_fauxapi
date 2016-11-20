# FauxAPI - binary package

Until the FauxAPI is added to the pfSense FreeBSD-ports tree a binary release is
made herewith allowing you to get started right away

You'll need to download the pfSense-pkg-FauxAPI-1.txz package file directly onto
your pfSense system and perform a manual pkg install as shown below.

```
[2.3.2-RELEASE][root@pfsensedev]/root: 
[2.3.2-RELEASE][root@pfsensedev]/root: pkg install pfSense-pkg-FauxAPI-1.txz
Updating pfSense-core repository catalogue...
pfSense-core repository is up-to-date.
Updating pfSense repository catalogue...
pfSense repository is up-to-date.
All repositories are up-to-date.
Checking integrity... done (0 conflicting)
The following 1 package(s) will be affected (of 0 checked):

New packages to be INSTALLED:
	pfSense-pkg-FauxAPI: 1 [unknown-repository]

Number of packages to be installed: 1

Proceed with this action? [y/N]: y
[1/1] Installing pfSense-pkg-FauxAPI-1...
[1/1] Extracting pfSense-pkg-FauxAPI-1: 100%
Saving updated package information...
done.
Loading package configuration... done.
Configuring package components...
Custom commands...
Menu items... done.
Writing configuration... done.
[2.3.2-RELEASE][root@pfsensedev]/root: 
```
