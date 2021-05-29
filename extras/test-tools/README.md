# pfSense FauxAPI - test tools

Configuration files for `pyvboxmanage` to re-create pfSense images from ISO sources that 
can then be used testing pfSense-FauxAPI against various pfSense versions.

## Example
NB: this will **CLOBBER** any existing VM as configured in the .yml 
```shell
user@computer:~$ pyvboxmanage pyvboxmanage-pfsense-2.3.2.yml
```

## PyVBoxManage
- https://pyvboxmanage.readthedocs.io/en/latest/docs/configuration-file/

### Install PyVBoxManage
```shell
user@computer:~$ pip install pyvboxmanage
```

## ISO image SHA1
```text
009abcebc2fee1a57a61631b9cd3d9fe5ac33f37  pfSense-CE-2.3.2-RELEASE-amd64.iso
d634876139113eae1211805bb296b6668a6a1b0a  pfSense-CE-2.3.3-RELEASE-amd64.iso
eca64393f8da74e51028557c6470f0b1ae2994f4  pfSense-CE-2.3.4-RELEASE-amd64.iso
582160dc3e4b3b7b8de90fd1c7cb1247219505ae  pfSense-CE-2.4.3-RELEASE-amd64.iso
d07de5bde954876bab1a6391913cf3de37c83fd7  pfSense-CE-2.4.4-RELEASE-amd64.iso
c1d9a4e36deecdbd280f75881e52d90954435996  pfSense-CE-2.4.5-RELEASE-amd64.iso
98387ad66ed4800f864cded655439f4bd351c9b5  pfSense-CE-2.5.0-RELEASE-amd64.iso
fc61a85f13e6390dd2c18eb9049df366b701b814  pfSense-CE-2.5.1-RELEASE-amd64.iso
```
