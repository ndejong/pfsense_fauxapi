
# Build Notes

## Create a fresh FreeBSD VirtualBox host
 * Obtain a FreeBSD `VM-IMAGE` based release https://download.freebsd.org/ftp/releases/VM-IMAGES/
 * Obtain a VMDK based image that VirtualBox supports
 * Unpack the VMDK
 * Create a new VirtualBox using the unpacked VMDK, power on
 * Login to the instance at the console (root, no password)
 * Perform a package update - `pkg update`
 * Enable sshd at reboot by adding `sshd_enable="YES"` to the file `/etc/rc.conf`
 * Enable root login via ssh (if this approach suits you)
 * Add ssh key(s) to login user you plan to use as the VirtualBox reclone golden master
 * Powerdown instance and move VirtualBox (plus `vmdk`) file to a location that suits

## Create a fresh pfSense builder host
 * Install git and pull down the latest pfSense FreeBSD-ports fork
```bash
pkg install git
cd /usr/
git clone https://github.com/pfsense/FreeBSD-ports ports
```

## Create a quick VirtualBox clone
 * Using the `instances-reclone.sh` script, edit the paths (in the upper section)
 * Consider changing the MAC addresses in the (in the lower section)
 * Run the `instances-reclone.sh` and you'll get a nice fresh clone
 * NB: this tool will **CLOBBER** the target if the target is powered-off

## Build a package
 * Clean the build path if required before a build
```bash
cd /path/to/package
make clean
```

 * Change to the package directory and make
```bash
cd /path/to/package
make package
```

 * At end of the build process you should have a .txz package file ready for installation on a pfSense host

## Checking for errors
 * Before submitting a package you should check with `portlint`
 * Be sure that `portlint` in installed first `pkg install portlint`
```bash
echo DEVELOPER=yes >> /etc/make.conf
cd /path/to/package
portlint -CN
```

## Various pointers and other notes
 * https://forum.pfsense.org/index.php?topic=112807.0
 * https://gist.github.com/jdillard/3f44d06ba616fec60890488abfd7e5f5
