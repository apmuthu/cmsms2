# cmsms2
* CMS Made Simple v2.x
* This is the Installed version
* PHP backward compatibility fixes incorporated
* Has Form Builder and other extensions installed
* No installation Wizard needed

# Source for this fork
* 2020-11-27: [cmsms-2.2.15-install.expanded.zip](http://s3.amazonaws.com/cmsms/downloads/14831/cmsms-2.2.15-install.expanded.zip)

## Vulnerability and Fix
* 2020-12-25: [Authenticated Remote Control Execution](https://infosecresearchlab.blogspot.com/2020/12/cms-made-simple-2215-authenticated-rce.html)
* Do not disable functions in `php.ini` as CMS Made Simple used PHP functions like eval
* This vulnerability needs Administrator Access
* When **Add `custom_code`** feature is not in use, move the `admin/editusertag.php` file beyond webroot

# Installation Procedure
* Create your Database with user and non blank password
* Execute the `cmsms_2215.sql` into the Database created above
* Upload the core files to your application or web root
* Enter the Database credentials into the config.php file (make it readonly after such changes)

## Default Access credentials
* Default Admin login **admin**
* Initial Login Password **pwd123**
* Change credentials on first login!!!
* Admin login URL is with `<installed site URL>/admin`

# Project Links
* [Home](http://www.cmsmadesimple.org)
* [Dev Site](http://dev.cmsmadesimple.org/)
* [Forum](https://forum.cmsmadesimple.org/)
* [Documentation](https://docs.cmsmadesimple.org/)
