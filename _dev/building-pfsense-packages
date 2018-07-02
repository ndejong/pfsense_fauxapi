
https://forum.pfsense.org/index.php?topic=112807.0

https://gist.github.com/jdillard/3f44d06ba616fec60890488abfd7e5f5


# Making a package for pfSense 2.3

This a short set of notes from my experience making my first pfSense package. 

> This sort of thing is not my forte so there might be a better way to do certain parts and there certainly many different ways.

## Setting up a FreeBSD (build) server

### Download and Install FreeBSD

https://www.freebsd.org/where.html

I used the version of FreeBSD that matched the base version that I was developing for, as well as the architecture, and used the disc option. I'm sure you have leeway here.

The name of the image name I used: `FreeBSD-10.3-RELEASE-amd64-disc1.iso`

During installation, you can unselect the option to install the ports tree since it will be cloned from the pfSense repo later on.

### Allow root login over SSH

At the end of the install process choose the option to enter into shell and enable root access over ssh:

`vi /etc/ssh/sshd_config`

find `#PermitRootLogin no`

and change to: `PermitRootLogin yes`

### Clone the pfSense ports repo

reboot, ssh in, and choose Option 8 to enter the shell.

then `pkg install git` to install git

then `cd /usr/`

then `git clone https://github.com/pfsense/FreeBSD-ports.git` to clone the pfSense ports repo

then `mv FreeBSD-ports ports`

I just like to treat this as a build server and not commit to git directly from it.

## Making your package

For my use case I copied a previous package I had helped work on as it was similar to my new one.

## Building your package

run `make package` from inside the directory of the package you are making.

If you need to clean things up before running it again for whatever reason, run `make clean`.

Once that has completed successfully, there should be a .txz file in that directory that you can scp to the home directory of your pfSense instance.

## Installing your package in pfSense

ssh into our pfSense box and run `pkg install <the_name_of_your_built_package.txz>`

## Checking for errors

Before submitting your package you need to intall run portlint on your build server.

run `pkg install portlint`.

run `echo DEVELOPER=yes >> /etc/make.conf`.

cd into your package directory.

run `portlint -CN` and fix any errors.

congrats!
