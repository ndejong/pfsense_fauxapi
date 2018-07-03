
Build Notes
===

#### Based on these links
 * https://forum.pfsense.org/index.php?topic=112807.0
 * https://gist.github.com/jdillard/3f44d06ba616fec60890488abfd7e5f5

#### FreeBSD (build) server
 * Download and Install FreeBSD - https://www.freebsd.org/where.html
 * During installation, you can unselect the option to install the ports tree since it will be cloned from the pfSense repo later on.
 * Allow root login via ssh in `/etc/ssh/sshd_config` and remember to reload the new sshd config with `service sshd reload`
 ```
PermitRootLogin yes
```
 * Clone the pfSense ports repo (as root)
```bash
pkg install git
cd /usr/
git clone https://github.com/pfsense/FreeBSD-ports.git
mv FreeBSD-ports ports
```

 * Build your package
 ```bash
cd /path/to/package
make package
```
 * Clean the build path if needed
 ```bash
cd /path/to/package
make clean
```
 * Should have a .txz package file to use for installation 

#### Checking for errors
 * Before submitting your package you need to intall run portlint on your build server.
```bash
pkg install portlint
echo DEVELOPER=yes >> /etc/make.conf
cd /path/to/package
portlint -CN
```
