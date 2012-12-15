Pre - install
SOAP must be enabled Core side
SOAP must be enabled within the PHP environment.

Install
1. Copy the 'syncApp' directory to *IP.board*/admin/applications_addon/other/

2. Login in to the forums (ACP) admin control panel and navigate to

"System > Applications & Modules > Manage Applications & Modules."
Over on the right you'll see box with a title "Applications not installed" Hit the button 'Install'

3. After install complete navigate to System > System Settings > SyncApp > General.

Complete the form and be sure to enable SOAP.

4. Usernames must be limited to alphanumeric go to: System > System Settings > Members > Username Restrictions

5. Time to define the groups.. Navigate to Members > Member Groups > Manage Member Groups > *MEMBER GROUP* > syncApp

go through each group and select the proper permissions..
Admin = GM level: 3, Mod = GM level: 2, Member = Member, Banned = locked.


also check doc/Doc.pdf