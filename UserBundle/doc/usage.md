Using the User Bundle
=====================

Database Tables
---------------

This bundle uses a single database table: `nines_user`. Once the bundle is 
enabled, you can create the tables with a doctrine migration.

Migrations are not included in the bundles, you will need to generate them
yourself. For example,

```shell
$ ./bin/console doctrine:migrations:diff -n
```

Then carefully review the generated migration file.

Data Fixtures
-------------

For convenience, a few data fixtures are pre-configured and ready to be loaded.
There are three types of fixtures: `test` for testing, `dev` for development,
and `prod` with content suitable for production.

```shell
$ ./bin/console doctrine:fixtures:load --group=test
```

Entities
--------

There is a single entity defined in this bundle: user.

Users login by providing their email address and password. 

Commands
--------

Some commands are provided which can be run in a shell. The general form is

```shell
./bin/console nines:user:password <email> <new-password> 
```

The commands will prompt for any missing parameters. 

- `nines:user:create <email> <fullname> <affiliation>` \
Create a new user account that is not active, has no roles, and a random password 
- `nines:user:password <email> <new-password>` \
Change the password for a user
- `nines:user:activate <email>` \
Activate a user account. Inactive accounts cannot login.
- `nines:user:deactivate <email>` \
Dectivate a user account. The user automatically logged out.
- `nines:user:promote <email> <role>`\
Add a role to a user account. The user may need to logout and back in before 
the change takes affect.
- `nines:user:demote <email> <role>`\
Remove a role from a user account. The user automatically logged out.
- `nines:user:reset <email>`\
Send a password reset email to the user

Controllers
-----------

Users can edit their profile and change their name, email, affiliation, or 
password at `/user`. Regular users cannot delete or deactivate their account, 
create new accounts, or alter any other account. They cannot view the list of
accounts.

Anyone with `ROLE_USER_ADMIN` can change the account details and password for 
any user at `/admin/user`. They can also activate or deactivate user accounts 
and remove accounts. Admin users can also view the list of users.

Users can login at `/login`, and initiate a password reset at `/request` which
will send an email to their account and direct them to `/reset`.

Menus
-----

The bundle provides one menu in Menu/Builder.php. It will link to user profiles
and (if the user has the ROLE_ADMIN permission) to the user admin interface.

```twig
{# templates/base.html.twig #}

    {{ knp_menu_render('nines_user') }}
```

Templates
---------

Templates are provided in `templates/` and can be 
[easily overridden][override].

[override]: https://symfony.com/doc/current/bundles/override.html#templates
