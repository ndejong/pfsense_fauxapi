
# Build Notes

## Create a fresh FreeBSD VirtualBox host
 * Obtain a FreeBSD `VM-IMAGE` based release https://download.freebsd.org/ftp/releases/VM-IMAGES/
 * Recommended image type is VMDK which seems to work well with VirtualBox
 * Decompress the VMDK `xz`, create a new VirtualBox using this HDD image and power-on
 * Login to the instance at the console (root, no password)
 * Perform a package update `pkg update`
 * Enable sshd at reboot by adding `sshd_enable="YES"` to the file `/etc/rc.conf`
 * Enable root login via ssh (if this approach suits you)
 * Consider adding your ssh key(s) to login user (again, if this approach suits you)
 * Powerdown instance and move VirtualBox (plus `vmdk`) file to a location that suits

## Create a VirtualBox clone to work with
 * Using the `instances-reclone.sh` script, edit the paths in this script (in the upper section)
 * Consider changing the MAC addresses in the (in the lower section) of the `instances-reclone.sh` script
 * Run the `instances-reclone.sh` to get a "nice" fresh clone - this tool will **CLOBBER** the target image if target is in powered-off state!

## Create a fresh pfSense builder host
 * Install git and pull down the latest pfSense FreeBSD-ports fork
```bash
pkg install git
cd /usr/
git clone https://github.com/pfsense/FreeBSD-ports ports
```

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

 * At end of the build process you should have a `.txz` package file ready for installation on a pfSense host

## Checking for errors
 * Before submitting a package you should check with `portlint`
```bash
pkg install portlint
echo DEVELOPER=yes >> /etc/make.conf
cd /path/to/package
portlint -CN
```

## Various pointers and other notes
 * https://forum.pfsense.org/index.php?topic=112807.0
 * https://gist.github.com/jdillard/3f44d06ba616fec60890488abfd7e5f5
