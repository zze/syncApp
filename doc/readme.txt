Pre - install
SOAP must be enabled Core side
SOAP must be enabled within the PHP environment.

Install
1) Move the "syncApp/admin/applications_addon/other/*syncApp*" directory to your webisite *IP.board*/admin/applications_addon/other/*HERE*
1.5) Move 'conf_multiRealm.php' to *IP.board*/HERE*
(You also have to must define the realm ID and character database name in this file

2) Login in to the forums (ACP) admin control panel and navigate to "System > Applications & Modules > Manage Applications & Modules."
Find the box with the title "Applications not installed" and hit the button 'Install' for SyncApp.

3) After install is complete navigate to System > System Settings > SyncApp > General.
3.5) Go through the entire form and be sure to enable SOAP.
[Most features depend on SOAP being enabled with the ability to communicate]

4) Usernames must be limited to alphanumeric go to: System > System Settings > Members > Username Restrictions

5) Define groups.. Navigate to Members > Member Groups > Manage Member Groups > *MEMBER GROUP* > syncApp
Go through each group and select Permissions. Admin = GM level: 3, Mod = GM level: 2, Member = Member, Banned = locked.

also check doc/Doc.pdf
_______________________________

To enable multi-realm edit *IP.board*/conf_multiRealm.php
the ['<ID>'] must match the realm_ID for each specific realm (to ->) character_database

*Unique DB login details TODO