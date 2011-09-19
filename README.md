ILIAS ParentPathCheck Repository Object Plugin
==============================================

Checks if course members can access all objects in the path leading to the course (the "No access to a superordinated object!" - error) 

This is a plugin which is more intended for ILIAS administrators than normal users. The plugin should be added to some category where only administrators have access.

Installation
------------

Place the files of this plugin in:

/Customizing/global/plugins/Services/Repository/RepositoryObject/ParentPathCheck

Then enable the the Plugin in the ILIAS administration: Modules, Services, Plugins -> RepositoryObject Administration -> Refresh -> Activate

Please make shure that only ILIAS administrators and not regular users can create objects of this type.

Usage
-------

You place this object somewhere in your repository. It then lists all courses (in a paged manner) and if there are users found who don't have access to the parent object of the course it will list them. It also provides a direct link to the member management of each course.

### Users / Administrators

This plugin is mostly intended for administrators and it will also only list all courses if the person looking at is has an ILIAS administrator role. If a person / normal user (like a course administrator) also has access to this plugin it will only list the courses to that user where he is the owner of the course.

Contact
-------

Send bug reports and suggestions to: https://github.com/jrocho/ILIAS-ParentPathCheck-Repository-Plugin/issues

License
-------

This software is licensed via the GPL v3, included in the LICENSE file.